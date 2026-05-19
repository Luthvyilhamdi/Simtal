<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Exports\HistoryJabatanExport;
use Maatwebsite\Excel\Facades\Excel;
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

        $karyawans = $query->latest()->paginate(10);
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

        return view('history_karyawan.show', compact('karyawan', 'histories'));
    }

    public function export()
    {
        $filename = 'history-jabatan-semua-karyawan-' . now()->format('d-m-Y') . '.xlsx';

        return Excel::download(new HistoryJabatanExport(), $filename);
    }
}