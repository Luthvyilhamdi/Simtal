<?php

namespace App\Http\Controllers;

use App\Exports\StrukturOrganisasiVersiExport;
use App\Imports\StrukturOrganisasiBaselineImport;
use App\Imports\StrukturOrganisasiLanjutanImport;
use App\Models\StrukturOrganisasiVersi;
use App\Models\UnitOrganisasi;
use App\Models\UnitOrganisasiSnapshot;
use App\Models\UnitOrganisasiTransisi;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StrukturOrganisasiVersiController extends Controller
{
    use LogsActivity;

    const LEVELS = ['direktorat', 'kompartemen', 'departemen', 'bagian', 'seksi', 'foreman', 'fungsional'];

    // Nilai jenis_transisi yg valid diisi eksplisit di kolom Excel import lanjutan.
    // 'bubar' termasuk dalam enum DB tapi sengaja tidak dipakai lewat baris file (lihat
    // validateAndResolveLanjutanRows) — unit bubar dideteksi otomatis dari absensi, bukan
    // dideklarasikan lewat baris. 'lanjut' juga tidak masuk sini krn direpresentasikan
    // dgn kolom KOSONG, bukan nilai string 'lanjut'. 'ganti_level' dipakai kalau nama_unit
    // & parent PERSIS SAMA tapi level berubah (bukan rename krn nama tdk berubah, bukan
    // pindah_induk krn parent tdk berubah) — identitas lama tetap dipakai, sama spt rename/
    // pindah_induk.
    const TRANSISI_LANJUTAN_ENUM = ['rename', 'pindah_induk', 'pecah', 'gabung', 'baru', 'bubar', 'ganti_level'];

    // Jenis transisi yg TIDAK mengganti identitas unit_organisasi (identitas lama dipakai
    // lagi, cuma atributnya berubah) — dipakai berulang di beberapa tempat commit/validasi.
    const TRANSISI_IDENTITAS_LANJUT = ['rename', 'pindah_induk', 'ganti_level'];

    public function index()
    {
        $versiList = StrukturOrganisasiVersi::withCount('unitOrganisasiSnapshots')
            ->orderByDesc('tanggal_mulai_berlaku')
            ->get();

        return view('organisasi.struktur.index', compact('versiList'));
    }

    public function search(Request $request)
    {
        $q            = trim((string) $request->input('q', ''));
        $level        = $request->input('level');
        $direktoratId = $request->filled('direktorat') ? (int) $request->input('direktorat') : null;

        $ledger = $this->buildUnitLedger();

        // Opsi dropdown direktorat: unit level 'direktorat' pada versi final TERBARU saja
        $direktoratOptions = $ledger['latestVersiId']
            ? $ledger['snapshotsByVersi']->get($ledger['latestVersiId'], collect())
                ->where('level', 'direktorat')
                ->sortBy('nama_unit')
                ->values()
            : collect();

        $results = [];

        foreach ($ledger['snapshotsByUnit'] as $unitId => $snaps) {
            if ($q !== '' && $snaps->first(fn ($s) => stripos($s->nama_unit, $q) !== false) === null) {
                continue;
            }

            $latestSnap = $snaps->last();

            if ($level && $latestSnap->level !== $level) {
                continue;
            }

            if ($direktoratId) {
                $versiSnapshotsByUnitId = $ledger['snapshotsByVersi']
                    ->get($latestSnap->struktur_organisasi_versi_id, collect())
                    ->keyBy('unit_organisasi_id');
                $root = $this->rootAncestorSnapshot($latestSnap, $versiSnapshotsByUnitId);
                if (!$root || $root->unit_organisasi_id !== $direktoratId) {
                    continue;
                }
            }

            $riwayat = $this->riwayatNama($snaps);

            $results[] = [
                'unit_organisasi_id' => $unitId,
                'nama_saat_ini'      => $latestSnap->nama_unit,
                'level'              => $latestSnap->level,
                'status'             => $this->statusUnit($unitId, $ledger),
                'nama_sebelumnya'    => count($riwayat) > 1 ? array_slice($riwayat, 0, -1) : [],
            ];
        }

        usort($results, fn ($a, $b) => strcmp($a['nama_saat_ini'], $b['nama_saat_ini']));

        return view('organisasi.struktur.search', [
            'q'                 => $q,
            'level'             => $level,
            'direktoratId'      => $direktoratId,
            'levels'            => self::LEVELS,
            'direktoratOptions' => $direktoratOptions,
            'results'           => $results,
        ]);
    }

    public function unitTimeline(UnitOrganisasi $unit)
    {
        $ledger = $this->buildUnitLedger();

        $snaps = $ledger['snapshotsByUnit']->get($unit->id, collect());

        if ($snaps->isEmpty()) {
            abort(404, 'Unit ini tidak ditemukan di versi final manapun.');
        }

        $transisiTerkait = $ledger['allTransisi']->filter(
            fn ($t) => $t->unit_asal_id === $unit->id || $t->unit_baru_id === $unit->id
        );
        $transisiByVersi = $transisiTerkait->groupBy('struktur_organisasi_versi_id');

        $points = [];
        $prev = null;

        foreach ($snaps as $snap) {
            $versiId = $snap->struktur_organisasi_versi_id;
            $versiSnapshotsByUnitId = $ledger['snapshotsByVersi']->get($versiId, collect())->keyBy('unit_organisasi_id');

            $parentNama = $snap->parent_unit_organisasi_id
                ? ($versiSnapshotsByUnitId[$snap->parent_unit_organisasi_id]->nama_unit ?? '-')
                : '-';

            $perubahan = [];
            if ($prev) {
                if ($prev['snap']->nama_unit !== $snap->nama_unit) {
                    $perubahan[] = "Nama: {$prev['snap']->nama_unit} → {$snap->nama_unit}";
                }
                if ($prev['parent_nama'] !== $parentNama) {
                    $perubahan[] = "Parent: {$prev['parent_nama']} → {$parentNama}";
                }
                if ($prev['snap']->level !== $snap->level) {
                    $perubahan[] = "Level: {$prev['snap']->level} → {$snap->level}";
                }
                if ($prev['snap']->mc_formasi !== $snap->mc_formasi) {
                    $perubahan[] = "Formasi: {$prev['snap']->mc_formasi} → {$snap->mc_formasi}";
                }
            }

            $transisiResmi = $transisiByVersi->get($versiId, collect())
                ->first(fn ($t) => $t->unit_baru_id === $unit->id || $t->unit_asal_id === $unit->id);

            $points[] = [
                'versi'          => StrukturOrganisasiVersi::find($versiId) ?? $ledger['finalVersions']->firstWhere('id', $versiId),
                'nama_unit'      => $snap->nama_unit,
                'level'          => $snap->level,
                'parent_nama'    => $parentNama,
                'mc_formasi'     => $snap->mc_formasi,
                'perubahan'      => $perubahan,
                'jenis_resmi'    => $transisiResmi->jenis_transisi ?? null,
                'anomali'        => empty($transisiResmi) && !empty($perubahan) && $prev !== null,
            ];

            $prev = ['snap' => $snap, 'parent_nama' => $parentNama];
        }

        $statusInfo = $this->statusUnit($unit->id, $ledger);

        // Relasi asal: event di mana unit ini adalah HASIL (unit_baru_id) dari pecah/gabung unit lain
        $asalDari = $transisiTerkait
            ->where('unit_baru_id', $unit->id)
            ->whereIn('jenis_transisi', ['pecah', 'gabung'])
            ->map(fn ($t) => [
                'jenis' => $t->jenis_transisi,
                'unit_organisasi_id' => $t->unit_asal_id,
                'nama'  => $ledger['latestNameByUnit'][$t->unit_asal_id] ?? '-',
            ])
            ->values();

        return view('organisasi.struktur.unit-timeline', [
            'unit'       => $unit,
            'points'     => $points,
            'statusInfo' => $statusInfo,
            'asalDari'   => $asalDari,
        ]);
    }

    /**
     * Kumpulan data lintas-versi (final saja) yang dipakai bersama oleh search() dan
     * unitTimeline() — dihitung sekali, dipakai berulang lewat helper status/riwayat di bawah.
     */
    private function buildUnitLedger(): array
    {
        $finalVersions = StrukturOrganisasiVersi::where('status', 'final')
            ->orderBy('tanggal_mulai_berlaku')
            ->get();

        if ($finalVersions->isEmpty()) {
            return [
                'finalVersions'    => $finalVersions,
                'latestVersiId'    => null,
                'snapshotsByUnit'  => collect(),
                'snapshotsByVersi' => collect(),
                'latestNameByUnit' => collect(),
                'allTransisi'      => collect(),
                'transisiByAsal'   => collect(),
            ];
        }

        $finalVersiIds = $finalVersions->pluck('id');
        $versiOrder    = $finalVersions->pluck('tanggal_mulai_berlaku', 'id');

        $allSnapshots = UnitOrganisasiSnapshot::whereIn('struktur_organisasi_versi_id', $finalVersiIds)->get();

        $snapshotsByUnit = $allSnapshots->groupBy('unit_organisasi_id')
            ->map(fn ($g) => $g->sortBy(fn ($s) => $versiOrder[$s->struktur_organisasi_versi_id])->values());

        $allTransisi = UnitOrganisasiTransisi::whereIn('struktur_organisasi_versi_id', $finalVersiIds)->get();

        return [
            'finalVersions'    => $finalVersions,
            'latestVersiId'    => $finalVersions->last()->id,
            'snapshotsByUnit'  => $snapshotsByUnit,
            'snapshotsByVersi' => $allSnapshots->groupBy('struktur_organisasi_versi_id'),
            'latestNameByUnit' => $snapshotsByUnit->map(fn ($g) => $g->last()->nama_unit),
            'allTransisi'      => $allTransisi,
            'transisiByAsal'   => $allTransisi->groupBy('unit_asal_id'),
        ];
    }

    /** Status akhir 1 unit: aktif (masih ada di versi final terbaru) atau bubar/pecah/gabung (dgn penerusnya). */
    private function statusUnit(int $unitId, array $ledger): array
    {
        $snaps = $ledger['snapshotsByUnit']->get($unitId, collect());
        if ($snaps->isEmpty()) {
            return ['jenis' => 'tidak_diketahui', 'label' => 'Tidak ditemukan', 'successors' => []];
        }

        if ($snaps->last()->struktur_organisasi_versi_id === $ledger['latestVersiId']) {
            return ['jenis' => 'aktif', 'label' => 'Aktif', 'successors' => []];
        }

        $eventRows = $ledger['transisiByAsal']->get($unitId, collect());
        if ($eventRows->isEmpty()) {
            return [
                'jenis'      => 'tidak_jelas',
                'label'      => 'Tidak ada di versi final terbaru',
                'successors' => [],
            ];
        }

        $jenis  = $eventRows->first()->jenis_transisi;
        $labels = ['bubar' => 'Bubar', 'pecah' => 'Terpecah menjadi', 'gabung' => 'Bergabung menjadi'];

        $successors = $eventRows->pluck('unit_baru_id')->filter()->map(fn ($id) => [
            'unit_organisasi_id' => $id,
            'nama'               => $ledger['latestNameByUnit'][$id] ?? '-',
        ])->values()->all();

        return [
            'jenis'      => $jenis,
            'label'      => $labels[$jenis] ?? ucfirst($jenis),
            'successors' => $successors,
        ];
    }

    /** Daftar nama berurutan sepanjang histori unit ini (nama duplikat berturut-turut digabung jadi 1 "era"). */
    private function riwayatNama(Collection $snapsKronologis): array
    {
        $eras = [];
        foreach ($snapsKronologis as $s) {
            if (empty($eras) || end($eras) !== $s->nama_unit) {
                $eras[] = $s->nama_unit;
            }
        }
        return $eras;
    }

    /** Telusuri parent_unit_organisasi_id ke atas sampai root (dalam 1 versi yang sama). */
    private function rootAncestorSnapshot($snapshot, Collection $snapshotsKeyedByUnitId)
    {
        $current = $snapshot;
        $seen = [];
        while ($current && $current->parent_unit_organisasi_id && !isset($seen[$current->unit_organisasi_id])) {
            $seen[$current->unit_organisasi_id] = true;
            $parent = $snapshotsKeyedByUnitId->get($current->parent_unit_organisasi_id);
            if (!$parent) break;
            $current = $parent;
        }
        return $current;
    }

    public function create()
    {
        $lastVersi  = StrukturOrganisasiVersi::orderByDesc('tanggal_mulai_berlaku')->first();
        $isBaseline = !$lastVersi;

        $existingUnits = collect();

        if (!$isBaseline) {
            $existingUnits = $lastVersi->unitOrganisasiSnapshots()
                ->orderBy('level')
                ->orderBy('nama_unit')
                ->get()
                ->map(fn ($s) => [
                    'key'                => 'e' . $s->unit_organisasi_id,
                    'unit_organisasi_id' => $s->unit_organisasi_id,
                    'nama_unit'          => $s->nama_unit,
                    'level'              => $s->level,
                    'parent_key'         => $s->parent_unit_organisasi_id ? ('e' . $s->parent_unit_organisasi_id) : null,
                    'mc_formasi'         => $s->mc_formasi,
                    'keterangan'         => $s->keterangan,
                ])
                ->values();
        }

        return view('organisasi.struktur.create', [
            'isBaseline'      => $isBaseline,
            'lastVersi'       => $lastVersi,
            'lastVersiDraft'  => $lastVersi && $lastVersi->isDraft(),
            'existingUnits'   => $existingUnits,
            'levels'          => self::LEVELS,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_sk'              => 'required|string|max:100|unique:struktur_organisasi_versi,nomor_sk',
            'tanggal_sk'            => 'required|date',
            'tanggal_mulai_berlaku' => 'required|date|unique:struktur_organisasi_versi,tanggal_mulai_berlaku',
            'keterangan'            => 'nullable|string',
            'payload'               => 'required|json',
        ]);

        $lastVersi  = StrukturOrganisasiVersi::orderByDesc('tanggal_mulai_berlaku')->first();
        $isBaseline = !$lastVersi;

        if (!$isBaseline && Carbon::parse($request->tanggal_mulai_berlaku)->lte($lastVersi->tanggal_mulai_berlaku)) {
            return back()->withInput()->with('error',
                'Tanggal mulai berlaku harus setelah versi terakhir (' .
                $lastVersi->tanggal_mulai_berlaku->translatedFormat('d F Y') . ').');
        }

        $payload = json_decode($request->input('payload'), true);

        $validator = Validator::make($payload ?? [], [
            'units'                       => 'required|array|min:1',
            'units.*.key'                 => 'required|string',
            'units.*.unit_organisasi_id'  => 'nullable|integer|exists:unit_organisasi,id',
            'units.*.nama_unit'           => 'required|string|max:255',
            'units.*.level'               => 'required|in:' . implode(',', self::LEVELS),
            'units.*.parent_key'          => 'nullable|string',
            'units.*.mc_formasi'          => 'required|integer|min:0',
            'units.*.keterangan'          => 'nullable|string',
            'units.*.jenis_transisi'      => 'nullable|in:lanjut,rename,pindah_induk,ganti_level,baru',
            'bubar'                       => 'array',
            'bubar.*.unit_organisasi_id'  => 'required|integer|exists:unit_organisasi,id',
            'pecah'                       => 'array',
            'pecah.*.unit_organisasi_id'  => 'required|integer|exists:unit_organisasi,id',
            'pecah.*.targets'             => 'required|array|min:2',
            'pecah.*.targets.*'           => 'required|string',
            'gabung'                      => 'array',
            'gabung.*.unit_organisasi_ids'   => 'required|array|min:2',
            'gabung.*.unit_organisasi_ids.*' => 'integer|exists:unit_organisasi,id',
            'gabung.*.target_key'         => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $hierarkiErrors = $this->validateParentHierarchy($payload['units']);
        if (!empty($hierarkiErrors)) {
            return back()->withInput()->withErrors($hierarkiErrors);
        }

        $units  = $payload['units'];
        $bubar  = $payload['bubar']  ?? [];
        $pecah  = $payload['pecah']  ?? [];
        $gabung = $payload['gabung'] ?? [];

        $versi = DB::transaction(function () use ($request, $units, $bubar, $pecah, $gabung, $isBaseline) {
            $versi = StrukturOrganisasiVersi::create([
                'nomor_sk'              => $request->nomor_sk,
                'tanggal_sk'            => $request->tanggal_sk,
                'tanggal_mulai_berlaku' => $request->tanggal_mulai_berlaku,
                'keterangan'            => $request->keterangan,
                'created_by'            => Auth::id(),
            ]);

            // 1) Pastikan identitas unit_organisasi untuk tiap baris (baru dibuat kalau belum ada)
            $keyToUnitId = [];
            foreach ($units as $row) {
                $keyToUnitId[$row['key']] = !empty($row['unit_organisasi_id'])
                    ? (int) $row['unit_organisasi_id']
                    : UnitOrganisasi::create()->id;
            }

            // 2) Snapshot isi struktur versi ini
            foreach ($units as $row) {
                UnitOrganisasiSnapshot::create([
                    'unit_organisasi_id'           => $keyToUnitId[$row['key']],
                    'struktur_organisasi_versi_id' => $versi->id,
                    'nama_unit'                    => $row['nama_unit'],
                    'level'                        => $row['level'],
                    'parent_unit_organisasi_id'    => !empty($row['parent_key']) ? ($keyToUnitId[$row['parent_key']] ?? null) : null,
                    'mc_formasi'                   => $row['mc_formasi'],
                    'keterangan'                   => $row['keterangan'] ?? null,
                ]);
            }

            // 3) Transisi bubar
            foreach ($bubar as $b) {
                UnitOrganisasiTransisi::create([
                    'struktur_organisasi_versi_id' => $versi->id,
                    'jenis_transisi'                => 'bubar',
                    'unit_asal_id'                   => $b['unit_organisasi_id'],
                    'unit_baru_id'                   => null,
                ]);
            }

            // 4) Transisi pecah (1 unit asal -> N unit baru)
            $targetKeysHandled = [];
            foreach ($pecah as $p) {
                foreach ($p['targets'] as $targetKey) {
                    $targetKeysHandled[$targetKey] = true;
                    UnitOrganisasiTransisi::create([
                        'struktur_organisasi_versi_id' => $versi->id,
                        'jenis_transisi'                => 'pecah',
                        'unit_asal_id'                   => $p['unit_organisasi_id'],
                        'unit_baru_id'                   => $keyToUnitId[$targetKey] ?? null,
                    ]);
                }
            }

            // 5) Transisi gabung (N unit asal -> 1 unit baru)
            foreach ($gabung as $g) {
                $targetKeysHandled[$g['target_key']] = true;
                $baruId = $keyToUnitId[$g['target_key']] ?? null;
                foreach ($g['unit_organisasi_ids'] as $asalId) {
                    UnitOrganisasiTransisi::create([
                        'struktur_organisasi_versi_id' => $versi->id,
                        'jenis_transisi'                => 'gabung',
                        'unit_asal_id'                   => $asalId,
                        'unit_baru_id'                   => $baruId,
                    ]);
                }
            }

            // 6) Transisi baru/rename/pindah_induk/ganti_level untuk baris yang bukan target
            //    pecah/gabung (target pecah/gabung sudah dicatat di langkah 4 & 5, jangan dobel)
            foreach ($units as $row) {
                if (isset($targetKeysHandled[$row['key']])) {
                    continue;
                }

                $jenis = $isBaseline ? 'baru' : ($row['jenis_transisi'] ?? 'lanjut');
                if ($jenis === 'lanjut') {
                    continue;
                }

                $unitId = $keyToUnitId[$row['key']];
                UnitOrganisasiTransisi::create([
                    'struktur_organisasi_versi_id' => $versi->id,
                    'jenis_transisi'                => $jenis,
                    'unit_asal_id'                   => in_array($jenis, self::TRANSISI_IDENTITAS_LANJUT, true) ? $unitId : null,
                    'unit_baru_id'                   => $unitId,
                ]);
            }

            return $versi;
        });

        $this->log('tambah', 'Struktur Organisasi (Versi)', $versi->nomor_sk,
            'Tanggal mulai berlaku: ' . $versi->tanggal_mulai_berlaku->translatedFormat('d F Y')
            . ' · ' . count($units) . ' unit'
            . ($isBaseline ? ' · Versi pertama' : ' · Versi lanjutan'));

        return redirect()->route('organisasi.struktur.show', $versi)
            ->with('success', 'Versi struktur organisasi berhasil disimpan.');
    }

    public function importForm()
    {
        if (StrukturOrganisasiVersi::exists()) {
            return redirect()->route('organisasi.struktur.index')
                ->with('error', 'Import baseline hanya tersedia untuk versi pertama (belum ada versi tercatat).');
        }

        return view('organisasi.struktur.import', ['rowErrors' => [], 'old' => []]);
    }

    public function importUpload(Request $request)
    {
        if (StrukturOrganisasiVersi::exists()) {
            return redirect()->route('organisasi.struktur.index')
                ->with('error', 'Import baseline hanya tersedia untuk versi pertama.');
        }

        $request->validate([
            'nomor_sk'              => 'required|string|max:100|unique:struktur_organisasi_versi,nomor_sk',
            'tanggal_sk'            => 'required|date',
            'tanggal_mulai_berlaku' => 'required|date',
            'keterangan'            => 'nullable|string',
            'file'                  => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'File wajib dipilih.',
            'file.mimes'    => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $import = new StrukturOrganisasiBaselineImport();
            $import->parse($request->file('file')->getRealPath());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        $rawRows = $import->rows ?? collect();

        if ($rawRows->isEmpty()) {
            return back()->withInput()->with('error',
                'File tidak berisi data, atau nama kolom header tidak sesuai template ' .
                '(harus ada: kode_sementara, nama_unit, level, parent_kode_sementara, formasi).');
        }

        ['errors' => $rowErrors, 'rows' => $validRows] = $this->validateBaselineRows($rawRows);

        if (!empty($rowErrors)) {
            return view('organisasi.struktur.import', [
                'rowErrors' => $rowErrors,
                'old'       => $request->only('nomor_sk', 'tanggal_sk', 'tanggal_mulai_berlaku', 'keterangan'),
            ]);
        }

        session(['baseline_import' => [
            'header' => $request->only('nomor_sk', 'tanggal_sk', 'tanggal_mulai_berlaku', 'keterangan'),
            'rows'   => $validRows,
        ]]);

        return redirect()->route('organisasi.struktur.import.preview');
    }

    public function importPreview()
    {
        $data = session('baseline_import');
        if (!$data) {
            return redirect()->route('organisasi.struktur.import')
                ->with('error', 'Tidak ada data import yang menunggu konfirmasi. Silakan upload ulang.');
        }

        $rows = collect($data['rows']);

        return view('organisasi.struktur.import-preview', [
            'header'         => $data['header'],
            'previewRows'    => $this->computeBaselinePreviewRows($rows),
            'ringkasanLevel' => $rows->groupBy('level')->map->count(),
            'totalUnit'      => $rows->count(),
        ]);
    }

    public function importConfirm()
    {
        $data = session('baseline_import');
        if (!$data) {
            return redirect()->route('organisasi.struktur.import')
                ->with('error', 'Tidak ada data import yang menunggu konfirmasi. Silakan upload ulang.');
        }

        if (StrukturOrganisasiVersi::exists()) {
            session()->forget('baseline_import');
            return redirect()->route('organisasi.struktur.index')
                ->with('error', 'Sudah ada versi lain dibuat sejak proses import ini dimulai. Import dibatalkan, silakan mulai ulang.');
        }

        $rows   = collect($data['rows']);
        $header = $data['header'];

        $versi = DB::transaction(function () use ($rows, $header) {
            $versi = StrukturOrganisasiVersi::create([
                'nomor_sk'              => $header['nomor_sk'],
                'tanggal_sk'            => $header['tanggal_sk'],
                'tanggal_mulai_berlaku' => $header['tanggal_mulai_berlaku'],
                'keterangan'            => $header['keterangan'] ?? null,
                'status'                => 'draft',
                'created_by'            => Auth::id(),
            ]);

            $kodeToUnitId = [];
            foreach ($rows as $row) {
                $kodeToUnitId[$row['kode_sementara']] = UnitOrganisasi::create()->id;
            }

            foreach ($rows as $row) {
                $unitId = $kodeToUnitId[$row['kode_sementara']];

                UnitOrganisasiSnapshot::create([
                    'unit_organisasi_id'           => $unitId,
                    'struktur_organisasi_versi_id' => $versi->id,
                    'nama_unit'                    => $row['nama_unit'],
                    'level'                        => $row['level'],
                    'parent_unit_organisasi_id'    => $row['parent_kode_sementara'] ? ($kodeToUnitId[$row['parent_kode_sementara']] ?? null) : null,
                    'mc_formasi'                   => $row['mc_formasi'],
                ]);

                // Konsisten dengan alur baseline manual (create.blade.php): tiap unit baseline
                // dicatat sebagai transisi 'baru' juga, bukan cuma snapshot.
                UnitOrganisasiTransisi::create([
                    'struktur_organisasi_versi_id' => $versi->id,
                    'jenis_transisi'                => 'baru',
                    'unit_asal_id'                   => null,
                    'unit_baru_id'                   => $unitId,
                ]);
            }

            return $versi;
        });

        session()->forget('baseline_import');

        $this->log('import', 'Struktur Organisasi (Versi)', $versi->nomor_sk,
            'Import baseline dari Excel: ' . $rows->count() . ' unit');

        return redirect()->route('organisasi.struktur.show', $versi)
            ->with('success', 'Import baseline berhasil disimpan sebagai draft — ' . $rows->count() . ' unit.');
    }

    /**
     * Validasi baris mentah hasil parse Excel: kode_sementara wajib & unik, level harus
     * salah satu dari enum yang valid, parent_kode_sementara harus merujuk kode lain di
     * file yang sama (atau kosong = root), dan tidak boleh circular. Baris yang seluruh
     * kolomnya kosong (baris kosong sisa di Excel) dilewati diam-diam.
     */
    private function validateBaselineRows(Collection $rawRows): array
    {
        $errors = [];
        $seenKode = [];
        $byKode = [];

        foreach ($rawRows as $i => $row) {
            $excelRow = $i + 2; // +1 index->baris, +1 lagi utk baris header

            $allBlank = collect($row)->every(fn ($v) => $v === null || trim((string) $v) === '');
            if ($allBlank) {
                continue;
            }

            $kode       = trim((string) ($row['kode_sementara'] ?? ''));
            $nama       = trim((string) ($row['nama_unit'] ?? ''));
            $levelRaw   = (string) ($row['level'] ?? '');
            $level      = strtolower(trim($levelRaw));
            $parentKode = trim((string) ($row['parent_kode_sementara'] ?? ''));
            $mcRaw      = $row['mc_formasi'] ?? null;

            if ($kode === '') {
                $errors[] = ['baris' => $excelRow, 'alasan' => 'kode_sementara kosong.'];
                continue;
            }

            if (isset($seenKode[$kode])) {
                $errors[] = ['baris' => $excelRow, 'alasan' => "kode_sementara '{$kode}' duplikat (sudah dipakai baris {$seenKode[$kode]})."];
            } else {
                $seenKode[$kode] = $excelRow;
            }

            if ($nama === '') {
                $errors[] = ['baris' => $excelRow, 'alasan' => 'nama_unit kosong.'];
            }

            if (!in_array($level, self::LEVELS, true)) {
                $errors[] = ['baris' => $excelRow, 'alasan' => "level '{$levelRaw}' tidak valid. Harus salah satu dari: " . implode(', ', self::LEVELS) . '.'];
            }

            $mcFormasi = 0;
            if ($mcRaw !== null && trim((string) $mcRaw) !== '') {
                if (!is_numeric($mcRaw) || (float) $mcRaw < 0 || (float) $mcRaw != (int) $mcRaw) {
                    $errors[] = ['baris' => $excelRow, 'alasan' => "formasi '{$mcRaw}' harus angka bulat >= 0."];
                } else {
                    $mcFormasi = (int) $mcRaw;
                }
            }

            // Kalau kode duplikat, baris terakhir yang menang di sini — tidak masalah,
            // proses tetap diblokir karena error duplikat di atas sudah tercatat.
            $byKode[$kode] = [
                'baris'                 => $excelRow,
                'kode_sementara'        => $kode,
                'nama_unit'             => $nama,
                'level'                 => $level,
                'parent_kode_sementara' => $parentKode !== '' ? $parentKode : null,
                'mc_formasi'            => $mcFormasi,
            ];
        }

        foreach ($byKode as $kode => $r) {
            if ($r['parent_kode_sementara'] !== null && !isset($byKode[$r['parent_kode_sementara']])) {
                $errors[] = ['baris' => $r['baris'], 'alasan' => "parent_kode_sementara '{$r['parent_kode_sementara']}' tidak ditemukan di file ini."];
            }
        }

        foreach ($byKode as $kode => $r) {
            $visited = [];
            $current = $kode;
            while ($current !== null) {
                if (isset($visited[$current])) {
                    $errors[] = ['baris' => $r['baris'], 'alasan' => "parent_kode_sementara membentuk referensi melingkar (circular) melalui '{$current}'."];
                    break;
                }
                $visited[$current] = true;
                $parentKode = $byKode[$current]['parent_kode_sementara'] ?? null;
                if ($parentKode !== null && !isset($byKode[$parentKode])) {
                    break; // sudah dilaporkan sbg "tidak ditemukan" di atas
                }
                $current = $parentKode;
            }
        }

        usort($errors, fn ($a, $b) => $a['baris'] <=> $b['baris']);

        return ['errors' => $errors, 'rows' => array_values($byKode)];
    }

    /**
     * Validasi hierarki parent utk 1 payload units[] (dipakai store() & update()):
     * - parent harus row lain DI DALAM payload yang sama (otomatis berarti 1 versi yang sama,
     *   karena seluruh units[] di sini memang akan jadi snapshot 1 versi yang sama persis).
     * - level parent harus LEBIH TINGGI (rank lebih kecil di self::LEVELS) dari level unit ini —
     *   skip-level diperbolehkan (bagian boleh langsung ke direktorat), tapi tidak boleh setara
     *   atau lebih rendah.
     * - tidak boleh circular, baik langsung (parent = diri sendiri) maupun tidak langsung lewat
     *   rantai parent → parent → ... Catatan: aturan rank-lebih-tinggi di atas SEHARUSNYA sudah
     *   otomatis mencegah semua circular reference (rank naik ketat di tiap edge tidak mungkin
     *   kembali ke rank yang sama), tapi pengecekan rantai eksplisit tetap dijalankan sbg jaring
     *   pengaman independen, bukan cuma mengandalkan penalaran itu.
     *
     * @return string[] daftar pesan error (kosong = valid)
     */
    private function validateParentHierarchy(array $units): array
    {
        $errors = [];
        $byKey = collect($units)->keyBy('key');
        $levelRank = array_flip(self::LEVELS);

        foreach ($units as $row) {
            if (empty($row['parent_key'])) {
                continue;
            }

            $parentRow = $byKey->get($row['parent_key']);

            if (!$parentRow) {
                $errors[] = "Unit '{$row['nama_unit']}' ({$row['level']}): parent yang dipilih tidak ditemukan di roster versi ini.";
                continue;
            }

            if ($parentRow['key'] === $row['key']) {
                $errors[] = "Unit '{$row['nama_unit']}' ({$row['level']}): tidak boleh menjadi parent dari dirinya sendiri.";
                continue;
            }

            $rankSelf   = $levelRank[$row['level']] ?? null;
            $rankParent = $levelRank[$parentRow['level']] ?? null;

            if ($rankSelf === null || $rankParent === null) {
                continue;
            }

            if ($rankParent > $rankSelf) {
                $errors[] = "Unit '{$row['nama_unit']}' (level {$row['level']}): parent '{$parentRow['nama_unit']}' " .
                    "(level {$parentRow['level']}) harus levelnya lebih tinggi dari '{$row['level']}' pada urutan hierarki " .
                    '(' . implode(' > ', self::LEVELS) . '), bukan lebih rendah.';
            } elseif ($rankParent === $rankSelf) {
                // Pengecualian: same-level HANYA sah kalau parent itu sendiri root (tanpa parent) —
                // mengakomodasi struktur nyata "Utama" (direktorat, root) membawahi beberapa
                // Direktorat lain (direktorat juga) yg levelnya sama krn cuma 1 nilai enum utk
                // 2 tingkat riil. Di luar kasus ini, same-level tetap ditolak seperti biasa.
                $parentIsRoot = empty($parentRow['parent_key']);
                if (!$parentIsRoot) {
                    $errors[] = "Unit '{$row['nama_unit']}' (level {$row['level']}): parent '{$parentRow['nama_unit']}' " .
                        "levelnya setara ('{$parentRow['level']}'), ini hanya diperbolehkan kalau parent tsb adalah unit " .
                        "paling atas (root) tanpa parent sendiri. '{$parentRow['nama_unit']}' justru punya parent lain.";
                }
            }
        }

        foreach ($units as $row) {
            $visited = [];
            $current = $row['key'];
            while ($current !== null) {
                if (isset($visited[$current])) {
                    $errors[] = "Unit '{$row['nama_unit']}': terdeteksi referensi melingkar (circular) pada rantai parent.";
                    break;
                }
                $visited[$current] = true;
                $currentRow = $byKey->get($current);
                $current = $currentRow['parent_key'] ?? null;
            }
        }

        return $errors;
    }

    /** Susun baris preview import (urutan depth-first + total bawahan) dari data mentah kode_sementara, sebelum di-commit jadi model asli. */
    private function computeBaselinePreviewRows(Collection $rows): array
    {
        $byKode = $rows->keyBy('kode_sementara');
        $byParentKode = $rows->groupBy(fn ($r) => $r['parent_kode_sementara'] ?? '');
        $roots = $byParentKode->get('', collect())->sortBy('nama_unit')->values();

        $totalBawahan = function ($kode) use (&$totalBawahan, $byParentKode) {
            $anak = $byParentKode->get($kode, collect());
            if ($anak->isEmpty()) {
                return null;
            }
            $total = 0;
            foreach ($anak as $a) {
                $total += $a['mc_formasi'] + ($totalBawahan($a['kode_sementara']) ?? 0);
            }
            return $total;
        };

        $ordered = [];
        $walk = function ($row, $depth) use (&$walk, &$ordered, $byParentKode, $byKode, $totalBawahan) {
            $tb = $totalBawahan($row['kode_sementara']);
            $ordered[] = [
                'depth'         => $depth,
                'nama_unit'     => $row['nama_unit'],
                'level'         => $row['level'],
                'parent_nama'   => $row['parent_kode_sementara'] ? ($byKode[$row['parent_kode_sementara']]['nama_unit'] ?? '-') : '-',
                'mc_formasi'    => $row['mc_formasi'],
                'total_bawahan' => $tb,
                'grand_total'   => $row['mc_formasi'] + ($tb ?? 0),
            ];
            foreach ($byParentKode->get($row['kode_sementara'], collect())->sortBy('nama_unit') as $child) {
                $walk($child, $depth + 1);
            }
        };

        foreach ($roots as $root) {
            $walk($root, 0);
        }

        return $ordered;
    }

    // ============================================================
    // Import Versi Lanjutan dari Excel — fitur terpisah dari Import Baseline di atas.
    // Aktif kalau minimal ada 1 versi berstatus final. Alur sama (upload -> preview ->
    // confirm, data pending disimpan di session), tapi tiap baris di file bisa punya
    // jenis_transisi & referensi ke unit versi dasar yg dipilih, sehingga perlu resolusi
    // tambahan + deteksi bubar sebelum preview bisa dikonfirmasi.
    // ============================================================

    public function importLanjutanForm()
    {
        if (!StrukturOrganisasiVersi::where('status', 'final')->exists()) {
            return redirect()->route('organisasi.struktur.index')
                ->with('error', 'Import versi lanjutan hanya tersedia setelah minimal ada 1 versi berstatus final.');
        }

        return view('organisasi.struktur.import-lanjutan', [
            'rowErrors'         => [],
            'old'               => [],
            'versiFinalOptions' => StrukturOrganisasiVersi::where('status', 'final')->orderByDesc('tanggal_mulai_berlaku')->get(),
        ]);
    }

    public function importLanjutanUpload(Request $request)
    {
        if (!StrukturOrganisasiVersi::where('status', 'final')->exists()) {
            return redirect()->route('organisasi.struktur.index')
                ->with('error', 'Import versi lanjutan hanya tersedia setelah minimal ada 1 versi berstatus final.');
        }

        $versiFinalOptions = StrukturOrganisasiVersi::where('status', 'final')->orderByDesc('tanggal_mulai_berlaku')->get();

        $request->validate([
            'nomor_sk'              => 'required|string|max:100|unique:struktur_organisasi_versi,nomor_sk',
            'tanggal_sk'            => 'required|date',
            'tanggal_mulai_berlaku' => 'required|date|unique:struktur_organisasi_versi,tanggal_mulai_berlaku',
            'keterangan'            => 'nullable|string',
            'versi_dasar_id'        => 'required|integer|exists:struktur_organisasi_versi,id',
            'file'                  => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'File wajib dipilih.',
            'file.mimes'    => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        $versiDasar = StrukturOrganisasiVersi::find($request->integer('versi_dasar_id'));
        if (!$versiDasar || !$versiDasar->isFinal()) {
            return back()->withInput()->with('error', 'Versi Dasar yang dipilih tidak valid atau bukan versi final.');
        }

        $lastVersi = StrukturOrganisasiVersi::orderByDesc('tanggal_mulai_berlaku')->first();
        if ($lastVersi && Carbon::parse($request->tanggal_mulai_berlaku)->lte($lastVersi->tanggal_mulai_berlaku)) {
            return back()->withInput()->with('error',
                'Tanggal mulai berlaku harus setelah versi terakhir tercatat (' .
                $lastVersi->tanggal_mulai_berlaku->translatedFormat('d F Y') . ').');
        }

        try {
            $import = new StrukturOrganisasiLanjutanImport();
            $import->parse($request->file('file')->getRealPath());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        $rawRows = $import->rows ?? collect();

        if ($rawRows->isEmpty()) {
            return back()->withInput()->with('error',
                'File tidak berisi data, atau nama kolom header tidak sesuai template (harus ada: kode_sementara, ' .
                'nama_unit, level, parent_kode_sementara, formasi, jenis_transisi, unit_versi_sebelumnya, keterangan_transisi).');
        }

        $versiDasarSnapshots = $versiDasar->unitOrganisasiSnapshots()->get();

        ['errors' => $rowErrors, 'rows' => $resolvedRows, 'bubarCandidates' => $bubarCandidates, 'warnings' => $warnings]
            = $this->validateAndResolveLanjutanRows($rawRows, $versiDasarSnapshots);

        if (!empty($rowErrors)) {
            return view('organisasi.struktur.import-lanjutan', [
                'rowErrors'         => $rowErrors,
                'old'               => $request->only('nomor_sk', 'tanggal_sk', 'tanggal_mulai_berlaku', 'keterangan', 'versi_dasar_id'),
                'versiFinalOptions' => $versiFinalOptions,
            ]);
        }

        session(['lanjutan_import' => [
            'header'           => $request->only('nomor_sk', 'tanggal_sk', 'tanggal_mulai_berlaku', 'keterangan'),
            'versi_dasar_id'   => $versiDasar->id,
            'rows'             => $resolvedRows,
            'bubar_candidates' => $bubarCandidates,
            'warnings'         => $warnings,
        ]]);

        return redirect()->route('organisasi.struktur.import-lanjutan.preview');
    }

    public function importLanjutanPreview()
    {
        $data = session('lanjutan_import');
        if (!$data) {
            return redirect()->route('organisasi.struktur.import-lanjutan')
                ->with('error', 'Tidak ada data import yang menunggu konfirmasi. Silakan upload ulang.');
        }

        $rows       = collect($data['rows']);
        $versiDasar = StrukturOrganisasiVersi::find($data['versi_dasar_id']);

        $perluReview = $rows->filter(fn ($r) => $r['perlu_review'])->values();

        ['byParent' => $byParent, 'roots' => $roots, 'units' => $units, 'totals' => $totals] = $this->computeLanjutanPreviewTree($rows);

        $hierarkiErrors = $this->validateParentHierarchy($rows->map(fn ($r) => [
            'key'        => $r['kode_sementara'],
            'parent_key' => $r['parent_kode_sementara'],
            'nama_unit'  => $r['nama_unit'],
            'level'      => $r['level'],
        ])->all());

        $ringkasanTransisi = $rows->reject(fn ($r) => $r['perlu_review'])->groupBy('jenis_transisi')->map->count();

        return view('organisasi.struktur.import-lanjutan-preview', [
            'header'            => $data['header'],
            'versiDasar'        => $versiDasar,
            'totalUnit'         => $rows->count(),
            'ringkasanTransisi' => $ringkasanTransisi,
            'perluReview'       => $perluReview,
            'bubarCandidates'   => collect($data['bubar_candidates']),
            'hierarkiErrors'    => $hierarkiErrors,
            'warnings'          => collect($data['warnings'] ?? []),
            'byParent'          => $byParent,
            'roots'             => $roots,
            'units'             => $units,
            'totals'            => $totals,
        ]);
    }

    public function importLanjutanConfirm(Request $request)
    {
        $data = session('lanjutan_import');
        if (!$data) {
            return redirect()->route('organisasi.struktur.import-lanjutan')
                ->with('error', 'Tidak ada data import yang menunggu konfirmasi. Silakan upload ulang.');
        }

        $header = $data['header'];

        if (StrukturOrganisasiVersi::where('nomor_sk', $header['nomor_sk'])->exists()
            || StrukturOrganisasiVersi::where('tanggal_mulai_berlaku', $header['tanggal_mulai_berlaku'])->exists()) {
            session()->forget('lanjutan_import');
            return redirect()->route('organisasi.struktur.import-lanjutan')
                ->with('error', 'Nomor SK atau tanggal mulai berlaku ini sudah dipakai versi lain sejak proses import ini dimulai. Import dibatalkan, silakan mulai ulang.');
        }

        $rows = collect($data['rows']);

        $anggapBaru = collect($request->input('anggap_baru', []))->filter()->keys()->all();

        $belumResolve = $rows->filter(fn ($r) => $r['perlu_review'] && !in_array($r['kode_sementara'], $anggapBaru, true));
        if ($belumResolve->isNotEmpty()) {
            return redirect()->route('organisasi.struktur.import-lanjutan.preview')
                ->with('error', 'Masih ada ' . $belumResolve->count() . ' baris PERLU_REVIEW yang belum diselesaikan ' .
                    '(centang "Anggap Baru" atau upload ulang dengan jenis_transisi eksplisit): ' .
                    $belumResolve->pluck('nama_unit')->implode(', '));
        }

        $hierarkiErrors = $this->validateParentHierarchy($rows->map(fn ($r) => [
            'key'        => $r['kode_sementara'],
            'parent_key' => $r['parent_kode_sementara'],
            'nama_unit'  => $r['nama_unit'],
            'level'      => $r['level'],
        ])->all());
        if (!empty($hierarkiErrors)) {
            return redirect()->route('organisasi.struktur.import-lanjutan.preview')
                ->with('error', 'Hierarki parent belum valid: ' . implode(' | ', $hierarkiErrors));
        }

        $bubarChecked    = collect($request->input('bubar', []))->map(fn ($v) => (int) $v)->all();
        $bubarCandidates = collect($data['bubar_candidates'])->keyBy('unit_organisasi_id');
        $bubarConfirmed  = $bubarCandidates->only($bubarChecked);

        $ringkasan = ['lanjut' => 0, 'rename' => 0, 'pindah_induk' => 0, 'ganti_level' => 0, 'pecah' => 0, 'gabung' => 0, 'baru' => 0, 'bubar' => $bubarConfirmed->count()];

        $versi = DB::transaction(function () use ($rows, $header, $bubarConfirmed, &$ringkasan) {
            $versi = StrukturOrganisasiVersi::create([
                'nomor_sk'              => $header['nomor_sk'],
                'tanggal_sk'            => $header['tanggal_sk'],
                'tanggal_mulai_berlaku' => $header['tanggal_mulai_berlaku'],
                'keterangan'            => $header['keterangan'] ?? null,
                'status'                => 'draft',
                'created_by'            => Auth::id(),
            ]);

            // 1) Identitas unit_organisasi tiap baris: pakai yg lama kalau sudah resolve
            //    (lanjut/rename/pindah_induk), buat baru utk sisanya (baru/pecah-target/
            //    gabung-target/perlu_review yg dikonfirmasi "anggap baru" di preview).
            $kodeToUnitId = [];
            foreach ($rows as $r) {
                $kodeToUnitId[$r['kode_sementara']] = $r['resolved_unit_organisasi_id'] ?? UnitOrganisasi::create()->id;
            }

            // 2) Snapshot roster versi baru
            foreach ($rows as $r) {
                UnitOrganisasiSnapshot::create([
                    'unit_organisasi_id'           => $kodeToUnitId[$r['kode_sementara']],
                    'struktur_organisasi_versi_id' => $versi->id,
                    'nama_unit'                    => $r['nama_unit'],
                    'level'                        => $r['level'],
                    'parent_unit_organisasi_id'    => $r['parent_kode_sementara'] ? ($kodeToUnitId[$r['parent_kode_sementara']] ?? null) : null,
                    'mc_formasi'                   => $r['mc_formasi'],
                ]);
            }

            // 3) Transisi per baris (baris "perlu_review" yg lolos ke sini sudah pasti
            //    dikonfirmasi "anggap baru" di langkah validasi confirm() di atas)
            foreach ($rows as $r) {
                $jenis  = $r['perlu_review'] ? 'baru' : $r['jenis_transisi'];
                $unitId = $kodeToUnitId[$r['kode_sementara']];

                if ($jenis === 'lanjut') {
                    $ringkasan['lanjut']++;
                    continue; // implicit, tidak dicatat di tabel transisi
                }

                if (in_array($jenis, self::TRANSISI_IDENTITAS_LANJUT, true)) {
                    UnitOrganisasiTransisi::create([
                        'struktur_organisasi_versi_id' => $versi->id,
                        'jenis_transisi'                => $jenis,
                        'unit_asal_id'                   => $r['unit_asal_ids'][0] ?? $unitId,
                        'unit_baru_id'                   => null,
                        'keterangan'                     => $r['keterangan_transisi'],
                    ]);
                    $ringkasan[$jenis]++;
                    continue;
                }

                if ($jenis === 'pecah') {
                    UnitOrganisasiTransisi::create([
                        'struktur_organisasi_versi_id' => $versi->id,
                        'jenis_transisi'                => 'pecah',
                        'unit_asal_id'                   => $r['unit_asal_ids'][0] ?? null,
                        'unit_baru_id'                   => $unitId,
                        'keterangan'                     => $r['keterangan_transisi'],
                    ]);
                    $ringkasan['pecah']++;
                    continue;
                }

                if ($jenis === 'gabung') {
                    foreach ($r['unit_asal_ids'] as $asalId) {
                        UnitOrganisasiTransisi::create([
                            'struktur_organisasi_versi_id' => $versi->id,
                            'jenis_transisi'                => 'gabung',
                            'unit_asal_id'                   => $asalId,
                            'unit_baru_id'                   => $unitId,
                            'keterangan'                     => $r['keterangan_transisi'],
                        ]);
                    }
                    $ringkasan['gabung']++;
                    continue;
                }

                // baru (termasuk perlu_review yg dikonfirmasi "anggap baru")
                UnitOrganisasiTransisi::create([
                    'struktur_organisasi_versi_id' => $versi->id,
                    'jenis_transisi'                => 'baru',
                    'unit_asal_id'                   => null,
                    'unit_baru_id'                   => $unitId,
                    'keterangan'                     => $r['keterangan_transisi'],
                ]);
                $ringkasan['baru']++;
            }

            // 4) Bubar yang dikonfirmasi via checkbox di preview
            foreach ($bubarConfirmed as $b) {
                UnitOrganisasiTransisi::create([
                    'struktur_organisasi_versi_id' => $versi->id,
                    'jenis_transisi'                => 'bubar',
                    'unit_asal_id'                   => $b['unit_organisasi_id'],
                    'unit_baru_id'                   => null,
                ]);
            }

            return $versi;
        });

        session()->forget('lanjutan_import');

        $this->log('import', 'Struktur Organisasi (Versi)', $versi->nomor_sk,
            'Import versi lanjutan dari Excel: ' . $rows->count() . ' unit di roster · ' .
            "lanjut {$ringkasan['lanjut']}, rename {$ringkasan['rename']}, pindah_induk {$ringkasan['pindah_induk']}, " .
            "ganti_level {$ringkasan['ganti_level']}, pecah {$ringkasan['pecah']}, gabung {$ringkasan['gabung']}, " .
            "baru {$ringkasan['baru']}, bubar {$ringkasan['bubar']}");

        return redirect()->route('organisasi.struktur.show', $versi)
            ->with('success', 'Import versi lanjutan berhasil disimpan sebagai draft.');
    }

    /**
     * Validasi + resolusi baris mentah hasil parse Excel utk import versi lanjutan:
     * 1) validasi struktural per baris (sama pola dgn validateBaselineRows): kode_sementara
     *    wajib & unik, level valid, parent_kode_sementara resolve dlm file yg sama / kosong
     *    utk root, tidak circular, jenis_transisi harus salah satu enum yg dikenal atau
     *    kosong. Kalau ada pelanggaran di tahap ini, proses berhenti di sini (tidak lanjut
     *    ke resolusi transisi) & rows dikembalikan kosong — user harus upload ulang.
     * 2) utk baris jenis_transisi terisi: resolve unit_versi_sebelumnya thd snapshot Versi
     *    Dasar (match nama_unit, trim+case-insensitive) sesuai aturan per jenis (rename/
     *    pindah_induk/pecah/ganti_level -> tepat 1 match; gabung -> tiap nama dipisah koma
     *    tepat 1 match, min 2 nama; baru -> unit_versi_sebelumnya harus kosong; bubar ->
     *    ditolak, unit bubar tidak direpresentasikan sbg baris, cukup dihilangkan dari file).
     *    Kalau nama_unit sendiri tidak unik di Versi Dasar (mis. Direktorat "Komersil" vs
     *    Fungsional "Komersil", atau "Bengkel Listrik" seksi+foreman dlm 1 rantai), level
     *    baris yg diproses dipakai sbg disambiguator lewat resolveVersiDasarUnitReference()
     *    — kecuali kolom opsional unit_versi_sebelumnya_level diisi, itu yg dipakai sbg
     *    pengganti (wajib dipakai kalau jenis_transisi='ganti_level', krn level baris baru
     *    otomatis beda dr level unit lama yg dirujuk). Lihat method itu utk detail aturan
     *    match/warning/error-nya (termasuk warning non-blocking kalau match nama cuma 1 tapi
     *    levelnya beda). 'ganti_level' & 'rename'/'pindah_induk' sama-sama mempertahankan
     *    identitas unit_organisasi lama (lihat self::TRANSISI_IDENTITAS_LANJUT).
     * 3) utk baris jenis_transisi kosong: auto-match thd snapshot Versi Dasar by (nama_unit,
     *    level) persis — sama spt poin 2, level sudah jadi bagian dari kunci pencarian sejak
     *    awal di sini (bukan cuma disambiguator tambahan), krn tidak ada info jenis_transisi
     *    lain yg bisa dipakai. Match=1 -> auto-resolved lanjut. Match=0 atau match>1 -> BUKAN
     *    error keras, ditandai perlu_review (tetap lolos ke preview, diblokir di sana lewat
     *    checkbox "anggap baru" per baris, default unchecked).
     * Terakhir, unit Versi Dasar yg tidak pernah ke-cover baik sbg lanjut maupun sbg sumber
     * transisi manapun dikumpulkan sbg Kandidat Bubar.
     *
     * @return array{errors: array, rows: array, bubarCandidates: array, warnings: array}
     */
    private function validateAndResolveLanjutanRows(Collection $rawRows, Collection $versiDasarSnapshots): array
    {
        $errors = [];
        $seenKode = [];
        $byKode = [];

        foreach ($rawRows as $i => $row) {
            $excelRow = $i + 2;

            $allBlank = collect($row)->every(fn ($v) => $v === null || trim((string) $v) === '');
            if ($allBlank) {
                continue;
            }

            $kode        = trim((string) ($row['kode_sementara'] ?? ''));
            $nama        = trim((string) ($row['nama_unit'] ?? ''));
            $levelRaw    = (string) ($row['level'] ?? '');
            $level       = strtolower(trim($levelRaw));
            $parentKode  = trim((string) ($row['parent_kode_sementara'] ?? ''));
            $mcRaw       = $row['mc_formasi'] ?? null;
            $jenisRaw    = trim((string) ($row['jenis_transisi'] ?? ''));
            $jenis       = strtolower($jenisRaw);
            $unitSebelum = trim((string) ($row['unit_versi_sebelumnya'] ?? ''));
            $ketTransisi = trim((string) ($row['keterangan_transisi'] ?? ''));
            $refLevelRaw = trim((string) ($row['unit_versi_sebelumnya_level'] ?? ''));
            $refLevel    = strtolower($refLevelRaw);

            if ($kode === '') {
                $errors[] = ['baris' => $excelRow, 'alasan' => 'kode_sementara kosong.'];
                continue;
            }

            if (isset($seenKode[$kode])) {
                $errors[] = ['baris' => $excelRow, 'alasan' => "kode_sementara '{$kode}' duplikat (sudah dipakai baris {$seenKode[$kode]})."];
            } else {
                $seenKode[$kode] = $excelRow;
            }

            if ($nama === '') {
                $errors[] = ['baris' => $excelRow, 'alasan' => 'nama_unit kosong.'];
            }

            if (!in_array($level, self::LEVELS, true)) {
                $errors[] = ['baris' => $excelRow, 'alasan' => "level '{$levelRaw}' tidak valid. Harus salah satu dari: " . implode(', ', self::LEVELS) . '.'];
            }

            $mcFormasi = 0;
            if ($mcRaw !== null && trim((string) $mcRaw) !== '') {
                if (!is_numeric($mcRaw) || (float) $mcRaw < 0 || (float) $mcRaw != (int) $mcRaw) {
                    $errors[] = ['baris' => $excelRow, 'alasan' => "formasi '{$mcRaw}' harus angka bulat >= 0."];
                } else {
                    $mcFormasi = (int) $mcRaw;
                }
            }

            if ($jenisRaw !== '' && !in_array($jenis, self::TRANSISI_LANJUTAN_ENUM, true)) {
                $errors[] = ['baris' => $excelRow, 'alasan' => "jenis_transisi '{$jenisRaw}' tidak valid. Harus kosong atau salah satu dari: " . implode(', ', self::TRANSISI_LANJUTAN_ENUM) . '.'];
            }

            if ($refLevelRaw !== '' && !in_array($refLevel, self::LEVELS, true)) {
                $errors[] = ['baris' => $excelRow, 'alasan' => "unit_versi_sebelumnya_level '{$refLevelRaw}' tidak valid. Harus kosong atau salah satu dari: " . implode(', ', self::LEVELS) . '.'];
            }

            $byKode[$kode] = [
                'baris'                 => $excelRow,
                'kode_sementara'        => $kode,
                'nama_unit'             => $nama,
                'level'                 => $level,
                'parent_kode_sementara' => $parentKode !== '' ? $parentKode : null,
                'mc_formasi'            => $mcFormasi,
                'jenis_transisi_raw'    => $jenisRaw !== '' ? $jenis : null,
                'unit_versi_sebelumnya' => $unitSebelum,
                'unit_versi_sebelumnya_level' => $refLevelRaw !== '' ? $refLevel : null,
                'keterangan_transisi'   => $ketTransisi !== '' ? $ketTransisi : null,
            ];
        }

        foreach ($byKode as $r) {
            if ($r['parent_kode_sementara'] !== null && !isset($byKode[$r['parent_kode_sementara']])) {
                $errors[] = ['baris' => $r['baris'], 'alasan' => "parent_kode_sementara '{$r['parent_kode_sementara']}' tidak ditemukan di file ini."];
            }
        }

        foreach ($byKode as $kode => $r) {
            $visited = [];
            $current = $kode;
            while ($current !== null) {
                if (isset($visited[$current])) {
                    $errors[] = ['baris' => $r['baris'], 'alasan' => "parent_kode_sementara membentuk referensi melingkar (circular) melalui '{$current}'."];
                    break;
                }
                $visited[$current] = true;
                $parentKode = $byKode[$current]['parent_kode_sementara'] ?? null;
                if ($parentKode !== null && !isset($byKode[$parentKode])) {
                    break; // sudah dilaporkan sbg "tidak ditemukan" di atas
                }
                $current = $parentKode;
            }
        }

        if (!empty($errors)) {
            usort($errors, fn ($a, $b) => $a['baris'] <=> $b['baris']);
            return ['errors' => $errors, 'rows' => [], 'bubarCandidates' => [], 'warnings' => []];
        }

        // --- Resolusi transisi terhadap Versi Dasar ---
        $normalize = fn ($s) => mb_strtolower(trim((string) $s));

        $versiDasarByName      = $versiDasarSnapshots->groupBy(fn ($s) => $normalize($s->nama_unit));
        $versiDasarByNameLevel = $versiDasarSnapshots->groupBy(fn ($s) => $normalize($s->nama_unit) . '|' . $s->level);
        $versiDasarNamaById    = $versiDasarSnapshots->pluck('nama_unit', 'unit_organisasi_id');

        $matchedLanjutIds = [];
        $consumedAsalIds  = [];
        $warnings         = [];

        foreach ($byKode as $kode => &$r) {
            $jenis = $r['jenis_transisi_raw'];

            if ($jenis === null) {
                $key     = $normalize($r['nama_unit']) . '|' . $r['level'];
                $matches = $versiDasarByNameLevel->get($key, collect());

                if ($matches->count() === 1) {
                    $unitId = $matches->first()->unit_organisasi_id;
                    $r['jenis_transisi']               = 'lanjut';
                    $r['unit_asal_ids']                 = [$unitId];
                    $r['resolved_unit_organisasi_id']   = $unitId;
                    $r['perlu_review']                  = false;
                    $matchedLanjutIds[] = $unitId;
                } else {
                    $r['jenis_transisi']               = null;
                    $r['unit_asal_ids']                 = [];
                    $r['resolved_unit_organisasi_id']   = null;
                    $r['perlu_review']                  = true;
                    $r['perlu_review_reason']           = $matches->count() === 0 ? 'tidak_ada_match' : 'ambigu';
                }
                continue;
            }

            $r['perlu_review'] = false;

            if ($jenis === 'bubar') {
                $errors[] = ['baris' => $r['baris'], 'alasan' => "jenis_transisi 'bubar' tidak dipakai di kolom ini — unit yang bubar cukup dihilangkan dari file, akan terdeteksi otomatis sebagai Kandidat Bubar saat preview."];
                $r['jenis_transisi'] = 'bubar';
                $r['unit_asal_ids']  = [];
                $r['resolved_unit_organisasi_id'] = null;
                continue;
            }

            if ($jenis === 'baru') {
                if ($r['unit_versi_sebelumnya'] !== '') {
                    $errors[] = ['baris' => $r['baris'], 'alasan' => "jenis_transisi 'baru' tidak boleh mengisi unit_versi_sebelumnya (isi saat ini: '{$r['unit_versi_sebelumnya']}')."];
                }
                $r['jenis_transisi'] = 'baru';
                $r['unit_asal_ids']  = [];
                $r['resolved_unit_organisasi_id'] = null;
                continue;
            }

            if ($r['unit_versi_sebelumnya'] === '') {
                $errors[] = ['baris' => $r['baris'], 'alasan' => "jenis_transisi '{$jenis}' wajib mengisi unit_versi_sebelumnya."];
                $r['jenis_transisi'] = $jenis;
                $r['unit_asal_ids']  = [];
                $r['resolved_unit_organisasi_id'] = null;
                continue;
            }

            if ($jenis === 'gabung') {
                $namaList = $this->splitGabungNames($r['unit_versi_sebelumnya'], $versiDasarByName);
                if (count($namaList) < 2) {
                    $errors[] = ['baris' => $r['baris'], 'alasan' => "jenis_transisi 'gabung' butuh minimal 2 nama di unit_versi_sebelumnya (dipisah koma)."];
                }
                $asalIds = [];
                foreach ($namaList as $nama) {
                    // Catatan: 1 kolom unit_versi_sebelumnya_level per baris dipakai sbg override
                    // yg SAMA utk tiap nama di daftar gabung ini (bukan per-nama individual) —
                    // cukup utk kasus umum, tapi kalau sumber gabung py level LAMA yg beda-beda
                    // DAN salah satunya juga ambigu, override ini tidak bisa membedakan per-nama.
                    $resolved = $this->resolveVersiDasarUnitReference($nama, $r['level'], $versiDasarByName, $versiDasarNamaById, $r['unit_versi_sebelumnya_level'] ?? null);
                    if ($resolved['status'] === 'error') {
                        $errors[] = ['baris' => $r['baris'], 'alasan' => $resolved['message']];
                        continue;
                    }
                    if ($resolved['status'] === 'warning') {
                        $warnings[] = ['baris' => $r['baris'], 'pesan' => $resolved['message']];
                    }
                    $asalIds[] = $resolved['unit_organisasi_id'];
                }
                $r['jenis_transisi'] = 'gabung';
                $r['unit_asal_ids']  = $asalIds;
                $r['resolved_unit_organisasi_id'] = null;
                foreach ($asalIds as $id) {
                    $consumedAsalIds[] = $id;
                }
                continue;
            }

            // rename / pindah_induk / pecah / ganti_level: harus resolve ke TEPAT 1 unit
            $resolved = $this->resolveVersiDasarUnitReference($r['unit_versi_sebelumnya'], $r['level'], $versiDasarByName, $versiDasarNamaById, $r['unit_versi_sebelumnya_level'] ?? null);
            if ($resolved['status'] === 'error') {
                $errors[] = ['baris' => $r['baris'], 'alasan' => $resolved['message'] . " (jenis_transisi '{$jenis}')."];
                $r['jenis_transisi'] = $jenis;
                $r['unit_asal_ids']  = [];
                $r['resolved_unit_organisasi_id'] = null;
                continue;
            }
            if ($resolved['status'] === 'warning') {
                $warnings[] = ['baris' => $r['baris'], 'pesan' => $resolved['message'] . " (jenis_transisi '{$jenis}')."];
            }

            $asalId = $resolved['unit_organisasi_id'];
            $r['jenis_transisi'] = $jenis;
            $r['unit_asal_ids']  = [$asalId];
            $consumedAsalIds[] = $asalId;
            // rename/pindah_induk/ganti_level: identitas lama TETAP dipakai (bukan identitas
            // baru); pecah tidak masuk sini krn tiap target pecah butuh identitas baru sendiri.
            $r['resolved_unit_organisasi_id'] = in_array($jenis, self::TRANSISI_IDENTITAS_LANJUT, true) ? $asalId : null;
        }
        unset($r);

        // Deteksi identitas unit Versi Dasar yang "direbut" lebih dari 1 baris sekaligus
        // (baik lewat auto-match 'lanjut' maupun eksplisit rename/pindah_induk/ganti_level).
        // validateParentHierarchy() cuma cek circular lewat rantai kode_sementara (yang
        // selalu unik per baris) — ia buta terhadap duplikasi macam ini, padahal
        // computeLanjutanPreviewTree() membangun tree pakai unit_organisasi_id HASIL RESOLUSI
        // sbg key node. Kalau 2 baris berbeda berbagi 1 id, dan salah satunya (langsung/
        // transitif) jadi parent baris lainnya di roster baru, graph parent-child jadi
        // CIRCULAR (node jadi leluhurnya sendiri) -> rekursi tak terbatas di <x-org-tree-node>
        // saat render preview (reproduksi nyata: 2 baris rename dari unit lama yang sama ->
        // memory exhausted / timeout 30 detik di totalFormasiBawahan()).
        collect($byKode)
            ->filter(fn ($r) => !empty($r['resolved_unit_organisasi_id']))
            ->groupBy('resolved_unit_organisasi_id')
            ->filter(fn ($g) => $g->count() > 1)
            ->each(function ($group, $unitId) use (&$errors, $versiDasarNamaById) {
                $namaLama = $versiDasarNamaById[$unitId] ?? "id={$unitId}";
                $daftarBaris = $group->map(fn ($r) => "baris {$r['baris']} ('{$r['nama_unit']}', {$r['jenis_transisi']})")->implode(', ');
                $errors[] = [
                    'baris' => $group->first()['baris'],
                    'alasan' => "unit_versi_sebelumnya '{$namaLama}' dirujuk sbg identitas lanjut oleh lebih dari 1 baris ({$daftarBaris}) — tiap unit Versi Dasar cuma boleh dilanjutkan TEPAT 1 baris (lanjut/rename/pindah_induk/ganti_level). Kalau unit ini memang terbagi jadi beberapa unit baru, ubah jenis_transisi jadi 'pecah'.",
                ];
            });

        if (!empty($errors)) {
            usort($errors, fn ($a, $b) => $a['baris'] <=> $b['baris']);
            return ['errors' => $errors, 'rows' => [], 'bubarCandidates' => [], 'warnings' => []];
        }

        $covered = array_unique(array_merge($matchedLanjutIds, $consumedAsalIds));
        $bubarCandidates = $versiDasarSnapshots
            ->reject(fn ($s) => in_array($s->unit_organisasi_id, $covered, true))
            ->map(fn ($s) => ['unit_organisasi_id' => $s->unit_organisasi_id, 'nama_unit' => $s->nama_unit, 'level' => $s->level])
            ->sortBy('nama_unit')
            ->values()
            ->all();

        return ['errors' => [], 'rows' => array_values($byKode), 'bubarCandidates' => $bubarCandidates, 'warnings' => $warnings];
    }

    /**
     * Pisah isi kolom unit_versi_sebelumnya utk gabung (beberapa nama dipisah koma) MENJADI
     * daftar nama individual — TIDAK cukup pakai explode(',', ...) polos, krn sejumlah nama
     * unit ASLI di Versi Dasar sendiri mengandung koma di dalamnya (mis. "Penjagaan Shift
     * (A,B,C,D)" atau "Rendal Listrik, Instrumen & Bhn Penolong") — kalau dipotong di SETIAP
     * koma, nama begitu pecah jadi beberapa fragmen palsu yg tidak akan pernah ketemu di Versi
     * Dasar (ditemukan nyata saat import data Jan 2021, baris 130 & 307).
     *
     * Strategi: greedy LONGEST-MATCH thd daftar nama asli yg ada di Versi Dasar
     * ($versiDasarByName). Dari posisi awal, coba potongan sepanjang mungkin dulu (sampai
     * koma paling akhir, mundur ke koma sebelumnya, dst) — begitu 1 potongan PERSIS cocok
     * dgn 1 nama asli (trim+case-insensitive), itu yg dipakai sbg 1 nama utuh, lanjut dari
     * situ. Kalau sampai potongan terpendek (sampai koma pertama) pun tidak ada yg cocok,
     * ambil potongan terpendek apa adanya (spy tetap dilaporkan "tidak ditemukan" sbg error
     * yg jelas oleh resolveVersiDasarUnitReference(), bukan diam-diam salah/hilang).
     *
     * Beroperasi di atas SUBSTRING string asli (bukan gabung ulang token dgn separator
     * tetap) supaya spasi/format asli di dalam nama yg mengandung koma tetap presisi persis
     * seperti tersimpan di Versi Dasar.
     *
     * @return string[] daftar nama individual (belum divalidasi ketemu/tidaknya — itu tugas
     *                   resolveVersiDasarUnitReference() per nama, dipanggil stlh ini)
     */
    private function splitGabungNames(string $raw, Collection $versiDasarByName): array
    {
        $normalize = fn ($s) => mb_strtolower(trim((string) $s));

        $commaPositions = [];
        $offset = 0;
        while (($pos = strpos($raw, ',', $offset)) !== false) {
            $commaPositions[] = $pos;
            $offset = $pos + 1;
        }

        $len = strlen($raw);
        $result = [];
        $start = 0;

        while ($start < $len) {
            $cutCandidates = array_values(array_filter($commaPositions, fn ($p) => $p > $start));
            $cutCandidates[] = $len; // akhir string juga kandidat potongan terakhir

            $chosenCut = null;
            foreach (array_reverse($cutCandidates) as $cut) {
                $piece = trim(substr($raw, $start, $cut - $start));
                if ($piece !== '' && $versiDasarByName->has($normalize($piece))) {
                    $chosenCut = $cut;
                    break;
                }
            }

            // Tidak ada kandidat potongan manapun (pendek maupun panjang) yg cocok nama asli
            // -> ambil potongan TERPENDEK (sampai koma pertama), spy tetap error yg predictable
            // & jelas (nama "X" tidak ditemukan), bukan diam-diam menelan sisa string.
            if ($chosenCut === null) {
                $chosenCut = $cutCandidates[0] ?? $len;
            }

            $piece = trim(substr($raw, $start, $chosenCut - $start));
            if ($piece !== '') {
                $result[] = $piece;
            }
            $start = $chosenCut + 1; // lewati karakter koma pemisah
        }

        return $result;
    }

    /**
     * Resolusi 1 nama di unit_versi_sebelumnya terhadap snapshot Versi Dasar, dgn nama_unit
     * sbg kunci pencarian utama tapi level dipakai sbg disambiguator kalau nama_unit-nya
     * sendiri tidak unik (mis. Direktorat "Komersil" vs Fungsional "Komersil" sama-sama ada
     * di Versi Dasar, atau "Bengkel Listrik" seksi+foreman dlm 1 rantai parent-anak):
     *
     * Level yg dipakai utk disambiguasi ($effectiveLevel) defaultnya level baris Excel yg
     * diproses ($rowLevel) — tapi kalau kolom opsional unit_versi_sebelumnya_level diisi
     * ($refLevelOverride), itu yg dipakai sbg pengganti. Override ini penting utk kasus
     * jenis_transisi='ganti_level' (level baris BARU otomatis beda dr level unit LAMA yg
     * dirujuk, jadi $rowLevel tidak bisa dipakai utk mencari unit lama itu sendiri) atau kapan
     * pun user perlu menunjuk eksplisit level lama yg dimaksud drpd mengandalkan tebakan dari
     * level baris baru.
     *
     * - 0 match nama -> error "tidak ditemukan".
     * - 1 match nama, level match jg -> ok.
     * - 1 match nama, level BEDA -> tetap dipakai (kandidatnya cuma itu²nya juga), tapi
     *   ditandai warning (bukan error) supaya user sadar levelnya berubah, siapa tau memang
     *   disengaja.
     * - >1 match nama -> coba disempitkan pakai $effectiveLevel; kalau hasil penyempitan itu
     *   PERSIS 1 -> ok (level berhasil jadi disambiguator). Kalau masih >1 (nama+level sama
     *   persis, 2+ unit) ATAU malah 0 (tidak ada yg levelnya cocok di antara kandidat nama yg
     *   >1 itu) -> tetap error ambigu, tampilkan detail tiap kandidat (id, level, parent).
     *
     * @return array{status: 'ok'|'warning'|'error', unit_organisasi_id: int|null, message: string|null}
     */
    private function resolveVersiDasarUnitReference(string $nama, string $rowLevel, Collection $versiDasarByName, Collection $versiDasarNamaById, ?string $refLevelOverride = null): array
    {
        $effectiveLevel = $refLevelOverride ?? $rowLevel;

        $normalize = fn ($s) => mb_strtolower(trim((string) $s));
        $nameMatches = $versiDasarByName->get($normalize($nama), collect());

        if ($nameMatches->isEmpty()) {
            return ['status' => 'error', 'unit_organisasi_id' => null, 'message' => "unit_versi_sebelumnya '{$nama}' tidak ditemukan di Versi Dasar."];
        }

        if ($nameMatches->count() === 1) {
            $candidate = $nameMatches->first();
            if ($candidate->level !== $effectiveLevel) {
                return [
                    'status'             => 'warning',
                    'unit_organisasi_id' => $candidate->unit_organisasi_id,
                    'message'            => "unit_versi_sebelumnya '{$nama}': ditemukan match nama tapi level berbeda " .
                        "(level lama: {$candidate->level}, level " . ($refLevelOverride ? 'rujukan (unit_versi_sebelumnya_level)' : 'baris baru') . ": {$effectiveLevel}) — pastikan ini perubahan level yang disengaja.",
                ];
            }
            return ['status' => 'ok', 'unit_organisasi_id' => $candidate->unit_organisasi_id, 'message' => null];
        }

        // >1 match nama_unit: coba disambiguasi pakai $effectiveLevel
        $levelFiltered = $nameMatches->filter(fn ($s) => $s->level === $effectiveLevel)->values();

        if ($levelFiltered->count() === 1) {
            return ['status' => 'ok', 'unit_organisasi_id' => $levelFiltered->first()->unit_organisasi_id, 'message' => null];
        }

        // Masih ambigu: >1 match di level yg sama, ATAU 0 match persis di level ini di antara
        // >1 kandidat nama yg ada (tidak aman ditebak salah satu) -> tampilkan detail lengkap.
        $detailSource = $levelFiltered->isNotEmpty() ? $levelFiltered : $nameMatches;
        $detail = $detailSource->map(function ($s) use ($versiDasarNamaById) {
            $parentNama = $s->parent_unit_organisasi_id ? ($versiDasarNamaById[$s->parent_unit_organisasi_id] ?? '-') : '(root)';
            return "id={$s->unit_organisasi_id}, level={$s->level}, parent={$parentNama}";
        })->implode(' | ');

        $saran = $refLevelOverride
            ? ''
            : ' Isi kolom unit_versi_sebelumnya_level dgn salah satu level kandidat di atas kalau level baris ini sendiri tidak cukup membedakan (mis. levelnya ikut berubah / jenis_transisi=ganti_level).';

        return [
            'status'             => 'error',
            'unit_organisasi_id' => null,
            'message'            => "unit_versi_sebelumnya '{$nama}' ambigu (lebih dari 1 unit cocok nama) di Versi Dasar — kandidat: {$detail}.{$saran}",
        ];
    }

    /**
     * Bangun koleksi UnitOrganisasiSnapshot TRANSIEN (belum disimpan) dari baris hasil
     * resolusi import lanjutan, supaya bisa dipakai ulang oleh komponen <x-org-tree-node>
     * & totalFormasiBawahan() yang sama dgn halaman tree/show biasa, sebelum commit ke DB.
     * Baris yang belum py identitas nyata (baru/pecah-target/gabung-target/perlu_review)
     * diberi id sintetis negatif sekadar utk linking parent/anak yg konsisten di preview —
     * diganti id asli nyata saat commit lewat UnitOrganisasi::create().
     */
    private function computeLanjutanPreviewTree(Collection $rows): array
    {
        $kodeToUnitId = [];
        $synthetic = -1;
        foreach ($rows as $r) {
            $kodeToUnitId[$r['kode_sementara']] = $r['resolved_unit_organisasi_id'] ?? $synthetic--;
        }

        $units = $rows->map(fn ($r) => new UnitOrganisasiSnapshot([
            'unit_organisasi_id'        => $kodeToUnitId[$r['kode_sementara']],
            'nama_unit'                  => $r['nama_unit'],
            'level'                      => $r['level'],
            'parent_unit_organisasi_id'  => $r['parent_kode_sementara'] ? ($kodeToUnitId[$r['parent_kode_sementara']] ?? null) : null,
            'mc_formasi'                 => $r['mc_formasi'],
        ]))->values();

        $byParent = $units->groupBy('parent_unit_organisasi_id');
        $roots    = $byParent->get(null, collect())->values();
        $totals   = UnitOrganisasiSnapshot::totalFormasiBawahanBatch($units);

        return ['byParent' => $byParent, 'roots' => $roots, 'units' => $units, 'totals' => $totals];
    }

    public function show(StrukturOrganisasiVersi $versi)
    {
        $units = $versi->unitOrganisasiSnapshots()
            ->orderBy('level')
            ->orderBy('nama_unit')
            ->get();

        $namaByUnitId = $units->pluck('nama_unit', 'unit_organisasi_id');
        $totals       = UnitOrganisasiSnapshot::totalFormasiBawahanBatch($units);

        $isBaseline = StrukturOrganisasiVersi::where('tanggal_mulai_berlaku', '<', $versi->tanggal_mulai_berlaku)->doesntExist();

        $ringkasanTransisi = $isBaseline
            ? collect()
            : $versi->unitOrganisasiTransisis()
                ->selectRaw('jenis_transisi, COUNT(*) as total')
                ->groupBy('jenis_transisi')
                ->pluck('total', 'jenis_transisi');

        return view('organisasi.struktur.show', compact('versi', 'units', 'namaByUnitId', 'totals', 'isBaseline', 'ringkasanTransisi'));
    }

    public function exportExcel(StrukturOrganisasiVersi $versi)
    {
        $filename = 'struktur-organisasi-' . Str::slug($versi->nomor_sk) . '.xlsx';

        $this->log('export', 'Struktur Organisasi (Versi)', $versi->nomor_sk, 'Export Excel');

        return Excel::download(new StrukturOrganisasiVersiExport($versi), $filename);
    }

    public function exportPdf(StrukturOrganisasiVersi $versi)
    {
        // dompdf membangun seluruh frame tree di memori sebelum reflow — utk versi besar
        // (ratusan unit) 128M default bisa kurang meski HTML/CSS sudah dioptimasi (table-layout
        // fixed, dst di pdf.blade.php). Naikkan jaring pengaman utk request export ini saja,
        // sama seperti pola @set_time_limit() yg sudah dipakai utk export besar lain di project.
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        $units        = $versi->unitOrganisasiSnapshots()->get();
        $namaByUnitId = $units->pluck('nama_unit', 'unit_organisasi_id');
        $byParent     = $units->groupBy('parent_unit_organisasi_id');
        $roots        = $byParent->get(null, collect())->values()->sortBy('nama_unit');

        // Urutan depth-first (bukan flat alfabetis) supaya hierarki tetap kebaca di tabel cetak,
        // meski PDF tidak menampilkan garis penghubung visual seperti tree.blade.php.
        $ordered = [];
        $walk = function ($node, $depth) use (&$walk, &$ordered, $byParent) {
            $ordered[] = ['node' => $node, 'depth' => $depth];
            foreach ($byParent->get($node->unit_organisasi_id, collect())->sortBy('nama_unit') as $child) {
                $walk($child, $depth + 1);
            }
        };
        foreach ($roots as $root) {
            $walk($root, 0);
        }

        $totals = UnitOrganisasiSnapshot::totalFormasiBawahanBatch($units);

        $rows = collect($ordered)->map(function ($item) use ($namaByUnitId, $totals) {
            $unit = $item['node'];
            $totalBawahan = $totals[$unit->unit_organisasi_id] ?? null;

            return [
                'depth'         => $item['depth'],
                'nama_unit'     => $unit->nama_unit,
                'level'         => ucfirst($unit->level),
                'parent'        => $unit->parent_unit_organisasi_id ? ($namaByUnitId[$unit->parent_unit_organisasi_id] ?? '-') : '-',
                'formasi_unit'  => $unit->mc_formasi,
                'total_bawahan' => is_null($totalBawahan) ? '-' : $totalBawahan,
                'grand_total'   => $unit->mc_formasi + ($totalBawahan ?? 0),
                'keterangan'    => $unit->keterangan ?: '-',
            ];
        });

        $filename = 'struktur-organisasi-' . Str::slug($versi->nomor_sk) . '.pdf';

        $this->log('export', 'Struktur Organisasi (Versi)', $versi->nomor_sk, 'Export PDF');

        return Pdf::loadView('organisasi.struktur.pdf', [
            'versi' => $versi,
            'rows'  => $rows,
        ])->setPaper('a4', 'portrait')->download($filename);
    }

    public function compare(Request $request)
    {
        $request->validate([
            'lama' => 'required|integer|exists:struktur_organisasi_versi,id',
            'baru' => 'required|integer|exists:struktur_organisasi_versi,id',
        ]);

        $lama = StrukturOrganisasiVersi::findOrFail($request->integer('lama'));
        $baru = StrukturOrganisasiVersi::findOrFail($request->integer('baru'));

        if ($lama->id === $baru->id) {
            return back()->with('error', 'Pilih dua versi yang berbeda untuk dibandingkan.');
        }

        if ($lama->tanggal_mulai_berlaku->gte($baru->tanggal_mulai_berlaku)) {
            return back()->with('error',
                'Versi "baru" harus punya tanggal mulai berlaku setelah versi "lama". Tukar urutan pilihan Anda.');
        }

        if ($lama->isDraft() || $baru->isDraft()) {
            return back()->with('error', 'Perbandingan hanya bisa dilakukan antar versi yang sudah final.');
        }

        // Semua versi di antara $lama (eksklusif) sampai $baru (inklusif), urut kronologis.
        // Tiap versi di sini ("hop") transisi table-nya menyimpan delta dari versi TEPAT
        // sebelumnya — jadi rentetan hop ini merepresentasikan rantai lengkap $lama -> $baru,
        // walau $lama dan $baru yang dipilih user tidak berurutan langsung.
        $hops = StrukturOrganisasiVersi::where('tanggal_mulai_berlaku', '>', $lama->tanggal_mulai_berlaku)
            ->where('tanggal_mulai_berlaku', '<=', $baru->tanggal_mulai_berlaku)
            ->orderBy('tanggal_mulai_berlaku')
            ->get();

        $snapshotsA = $lama->unitOrganisasiSnapshots()->get()->keyBy('unit_organisasi_id');
        $snapshotsB = $baru->unitOrganisasiSnapshots()->get()->keyBy('unit_organisasi_id');
        $namaByIdA  = $snapshotsA->map(fn ($s) => $s->nama_unit);
        $namaByIdB  = $snapshotsB->map(fn ($s) => $s->nama_unit);

        // Union-Find: satukan unit_organisasi_id yang pernah "bersinggungan" lewat transisi
        // apa pun di sepanjang rentang hop, supaya rename/pindah_induk/pecah/gabung berantai
        // lintas beberapa versi tetap kekelompokkan sebagai satu cerita lineage yang sama.
        $parent = [];
        $hopTransisiByUnit = [];

        foreach ($hops as $hop) {
            foreach ($hop->unitOrganisasiTransisis()->get() as $t) {
                $asal = $t->unit_asal_id;
                $tujuan = $t->unit_baru_id;

                if ($asal)   $hopTransisiByUnit[$asal][] = $t;
                if ($tujuan) $hopTransisiByUnit[$tujuan][] = $t;

                if ($asal && $tujuan) {
                    $this->ufUnion($parent, $asal, $tujuan);
                } elseif ($asal) {
                    $this->ufFind($parent, $asal);
                } elseif ($tujuan) {
                    $this->ufFind($parent, $tujuan);
                }
            }
        }
        foreach ($snapshotsA->keys() as $id) $this->ufFind($parent, $id);
        foreach ($snapshotsB->keys() as $id) $this->ufFind($parent, $id);

        $groups = [];
        foreach (array_keys($parent) as $id) {
            $root = $this->ufFind($parent, $id);
            $groups[$root][] = $id;
        }

        $hasil = [
            'baru' => [], 'bubar' => [], 'rename' => [], 'pindah_induk' => [], 'ganti_level' => [],
            'pecah' => [], 'gabung' => [], 'reorganisasi' => [],
            'formasi_berubah' => [], 'anomali' => [], 'unchanged_count' => 0,
        ];

        foreach ($groups as $ids) {
            $inA = array_values(array_filter($ids, fn ($id) => $snapshotsA->has($id)));
            $inB = array_values(array_filter($ids, fn ($id) => $snapshotsB->has($id)));
            $countA = count($inA);
            $countB = count($inB);

            if ($countA === 0 && $countB === 0) {
                continue; // churn transien di tengah rentang, tidak menyentuh A maupun B
            }

            if ($countA === 0) {
                foreach ($inB as $id) {
                    $hasil['baru'][] = ['nama' => $namaByIdB[$id] ?? '-'];
                }
                continue;
            }

            if ($countB === 0) {
                foreach ($inA as $id) {
                    $hasil['bubar'][] = ['nama' => $namaByIdA[$id] ?? '-'];
                }
                continue;
            }

            if ($countA === 1 && $countB === 1) {
                $idA = $inA[0];
                $idB = $inB[0];
                $snapA = $snapshotsA[$idA];
                $snapB = $snapshotsB[$idB];
                $parentNamaA = $snapA->parent_unit_organisasi_id ? ($namaByIdA[$snapA->parent_unit_organisasi_id] ?? '-') : '-';
                $parentNamaB = $snapB->parent_unit_organisasi_id ? ($namaByIdB[$snapB->parent_unit_organisasi_id] ?? '-') : '-';
                // Dipakai HANYA utk gate anomali di bawah (bukan utk membatasi atribut apa yg
                // dibandingkan) — "pernah terdeklarasi" berarti ADA row transisi resmi apa pun
                // yg menyentuh unit ini sepanjang rentang hop, walau labelnya cuma satu jenis.
                $pernahDideklarasikan = !empty($hopTransisiByUnit[$idA] ?? []) || !empty($hopTransisiByUnit[$idB] ?? []);

                // Diff SEMUA atribut independen satu sama lain, terlepas dari jenis_transisi apa
                // yg (kalau ada) terdeklarasi utk unit ini — 1 unit bisa punya lebih dari 1
                // perubahan sekaligus (mis. nama + level berubah bersamaan), semua harus tetap
                // kelihatan, bukan cuma yg "sesuai" dgn label yg dipilih saat input.
                $bedaNama    = $snapA->nama_unit !== $snapB->nama_unit;
                $bedaLevel   = $snapA->level !== $snapB->level;
                $bedaParent  = $parentNamaA !== $parentNamaB;
                $bedaFormasi = $snapA->mc_formasi !== $snapB->mc_formasi;
                $adaPerubahan = $bedaNama || $bedaLevel || $bedaParent || $bedaFormasi;

                if ($bedaNama) {
                    $hasil['rename'][] = ['dari' => $snapA->nama_unit, 'ke' => $snapB->nama_unit];
                }
                if ($bedaParent) {
                    $hasil['pindah_induk'][] = ['nama' => $snapB->nama_unit, 'dari' => $parentNamaA, 'ke' => $parentNamaB];
                }
                if ($bedaLevel) {
                    $hasil['ganti_level'][] = ['nama' => $snapB->nama_unit, 'dari' => $snapA->level, 'ke' => $snapB->level];
                }
                if ($bedaFormasi) {
                    $hasil['formasi_berubah'][] = ['nama' => $snapB->nama_unit, 'dari' => $snapA->mc_formasi, 'ke' => $snapB->mc_formasi];
                }

                if ($adaPerubahan && !$pernahDideklarasikan) {
                    // Berubah tapi TIDAK PERNAH tercatat sbg transisi resmi apa pun sepanjang
                    // rentang ini — kemungkinan luput didokumentasikan tim OD saat input, perlu
                    // dicek ulang. (Perubahannya sendiri tetap sudah masuk bucket di atas.)
                    $detail = [];
                    if ($bedaNama)    $detail[] = "Nama: {$snapA->nama_unit} → {$snapB->nama_unit}";
                    if ($bedaLevel)   $detail[] = "Level: {$snapA->level} → {$snapB->level}";
                    if ($bedaParent)  $detail[] = "Parent: {$parentNamaA} → {$parentNamaB}";
                    if ($bedaFormasi) $detail[] = "Formasi: {$snapA->mc_formasi} → {$snapB->mc_formasi}";
                    $hasil['anomali'][] = ['nama' => $snapB->nama_unit, 'detail' => $detail];
                }

                if (!$adaPerubahan) {
                    $hasil['unchanged_count']++;
                }
                continue;
            }

            if ($countA === 1 && $countB >= 2) {
                $hasil['pecah'][] = [
                    'dari' => $namaByIdA[$inA[0]] ?? '-',
                    'ke'   => collect($inB)->map(fn ($id) => $namaByIdB[$id] ?? '-')->all(),
                ];
                continue;
            }

            if ($countA >= 2 && $countB === 1) {
                $hasil['gabung'][] = [
                    'dari' => collect($inA)->map(fn ($id) => $namaByIdA[$id] ?? '-')->all(),
                    'ke'   => $namaByIdB[$inB[0]] ?? '-',
                ];
                continue;
            }

            // countA >= 2 && countB >= 2: reorganisasi kompleks (banyak ke banyak), kasus sangat jarang
            $hasil['reorganisasi'][] = [
                'dari' => collect($inA)->map(fn ($id) => $namaByIdA[$id] ?? '-')->all(),
                'ke'   => collect($inB)->map(fn ($id) => $namaByIdB[$id] ?? '-')->all(),
            ];
        }

        return view('organisasi.struktur.compare', [
            'lama'      => $lama,
            'baru'      => $baru,
            'hops'      => $hops,
            'hasil'     => $hasil,
        ]);
    }

    /** Union-Find sederhana untuk mengelompokkan lineage unit lintas beberapa hop versi. */
    private function ufFind(array &$parent, $x)
    {
        if (!isset($parent[$x])) {
            $parent[$x] = $x;
        }
        if ($parent[$x] !== $x) {
            $parent[$x] = $this->ufFind($parent, $parent[$x]);
        }
        return $parent[$x];
    }

    private function ufUnion(array &$parent, $a, $b): void
    {
        $rootA = $this->ufFind($parent, $a);
        $rootB = $this->ufFind($parent, $b);
        if ($rootA !== $rootB) {
            $parent[$rootA] = $rootB;
        }
    }

    public function tree(StrukturOrganisasiVersi $versi)
    {
        $units = $versi->unitOrganisasiSnapshots()
            ->orderBy('nama_unit')
            ->get();

        $byParent = $units->groupBy('parent_unit_organisasi_id');
        $roots    = $byParent->get(null, collect())->values();
        $totals   = UnitOrganisasiSnapshot::totalFormasiBawahanBatch($units);

        // Depth tiap unit (BFS dari root) — dipakai untuk default expand 2 level teratas
        $defaultExpandedIds = [];
        $queue = $roots->map(fn ($r) => [$r, 0])->all();
        while (!empty($queue)) {
            [$node, $depth] = array_shift($queue);
            if ($depth <= 1) {
                $defaultExpandedIds[] = $node->unit_organisasi_id;
            }
            foreach ($byParent->get($node->unit_organisasi_id, collect()) as $child) {
                $queue[] = [$child, $depth + 1];
            }
        }

        return view('organisasi.struktur.tree', [
            'versi'              => $versi,
            'units'              => $units,
            'byParent'           => $byParent,
            'roots'              => $roots,
            'totals'             => $totals,
            'defaultExpandedIds' => $defaultExpandedIds,
            'allIds'             => $units->pluck('unit_organisasi_id')->values(),
        ]);
    }

    public function edit(StrukturOrganisasiVersi $versi)
    {
        $existingUnits = collect();

        if ($versi->isDraft()) {
            $existingUnits = $versi->unitOrganisasiSnapshots()
                ->orderBy('level')
                ->orderBy('nama_unit')
                ->get()
                ->map(fn ($s) => [
                    'key'                => 'e' . $s->unit_organisasi_id,
                    'unit_organisasi_id' => $s->unit_organisasi_id,
                    'nama_unit'          => $s->nama_unit,
                    'level'              => $s->level,
                    'parent_key'         => $s->parent_unit_organisasi_id ? ('e' . $s->parent_unit_organisasi_id) : null,
                    'mc_formasi'         => $s->mc_formasi,
                    'keterangan'         => $s->keterangan,
                ])
                ->values();
        }

        return view('organisasi.struktur.edit', [
            'versi'         => $versi,
            'existingUnits' => $existingUnits,
            'levels'        => self::LEVELS,
        ]);
    }

    public function update(Request $request, StrukturOrganisasiVersi $versi)
    {
        // Versi final: roster terkunci total. Kalau ada yang coba kirim payload roster
        // langsung ke endpoint ini (mis. lewat URL/form lama), tolak tegas di sini.
        if ($versi->isFinal() && $request->has('payload')) {
            return redirect()->route('organisasi.struktur.edit', $versi)
                ->with('error', 'Versi ini sudah final — roster unit tidak bisa diubah lagi.');
        }

        if ($versi->isFinal()) {
            $request->validate([
                'nomor_sk'   => 'required|string|max:100|unique:struktur_organisasi_versi,nomor_sk,' . $versi->id,
                'tanggal_sk' => 'required|date',
                'keterangan' => 'nullable|string',
            ]);

            DB::transaction(function () use ($request, $versi) {
                $versi->update($request->only('nomor_sk', 'tanggal_sk', 'keterangan'));
            });

            $this->log('edit', 'Struktur Organisasi (Versi)', $versi->nomor_sk, 'Update data SK (versi final)');

            return redirect()->route('organisasi.struktur.show', $versi)
                ->with('success', 'Data SK berhasil diperbarui.');
        }

        // Versi draft: header + roster unit sekaligus bisa diubah bebas.
        $request->validate([
            'nomor_sk'              => 'required|string|max:100|unique:struktur_organisasi_versi,nomor_sk,' . $versi->id,
            'tanggal_sk'            => 'required|date',
            'tanggal_mulai_berlaku' => 'required|date|unique:struktur_organisasi_versi,tanggal_mulai_berlaku,' . $versi->id,
            'keterangan'            => 'nullable|string',
            'payload'               => 'required|json',
        ]);

        $payload = json_decode($request->input('payload'), true);

        $validator = Validator::make($payload ?? [], [
            'units'                      => 'required|array|min:1',
            'units.*.key'                => 'required|string',
            'units.*.unit_organisasi_id' => 'nullable|integer|exists:unit_organisasi,id',
            'units.*.nama_unit'          => 'required|string|max:255',
            'units.*.level'              => 'required|in:' . implode(',', self::LEVELS),
            'units.*.parent_key'         => 'nullable|string',
            'units.*.mc_formasi'         => 'required|integer|min:0',
            'units.*.keterangan'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $hierarkiErrors = $this->validateParentHierarchy($payload['units']);
        if (!empty($hierarkiErrors)) {
            return back()->withInput()->withErrors($hierarkiErrors);
        }

        $units = $payload['units'];

        DB::transaction(function () use ($request, $versi, $units) {
            $versi->update($request->only('nomor_sk', 'tanggal_sk', 'tanggal_mulai_berlaku', 'keterangan'));

            $beforeUnitIds = $versi->unitOrganisasiSnapshots()->pluck('unit_organisasi_id')->all();

            // Pastikan identitas unit_organisasi untuk tiap baris (baru dibuat kalau belum ada)
            $keyToUnitId = [];
            foreach ($units as $row) {
                $keyToUnitId[$row['key']] = !empty($row['unit_organisasi_id'])
                    ? (int) $row['unit_organisasi_id']
                    : UnitOrganisasi::create()->id;
            }

            // Ganti seluruh snapshot versi ini dengan isian terbaru dari form (koreksi bebas, bukan transisi)
            UnitOrganisasiSnapshot::where('struktur_organisasi_versi_id', $versi->id)->delete();

            foreach ($units as $row) {
                UnitOrganisasiSnapshot::create([
                    'unit_organisasi_id'           => $keyToUnitId[$row['key']],
                    'struktur_organisasi_versi_id' => $versi->id,
                    'nama_unit'                    => $row['nama_unit'],
                    'level'                        => $row['level'],
                    'parent_unit_organisasi_id'    => !empty($row['parent_key']) ? ($keyToUnitId[$row['parent_key']] ?? null) : null,
                    'mc_formasi'                   => $row['mc_formasi'],
                    'keterangan'                   => $row['keterangan'] ?? null,
                ]);
            }

            // Unit yang dibuang dari roster: hapus identitas unit_organisasi-nya kalau memang
            // tidak lagi dipakai snapshot manapun (murni salah tambah, bukan peristiwa bubar).
            $afterUnitIds = array_values($keyToUnitId);
            $removedIds   = array_diff($beforeUnitIds, $afterUnitIds);
            foreach ($removedIds as $uid) {
                if (!UnitOrganisasiSnapshot::where('unit_organisasi_id', $uid)->exists()) {
                    UnitOrganisasi::where('id', $uid)->delete();
                }
            }
        });

        $this->log('edit', 'Struktur Organisasi (Versi)', $versi->nomor_sk,
            'Update roster draft: ' . count($units) . ' unit');

        return redirect()->route('organisasi.struktur.show', $versi)
            ->with('success', 'Versi (draft) berhasil diperbarui.');
    }

    public function finalize(StrukturOrganisasiVersi $versi)
    {
        if ($versi->isFinal()) {
            return redirect()->route('organisasi.struktur.show', $versi)
                ->with('error', 'Versi ini sudah final.');
        }

        DB::transaction(function () use ($versi) {
            $versi->update(['status' => 'final']);
        });

        $this->log('finalisasi', 'Struktur Organisasi (Versi)', $versi->nomor_sk, 'Versi difinalisasi — roster unit dikunci');

        return redirect()->route('organisasi.struktur.show', $versi)
            ->with('success', 'Versi berhasil difinalisasi. Roster unit sekarang terkunci.');
    }
}
