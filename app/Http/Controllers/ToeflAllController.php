<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Exports\ToeflExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Halaman GLOBAL nilai TOEFL — 1 baris = 1 karyawan (yang punya data TOEFL).
 * Detail per tes dilihat di halaman karyawan (toefl.index). Plus Export Excel.
 */
class ToeflAllController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::query()
            ->has('toefls')
            ->withCount('toefls')
            ->with([
                'jabatan', 'departemen',
                'toefls' => fn ($q) => $q->orderByDesc('tanggal_tes')->orderByDesc('id'),
            ])
            ->orderBy('nama');

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%");
            });
        }

        $karyawans = $query->paginate(15)->withQueryString();

        return view('toefl_all.index', compact('karyawans'));
    }

    public function export(Request $request)
    {
        $filename = 'toefl-' . now()->format('d-m-Y') . '.xlsx';
        return Excel::download(new ToeflExport($request->search), $filename);
    }
}
