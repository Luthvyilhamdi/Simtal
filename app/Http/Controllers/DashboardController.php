<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HistoryJabatan;
use App\Models\HistoryAssessment;
use App\Models\HistoryAssessmentKompetensi;
use App\Models\HistoryPejabat;
use App\Models\PgsPjs;
use App\Models\Direktorat;
use App\Models\StrukturOrganisasi;
use App\Models\Departemen;
use App\Models\TalentPool;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // === STATS UTAMA ===
        $totalKaryawan      = Karyawan::count();
        $karyawanAktif      = Karyawan::where('status', 'aktif')->count();
        $karyawanTidakAktif = Karyawan::where('status', 'tidak aktif')->count();
        $karyawanBaru       = Karyawan::whereMonth('tanggal_masuk', now()->month)
                                ->whereYear('tanggal_masuk', now()->year)->count();

        // === HISTORY JABATAN ===
        $totalHistoryJabatan = HistoryJabatan::count();
        $promosiThisYear     = HistoryJabatan::where('tipe', 'promosi')->whereYear('tanggal_mulai', now()->year)->count();
        $mutasiThisYear      = HistoryJabatan::where('tipe', 'mutasi')->whereYear('tanggal_mulai', now()->year)->count();
        $demosiThisYear      = HistoryJabatan::where('tipe', 'demosi')->whereYear('tanggal_mulai', now()->year)->count();

        // === ASSESSMENT REKOMENDASI ===
        $totalAssessment = HistoryAssessment::count();
        $assessmentReady = HistoryAssessment::where('rekomendasi_final', 'ready')->count();
        $assessmentRWD   = HistoryAssessment::where('rekomendasi_final', 'ready_with_development')->count();
        $assessmentNR    = HistoryAssessment::where('rekomendasi_final', 'not_ready')->count();

        // === ASSESSMENT KOMPETENSI ===
        $totalKompetensi   = HistoryAssessmentKompetensi::count();
        $totalQualified    = HistoryAssessmentKompetensi::where('kesimpulan', 'QUALIFIED')->count();
        $totalNotQualified = HistoryAssessmentKompetensi::where('kesimpulan', 'NOT QUALIFIED')->count();

        // === PEJABAT ===
        $pejabatAktif = HistoryPejabat::whereNull('tanggal_selesai')->count();
        $pejabatSVP   = HistoryPejabat::where('jabatan', 'SVP')->whereNull('tanggal_selesai')->count();
        $pejabatVP    = HistoryPejabat::where('jabatan', 'VP')->whereNull('tanggal_selesai')->count();
        $pejabatSPM   = HistoryPejabat::where('jabatan', 'SPM')->whereNull('tanggal_selesai')->count();
        $pejabatPM    = HistoryPejabat::where('jabatan', 'PM')->whereNull('tanggal_selesai')->count();

        // === PGS & PJS ===
        PgsPjs::where('is_active', true)
            ->where('tanggal_berakhir', '<', now())
            ->whereNotNull('tanggal_berakhir')
            ->update(['is_active' => false]);
        $pgsAktif = PgsPjs::where('is_active', true)->where('tipe', 'pgs')->count();
        $pjsAktif = PgsPjs::where('is_active', true)->where('tipe', 'pjs')->count();

        // === TALENT POOL ===
        $tpTahunIni  = now()->year;
        $tpTahunLalu = now()->year - 1;

        $tpIni  = TalentPool::where('periode', $tpTahunIni)
            ->selectRaw('COUNT(*) as total, SUM(klasifikasi="longlist") as longlist, SUM(klasifikasi="shortlist") as shortlist')
            ->first();
        $tpLalu = TalentPool::where('periode', $tpTahunLalu)
            ->selectRaw('COUNT(*) as total, SUM(klasifikasi="longlist") as longlist, SUM(klasifikasi="shortlist") as shortlist')
            ->first();

        $talentPool = [
            'tahun_ini'  => $tpTahunIni,
            'tahun_lalu' => $tpTahunLalu,
            'ini'  => [
                'total'     => $tpIni->total     ?? 0,
                'longlist'  => $tpIni->longlist  ?? 0,
                'shortlist' => $tpIni->shortlist ?? 0,
            ],
            'lalu' => [
                'total'     => $tpLalu->total     ?? 0,
                'longlist'  => $tpLalu->longlist  ?? 0,
                'shortlist' => $tpLalu->shortlist ?? 0,
            ],
        ];

        // === STRUKTUR ORGANISASI ===
        $soBulan = now()->month;
        $soTahun = now()->year;
        $soStats = StrukturOrganisasi::where('bulan', $soBulan)
            ->where('tahun', $soTahun)
            ->where('posisi', '!=', '-')
            ->selectRaw('
                COUNT(*) as total_posisi,
                SUM(mc_tko) as total_mc,
                SUM(pengisian) as total_terisi,
                SUM(CASE WHEN core = "Core" THEN 1 ELSE 0 END) as total_core,
                SUM(CASE WHEN core = "Non Core" THEN 1 ELSE 0 END) as total_non_core,
                SUM(CASE WHEN core = "Core" AND pengisian > 0 THEN pengisian ELSE 0 END) as core_terisi,
                SUM(CASE WHEN core = "Non Core" AND pengisian > 0 THEN pengisian ELSE 0 END) as non_core_terisi,
                SUM(CASE WHEN core = "Core" THEN mc_tko ELSE 0 END) as core_mc,
                SUM(CASE WHEN core = "Non Core" THEN mc_tko ELSE 0 END) as non_core_mc
            ')->first();

        $soTotalPosisi   = $soStats->total_posisi   ?? 0;
        $soTotalMc       = $soStats->total_mc       ?? 0;
        $soTerisi        = $soStats->total_terisi   ?? 0;
        $soCore          = $soStats->total_core     ?? 0;
        $soNonCore       = $soStats->total_non_core ?? 0;
        $soDeviasi       = $soTerisi - $soTotalMc;
        $soCoreTerisi    = $soStats->core_terisi    ?? 0;
        $soNonCoreTerisi = $soStats->non_core_terisi ?? 0;
        $soCoreMc        = $soStats->core_mc        ?? 0;
        $soNonCoreMc     = $soStats->non_core_mc    ?? 0;

        // Status pengisian: per posisi yang MC/TKO-nya tersedia (mc_tko > 0),
        // dihitung terisi atau belum terisi (bukan selisih angka mc vs pengisian).
        $soStatusQuery = fn ($kolom) => StrukturOrganisasi::where('bulan', $soBulan)
            ->where('tahun', $soTahun)
            ->where('posisi', '!=', '-')
            ->where('mc_tko', '>', 0)
            ->whereNotNull($kolom)
            ->where($kolom, '!=', '')
            ->selectRaw("
                {$kolom} as nama,
                COUNT(*) as tersedia,
                SUM(CASE WHEN pengisian > 0 THEN 1 ELSE 0 END) as terisi,
                SUM(CASE WHEN pengisian = 0 THEN 1 ELSE 0 END) as belum_terisi
            ")
            ->groupBy($kolom)
            ->orderByDesc('belum_terisi');

        $soPerDirektorat  = $soStatusQuery('direktorat')->get();
        $soPerKompartemen = $soStatusQuery('kompartemen')->get();
        $soPerDepartemen  = $soStatusQuery('dept')->get();

        // === CHART: Tren Jabatan ===
        $trenBulan = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $trenBulan[] = [
                'bulan'   => $bulan->translatedFormat('M Y'),
                'promosi' => HistoryJabatan::where('tipe', 'promosi')->whereYear('tanggal_mulai', $bulan->year)->whereMonth('tanggal_mulai', $bulan->month)->count(),
                'mutasi'  => HistoryJabatan::where('tipe', 'mutasi')->whereYear('tanggal_mulai', $bulan->year)->whereMonth('tanggal_mulai', $bulan->month)->count(),
                'demosi'  => HistoryJabatan::where('tipe', 'demosi')->whereYear('tanggal_mulai', $bulan->year)->whereMonth('tanggal_mulai', $bulan->month)->count(),
            ];
        }

        // === CHART: Tren Kompetensi ===
        $trenKompetensi = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $trenKompetensi[] = [
                'bulan'         => $bulan->translatedFormat('M Y'),
                'qualified'     => HistoryAssessmentKompetensi::where('kesimpulan', 'QUALIFIED')->whereYear('tanggal_assessment', $bulan->year)->whereMonth('tanggal_assessment', $bulan->month)->count(),
                'not_qualified' => HistoryAssessmentKompetensi::where('kesimpulan', 'NOT QUALIFIED')->whereYear('tanggal_assessment', $bulan->year)->whereMonth('tanggal_assessment', $bulan->month)->count(),
            ];
        }

        // === CHART: Distribusi Direktorat ===
        $distribusiDirektorat = Karyawan::where('status', 'aktif')
            ->select('direktorat_id', DB::raw('count(*) as total'))
            ->with('direktorat')->groupBy('direktorat_id')->orderBy('total', 'desc')
            ->get()->map(fn($k) => ['nama' => $k->direktorat->nama_direktorat ?? 'Belum Ditentukan', 'total' => $k->total]);

        // === CHART: Assessment Pie ===
        $assessmentChart = [
            ['label' => 'Ready',                  'value' => $assessmentReady, 'color' => '#16a34a'],
            ['label' => 'Ready with Development',  'value' => $assessmentRWD,  'color' => '#f59e0b'],
            ['label' => 'Not Ready',               'value' => $assessmentNR,   'color' => '#ef4444'],
        ];

        // === CHART: Job Grade ===
        $distribusiJobGrade = Karyawan::where('status', 'aktif')
            ->select('job_grade_id', DB::raw('count(*) as total'))
            ->with('jobGrade')->groupBy('job_grade_id')->orderBy('total', 'desc')
            ->get()->map(fn($k) => ['nama' => 'JG ' . ($k->jobGrade->job_grade ?? '-'), 'total' => $k->total]);

        // === TABEL: Ringkasan Direktorat ===
        $ringkasanDirektorat = Direktorat::withCount([
            'karyawans as total_karyawan',
            'karyawans as karyawan_aktif' => fn($q) => $q->where('status', 'aktif'),
        ])->orderBy('total_karyawan', 'desc')->get()->map(function ($d) {
            $ids = $d->karyawans()->pluck('id');
            return [
                'nama'       => $d->nama_direktorat,
                'total'      => $d->total_karyawan,
                'aktif'      => $d->karyawan_aktif,
                'promosi'    => HistoryJabatan::whereIn('karyawan_id', $ids)->where('tipe', 'promosi')->whereYear('tanggal_mulai', now()->year)->count(),
                'mutasi'     => HistoryJabatan::whereIn('karyawan_id', $ids)->where('tipe', 'mutasi')->whereYear('tanggal_mulai', now()->year)->count(),
                'assessment' => HistoryAssessment::whereIn('karyawan_id', $ids)->count(),
                'ready'      => HistoryAssessment::whereIn('karyawan_id', $ids)->where('rekomendasi_final', 'ready')->count(),
                'qualified'  => HistoryAssessmentKompetensi::whereIn('karyawan_id', $ids)->where('kesimpulan', 'QUALIFIED')->count(),
            ];
        });

        // === AKAN PENSIUN ===
        $akanPensiun = Karyawan::where('status', 'aktif')
            ->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 53')
            ->orderByRaw('tanggal_lahir ASC')->take(5)->get();

        $aktivitasTerbaru = HistoryJabatan::with(['karyawan', 'jabatan'])->latest()->take(5)->get();

        $assessmentExpire = HistoryAssessment::with('karyawan')
            ->whereNotNull('tanggal_exp_idp')
            ->whereBetween('tanggal_exp_idp', [now(), now()->addDays(30)])
            ->orderBy('tanggal_exp_idp')->take(5)->get();

        $karyawanTerbaru = Karyawan::with(['jabatan', 'departemen'])->latest('tanggal_masuk')->take(5)->get();

        $genderChart = [
            'L' => Karyawan::where('status', 'aktif')->where('jenis_kelamin', 'L')->count(),
            'P' => Karyawan::where('status', 'aktif')->where('jenis_kelamin', 'P')->count(),
        ];

        $usiaChart = [
            '< 30'  => Karyawan::where('status', 'aktif')->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 30')->count(),
            '30-39' => Karyawan::where('status', 'aktif')->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 30 AND 39')->count(),
            '40-49' => Karyawan::where('status', 'aktif')->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 40 AND 49')->count(),
            '50+'   => Karyawan::where('status', 'aktif')->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 50')->count(),
        ];

        return view('dashboard', compact(
            'totalKaryawan', 'karyawanAktif', 'karyawanTidakAktif', 'karyawanBaru',
            'totalHistoryJabatan', 'promosiThisYear', 'mutasiThisYear', 'demosiThisYear',
            'totalAssessment', 'assessmentReady', 'assessmentRWD', 'assessmentNR',
            'totalKompetensi', 'totalQualified', 'totalNotQualified', 'trenKompetensi',
            'pejabatAktif', 'pejabatSVP', 'pejabatVP', 'pejabatSPM', 'pejabatPM',
            'pgsAktif', 'pjsAktif', 'talentPool',
            'trenBulan', 'distribusiDirektorat', 'assessmentChart', 'distribusiJobGrade',
            'ringkasanDirektorat', 'akanPensiun', 'aktivitasTerbaru', 'assessmentExpire',
            'karyawanTerbaru', 'genderChart', 'usiaChart',
            'soTotalPosisi', 'soTotalMc', 'soTerisi', 'soCore', 'soNonCore', 'soDeviasi', 'soBulan', 'soTahun',
            'soCoreTerisi', 'soNonCoreTerisi', 'soCoreMc', 'soNonCoreMc',
            'soPerDirektorat', 'soPerKompartemen', 'soPerDepartemen'
        ));
    }
}