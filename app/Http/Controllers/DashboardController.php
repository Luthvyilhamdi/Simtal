<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HistoryJabatan;
use App\Models\HistoryAssessment;
use App\Models\HistoryPejabat;
use App\Models\PgsPjs;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // === KARYAWAN ===
        $totalKaryawan      = Karyawan::count();
        $karyawanAktif      = Karyawan::where('status', 'aktif')->count();
        $karyawanTidakAktif = Karyawan::where('status', 'tidak aktif')->count();
        $karyawanBaru       = Karyawan::whereMonth('tanggal_masuk', now()->month)
                                ->whereYear('tanggal_masuk', now()->year)->count();

        // === HISTORY JABATAN ===
        $totalHistoryJabatan = HistoryJabatan::count();
        $promosiThisYear     = HistoryJabatan::where('tipe', 'promosi')
                                ->whereYear('tanggal_mulai', now()->year)->count();
        $mutasiThisYear      = HistoryJabatan::where('tipe', 'mutasi')
                                ->whereYear('tanggal_mulai', now()->year)->count();
        $demosiThisYear      = HistoryJabatan::where('tipe', 'demosi')
                                ->whereYear('tanggal_mulai', now()->year)->count();

        // === ASSESSMENT ===
        $totalAssessment  = HistoryAssessment::count();
        $assessmentReady  = HistoryAssessment::where('rekomendasi_final', 'ready')->count();
        $assessmentRWD    = HistoryAssessment::where('rekomendasi_final', 'ready_with_development')->count();
        $assessmentNR     = HistoryAssessment::where('rekomendasi_final', 'not_ready')->count();

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

        // === AKAN PENSIUN (usia >= 55 tahun) ===
        $akanPensiun = Karyawan::where('status', 'aktif')
            ->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 53')
            ->orderByRaw('tanggal_lahir ASC')
            ->take(5)
            ->get();

        // === KARYAWAN TERBARU ===
        $karyawanTerbaru = Karyawan::with(['jabatan', 'departemen'])
            ->latest('tanggal_masuk')
            ->take(5)
            ->get();

        // === AKTIVITAS TERBARU (history jabatan) ===
        $aktivitasTerbaru = HistoryJabatan::with(['karyawan', 'jabatan'])
            ->latest()
            ->take(5)
            ->get();

        // === ASSESSMENT EXPIRE SOON (30 hari ke depan) ===
        $assessmentExpire = HistoryAssessment::with('karyawan')
            ->whereNotNull('tanggal_exp_idp')
            ->whereBetween('tanggal_exp_idp', [now(), now()->addDays(30)])
            ->orderBy('tanggal_exp_idp')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalKaryawan', 'karyawanAktif', 'karyawanTidakAktif', 'karyawanBaru',
            'totalHistoryJabatan', 'promosiThisYear', 'mutasiThisYear', 'demosiThisYear',
            'totalAssessment', 'assessmentReady', 'assessmentRWD', 'assessmentNR',
            'pejabatAktif', 'pejabatSVP', 'pejabatVP', 'pejabatSPM', 'pejabatPM',
            'pgsAktif', 'pjsAktif',
            'akanPensiun', 'karyawanTerbaru', 'aktivitasTerbaru', 'assessmentExpire'
        ));
    }
}