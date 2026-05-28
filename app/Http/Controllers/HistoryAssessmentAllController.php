<?php

namespace App\Http\Controllers;

use App\Models\HistoryAssessment;
use App\Models\HistoryAssessmentKompetensi;
use App\Exports\HistoryAssessmentExport;
use Illuminate\Http\Request;

class HistoryAssessmentAllController extends Controller
{
    public function index(Request $request)
    {
        $query = HistoryAssessment::with('karyawan')
            ->orderBy('tanggal_pelaksanaan', 'desc');

        if ($request->search) {
            $query->whereHas('karyawan', function($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nik', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->rekomendasi) {
            $query->where('rekomendasi_final', $request->rekomendasi);
        }

        if ($request->tahun) {
            $query->whereYear('tanggal_pelaksanaan', $request->tahun);
        }

        $assessments = $query->paginate(15);

        // Data kompetensi semua karyawan
        $assessmentKompetensi = HistoryAssessmentKompetensi::with('karyawan')
            ->orderBy('tanggal_assessment', 'desc')
            ->get();

        // Stats
        $stats = [
            'total'            => HistoryAssessment::count(),
            'total_kompetensi' => HistoryAssessmentKompetensi::count(),
            'ready'            => HistoryAssessment::where('rekomendasi_final', 'ready')->count(),
            'rwd'              => HistoryAssessment::where('rekomendasi_final', 'ready_with_development')->count(),
            'nr'               => HistoryAssessment::where('rekomendasi_final', 'not_ready')->count(),
            'expire'           => HistoryAssessment::whereNotNull('tanggal_exp_idp')
                                    ->where('tanggal_exp_idp', '<', now())->count(),
            'qualified'        => HistoryAssessmentKompetensi::where('kesimpulan', 'QUALIFIED')->count(),
        ];

        // Tahun untuk filter
        $tahuns = HistoryAssessment::selectRaw('YEAR(tanggal_pelaksanaan) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return view('history_assessment_all.index', compact(
            'assessments', 'assessmentKompetensi', 'stats', 'tahuns'
        ));
    }

    public function export(Request $request)
    {
        $filename = 'history-assessment-' . now()->format('d-m-Y') . '.xlsx';

        return (new HistoryAssessmentExport(
            $request->search,
            $request->rekomendasi,
            $request->tahun
        ))->download($filename);
    }

    public function destroy(HistoryAssessment $assessment)
    {
        $assessment->delete();
        return redirect()
            ->route('history_assessment_all.index')
            ->with('success', 'Data assessment berhasil dihapus!');
    }

    public function destroyKompetensi(HistoryAssessmentKompetensi $kompetensi)
    {
        $kompetensi->delete();
        return redirect()
            ->route('history_assessment_all.index', ['tab' => 'komp'])
            ->with('success', 'Data assessment kompetensi berhasil dihapus!');
    }
}