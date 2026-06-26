<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HistoryJabatan;
use App\Models\HistoryAssessment;
use App\Models\HistoryAssessmentKompetensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function bulanan(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $awal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $akhir = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
        $namaBulan = $awal->translatedFormat('F Y');

        // === KARYAWAN MASUK ===
        $karyawanMasuk = Karyawan::whereBetween('tanggal_masuk', [$awal, $akhir])
            ->with(['direktorat', 'jabatan'])
            ->orderBy('tanggal_masuk')
            ->get();

        // === KARYAWAN KELUAR (tidak aktif, tanggal update di bulan ini) ===
        $karyawanKeluar = Karyawan::where('status', 'tidak aktif')
            ->whereBetween('updated_at', [$awal, $akhir])
            ->with(['direktorat', 'jabatan'])
            ->orderBy('updated_at')
            ->get();

        // === PERGERAKAN JABATAN ===
        $pergerakan = HistoryJabatan::whereBetween('tanggal_mulai', [$awal, $akhir])
            ->with(['karyawan.direktorat'])
            ->orderBy('tanggal_mulai')
            ->get();

        $promosi = $pergerakan->where('tipe', 'promosi');
        $mutasi  = $pergerakan->where('tipe', 'mutasi');
        $demosi  = $pergerakan->where('tipe', 'demosi');

        // === ASSESSMENT REKOMENDASI ===
        $assessments = HistoryAssessment::whereBetween('created_at', [$awal, $akhir])
            ->with('karyawan')
            ->orderBy('created_at')
            ->get();

        // === ASSESSMENT KOMPETENSI ===
        $kompetensi = HistoryAssessmentKompetensi::whereBetween('created_at', [$awal, $akhir])
            ->with('karyawan')
            ->orderBy('created_at')
            ->get();

        // === AKAN PENSIUN BULAN INI ===
        $akanPensiun = Karyawan::where('status', 'aktif')
            ->whereRaw('DATE_ADD(tanggal_lahir, INTERVAL 56 YEAR) BETWEEN ? AND ?', [$awal, $akhir])
            ->with(['direktorat'])
            ->get();

        // === SUMMARY STATS ===
        $stats = [
            'karyawan_masuk'   => $karyawanMasuk->count(),
            'karyawan_keluar'  => $karyawanKeluar->count(),
            'total_pergerakan' => $promosi->count() + $mutasi->count() + $demosi->count(),
            'promosi'          => $promosi->count(),
            'mutasi'           => $mutasi->count(),
            'demosi'           => $demosi->count(),
            'assessment'       => $assessments->count(),
            'kompetensi'       => $kompetensi->count(),
            'pensiun'          => $akanPensiun->count(),
        ];

        // === PERIODE LIST ===
        $periodeList = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $periodeList[] = ['bulan' => $d->month, 'tahun' => $d->year, 'label' => $d->translatedFormat('F Y')];
        }

        return view('laporan.bulanan', compact(
            'bulan', 'tahun', 'namaBulan', 'stats',
            'karyawanMasuk', 'karyawanKeluar',
            'promosi', 'mutasi', 'demosi',
            'assessments', 'kompetensi',
            'akanPensiun', 'periodeList'
        ));
    }
}