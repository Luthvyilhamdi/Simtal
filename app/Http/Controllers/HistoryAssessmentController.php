<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HistoryAssessment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HistoryAssessmentController extends Controller
{
    public function index(Karyawan $karyawan)
    {
        $karyawan->load(['jabatan', 'departemen', 'jobGrade', 'personGrade']);
        $assessments = $karyawan->historyAssessment()
            ->orderBy('tanggal_pelaksanaan', 'desc')
            ->get();

        return view('history_assessment.index', compact('karyawan', 'assessments'));
    }

    public function create(Karyawan $karyawan)
    {
        $karyawan->load(['jobGrade', 'personGrade']);
        return view('history_assessment.create', compact('karyawan'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'job_stream'            => 'nullable|string',
            'tanggal_pelaksanaan'   => 'required|date',
            'tingkat_pengukuran'    => 'nullable|string',
            'rekomendasi_inti'      => 'nullable|numeric|min:0|max:100',
            'rekomendasi_primer'    => 'nullable|numeric|min:0|max:100',
            'rekomendasi_skunder'   => 'nullable|numeric|min:0|max:100',
            'rekomendasi_final'     => 'nullable|in:ready,ready_with_development,not_ready',
            'keterangan'            => 'nullable|string',
        ]);

        $karyawan->load(['jobGrade', 'personGrade']);

        // Hitung usia saat assessment
        $usia = Carbon::parse($karyawan->tanggal_lahir)->age;

        // Tanggal exp IDP = tanggal assessment + 2 tahun
        $tanggalExpIdp = Carbon::parse($request->tanggal_pelaksanaan)->addYears(2);

        HistoryAssessment::create([
            'karyawan_id'         => $karyawan->id,
            'jabatan_saat_ini'    => $karyawan->jabatan_saat_ini,
            'job_grade'           => $karyawan->jobGrade->job_grade ?? null,
            'person_grade'        => $karyawan->personGrade->person_grade ?? null,
            'jenis_kelamin'       => $karyawan->jenis_kelamin,
            'usia'                => $usia,
            'job_stream'          => $request->job_stream,
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'tingkat_pengukuran'  => $request->tingkat_pengukuran,
            'rekomendasi_inti'    => $request->rekomendasi_inti,
            'rekomendasi_primer'  => $request->rekomendasi_primer,
            'rekomendasi_skunder' => $request->rekomendasi_skunder,
            'rekomendasi_final'   => $request->rekomendasi_final,
            'tanggal_exp_idp'     => $tanggalExpIdp,
            'keterangan'          => $request->keterangan,
        ]);

        return redirect()
            ->route('history_assessment.index', $karyawan)
            ->with('success', 'History assessment berhasil ditambahkan!');
    }

    public function destroy(Karyawan $karyawan, HistoryAssessment $historyAssessment)
    {
        $historyAssessment->delete();
        return redirect()
            ->route('history_assessment.index', $karyawan)
            ->with('success', 'History assessment berhasil dihapus!');
    }
}