<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Modul yang selalu tersedia di filter, walau belum ada catatannya.
     *
     * Sebelumnya pilihan filter hanya diambil dari modul yang SUDAH pernah
     * tercatat — akibatnya modul baru (mis. Organisasi) tidak bisa dipilih
     * sampai ada orang yang memakainya lebih dulu. Nama di sini harus sama
     * persis dengan argumen $modul pada pemanggilan log() di tiap controller.
     */
    private const MODUL_DIKENAL = [
        // Organisasi
        'Struktur Organisasi',
        'Struktur Organisasi (Versi)',
        'Job Profile',
        'Kompetensi Teknis',
        'Job Family',
        // Data karyawan & riwayat
        'Karyawan',
        'History Jabatan',
        'History Pendidikan',
        'Assessment',
        'Assessment Kompetensi',
        'Penilaian Karyawan',
        'Kalibrasi',
        'TOEFL',
        // Kepejabatan & talenta
        'History Pejabat',
        'PGS/PJS',
        'Talent Pool',
        'Usulan Promosi',
        'Usulan Mutasi',   // dari 'Usulan ' . ucfirst($jenis)
        'Usulan Rotasi',   // idem — enum jenis: rotasi | mutasi
        // Layanan & administrasi
        'Surat Penting',
        'Export Data',
        'Akun',
        'Backup',
    ];

    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('user_name', 'like', '%'.$request->search.'%')
                  ->orWhere('target', 'like', '%'.$request->search.'%')
                  ->orWhere('keterangan', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->aksi) {
            $query->where('aksi', $request->aksi);
        }

        if ($request->modul) {
            $query->where('modul', $request->modul);
        }

        if ($request->tanggal) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $logs = $query->paginate(10)->appends(request()->query());

        // Gabung modul yang dikenal dengan yang benar-benar ada di catatan,
        // supaya modul lama (yang namanya sudah berubah) tetap bisa disaring.
        $moduls = collect(self::MODUL_DIKENAL)
            ->merge(ActivityLog::distinct()->pluck('modul'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('activity_log.index', compact('logs', 'moduls'));
    }

    public function destroy(Request $request)
    {
        ActivityLog::truncate();
        return redirect()->route('activity_log.index')
            ->with('success', 'Semua log aktivitas berhasil dihapus!');
    }
}