<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HistoryJabatan;
use App\Models\HistoryAssessment;
use App\Models\HistoryPejabat;
use App\Models\PgsPjs;
use App\Models\Direktorat;
use App\Models\Departemen;
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

        // === ASSESSMENT ===
        $totalAssessment = HistoryAssessment::count();
        $assessmentReady = HistoryAssessment::where('rekomendasi_final', 'ready')->count();
        $assessmentRWD   = HistoryAssessment::where('rekomendasi_final', 'ready_with_development')->count();
        $assessmentNR    = HistoryAssessment::where('rekomendasi_final', 'not_ready')->count();

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

        // === CHART 1: Tren Pergerakan Jabatan 12 Bulan Terakhir ===
        $trenBulan = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $trenBulan[] = [
                'bulan'    => $bulan->translatedFormat('M Y'),
                'promosi'  => HistoryJabatan::where('tipe', 'promosi')
                                ->whereYear('tanggal_mulai', $bulan->year)
                                ->whereMonth('tanggal_mulai', $bulan->month)->count(),
                'mutasi'   => HistoryJabatan::where('tipe', 'mutasi')
                                ->whereYear('tanggal_mulai', $bulan->year)
                                ->whereMonth('tanggal_mulai', $bulan->month)->count(),
                'demosi'   => HistoryJabatan::where('tipe', 'demosi')
                                ->whereYear('tanggal_mulai', $bulan->year)
                                ->whereMonth('tanggal_mulai', $bulan->month)->count(),
            ];
        }

        // === CHART 2: Distribusi Karyawan per Direktorat ===
        $distribusiDirektorat = Karyawan::where('status', 'aktif')
            ->select('direktorat_id', DB::raw('count(*) as total'))
            ->with('direktorat')
            ->groupBy('direktorat_id')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn($k) => [
                'nama'  => $k->direktorat->nama_direktorat ?? 'Belum Ditentukan',
                'total' => $k->total,
            ]);

        // === CHART 3: Assessment Rekomendasi Final (Pie) ===
        $assessmentChart = [
            ['label' => 'Ready',                'value' => $assessmentReady, 'color' => '#16a34a'],
            ['label' => 'Ready with Development','value' => $assessmentRWD,  'color' => '#f59e0b'],
            ['label' => 'Not Ready',             'value' => $assessmentNR,   'color' => '#ef4444'],
        ];

        // === CHART 4: Karyawan per Job Grade ===
        $distribusiJobGrade = Karyawan::where('status', 'aktif')
            ->select('job_grade_id', DB::raw('count(*) as total'))
            ->with('jobGrade')
            ->groupBy('job_grade_id')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn($k) => [
                'nama'  => $k->jobGrade->job_grade ?? '-',
                'total' => $k->total,
            ]);

        // === TABEL: Ringkasan per Direktorat ===
        $ringkasanDirektorat = Direktorat::withCount([
            'karyawans as total_karyawan',
            'karyawans as karyawan_aktif' => fn($q) => $q->where('status', 'aktif'),
        ])
        ->orderBy('total_karyawan', 'desc')
        ->get()
        ->map(function($d) {
            $ids = $d->karyawans()->pluck('id');
            return [
                'nama'           => $d->nama_direktorat,
                'total'          => $d->total_karyawan,
                'aktif'          => $d->karyawan_aktif,
                'promosi'        => HistoryJabatan::whereIn('karyawan_id', $ids)->where('tipe','promosi')->whereYear('tanggal_mulai', now()->year)->count(),
                'mutasi'         => HistoryJabatan::whereIn('karyawan_id', $ids)->where('tipe','mutasi')->whereYear('tanggal_mulai', now()->year)->count(),
                'assessment'     => HistoryAssessment::whereIn('karyawan_id', $ids)->count(),
                'ready'          => HistoryAssessment::whereIn('karyawan_id', $ids)->where('rekomendasi_final','ready')->count(),
            ];
        });

        // === AKAN PENSIUN ===
        $akanPensiun = Karyawan::where('status', 'aktif')
            ->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 53')
            ->orderByRaw('tanggal_lahir ASC')
            ->take(5)->get();

        // === AKTIVITAS TERBARU ===
        $aktivitasTerbaru = HistoryJabatan::with(['karyawan', 'jabatan'])
            ->latest()->take(5)->get();

        // === ASSESSMENT EXPIRE SOON ===
        $assessmentExpire = HistoryAssessment::with('karyawan')
            ->whereNotNull('tanggal_exp_idp')
            ->whereBetween('tanggal_exp_idp', [now(), now()->addDays(30)])
            ->orderBy('tanggal_exp_idp')
            ->take(5)->get();

        // === KARYAWAN TERBARU ===
        $karyawanTerbaru = Karyawan::with(['jabatan', 'departemen'])
            ->latest('tanggal_masuk')->take(5)->get();

        // === GENDER DISTRIBUTION ===
        $genderChart = [
            'L' => Karyawan::where('status', 'aktif')->where('jenis_kelamin', 'L')->count(),
            'P' => Karyawan::where('status', 'aktif')->where('jenis_kelamin', 'P')->count(),
        ];

        // === USIA DISTRIBUTION ===
        $usiaChart = [
            '< 30'  => Karyawan::where('status','aktif')->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 30')->count(),
            '30-39' => Karyawan::where('status','aktif')->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 30 AND 39')->count(),
            '40-49' => Karyawan::where('status','aktif')->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 40 AND 49')->count(),
            '50+'   => Karyawan::where('status','aktif')->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 50')->count(),
        ];

        return view('dashboard', compact(
            'totalKaryawan','karyawanAktif','karyawanTidakAktif','karyawanBaru',
            'totalHistoryJabatan','promosiThisYear','mutasiThisYear','demosiThisYear',
            'totalAssessment','assessmentReady','assessmentRWD','assessmentNR',
            'pejabatAktif','pejabatSVP','pejabatVP','pejabatSPM','pejabatPM',
            'pgsAktif','pjsAktif',
            'trenBulan','distribusiDirektorat','assessmentChart','distribusiJobGrade',
            'ringkasanDirektorat','akanPensiun','aktivitasTerbaru','assessmentExpire',
            'karyawanTerbaru','genderChart','usiaChart'
        ));
    }
}