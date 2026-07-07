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
            'lembaga'            => 'nullable|string|max:255',
            'link_file'          => 'nullable|url|max:2048',
        ];

        foreach (array_keys(HistoryAssessmentKompetensi::competencies()) as $key) {
            $rules[$key] = 'required|integer|min:1|max:4';
        }
        foreach (array_keys(HistoryAssessmentKompetensi::qualifications()) as $key) {
            $rules[$key] = 'required|integer|min:1|max:4';
        }

        $request->validate($rules);

        $data = $request->only(array_merge(
            ['tanggal_assessment', 'periode', 'keterangan', 'lembaga', 'link_file'],
            array_keys(HistoryAssessmentKompetensi::competencies()),
            array_keys(HistoryAssessmentKompetensi::qualifications())
        ));

        $data['karyawan_id'] = $karyawan->id;

        $compR1    = 0;
        $compR2    = 0;
        $compUnder = 0;
        $qualUnder = 0;

        foreach (array_keys(HistoryAssessmentKompetensi::competencies()) as $key) {
            $val = (int) ($data[$key] ?? 0);
            if ($val === 1) { $compR1++; $compUnder++; }
            if ($val === 2) { $compR2++; $compUnder++; }
        }

        foreach (array_keys(HistoryAssessmentKompetensi::qualifications()) as $key) {
            $val = (int) ($data[$key] ?? 0);
            if ($val < 2) $qualUnder++;
        }

        $data['total_competency_under']    = $compUnder;
        $data['total_qualification_under'] = $qualUnder;
        $data['kesimpulan'] = ($compR1 === 0 && $compR2 <= 3 && $qualUnder === 0)
            ? 'QUALIFIED'
            : 'NOT QUALIFIED';

        HistoryAssessmentKompetensi::create($data);

        $this->log(
            'tambah',
            'Assessment Kompetensi',
            $karyawan->nama,
            'Kesimpulan: ' . $data['kesimpulan']
            . ' | Rating1: ' . $compR1
            . ' | Rating2: ' . $compR2
            . ' | QualUnder: ' . $qualUnder
            . ' | Lembaga: ' . ($data['lembaga'] ?? '-')
        );

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