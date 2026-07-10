<?php

namespace App\Http\Controllers;

use App\Models\HistoryAssessment;
use App\Models\HistoryAssessmentKompetensi;
use App\Exports\HistoryAssessmentExport;
use App\Exports\HistoryAssessmentKompetensiExport;
use Illuminate\Http\Request;

class HistoryAssessmentAllController extends Controller
{
    public function index(Request $request)
    {
        // ===== QUERY REKOMENDASI =====
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

        // Nama parameter halaman dibedakan agar paginasi kedua tab tidak saling menggeser.
        $assessments = $query->paginate(15, ['*'], 'page_rekom')->withQueryString();

        // ===== QUERY KOMPETENSI =====
        $queryKomp = HistoryAssessmentKompetensi::with('karyawan')
            ->orderBy('tanggal_assessment', 'desc');

        if ($request->search_komp) {
            $queryKomp->whereHas('karyawan', function($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search_komp.'%')
                  ->orWhere('nik', 'like', '%'.$request->search_komp.'%');
            });
        }

        $assessmentKompetensi = $queryKomp->paginate(15, ['*'], 'page_komp')->withQueryString();

        // ===== STATS =====
        $stats = [
            'total'            => HistoryAssessment::count(),
            'total_kompetensi' => HistoryAssessmentKompetensi::count(),
            'ready'            => HistoryAssessment::where('rekomendasi_final', 'ready')->count(),
            'rwd'              => HistoryAssessment::where('rekomendasi_final', 'ready_with_development')->count(),
            'nr'               => HistoryAssessment::where('rekomendasi_final', 'not_ready')->count(),
            'expire'           => HistoryAssessment::whereNotNull('tanggal_exp_idp')
                                    ->where('tanggal_exp_idp', '<', now())->count(),
            'qualified'        => HistoryAssessmentKompetensi::where('kesimpulan', 'QUALIFIED')->count(),
            'not_qualified'    => HistoryAssessmentKompetensi::where('kesimpulan', 'NOT QUALIFIED')->count(),
        ];

        // ===== TAHUN FILTER =====
        $tahuns = HistoryAssessment::selectRaw('YEAR(tanggal_pelaksanaan) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return view('history_assessment_all.index', compact(
            'assessments', 'assessmentKompetensi', 'stats', 'tahuns'
        ));
    }

    // ===== EXPORT REKOMENDASI =====
    public function export(Request $request)
    {
        $filename = 'history-assessment-rekomendasi-' . now()->format('d-m-Y') . '.xlsx';

        return (new HistoryAssessmentExport(
            $request->search,
            $request->rekomendasi,
            $request->tahun
        ))->download($filename);
    }

    // ===== EXPORT KOMPETENSI =====
    public function exportKompetensi(Request $request)
    {
        $filename = 'history-assessment-kompetensi-' . now()->format('d-m-Y') . '.xlsx';

        return (new HistoryAssessmentKompetensiExport(
            $request->search_komp
        ))->download($filename);
    }

    // ===== DESTROY REKOMENDASI =====
    public function destroy(HistoryAssessment $assessment)
    {
        $assessment->delete();
        return redirect()
            ->route('history_assessment_all.index')
            ->with('success', 'Data assessment berhasil dihapus!');
    }

    // ===== DESTROY KOMPETENSI =====
    public function destroyKompetensi(HistoryAssessmentKompetensi $kompetensi)
    {
        $kompetensi->delete();
        return redirect()
            ->route('history_assessment_all.index', ['tab' => 'komp'])
            ->with('success', 'Data assessment kompetensi berhasil dihapus!');
    }

    // ===== UPDATE LINK FILE (assessment rekomendasi) =====
    public function updateLinkFile(Request $request, HistoryAssessment $assessment)
    {
        $data = $request->validate(['link_file' => 'nullable|url|max:2048']);
        $assessment->update(['link_file' => $data['link_file'] ?: null]);
        return back()->with('success', 'Link file assessment berhasil disimpan.');
    }

    // ===== UPDATE LINK FILE (assessment kompetensi) =====
    public function updateLinkFileKompetensi(Request $request, HistoryAssessmentKompetensi $kompetensi)
    {
        $data = $request->validate(['link_file' => 'nullable|url|max:2048']);
        $kompetensi->update(['link_file' => $data['link_file'] ?: null]);
        return back()->with('success', 'Link file assessment kompetensi berhasil disimpan.');
    }
}