<?php

namespace App\Http\Controllers;

use App\Exports\DynamicExport;
use App\Models\Departemen;
use App\Models\Direktorat;
use App\Models\HistoryPejabat;
use App\Models\KalibrasiKaryawan;
use App\Models\Karyawan;
use App\Models\Kompartemen;
use App\Models\PenilaianKaryawan;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExportBuilderController extends Controller
{
    use LogsActivity;

    /** Nama bulan untuk label & dropdown. */
    public const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /**
     * Registry kolom terpusat — dipakai oleh form, preview, Excel, dan PDF.
     *
     * Tiap entri: 'key' => [grup, label, relasi_eager_load|null, resolver].
     * Resolver menerima satu Karyawan dan mengembalikan nilai sel (string).
     *
     * $tahun & $bulan dipakai kolom data tahunan. Jika $tahun null = "semua
     * tahun" → ambil data terbaru. $bulan hanya berlaku untuk data berbasis
     * tanggal (assessment & kompetensi); kalibrasi/KPI/talent hanya per tahun.
     *
     * @return array<string, array{0:string,1:string,2:?string,3:callable}>
     */
    public static function columnRegistry(?int $tahun = null, ?int $bulan = null): array
    {
        $suffix      = self::periodeSuffix($tahun, $bulan); // untuk data berbasis tanggal (assessment/kompetensi)
        $suffixTahun = self::periodeSuffix($tahun, null);    // untuk data per tahun saja (kalibrasi/KPI/talent)

        return [
            // ── Data Diri ──
            'nik'           => ['Data Diri', 'NIK', null, fn ($k) => $k->nik],
            'nama'          => ['Data Diri', 'Nama', null, fn ($k) => $k->nama],
            'jenis_kelamin' => ['Data Diri', 'Jenis Kelamin', null, fn ($k) => match ($k->jenis_kelamin) {
                'L' => 'Laki-laki', 'P' => 'Perempuan', default => '-',
            }],
            'tempat_lahir'  => ['Data Diri', 'Tempat Lahir', null, fn ($k) => $k->tempat_lahir ?: '-'],
            'tanggal_lahir' => ['Data Diri', 'Tanggal Lahir', null, fn ($k) => $k->tanggal_lahir?->format('d/m/Y') ?? '-'],
            'usia'          => ['Data Diri', 'Usia', null, fn ($k) => $k->tanggal_lahir ? $k->tanggal_lahir->age : '-'],
            'status'        => ['Data Diri', 'Status', null, fn ($k) => ucfirst($k->status ?? '-')],
            'no_hp'         => ['Data Diri', 'No. HP', null, fn ($k) => $k->no_hp ?: '-'],
            'email'         => ['Data Diri', 'Email', null, fn ($k) => $k->email ?: '-'],
            'jenjang_pendidikan' => ['Data Diri', 'Jenjang Pendidikan', null, fn ($k) => $k->jenjang_pendidikan ?: '-'],
            'jurusan'       => ['Data Diri', 'Jurusan', null, fn ($k) => $k->jurusan ?: '-'],

            // ── Jabatan & Unit ──
            'jabatan'               => ['Jabatan & Unit', 'Jabatan', 'jabatan', fn ($k) => $k->jabatan_saat_ini ?: ($k->jabatan->nama_jabatan ?? '-')],
            'jobs'                  => ['Jabatan & Unit', 'Jobs', 'strukturAssignments', fn ($k) => $k->jobs ?: '-'],
            'job_stream'            => ['Jabatan & Unit', 'Job Stream', 'strukturAssignments', fn ($k) => $k->job_stream ?: '-'],
            'core'                  => ['Jabatan & Unit', 'Core / Non Core', 'strukturAssignments', fn ($k) => $k->core ?: '-'],
            'direktorat'            => ['Jabatan & Unit', 'Direktorat', 'direktorat', fn ($k) => $k->direktorat->nama_direktorat ?? '-'],
            'kompartemen'           => ['Jabatan & Unit', 'Kompartemen', 'kompartemen', fn ($k) => $k->kompartemen->nama_kompartemen ?? '-'],
            'departemen'            => ['Jabatan & Unit', 'Departemen', 'departemen', fn ($k) => $k->departemen->nama_departemen ?? '-'],
            'struktural_fungsional' => ['Jabatan & Unit', 'Struktural/Fungsional', null, fn ($k) => $k->struktural_fungsional ?: '-'],

            // ── Grade ──
            'job_grade'        => ['Grade', 'Job Grade', 'jobGrade', fn ($k) => $k->jobGrade->job_grade ?? '-'],
            'person_grade'     => ['Grade', 'Person Grade', 'personGrade', fn ($k) => $k->personGrade->person_grade ?? '-'],
            'band'             => ['Grade', 'Band', 'jobGrade', fn ($k) => $k->band ?? '-'],
            'kode_struktur'    => ['Grade', 'Kode Struktur', 'kodeStruktur', fn ($k) => $k->kodeStruktur->kode_struktur ?? '-'],
            'tanggal_masuk'    => ['Grade', 'Tanggal Masuk', null, fn ($k) => $k->tanggal_masuk?->format('d/m/Y') ?? '-'],
            'masa_kerja'       => ['Grade', 'Masa Kerja (thn)', null, fn ($k) => $k->tanggal_masuk ? (int) $k->tanggal_masuk->diffInYears(now()) : '-'],
            'tanggal_mulai_pg' => ['Grade', 'Tgl Mulai PG', null, fn ($k) => $k->tanggal_mulai_pg?->format('d/m/Y') ?? '-'],
            'tanggal_mulai_jg' => ['Grade', 'Tgl Mulai JG', null, fn ($k) => $k->tanggal_mulai_jg?->format('d/m/Y') ?? '-'],
            'mdg_pg'           => ['Grade', 'MDG PG (thn)', null, fn ($k) => $k->mdg_pg ?? '-'],
            'mdg_jg'           => ['Grade', 'MDG JG (thn)', null, fn ($k) => $k->mdg_jg ?? '-'],
            'mdg_band'         => ['Grade', 'MDG Band (thn)', null, fn ($k) => $k->mdg_band],
            'status_kenaikan'  => ['Grade', 'Status Kenaikan', 'jobGrade,personGrade', fn ($k) => $k->status_kenaikan['label'] ?? '-'],
            'eligible_naik'    => ['Grade', 'Eligible Naik', 'jobGrade,personGrade', fn ($k) => ($k->status_kenaikan['eligible'] ?? false) ? 'Ya' : 'Belum'],

            // ── Kalibrasi (per tahun; "semua tahun" = terbaru) ──
            'kalibrasi'     => ['Kalibrasi', 'Nilai Kalibrasi'.$suffixTahun, 'kalibrasis',
                fn ($k) => optional(self::byTahun($k->kalibrasis, 'tahun', $tahun))->nilai ?? '-'],
            'kalibrasi_ket' => ['Kalibrasi', 'Keterangan Kalibrasi'.$suffixTahun, 'kalibrasis',
                fn ($k) => optional(self::byTahun($k->kalibrasis, 'tahun', $tahun))->keterangan ?? '-'],

            // ── Penilaian / KPI per periode (per tahun; "semua tahun" = terbaru) ──
            'kpi_tw1'       => ['Penilaian', 'KPI TW1'.$suffixTahun, 'penilaians', fn ($k) => optional(self::byTahun($k->penilaians->where('tipe', 'KPI')->where('periode', 'triwulan_1'), 'tahun', $tahun))->nilai ?? '-'],
            'kpi_tw2'       => ['Penilaian', 'KPI TW2'.$suffixTahun, 'penilaians', fn ($k) => optional(self::byTahun($k->penilaians->where('tipe', 'KPI')->where('periode', 'triwulan_2'), 'tahun', $tahun))->nilai ?? '-'],
            'kpi_tw3'       => ['Penilaian', 'KPI TW3'.$suffixTahun, 'penilaians', fn ($k) => optional(self::byTahun($k->penilaians->where('tipe', 'KPI')->where('periode', 'triwulan_3'), 'tahun', $tahun))->nilai ?? '-'],
            'kpi_tw4'       => ['Penilaian', 'KPI TW4'.$suffixTahun, 'penilaians', fn ($k) => optional(self::byTahun($k->penilaians->where('tipe', 'KPI')->where('periode', 'triwulan_4'), 'tahun', $tahun))->nilai ?? '-'],
            'kpi_tahunan'   => ['Penilaian', 'KPI Tahunan'.$suffixTahun, 'penilaians', fn ($k) => optional(self::byTahun($k->penilaians->where('tipe', 'KPI')->where('periode', 'tahunan'), 'tahun', $tahun))->nilai ?? '-'],
            'penilaian_360' => ['Penilaian', 'Nilai 360'.$suffixTahun, 'penilaians',
                fn ($k) => optional(self::byTahun($k->penilaians->where('tipe', '360'), 'tahun', $tahun))->nilai ?? '-'],

            // ── Assessment (tahun + bulan; kosong = terbaru) ──
            'assessment'            => ['Assessment', 'Rekomendasi Final'.$suffix, 'historyAssessment', fn ($k) => optional(self::byTanggal($k->historyAssessment, 'tanggal_pelaksanaan', $tahun, $bulan))->rekomendasi_final ?? '-'],
            'assessment_inti'       => ['Assessment', 'Rekomendasi Inti'.$suffix, 'historyAssessment', fn ($k) => optional(self::byTanggal($k->historyAssessment, 'tanggal_pelaksanaan', $tahun, $bulan))->rekomendasi_inti ?? '-'],
            'assessment_primer'     => ['Assessment', 'Rekomendasi Primer'.$suffix, 'historyAssessment', fn ($k) => optional(self::byTanggal($k->historyAssessment, 'tanggal_pelaksanaan', $tahun, $bulan))->rekomendasi_primer ?? '-'],
            'assessment_skunder'    => ['Assessment', 'Rekomendasi Sekunder'.$suffix, 'historyAssessment', fn ($k) => optional(self::byTanggal($k->historyAssessment, 'tanggal_pelaksanaan', $tahun, $bulan))->rekomendasi_skunder ?? '-'],
            'assessment_job_stream' => ['Assessment', 'Job Stream'.$suffix, 'historyAssessment', fn ($k) => optional(self::byTanggal($k->historyAssessment, 'tanggal_pelaksanaan', $tahun, $bulan))->job_stream ?? '-'],
            'assessment_lembaga'    => ['Assessment', 'Lembaga Assessment'.$suffix, 'historyAssessment', fn ($k) => optional(self::byTanggal($k->historyAssessment, 'tanggal_pelaksanaan', $tahun, $bulan))->lembaga ?? '-'],
            'assessment_tgl'        => ['Assessment', 'Tgl Pelaksanaan'.$suffix, 'historyAssessment', fn ($k) => optional(self::byTanggal($k->historyAssessment, 'tanggal_pelaksanaan', $tahun, $bulan))->tanggal_pelaksanaan?->format('d/m/Y') ?? '-'],
            'assessment_exp_idp'    => ['Assessment', 'Tgl Exp IDP'.$suffix, 'historyAssessment', fn ($k) => optional(self::byTanggal($k->historyAssessment, 'tanggal_pelaksanaan', $tahun, $bulan))->tanggal_exp_idp?->format('d/m/Y') ?? '-'],

            // ── Assessment Kompetensi (tahun + bulan; kosong = terbaru) ──
            'kompetensi_kesimpulan' => ['Assessment Kompetensi', 'Kesimpulan Kompetensi'.$suffix, 'historyAssessmentKompetensi', fn ($k) => optional(self::byTanggal($k->historyAssessmentKompetensi, 'tanggal_assessment', $tahun, $bulan))->kesimpulan ?? '-'],
            'kompetensi_lembaga'    => ['Assessment Kompetensi', 'Lembaga Kompetensi'.$suffix, 'historyAssessmentKompetensi', fn ($k) => optional(self::byTanggal($k->historyAssessmentKompetensi, 'tanggal_assessment', $tahun, $bulan))->lembaga ?? '-'],
            'kompetensi_tgl'        => ['Assessment Kompetensi', 'Tgl Kompetensi'.$suffix, 'historyAssessmentKompetensi', fn ($k) => optional(self::byTanggal($k->historyAssessmentKompetensi, 'tanggal_assessment', $tahun, $bulan))->tanggal_assessment?->format('d/m/Y') ?? '-'],

            // ── Talent Pool (per periode/tahun; "semua tahun" = terbaru) ──
            'talent_klasifikasi' => ['Talent Pool', 'Klasifikasi Talent'.$suffixTahun, 'talentPools', fn ($k) => optional(self::byTahun($k->talentPools, 'periode', $tahun))->klasifikasi_label ?? '-'],
            'talent_catatan'     => ['Talent Pool', 'Catatan Talent'.$suffixTahun, 'talentPools', fn ($k) => optional(self::byTahun($k->talentPools, 'periode', $tahun))->catatan ?? '-'],

            // ── Riwayat Jabatan (ringkasan seluruh riwayat) ──
            'riwayat_jabatan_terakhir' => ['Riwayat Jabatan', 'Jabatan Terakhir (Riwayat)', 'historyJabatan',
                fn ($k) => optional($k->historyJabatan->sortByDesc('tanggal_mulai')->first())->jabatan_saat_ini ?? '-'],
            'riwayat_tgl_terakhir'     => ['Riwayat Jabatan', 'Tgl Perubahan Terakhir', 'historyJabatan',
                fn ($k) => optional($k->historyJabatan->sortByDesc('tanggal_mulai')->first())->tanggal_mulai?->format('d/m/Y') ?? '-'],
            'jumlah_promosi' => ['Riwayat Jabatan', 'Jumlah Promosi', 'historyJabatan', fn ($k) => $k->historyJabatan->where('tipe', 'promosi')->count()],
            'jumlah_mutasi'  => ['Riwayat Jabatan', 'Jumlah Mutasi', 'historyJabatan', fn ($k) => $k->historyJabatan->whereIn('tipe', ['mutasi', 'rotasi'])->count()],

            // ── PGS / PJS (penugasan aktif) ──
            'pgs_pjs_aktif'   => ['PGS / PJS', 'Status PGS/PJS', 'pgsPjs',
                fn ($k) => ($p = $k->pgsPjs->firstWhere('is_active', true)) ? strtoupper($p->tipe) : '-'],
            'pgs_pjs_jabatan' => ['PGS / PJS', 'Jabatan PGS/PJS', 'pgsPjs',
                fn ($k) => optional($k->pgsPjs->firstWhere('is_active', true))->jabatan_pgs_pjs ?? '-'],
        ];
    }

    public function index()
    {
        // Kelompokkan kolom per grup untuk ditampilkan sebagai checkbox.
        $grouped = [];
        foreach (self::columnRegistry() as $key => [$grup, $label]) {
            $grouped[$grup][$key] = $label;
        }

        $direktorats  = Direktorat::orderBy('nama_direktorat')->get();
        $kompartemens = Kompartemen::orderBy('nama_kompartemen')->get();
        $departemens  = Departemen::orderBy('nama_departemen')->get();
        $tierList     = HistoryPejabat::JABATAN_DIPANTAU; // SVP, VP, SPM, PM

        // Tahun tersedia dari data kalibrasi + penilaian.
        $tahunList = collect()
            ->merge(KalibrasiKaryawan::distinct()->pluck('tahun'))
            ->merge(PenilaianKaryawan::distinct()->pluck('tahun'))
            ->filter()->unique()->sortDesc()->values();

        $bulanList = self::BULAN;

        // Untuk pemilih karyawan (cari NIK/nama → tambah sebagai chip) di sisi klien.
        $karyawanPilih = Karyawan::orderBy('nama')->get(['nik', 'nama']);

        return view('export_builder.index', compact(
            'grouped', 'direktorats', 'kompartemens', 'departemens', 'tierList', 'tahunList', 'bulanList',
            'karyawanPilih'
        ));
    }

    /**
     * Preview data (AJAX) — kembalikan heading + sebagian baris sebagai JSON
     * sebelum user benar-benar mengunduh file.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate($this->baseRules());

        [$headings, $rows, $total] = $this->buildRows($validated, limit: 50);

        return response()->json([
            'headings' => $headings,
            'rows'     => $rows,
            'shown'    => count($rows),
            'total'    => $total,
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate(
            $this->baseRules() + ['format' => 'required|in:excel,pdf']
        );

        [$headings, $rows, $total] = $this->buildRows($validated);

        $namaFile = 'export-data-'.now()->format('Y-m-d');

        $this->log('export', 'Export Data', 'Custom Export',
            count($validated['columns']).' kolom, '.$total.' baris, format '.$validated['format']);

        if ($validated['format'] === 'excel') {
            return Excel::download(new DynamicExport($headings, $rows, 'Export Data'), $namaFile.'.xlsx');
        }

        return Pdf::loadView('export_builder.pdf', [
            'headings' => $headings,
            'rows'     => $rows,
            'periode'  => $this->periodeLabel($validated['tahun'] ?? null, $validated['bulan'] ?? null),
            'tanggal'  => now()->format('d/m/Y H:i'),
            'jumlah'   => $total,
        ])->setPaper('a4', count($headings) > 6 ? 'landscape' : 'portrait')
          ->download($namaFile.'.pdf');
    }

    /** Aturan validasi yang dipakai bersama preview & export. */
    private function baseRules(): array
    {
        return [
            'columns'       => 'required|array|min:1',
            'columns.*'     => 'in:'.implode(',', array_keys(self::columnRegistry())),
            'tahun'          => 'nullable|integer|min:2000|max:2100',
            'bulan'          => 'nullable|integer|min:1|max:12',
            'status'         => 'nullable|in:aktif,tidak aktif',
            'direktorat_id'  => 'nullable|exists:direktorat,id',
            'kompartemen_id' => 'nullable|exists:kompartemen,id',
            'departemen_id'  => 'nullable|exists:departemen,id',
            'tier'           => 'nullable|in:'.implode(',', HistoryPejabat::JABATAN_DIPANTAU),
            'nik_nama'       => 'nullable|string|max:20000',
        ];
    }

    /** Pecah teks NIK/nama yang di-paste (baris/koma/titik-koma) menjadi token bersih. */
    private static function parseNikNama(?string $raw): array
    {
        if (! $raw) return [];
        $tokens = preg_split('/[\r\n,;]+/', $raw) ?: [];
        $tokens = array_map('trim', $tokens);
        return array_values(array_unique(array_filter($tokens, fn ($t) => $t !== '')));
    }

    /**
     * Bangun heading + baris dari kolom & filter terpilih.
     *
     * @return array{0:array<int,string>,1:array<int,array>,2:int} [headings, rows, total]
     */
    private function buildRows(array $validated, ?int $limit = null): array
    {
        $tahun    = $validated['tahun'] ?? null;
        $bulan    = $validated['bulan'] ?? null;
        $selected = $validated['columns'];
        $registry = self::columnRegistry($tahun, $bulan);

        // Eager-load hanya relasi yang dibutuhkan kolom terpilih.
        // Index [2] boleh berisi beberapa relasi dipisah koma (mis. 'jobGrade,personGrade').
        $relations = collect($selected)
            ->map(fn ($key) => $registry[$key][2])
            ->filter()
            ->flatMap(fn ($r) => explode(',', $r))
            ->unique()->values()->all();

        $query = Karyawan::with($relations)->orderBy('nama');

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }
        if (! empty($validated['direktorat_id'])) {
            $query->where('direktorat_id', $validated['direktorat_id']);
        }
        if (! empty($validated['kompartemen_id'])) {
            $query->where('kompartemen_id', $validated['kompartemen_id']);
        }
        if (! empty($validated['departemen_id'])) {
            $query->where('departemen_id', $validated['departemen_id']);
        }
        if (! empty($validated['tier'])) {
            // Karyawan yang pejabat aktifnya bertier tertentu (SVP/VP/SPM/PM).
            $query->whereHas('pejabatAktif', fn ($q) => $q->where('jabatan', $validated['tier']));
        }

        // Pilih karyawan spesifik dengan paste NIK / nama (baris/koma/titik-koma).
        // Cocok bila NIK sama persis ATAU nama mengandung token.
        $tokens = self::parseNikNama($validated['nik_nama'] ?? null);
        if (! empty($tokens)) {
            $query->where(function ($q) use ($tokens) {
                foreach ($tokens as $t) {
                    $q->orWhere('nik', $t)
                      ->orWhere('nama', 'like', '%'.$t.'%');
                }
            });
        }

        // Mode "semua tahun" + ada kolom tahunan → satu baris per (karyawan, tahun).
        $adaKolomTahunan = (bool) array_intersect($selected, self::yearDependentKeys());
        $expand = ($tahun === null) && $adaKolomTahunan;

        if (! $expand) {
            $total = (clone $query)->count();
            if ($limit) {
                $query->limit($limit);
            }

            $headings = ['No'];
            foreach ($selected as $key) {
                $headings[] = $registry[$key][1];
            }

            $rows = [];
            $no = 1;
            foreach ($query->get() as $k) {
                $row = [$no++];
                foreach ($selected as $key) {
                    $row[] = (string) $registry[$key][3]($k);
                }
                $rows[] = $row;
            }

            return [$headings, $rows, $total];
        }

        // ── Mode expand: tetap 1 baris per karyawan, tiap kolom tahunan
        //    dipecah jadi beberapa kolom — satu kolom per tahun (pivot). ──
        $karyawans = $query->get();

        // Tahun yang tersedia per kolom tahunan (gabungan seluruh karyawan).
        $tahunPerKolom = [];
        foreach ($selected as $key) {
            if (in_array($key, self::yearDependentKeys(), true)) {
                $tahunPerKolom[$key] = self::tahunUntukKolom($karyawans, $key, $bulan);
            }
        }

        // Rencana kolom: 'static' (registry dasar) atau 'tahun' (registry per tahun).
        $headings = ['No'];
        $plan = [];
        foreach ($selected as $key) {
            if (! isset($tahunPerKolom[$key])) {
                $headings[] = $registry[$key][1];
                $plan[] = ['key' => $key, 'tahun' => null];
                continue;
            }

            $baseLabel = preg_replace('/ \(terbaru\)$/', '', $registry[$key][1]);
            if ($tahunPerKolom[$key]->isEmpty()) {
                $headings[] = $baseLabel;
                $plan[] = ['key' => $key, 'tahun' => 0]; // tak ada data → satu kolom '-'
                continue;
            }
            foreach ($tahunPerKolom[$key] as $th) {
                $headings[] = $baseLabel.' '.$th;
                $plan[] = ['key' => $key, 'tahun' => $th];
            }
        }

        $registryCache = [];
        $rows = [];
        $no = 1;
        foreach ($karyawans as $k) {
            $row = [$no++];
            foreach ($plan as $col) {
                if ($col['tahun'] === null) {
                    $row[] = (string) $registry[$col['key']][3]($k);
                } else {
                    $th  = $col['tahun'] ?: null;
                    $reg = $registryCache[$th] ??= self::columnRegistry($th, $bulan);
                    $row[] = (string) $reg[$col['key']][3]($k);
                }
            }
            $rows[] = $row;
        }

        $total = count($rows);
        if ($limit && $total > $limit) {
            $rows = array_slice($rows, 0, $limit);
        }

        return [$headings, $rows, $total];
    }

    /** Daftar key kolom yang nilainya bergantung pada tahun/periode. */
    private static function yearDependentKeys(): array
    {
        return [
            'kalibrasi', 'kalibrasi_ket',
            'kpi_tw1', 'kpi_tw2', 'kpi_tw3', 'kpi_tw4', 'kpi_tahunan', 'penilaian_360',
            'assessment', 'assessment_inti', 'assessment_primer', 'assessment_skunder',
            'assessment_job_stream', 'assessment_lembaga', 'assessment_tgl', 'assessment_exp_idp',
            'kompetensi_kesimpulan', 'kompetensi_lembaga', 'kompetensi_tgl',
            'talent_klasifikasi', 'talent_catatan',
        ];
    }

    /**
     * Daftar tahun (desc) yang tersedia untuk satu kolom tahunan, digabung dari
     * seluruh karyawan — menentukan berapa sub-kolom tahun yang dibuat (pivot).
     */
    private static function tahunUntukKolom($karyawans, string $key, ?int $bulan)
    {
        $tahun = collect();
        foreach ($karyawans as $k) {
            $tahun = $tahun->merge(self::sumberTahun($k, $key, $bulan));
        }

        return $tahun->filter()->unique()->sortDesc()->values();
    }

    /** Ambil daftar tahun dari sumber data yang sesuai dengan satu kolom. */
    private static function sumberTahun($k, string $key, ?int $bulan)
    {
        return match (true) {
            in_array($key, ['kalibrasi', 'kalibrasi_ket'], true) => $k->kalibrasis->pluck('tahun'),
            Str::startsWith($key, 'kpi_')  => $k->penilaians->where('tipe', 'KPI')->pluck('tahun'),
            $key === 'penilaian_360'       => $k->penilaians->where('tipe', '360')->pluck('tahun'),
            in_array($key, ['talent_klasifikasi', 'talent_catatan'], true) => $k->talentPools->pluck('periode'),
            Str::startsWith($key, 'assessment') => self::tahunDariTanggal($k->historyAssessment, 'tanggal_pelaksanaan', $bulan),
            Str::startsWith($key, 'kompetensi') => self::tahunDariTanggal($k->historyAssessmentKompetensi, 'tanggal_assessment', $bulan),
            default => collect(),
        };
    }

    /** Ambil daftar tahun dari koleksi berbasis tanggal, terfilter bulan bila ada. */
    private static function tahunDariTanggal($items, string $field, ?int $bulan)
    {
        return $items
            ->filter(fn ($x) => $x->{$field} && (! $bulan || $x->{$field}->month == $bulan))
            ->map(fn ($x) => $x->{$field}->year);
    }

    // ── Helper pemilihan data periodik ──

    /**
     * Pilih 1 item dari koleksi berdasarkan field tahun (integer).
     * $tahun null → ambil yang terbaru (field terbesar).
     */
    private static function byTahun($items, string $field, ?int $tahun)
    {
        if ($tahun) {
            return $items->firstWhere($field, $tahun);
        }

        return $items->sortByDesc($field)->first();
    }

    /**
     * Pilih item TERBARU dari koleksi yang cocok dengan tahun &/atau bulan
     * (berdasarkan field tanggal). Filter yang null diabaikan.
     */
    private static function byTanggal($items, string $field, ?int $tahun, ?int $bulan)
    {
        return $items
            ->filter(function ($x) use ($field, $tahun, $bulan) {
                $d = $x->{$field};
                if (! $d) {
                    return false;
                }
                if ($tahun && $d->year != $tahun) {
                    return false;
                }
                if ($bulan && $d->month != $bulan) {
                    return false;
                }

                return true;
            })
            ->sortByDesc($field)
            ->first();
    }

    /** Sufiks label kolom, mis. " Mar 2025", " 2025", atau " (terbaru)". */
    private static function periodeSuffix(?int $tahun, ?int $bulan): string
    {
        if ($tahun && $bulan) {
            return ' '.substr(self::BULAN[$bulan], 0, 3).' '.$tahun;
        }
        if ($tahun) {
            return ' '.$tahun;
        }
        if ($bulan) {
            return ' '.substr(self::BULAN[$bulan], 0, 3);
        }

        return ' (terbaru)';
    }

    /** Label periode lengkap untuk header PDF. */
    private function periodeLabel(?int $tahun, ?int $bulan): string
    {
        if ($tahun && $bulan) {
            return self::BULAN[$bulan].' '.$tahun;
        }
        if ($tahun) {
            return 'Tahun '.$tahun;
        }
        if ($bulan) {
            return self::BULAN[$bulan].' (semua tahun)';
        }

        return 'Semua tahun';
    }
}
