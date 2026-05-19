<?php

namespace App\Http\Controllers;

use App\Models\HistoryPejabat;
use App\Exports\HistoryPejabatExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class HistoryPejabatController extends Controller
{
    public function index(Request $request)
    {
        $query = HistoryPejabat::with('karyawan');

        // Filter jabatan
        if ($request->jabatan) {
            $query->where('jabatan', $request->jabatan);
        }

        // Search
        if ($request->search) {
            $query->whereHas('karyawan', function($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nik', 'like', '%'.$request->search.'%');
            });
        }

        // Pisah aktif dan selesai
        $aktif = (clone $query)
            ->whereNull('tanggal_selesai')
            ->orderBy('tanggal_mulai', 'desc')
            ->paginate(15, ['*'], 'page_aktif');

        $selesai = (clone $query)
            ->whereNotNull('tanggal_selesai')
            ->orderBy('tanggal_selesai', 'desc')
            ->paginate(15, ['*'], 'page_selesai');

        // Stats
        $stats = [
            'total' => HistoryPejabat::whereNull('tanggal_selesai')->count(),
            'svp'   => HistoryPejabat::where('jabatan', 'SVP')->whereNull('tanggal_selesai')->count(),
            'vp'    => HistoryPejabat::where('jabatan', 'VP')->whereNull('tanggal_selesai')->count(),
            'spm'   => HistoryPejabat::where('jabatan', 'SPM')->whereNull('tanggal_selesai')->count(),
            'pm'    => HistoryPejabat::where('jabatan', 'PM')->whereNull('tanggal_selesai')->count(),
        ];

        return view('history_pejabat.index', compact('aktif', 'selesai', 'stats'));
    }

    public function export(Request $request)
    {
        $jabatan  = $request->jabatan;
        $search   = $request->search;
        $filename = 'history-pejabat-' . now()->format('d-m-Y') . '.xlsx';

        return Excel::download(
            new HistoryPejabatExport($jabatan, $search),
            $filename
        );
    }
}