<?php

namespace App\Http\Controllers;

use App\Models\HistoryAssessment;
use App\Exports\HistoryAssessmentExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class HistoryAssessmentAllController extends Controller
{
    public function index(Request $request)
    {
        $query = HistoryAssessment::with('karyawan')
            ->orderBy('tanggal_pelaksanaan', 'desc');

        // Search
        if ($request->search) {
            $query->whereHas('karyawan', function($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nik', 'like', '%'.$request->search.'%');
            });
        }

        // Filter rekomendasi final
        if ($request->rekomendasi) {
            $query->where('rekomendasi_final', $request->rekomendasi);
        }

        // Filter tahun
        if ($request->tahun) {
            $query->whereYear('tanggal_pelaksanaan', $request->tahun);
        }

        $assessments = $query->paginate(15);

        // Stats
        $stats = [
            'total'   => HistoryAssessment::count(),
            'ready'   => HistoryAssessment::where('rekomendasi_final', 'ready')->count(),
            'rwd'     => HistoryAssessment::where('rekomendasi_final', 'ready_with_development')->count(),
            'nr'      => HistoryAssessment::where('rekomendasi_final', 'not_ready')->count(),
            'expire'  => HistoryAssessment::whereNotNull('tanggal_exp_idp')
                            ->where('tanggal_exp_idp', '<', now())->count(),
        ];

        // Tahun untuk filter
        $tahuns = HistoryAssessment::selectRaw('YEAR(tanggal_pelaksanaan) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return view('history_assessment_all.index', compact('assessments', 'stats', 'tahuns'));
    }

    public function export(Request $request)
    {
        $filename = 'history-assessment-' . now()->format('d-m-Y') . '.xlsx';

        return Excel::download(
            new HistoryAssessmentExport($request->search, $request->rekomendasi, $request->tahun),
            $filename
        );
    }
}