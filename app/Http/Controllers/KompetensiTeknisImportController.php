<?php

namespace App\Http\Controllers;

use App\Models\JobFamily;
use App\Models\StrukturOrganisasiVersi;
use App\Models\UnitOrganisasiSnapshot;
use App\Services\KompetensiTeknisImporter;
use App\Services\KompetensiTeknisParser;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Alur import self-service Kompetensi Teknis, bertahap:
 *   Step 1 (create/store/preview): upload Excel -> parse (App\Services\KompetensiTeknisParser,
 *   SAMA PERSIS logic dgn command CLI komtek:parse-preview, TIDAK diduplikasi) -> preview mentah.
 *   Step 2 (mapping/mappingStore): mapping kandidat_nama_unit -> unit_organisasi_id.
 *   Step 3 (primary/primaryStore): pilih manual baris mana yg prioritas='primary' (asal
 *   native MAUPUN generic bisa dipilih) — GANTI TOTAL dari deteksi border cell yg terbukti
 *   tidak reliable. Default semua 'secondary' kalau tidak dicentang.
 *   Step 4 (review/reviewCommit): dry-run laporan (App\Services\KompetensiTeknisImporter,
 *   SAMA PERSIS logic dgn command CLI komtek:import, TIDAK diduplikasi) lalu commit sungguhan.
 *
 * Semua state antar-step disimpan di 1 file temp JSON (storage/app/temp/kompetensi-teknis/
 * {token}.json, BUKAN ke database/session) — tiap step MENAMBAH key baru ke payload yg
 * sama (mis. Step 2 menambah key "unit_mapping", Step 3 menambah "primary_row_ids"), bukan
 * bikin file terpisah, supaya token di URL tetap 1 sepanjang alur — file ini BARU dihapus
 * setelah commit Step 4 SUKSES.
 *
 * LogsActivity dipasang DI SINI (bukan di Step 1/2/3) krn baru di reviewCommit() ada aksi
 * tulis data permanen dari UI — ikuti pola JobProfileController (trait dipakai di
 * Controller, bukan di Model).
 */
class KompetensiTeknisImportController extends Controller
{
    use LogsActivity;

    // storage_path() dipakai langsung (bukan Storage::disk('local')) krn disk 'local'
    // default Laravel 12 root-nya storage/app/private (lihat config/filesystems.php) —
    // supaya path fisiknya PERSIS storage/app/temp/kompetensi-teknis/ sesuai yg diminta,
    // bukan storage/app/private/temp/... Pola native mkdir()/file_put_contents() ini
    // SAMA dgn yg sudah dipakai ParseKompetensiTeknisPreview::defaultOutputPath().
    private static function tempDir(): string
    {
        $dir = storage_path('app/temp/kompetensi-teknis');

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    public function create()
    {
        $versiList = StrukturOrganisasiVersi::orderByDesc('tanggal_mulai_berlaku')->get();

        // WAJIB pilih dari master job_family (bukan text bebas lagi) — urut alfabetis
        // biar gampang di-scan di dropdown 15 opsi.
        $jobFamilyOptions = JobFamily::orderBy('nama')->get();

        return view('kompetensi_teknis.import.create', compact('versiList', 'jobFamilyOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file'                         => 'required|file|mimes:xlsx,xls|max:10240',
            'job_family_id'                => 'required|integer|exists:job_family,id',
            'struktur_organisasi_versi_id' => 'required|integer|exists:struktur_organisasi_versi,id',
        ], [
            'file.required'                         => 'File Excel wajib dipilih.',
            'file.mimes'                             => 'File harus berformat Excel (.xlsx atau .xls) — CSV tidak bisa dipakai krn parsing tipe primary/secondary butuh baca border cell asli.',
            'file.max'                               => 'Ukuran file maksimal 10MB.',
            'job_family_id.required'                 => 'Rumpun jabatan wajib dipilih.',
            'job_family_id.exists'                    => 'Rumpun jabatan tidak ditemukan di master Job Family.',
            'struktur_organisasi_versi_id.required'  => 'Versi struktur organisasi wajib dipilih.',
            'struktur_organisasi_versi_id.exists'    => 'Versi struktur organisasi tidak ditemukan.',
        ]);

        $jobFamily = JobFamily::find($data['job_family_id']);

        try {
            // getRealPath() aman dipakai di sini (bukan disimpan permanen) krn Parser
            // langsung baca & selesai dalam siklus request yg sama — file upload asli
            // TIDAK perlu ikut disimpan ke temp, cuma HASIL tidy-nya yg persist.
            $result = KompetensiTeknisParser::parse(
                $request->file('file')->getRealPath(),
                $jobFamily->id
            );
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $token   = (string) Str::uuid();
        $payload = [
            'token'                        => $token,
            'uploaded_at'                  => now()->toDateTimeString(),
            'original_filename'            => $request->file('file')->getClientOriginalName(),
            'job_family_id'                => $jobFamily->id,
            'job_family_nama'              => $jobFamily->nama, // di-cache di payload biar view tidak perlu query ulang
            'struktur_organisasi_versi_id' => (int) $data['struktur_organisasi_versi_id'],
            'tidyRows'                     => $result['tidyRows'],
            'warnings'                     => $result['warnings'],
        ];

        file_put_contents(self::tempDir() . DIRECTORY_SEPARATOR . "{$token}.json", json_encode($payload));

        return redirect()->route('organisasi.kompetensi-teknis.import.preview', ['token' => $token]);
    }

    /**
     * Validasi format token (UUID KETAT — dipakai nyusun path file, jadi harus dicegah
     * disalahgunakan utk path traversal mis. "../../.env") lalu baca payload JSON-nya.
     * null kalau file belum/tidak ada lagi (expired) — caller yg putuskan redirect/pesannya.
     */
    private function loadPayload(string $token): ?array
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $token)) {
            abort(404);
        }

        if (!is_file($this->tempFilePath($token))) {
            return null;
        }

        return json_decode(file_get_contents($this->tempFilePath($token)), true);
    }

    private function tempFilePath(string $token): string
    {
        return self::tempDir() . DIRECTORY_SEPARATOR . "{$token}.json";
    }

    private function payloadNotFoundRedirect()
    {
        return redirect()->route('organisasi.kompetensi-teknis.import.create')
            ->with('error', 'Data preview tidak ditemukan atau sudah kedaluwarsa. Silakan upload ulang.');
    }

    public function preview(string $token)
    {
        $payload = $this->loadPayload($token);

        if ($payload === null) {
            return $this->payloadNotFoundRedirect();
        }

        $tidyRows = $payload['tidyRows'];
        $warnings = $payload['warnings'];
        $versi    = StrukturOrganisasiVersi::find($payload['struktur_organisasi_versi_id']);

        // Resolve job_family_id -> nama SEKALI (bukan per baris) — dipakai di tabel tidy
        // mentah & daftar generic. Baris dgn job_family_id null (rumpun tidak dikenali,
        // lihat KompetensiTeknisParser) ditampilkan literal "Rumpun tidak dikenali" di view.
        $jobFamilyNames = JobFamily::pluck('nama', 'id');

        $units = collect($tidyRows)->pluck('kandidat_nama_unit')->unique()->filter()->sort()->values();

        $generic = collect($tidyRows)
            ->where('asal', 'generic')
            ->map(fn ($r) => $r['nama_kompetensi'] . ' || ' . ($r['job_family_id'] !== null
                ? ($jobFamilyNames[$r['job_family_id']] ?? "id={$r['job_family_id']}")
                : 'Rumpun tidak dikenali — perlu review manual'))
            ->unique()
            ->sort()
            ->values();

        // Tally asal saja di Step 1 — prioritas BELUM relevan ditampilkan di sini krn semua
        // baris masih default 'secondary' (belum lewat step Pilih Primary).
        $tally = collect($tidyRows)->countBy('asal');

        // Dipakai view utk nentuin tombol "Lanjut ke ..." mana yg ditampilkan (Mapping ->
        // Pilih Primary -> Review, 3 kemungkinan tahap berikutnya tergantung progress).
        $unitMappingComplete = $this->resolveCompleteUnitMapping($payload) !== null;

        return view('kompetensi_teknis.import.preview', [
            'token'               => $token,
            'payload'             => $payload,
            'versi'               => $versi,
            'tidyRows'            => $tidyRows,
            'warnings'            => $warnings,
            'units'               => $units,
            'generic'             => $generic,
            'tally'               => $tally,
            'unitMappingComplete' => $unitMappingComplete,
            'jobFamilyNames' => $jobFamilyNames,
        ]);
    }

    // Urutan level dari puncak ke bawah — dipakai utk urutan optgroup <select> unit
    // (Departemen dulu, baru Fungsional, dst), SAMA PERSIS $levelOrder yg sudah dipakai
    // di org-tree-node.blade.php.
    private const LEVEL_ORDER = ['direktorat', 'kompartemen', 'departemen', 'bagian', 'seksi', 'foreman', 'fungsional'];
    private const LEVEL_LABEL = [
        'direktorat' => 'Direktorat', 'kompartemen' => 'Kompartemen', 'departemen' => 'Departemen',
        'bagian' => 'Bagian', 'seksi' => 'Seksi', 'foreman' => 'Foreman', 'fungsional' => 'Fungsional',
    ];

    /**
     * STEP 2 — form mapping kandidat_nama_unit -> unit_organisasi_id. Exact match
     * (case-insensitive, trim) ke nama_unit snapshot versi ini di-pre-select via
     * `selected` di Blade (server-side) & ditandai badge "Auto-matched"; yg TIDAK exact
     * match dibiarkan kosong — TIDAK ditebak lewat fuzzy match sama sekali, admin WAJIB
     * pilih manual (beda dgn tahap analisis manual sebelumnya yg sempat pakai fuzzy —
     * di alur self-service ini sengaja lebih konservatif, auto-select cuma utk kepastian
     * 100%, "kelihatannya mirip" tetap harus keputusan manusia).
     */
    public function mapping(string $token)
    {
        $payload = $this->loadPayload($token);

        if ($payload === null) {
            return $this->payloadNotFoundRedirect();
        }

        $versi = StrukturOrganisasiVersi::find($payload['struktur_organisasi_versi_id']);

        if (!$versi) {
            return redirect()->route('organisasi.kompetensi-teknis.import.preview', ['token' => $token])
                ->with('error', 'Versi struktur organisasi yang dipilih di Step 1 tidak ditemukan lagi.');
        }

        $tidyRows = $payload['tidyRows'];

        // Kandidat unik + jumlah baris tidy terkait (biar admin tau dampak tiap pilihan).
        $kandidatCounts = collect($tidyRows)
            ->pluck('kandidat_nama_unit')
            ->filter(fn ($k) => trim((string) $k) !== '')
            ->countBy()
            ->sortKeys();

        $unitSnapshots = $versi->unitOrganisasiSnapshots()->get();
        $namaById      = $unitSnapshots->pluck('nama_unit', 'unit_organisasi_id');
        $levelById     = $unitSnapshots->pluck('level', 'unit_organisasi_id');

        // Format label: "{Level} {nama_unit}, dibawah {Level_parent} {nama_parent}" — level
        // jadi PREFIX (bukan di dalam kurung terpisah), berlaku utk unit sendiri MAUPUN
        // parent-nya. Tetap 1 tingkat parent saja (bukan traversal ke atas berkali-kali).
        // Unit tanpa parent (mis. root "Utama") -> tanpa embel2 "dibawah" sama sekali.
        $unitOptionsFlat = $unitSnapshots
            ->map(function ($u) use ($namaById, $levelById) {
                $levelLabel = self::LEVEL_LABEL[$u->level] ?? ucfirst($u->level);
                $label      = "{$levelLabel} {$u->nama_unit}";

                $parentNama  = $u->parent_unit_organisasi_id ? $namaById->get($u->parent_unit_organisasi_id) : null;
                $parentLevel = $u->parent_unit_organisasi_id ? $levelById->get($u->parent_unit_organisasi_id) : null;

                if ($parentNama && $parentLevel) {
                    $parentLevelLabel = self::LEVEL_LABEL[$parentLevel] ?? ucfirst($parentLevel);
                    $label .= ", dibawah {$parentLevelLabel} {$parentNama}";
                }

                return [
                    'unit_organisasi_id' => $u->unit_organisasi_id,
                    'nama_unit'          => $u->nama_unit,
                    'level'              => $u->level,
                    'label'              => $label,
                ];
            })
            ->sortBy('nama_unit')
            ->values();

        $byLevel = $unitOptionsFlat->groupBy('level');

        // Urutan grup mengikuti LEVEL_ORDER (Departemen dulu, baru Fungsional, dst), bukan
        // urutan kemunculan di data — cuma level yg BENAR2 ada unit-nya yg dimasukkan.
        $groupedOptions = collect(self::LEVEL_ORDER)
            ->mapWithKeys(fn ($level) => [(self::LEVEL_LABEL[$level] ?? ucfirst($level)) => $byLevel->get($level, collect())])
            ->filter(fn ($group) => $group->isNotEmpty());

        // Exact match (case-insensitive, trim) — TIDAK ada fuzzy/tebakan sama sekali.
        $autoMatch = [];
        foreach ($kandidatCounts->keys() as $kandidat) {
            $match = $unitSnapshots->first(
                fn ($u) => mb_strtolower(trim($u->nama_unit)) === mb_strtolower(trim($kandidat))
            );
            $autoMatch[$kandidat] = $match?->unit_organisasi_id;
        }

        return view('kompetensi_teknis.import.mapping', [
            'token'          => $token,
            'payload'        => $payload,
            'versi'          => $versi,
            'kandidatCounts' => $kandidatCounts,
            'groupedOptions' => $groupedOptions,
            'autoMatch'      => $autoMatch,
        ]);
    }

    /**
     * STEP 2 (submit) — validasi SEMUA kandidat sudah terisi (tidak boleh ada yg kosong)
     * & tiap unit_organisasi_id yg dipilih benar2 anggota snapshot versi ini (bukan id
     * sembarangan), lalu simpan sbg key "unit_mapping" DI DALAM file temp JSON yg sama
     * (bukan file terpisah) supaya token di URL tetap 1 sepanjang alur Step 1-2-3.
     */
    public function mappingStore(Request $request, string $token)
    {
        $payload = $this->loadPayload($token);

        if ($payload === null) {
            return $this->payloadNotFoundRedirect();
        }

        $versi = StrukturOrganisasiVersi::find($payload['struktur_organisasi_versi_id']);

        if (!$versi) {
            return redirect()->route('organisasi.kompetensi-teknis.import.preview', ['token' => $token])
                ->with('error', 'Versi struktur organisasi yang dipilih di Step 1 tidak ditemukan lagi.');
        }

        $kandidatList = collect($payload['tidyRows'])
            ->pluck('kandidat_nama_unit')
            ->filter(fn ($k) => trim((string) $k) !== '')
            ->unique()
            ->values();

        $submitted = (array) $request->input('unit_mapping', []);

        $missing = [];
        $mapping = [];
        foreach ($kandidatList as $kandidat) {
            $val = $submitted[$kandidat] ?? null;
            if ($val === null || $val === '') {
                $missing[] = $kandidat;
                continue;
            }
            $mapping[$kandidat] = (int) $val;
        }

        if (!empty($missing)) {
            return back()->withInput()->with('error',
                'Masih ada ' . count($missing) . ' kandidat unit yang belum dipilih: ' . implode(', ', $missing));
        }

        // Tiap id yg dipilih harus benar2 anggota snapshot versi ini — mencegah value
        // hasil oprek manual di <select> (bukan dari opsi yg disediakan) ke-simpan.
        $validUnitIds = UnitOrganisasiSnapshot::where('struktur_organisasi_versi_id', $versi->id)
            ->pluck('unit_organisasi_id')
            ->all();

        if (!empty(array_diff(array_values($mapping), $validUnitIds))) {
            return back()->withInput()->with('error', 'Ada pilihan unit yang tidak valid untuk versi ini.');
        }

        $payload['unit_mapping'] = $mapping;

        file_put_contents($this->tempFilePath($token), json_encode($payload));

        return redirect()->route('organisasi.kompetensi-teknis.import.primary', ['token' => $token])
            ->with('success', 'Mapping tersimpan — lanjut pilih kompetensi Primary per jenjang.');
    }

    /**
     * Ambil unit_mapping dari payload HANYA kalau sudah lengkap (semua kandidat_nama_unit
     * unik di tidyRows punya pasangan unit_organisasi_id) — null kalau belum ada sama
     * sekali atau masih ada yg kosong. Dipakai bareng oleh review() & reviewCommit() supaya
     * keduanya konsisten menolak lanjut kalau Step 2 belum tuntas.
     *
     * @return array<string, int>|null
     */
    private function resolveCompleteUnitMapping(array $payload): ?array
    {
        $kandidatList = collect($payload['tidyRows'])
            ->pluck('kandidat_nama_unit')
            ->filter(fn ($k) => trim((string) $k) !== '')
            ->unique()
            ->values();

        $mapping = $payload['unit_mapping'] ?? null;

        if (!is_array($mapping)) {
            return null;
        }

        foreach ($kandidatList as $kandidat) {
            if (!array_key_exists($kandidat, $mapping) || $mapping[$kandidat] === null || $mapping[$kandidat] === '') {
                return null;
            }
        }

        return array_map('intval', $mapping);
    }

    /**
     * STEP 3 — halaman pilih manual kompetensi mana yg prioritas='primary'. GANTI TOTAL
     * dari deteksi border cell Excel yg terbukti tidak reliable — asal native MAUPUN
     * generic sama-sama bisa dicentang jadi primary (kombinasi generic+primary yg dulu
     * TIDAK MUNGKIN muncul dari border, sekarang valid krn keputusan manusia).
     *
     * Grouping tampilan: unit (nama asli hasil mapping) -> jenjang_jabatan (urut
     * urutan_jenjang) -> list kompetensi (native+generic DIGABUNG, tidak dipisah, krn
     * keduanya sama2 bisa primary). 0 dicentang tetap valid (semua secondary).
     */
    public function primary(string $token)
    {
        $payload = $this->loadPayload($token);

        if ($payload === null) {
            return $this->payloadNotFoundRedirect();
        }

        $versi = StrukturOrganisasiVersi::find($payload['struktur_organisasi_versi_id']);

        if (!$versi) {
            return redirect()->route('organisasi.kompetensi-teknis.import.preview', ['token' => $token])
                ->with('error', 'Versi struktur organisasi yang dipilih di Step 1 tidak ditemukan lagi.');
        }

        $unitMapping = $this->resolveCompleteUnitMapping($payload);

        if ($unitMapping === null) {
            return redirect()->route('organisasi.kompetensi-teknis.import.mapping', ['token' => $token])
                ->with('error', 'Selesaikan mapping unit dulu — masih ada kandidat unit yang belum dipetakan.');
        }

        $unitNames = UnitOrganisasiSnapshot::where('struktur_organisasi_versi_id', $versi->id)
            ->whereIn('unit_organisasi_id', array_unique(array_values($unitMapping)))
            ->get()
            ->keyBy('unit_organisasi_id');

        $checkedSet = array_flip($payload['primary_row_ids'] ?? []);

        // unit_organisasi_id -> jenjang_jabatan -> data grup + items.
        $grouped = [];
        foreach ($payload['tidyRows'] as $row) {
            $kandidat = trim((string) $row['kandidat_nama_unit']);
            if (!isset($unitMapping[$kandidat])) {
                continue; // seharusnya tidak terjadi krn unit_mapping sudah divalidasi lengkap
            }
            $unitId  = $unitMapping[$kandidat];
            $jenjang = $row['jenjang_jabatan'];

            $grouped[$unitId]['unit_organisasi_id']      ??= $unitId;
            $grouped[$unitId]['jenjang'][$jenjang]['jenjang_jabatan'] ??= $jenjang;
            $grouped[$unitId]['jenjang'][$jenjang]['urutan_jenjang']  ??= $row['urutan_jenjang'];
            $grouped[$unitId]['jenjang'][$jenjang]['items'][] = [
                'row_id'          => $row['row_id'],
                'nama_kompetensi' => $row['nama_kompetensi'],
                'level'           => $row['level'],
                'asal'            => $row['asal'],
                'checked'         => isset($checkedSet[$row['row_id']]),
            ];
        }

        $unitGroups = [];
        foreach ($grouped as $unitId => $g) {
            $u = $unitNames->get($unitId);
            $namaUnitLabel = $u ? formatUnitLabel($u->nama_unit, $u->level) : "Unit #{$unitId}";

            $jenjangGroups = collect($g['jenjang'])
                ->sortBy('urutan_jenjang')
                ->map(function ($j) {
                    $j['items'] = collect($j['items'])->sortBy('nama_kompetensi')->values()->all();

                    return $j;
                })
                ->values()
                ->all();

            $unitGroups[] = [
                'unit_organisasi_id' => $unitId,
                'nama_unit_label'    => $namaUnitLabel,
                'jenjang_groups'     => $jenjangGroups,
            ];
        }
        usort($unitGroups, fn ($a, $b) => strcmp($a['nama_unit_label'], $b['nama_unit_label']));

        return view('kompetensi_teknis.import.primary', [
            'token'      => $token,
            'payload'    => $payload,
            'versi'      => $versi,
            'unitGroups' => $unitGroups,
        ]);
    }

    /**
     * STEP 3 (submit) — 0 dicentang tetap valid (tidak ada validasi minimal), makanya TIDAK
     * ada pengecekan "harus ada yg dipilih" spt di mappingStore(). Filter defensif: cuma
     * row_id yg BENAR2 ada di tidyRows yg disimpan (cegah value hasil oprek manual di
     * checkbox). Redirect LANGSUNG ke Review (bukan preview lagi) biar alur lebih pendek.
     */
    public function primaryStore(Request $request, string $token)
    {
        $payload = $this->loadPayload($token);

        if ($payload === null) {
            return $this->payloadNotFoundRedirect();
        }

        $unitMapping = $this->resolveCompleteUnitMapping($payload);

        if ($unitMapping === null) {
            return redirect()->route('organisasi.kompetensi-teknis.import.mapping', ['token' => $token])
                ->with('error', 'Selesaikan mapping unit dulu — masih ada kandidat unit yang belum dipetakan.');
        }

        $validRowIds = collect($payload['tidyRows'])->pluck('row_id')->filter()->all();
        $submitted   = (array) $request->input('primary_row_ids', []);

        $primaryRowIds = array_values(array_intersect($submitted, $validRowIds));

        $payload['primary_row_ids'] = $primaryRowIds;

        file_put_contents($this->tempFilePath($token), json_encode($payload));

        $pesan = count($primaryRowIds) > 0
            ? count($primaryRowIds) . ' kompetensi ditandai Primary — silakan review sebelum commit.'
            : 'Tidak ada kompetensi yang ditandai Primary (semua Secondary) — silakan review sebelum commit.';

        return redirect()->route('organisasi.kompetensi-teknis.import.review', ['token' => $token])
            ->with('success', $pesan);
    }

    /**
     * STEP 4 (review) — panggil App\Services\KompetensiTeknisImporter dalam MODE DRY-RUN
     * (parameter $commit default false, transaksi tetap dijalankan penuh lalu di-rollback,
     * lihat catatan di Service-nya) supaya laporan yg ditampilkan 100% akurat thd kondisi
     * database SEKARANG (termasuk duplikat by unique constraint) — bukan simulasi terpisah.
     * Tombol commit HANYA ditampilkan di view kalau dry-run ini 0 error & tidak stoppedEarly.
     */
    public function review(string $token)
    {
        $payload = $this->loadPayload($token);

        if ($payload === null) {
            return $this->payloadNotFoundRedirect();
        }

        $versi = StrukturOrganisasiVersi::find($payload['struktur_organisasi_versi_id']);

        if (!$versi) {
            return redirect()->route('organisasi.kompetensi-teknis.import.preview', ['token' => $token])
                ->with('error', 'Versi struktur organisasi yang dipilih di Step 1 tidak ditemukan lagi.');
        }

        $unitMapping = $this->resolveCompleteUnitMapping($payload);

        if ($unitMapping === null) {
            return redirect()->route('organisasi.kompetensi-teknis.import.mapping', ['token' => $token])
                ->with('error', 'Selesaikan mapping unit dulu — masih ada kandidat unit yang belum dipetakan.');
        }

        // Step "Pilih Primary" WAJIB dilewati dulu — array_key_exists (bukan !empty) krn 0
        // dipilih tetap valid (semua secondary), yg tidak valid itu BELUM PERNAH ke step ini.
        if (!array_key_exists('primary_row_ids', $payload)) {
            return redirect()->route('organisasi.kompetensi-teknis.import.primary', ['token' => $token])
                ->with('error', 'Selesaikan pemilihan kompetensi Primary dulu sebelum review.');
        }

        $result = KompetensiTeknisImporter::import($payload['tidyRows'], $unitMapping, $versi->id, false, $payload['primary_row_ids']);

        // Breakdown per unit pakai NAMA UNIT ASLI hasil mapping (bukan kandidat mentah).
        $unitNames = UnitOrganisasiSnapshot::where('struktur_organisasi_versi_id', $versi->id)
            ->whereIn('unit_organisasi_id', array_keys($result['perUnit']))
            ->get()
            ->keyBy('unit_organisasi_id');

        $perUnitNamed = collect($result['perUnit'])
            ->map(function ($jumlah, $unitId) use ($unitNames) {
                $u = $unitNames->get($unitId);

                return [
                    'unit_organisasi_id' => $unitId,
                    'nama_unit'          => $u ? formatUnitLabel($u->nama_unit, $u->level) : "Unit #{$unitId}",
                    'jumlah'             => $jumlah,
                ];
            })
            ->sortBy('nama_unit')
            ->values();

        return view('kompetensi_teknis.import.review', [
            'token'        => $token,
            'payload'      => $payload,
            'versi'        => $versi,
            'result'       => $result,
            'perUnitNamed' => $perUnitNamed,
        ]);
    }

    /**
     * STEP 4 (commit) — panggil Importer dalam MODE COMMIT sungguhan ($commit = true).
     * Kalau ada error (harusnya sudah dicegah dry-run di review(), tapi tetap ditangani
     * defensif — mis. race condition data berubah di antara buka halaman review & klik
     * commit): DB::transaction di dalam Service otomatis rollback, file temp TIDAK
     * dihapus (biar bisa dicoba lagi tanpa upload ulang). Sukses -> file temp dihapus
     * (sudah tidak diperlukan), log activity, redirect ke List dgn filter versi+rumpun.
     */
    public function reviewCommit(string $token)
    {
        $payload = $this->loadPayload($token);

        if ($payload === null) {
            return $this->payloadNotFoundRedirect();
        }

        $versi = StrukturOrganisasiVersi::find($payload['struktur_organisasi_versi_id']);

        if (!$versi) {
            return redirect()->route('organisasi.kompetensi-teknis.import.preview', ['token' => $token])
                ->with('error', 'Versi struktur organisasi yang dipilih di Step 1 tidak ditemukan lagi.');
        }

        $unitMapping = $this->resolveCompleteUnitMapping($payload);

        if ($unitMapping === null) {
            return redirect()->route('organisasi.kompetensi-teknis.import.mapping', ['token' => $token])
                ->with('error', 'Selesaikan mapping unit dulu — masih ada kandidat unit yang belum dipetakan.');
        }

        if (!array_key_exists('primary_row_ids', $payload)) {
            return redirect()->route('organisasi.kompetensi-teknis.import.primary', ['token' => $token])
                ->with('error', 'Selesaikan pemilihan kompetensi Primary dulu sebelum commit.');
        }

        $result = KompetensiTeknisImporter::import($payload['tidyRows'], $unitMapping, $versi->id, true, $payload['primary_row_ids']);

        if ($result['stoppedEarly'] || count($result['errors']) > 0) {
            return redirect()->route('organisasi.kompetensi-teknis.import.review', ['token' => $token])
                ->with('error', 'Commit dibatalkan — ditemukan ' . count($result['errors']) . ' error, transaksi di-rollback (tidak ada perubahan). File temp tetap tersimpan, silakan cek ulang lalu coba lagi. Detail: '
                    . implode(' | ', array_slice($result['errors'], 0, 3)));
        }

        // Sukses & sudah ter-commit permanen -> file temp sudah tidak diperlukan lagi.
        @unlink($this->tempFilePath($token));

        $target = $payload['job_family_nama'] . ' — SK ' . $versi->nomor_sk;
        $desc   = "Import Kompetensi Teknis via web dari file \"{$payload['original_filename']}\": "
            . "{$result['totalInsert']} baris tersimpan, " . count($result['kompetensiBaru']) . ' kompetensi baru dibuat, '
            . "{$result['totalDuplikat']} duplikat di-skip (rumpun \"{$payload['job_family_nama']}\", SK {$versi->nomor_sk}).";
        $this->log('import', 'Kompetensi Teknis', $target, $desc);

        $ringkasan = "Import berhasil: {$result['totalInsert']} baris kompetensi tersimpan";
        if (count($result['kompetensiBaru']) > 0) {
            $ringkasan .= ', ' . count($result['kompetensiBaru']) . ' kompetensi baru dibuat';
        }
        $ringkasan .= '.';

        return redirect()->route('organisasi.kompetensi-teknis.index', [
                'versi'  => $versi->id,
                'rumpun' => $payload['job_family_nama'],
            ])
            ->with('success', $ringkasan);
    }
}
