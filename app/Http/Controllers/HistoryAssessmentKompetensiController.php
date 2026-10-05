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
        $request->validate($this->aturan());

        $data = $this->siapkanData($request);
        $data['karyawan_id'] = $karyawan->id;

        $kompetensi = HistoryAssessmentKompetensi::create($data);

        $this->log('tambah', 'Assessment Kompetensi', $karyawan->nama,
            'Assessment kompetensi dibuat - kesimpulan ' . $data['kesimpulan'],
            $kompetensi);

        return redirect()
            ->route('history_assessment.index', $karyawan)
            ->with('success', 'Assessment kompetensi berhasil disimpan!');
    }

    public function edit(Karyawan $karyawan, HistoryAssessmentKompetensi $kompetensi)
    {
        abort_unless((int) $kompetensi->karyawan_id === (int) $karyawan->id, 404);

        return view('history_assessment_kompetensi.create', [
            'karyawan'       => $karyawan,
            'h'              => $kompetensi,
            'competencies'   => HistoryAssessmentKompetensi::competencies(),
            'qualifications' => HistoryAssessmentKompetensi::qualifications(),
        ]);
    }

    public function update(Request $request, Karyawan $karyawan, HistoryAssessmentKompetensi $kompetensi)
    {
        abort_unless((int) $kompetensi->karyawan_id === (int) $karyawan->id, 404);

        $request->validate($this->aturan());
        $data = $this->siapkanData($request);

        $perubahan = $this->ringkasPerubahan($kompetensi, $data);
        $kompetensi->update($data);

        $this->log('edit', 'Assessment Kompetensi', $karyawan->nama,
            $perubahan ?: 'Disimpan ulang tanpa perubahan nilai', $kompetensi);

        return redirect()
            ->route('history_assessment.index', $karyawan)
            ->with('success', 'Assessment kompetensi berhasil diperbarui!');
    }

    /** Aturan validasi dipakai bersama Tambah & Edit. */
    private function aturan(): array
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

        return $rules;
    }

    /** Susun data + hitung kesimpulan, dipakai Tambah & Edit. */
    private function siapkanData(Request $request): array
    {
        $kompetensiKeys  = array_keys(HistoryAssessmentKompetensi::competencies());
        $kualifikasiKeys = array_keys(HistoryAssessmentKompetensi::qualifications());

        $data = $request->only(array_merge(
            ['tanggal_assessment', 'periode', 'keterangan', 'lembaga', 'link_file'],
            $kompetensiKeys,
            $kualifikasiKeys
        ));

        $compR1 = 0; $compR2 = 0; $compUnder = 0; $qualUnder = 0;
        foreach ($kompetensiKeys as $key) {
            $val = (int) ($data[$key] ?? 0);
            if ($val === 1) { $compR1++; $compUnder++; }
            if ($val === 2) { $compR2++; $compUnder++; }
        }
        foreach ($kualifikasiKeys as $key) {
            if ((int) ($data[$key] ?? 0) < 2) $qualUnder++;
        }

        $data['total_competency_under']    = $compUnder;
        $data['total_qualification_under'] = $qualUnder;
        $data['kesimpulan'] = ($compR1 === 0 && $compR2 <= 3 && $qualUnder === 0)
            ? 'QUALIFIED'
            : 'NOT QUALIFIED';

        return $data;
    }

    /** Rangkum apa yang berubah, untuk riwayat per-record. */
    private function ringkasPerubahan(HistoryAssessmentKompetensi $lama, array $baru): string
    {
        $ubah = [];

        $tglLama = $lama->tanggal_assessment ? \Carbon\Carbon::parse($lama->tanggal_assessment)->toDateString() : null;
        $tglBaru = $baru['tanggal_assessment'] ? \Carbon\Carbon::parse($baru['tanggal_assessment'])->toDateString() : null;
        if ($tglLama !== $tglBaru) {
            $ubah[] = 'Tgl assessment: ' . ($tglLama ?: '(kosong)') . ' -> ' . ($tglBaru ?: '(kosong)');
        }
        if ($lama->kesimpulan !== $baru['kesimpulan']) {
            $ubah[] = 'Kesimpulan: ' . ($lama->kesimpulan ?: '(kosong)') . ' -> ' . $baru['kesimpulan'];
        }

        // Yang berguna di timeline: berapa nilai yang berubah.
        $nilaiKeys = array_merge(
            array_keys(HistoryAssessmentKompetensi::competencies()),
            array_keys(HistoryAssessmentKompetensi::qualifications())
        );
        $berubah = 0;
        foreach ($nilaiKeys as $key) {
            if ((int) $lama->{$key} !== (int) ($baru[$key] ?? 0)) $berubah++;
        }
        if ($berubah) $ubah[] = $berubah . ' nilai kompetensi diubah';

        foreach (['periode' => 'Periode', 'lembaga' => 'Lembaga'] as $kolom => $teks) {
            if ((string) $lama->{$kolom} !== (string) ($baru[$kolom] ?? null)) {
                $ubah[] = $teks . ': ' . ($lama->{$kolom} ?: '(kosong)') . ' -> ' . ($baru[$kolom] ?: '(kosong)');
            }
        }
        if ((string) $lama->link_file !== (string) ($baru['link_file'] ?? null)) $ubah[] = 'Tautan berkas diperbarui';
        if ((string) $lama->keterangan !== (string) ($baru['keterangan'] ?? null)) $ubah[] = 'Keterangan diperbarui';

        return implode(', ', $ubah);
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