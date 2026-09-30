<?php

namespace App\Http\Controllers;

use App\Models\HistoryJabatan;
use App\Models\Karyawan;
use App\Exports\HistoryJabatanExport;
use Illuminate\Http\Request;

class HistoryKaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::with([
            'jabatan', 'departemen', 'direktorat',
            'jobGrade', 'personGrade',
            'historyJabatan.jabatan',
            'historyJabatan.departemen',
        ]);

        if ($request->search) {
            $query->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nik', 'like', '%'.$request->search.'%');
        }

        $karyawans = $query->latest()->paginate(10)->withQueryString();
        return view('history_karyawan.index', compact('karyawans'));
    }

    public function show(Karyawan $karyawan)
    {
        $karyawan->load([
            'jabatan', 'departemen', 'direktorat',
            'jobGrade', 'personGrade',
        ]);

        $histories = $karyawan->historyJabatan()
            ->with(['jabatan', 'direktorat', 'kompartemen', 'departemen', 'jobGrade', 'personGrade', 'kodeStruktur'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        // Ringkasan masa dinas, disusun sama persis seperti halaman History
        // Jabatan per-karyawan supaya angkanya tidak berbeda antar halaman:
        // MDJ = periode jabatan yang sedang berjalan, MDG = sejak TMT Person Grade.
        $mdjAktif = collect(HistoryJabatan::ringkasPeriodeMdj($histories))->firstWhere('aktif', true);
        $mdgPg    = $this->masaDinasPersonGrade($karyawan);

        return view('history_karyawan.show', compact('karyawan', 'histories', 'mdjAktif', 'mdgPg'));
    }

    /** Masa Dinas Grade dari TMT Person Grade; null bila TMT-nya belum diisi. */
    private function masaDinasPersonGrade(Karyawan $karyawan): ?array
    {
        $mulai = $karyawan->tanggal_mulai_pg;
        if (! $mulai || $mulai->isFuture()) {
            return null;
        }

        $d = $mulai->diff(now());

        return [
            'mulai' => $mulai,
            'grade' => optional($karyawan->personGrade)->person_grade,
            'tahun' => $d->y,
            'bulan' => $d->m,
            'hari'  => $d->d,
        ];
    }

    public function export()
    {
        $filename = 'history-jabatan-semua-karyawan-' . now()->format('d-m-Y') . '.xlsx';

        return (new HistoryJabatanExport())->download($filename);
    }
}