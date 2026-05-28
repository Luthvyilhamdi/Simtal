<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HistoryAssessmentKompetensi;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class HistoryAssessmentKompetensiController extends Controller
{
    use LogsActivity;

    public function create(Karyawan $karyawan)
    {
        return view('history_assessment_kompetensi.create', [
            'karyawan'       => $karyawan,
            'competencies'   => HistoryAssessmentKompetensi::competencies(),
            'qualifications' => HistoryAssessmentKompetensi::qualifications(),
        ]);
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $rules = [
            'tanggal_assessment' => 'required|date',
            'periode'            => 'nullable|string',
            'keterangan'         => 'nullable|string',
        ];

        // Validasi semua kompetensi
        foreach (array_keys(HistoryAssessmentKompetensi::competencies()) as $key) {
            $rules[$key] = 'required|integer|min:1|max:4';
        }
        foreach (array_keys(HistoryAssessmentKompetensi::qualifications()) as $key) {
            $rules[$key] = 'required|integer|min:1|max:4';
        }

        $request->validate($rules);

        $data = $request->only(array_merge(
            ['tanggal_assessment', 'periode', 'keterangan'],
            array_keys(HistoryAssessmentKompetensi::competencies()),
            array_keys(HistoryAssessmentKompetensi::qualifications())
        ));

        $data['karyawan_id']              = $karyawan->id;
        $data['total_competency_under']   = HistoryAssessmentKompetensi::hitungUnderCompetency($data);
        $data['total_qualification_under']= HistoryAssessmentKompetensi::hitungUnderQualification($data);
        $data['kesimpulan']               = HistoryAssessmentKompetensi::hitungKesimpulan(
            $data['total_competency_under'],
            $data['total_qualification_under']
        );

        HistoryAssessmentKompetensi::create($data);

        $this->log('tambah', 'Assessment Kompetensi', $karyawan->nama,
            'Kesimpulan: ' . $data['kesimpulan'] . ' | Periode: ' . ($data['periode'] ?? '-'));

        return redirect()
            ->route('history_assessment.index', $karyawan)
            ->with('success', 'Assessment kompetensi berhasil disimpan!');
    }

    public function destroy(Karyawan $karyawan, HistoryAssessmentKompetensi $kompetensi)
    {
        $kompetensi->delete();
        $this->log('hapus', 'Assessment Kompetensi', $karyawan->nama, 'Hapus data assessment kompetensi');

        return redirect()
            ->route('history_assessment.index', $karyawan)
            ->with('success', 'Assessment kompetensi berhasil dihapus!');
    }
}