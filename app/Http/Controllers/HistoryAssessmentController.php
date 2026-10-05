<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\HistoryAssessment;
use App\Models\HistoryAssessmentKompetensi;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HistoryAssessmentController extends Controller
{
    use LogsActivity;

    public function index(Karyawan $karyawan)
    {
        $karyawan->load(['jabatan', 'departemen', 'jobGrade', 'personGrade']);

        $assessments = $karyawan->historyAssessment()
            ->orderBy('tanggal_pelaksanaan', 'desc')
            ->get();

        $assessmentKompetensi = $karyawan->historyAssessmentKompetensi()
            ->orderBy('tanggal_assessment', 'desc')
            ->get();

        // Riwayat penyuntingan per assessment.
        $riwayat = \App\Models\ActivityLog::where('subjek_type', HistoryAssessment::class)
            ->whereIn('subjek_id', $assessments->pluck('id'))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->get()->groupBy('subjek_id');

        return view('history_assessment.index', compact(
            'karyawan', 'assessments', 'assessmentKompetensi', 'riwayat'
        ));
    }

    public function create(Karyawan $karyawan)
    {
        $karyawan->load(['jobGrade', 'personGrade']);
        return view('history_assessment.create', compact('karyawan'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'job_stream'          => 'nullable|string',
            'tanggal_pelaksanaan' => 'required|date',
            'tingkat_pengukuran'  => 'nullable|string',
            'rekomendasi_inti'    => 'nullable|numeric|min:0|max:100',
            'rekomendasi_primer'  => 'nullable|numeric|min:0|max:100',
            'rekomendasi_skunder' => 'nullable|numeric|min:0|max:100',
            'rekomendasi_final'   => 'nullable|in:ready,ready_with_development,not_ready',
            'keterangan'          => 'nullable|string',
            'lembaga'             => 'nullable|string|max:255',
            'link_file'           => 'nullable|url|max:2048',
        ]);

        $karyawan->load(['jobGrade', 'personGrade']);

        $usia          = Carbon::parse($karyawan->tanggal_lahir)->age;
        $tanggalExpIdp = Carbon::parse($request->tanggal_pelaksanaan)->addYears(2);

        $assessment = HistoryAssessment::create([
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
            'lembaga'             => $request->lembaga,
            'link_file'           => $request->link_file,
        ]);

        $this->log('tambah', 'Assessment', $karyawan->nama,
            'Assessment dibuat - tanggal pelaksanaan ' . $request->tanggal_pelaksanaan,
            $assessment);

        return redirect()
            ->route('history_assessment.index', $karyawan)
            ->with('success', 'History assessment berhasil ditambahkan!');
    }

    public function edit(Karyawan $karyawan, HistoryAssessment $historyAssessment)
    {
        abort_unless((int) $historyAssessment->karyawan_id === (int) $karyawan->id, 404);

        $karyawan->load(['jobGrade', 'personGrade']);

        // Form sama dengan Tambah, pembedanya $h.
        return view('history_assessment.create', [
            'karyawan' => $karyawan,
            'h'        => $historyAssessment,
        ]);
    }

    public function update(Request $request, Karyawan $karyawan, HistoryAssessment $historyAssessment)
    {
        abort_unless((int) $historyAssessment->karyawan_id === (int) $karyawan->id, 404);

        $request->validate([
            'job_stream'          => 'nullable|string',
            'tanggal_pelaksanaan' => 'required|date',
            'tingkat_pengukuran'  => 'nullable|string',
            'rekomendasi_inti'    => 'nullable|numeric|min:0|max:100',
            'rekomendasi_primer'  => 'nullable|numeric|min:0|max:100',
            'rekomendasi_skunder' => 'nullable|numeric|min:0|max:100',
            'rekomendasi_final'   => 'nullable|in:ready,ready_with_development,not_ready',
            'keterangan'          => 'nullable|string',
            'lembaga'             => 'nullable|string|max:255',
            'link_file'           => 'nullable|url|max:2048',
        ]);

        $baru = [
            'job_stream'          => $request->job_stream,
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'tingkat_pengukuran'  => $request->tingkat_pengukuran,
            'rekomendasi_inti'    => $request->rekomendasi_inti,
            'rekomendasi_primer'  => $request->rekomendasi_primer,
            'rekomendasi_skunder' => $request->rekomendasi_skunder,
            'rekomendasi_final'   => $request->rekomendasi_final,
            'keterangan'          => $request->keterangan,
            'lembaga'             => $request->lembaga,
            'link_file'           => $request->link_file,
        ];

        // Tanggal exp = tanggal pelaksanaan + 2 tahun.
        $baru['tanggal_exp_idp'] = Carbon::parse($request->tanggal_pelaksanaan)->addYears(2);

        $perubahan = $this->ringkasPerubahan($historyAssessment, $baru);
        $historyAssessment->update($baru);

        $this->log('edit', 'Assessment', $karyawan->nama,
            $perubahan ?: 'Disimpan ulang tanpa perubahan nilai', $historyAssessment);

        return redirect()
            ->route('history_assessment.index', $karyawan)
            ->with('success', 'History assessment berhasil diperbarui!');
    }

    /** Ringkasan kolom yang berubah: "Label: lama -> baru". */
    private function ringkasPerubahan(HistoryAssessment $lama, array $baru): string
    {
        $label = [
            'tanggal_pelaksanaan' => 'Tgl pelaksanaan',
            'tingkat_pengukuran'  => 'Tingkat pengukuran',
            'job_stream'          => 'Job stream',
            'rekomendasi_inti'    => 'Rek. inti',
            'rekomendasi_primer'  => 'Rek. primer',
            'rekomendasi_skunder' => 'Rek. sekunder',
            'rekomendasi_final'   => 'Rekomendasi final',
            'lembaga'             => 'Lembaga',
            'tanggal_exp_idp'     => 'Tgl exp assessment',
        ];

        $rapi = function ($nilai) {
            if ($nilai instanceof \Carbon\Carbon) return $nilai->format('d/m/Y');
            if ($nilai === null || $nilai === '') return '(kosong)';
            return (string) $nilai;
        };

        $ubah = [];
        foreach ($label as $kolom => $teks) {
            $sebelum = $lama->{$kolom};
            $sesudah = $baru[$kolom] ?? null;

            // Bandingkan sebagai tanggal, bukan teks.
            if ($sebelum instanceof \Carbon\Carbon || str_starts_with($kolom, 'tanggal')) {
                $a = $sebelum ? \Carbon\Carbon::parse($sebelum)->toDateString() : null;
                $b = $sesudah ? \Carbon\Carbon::parse($sesudah)->toDateString() : null;
                if ($a === $b) continue;
                $ubah[] = $teks . ': ' . $rapi($sebelum ? \Carbon\Carbon::parse($sebelum) : null)
                    . ' -> ' . $rapi($sesudah ? \Carbon\Carbon::parse($sesudah) : null);
                continue;
            }

            if ((string) $sebelum === (string) $sesudah) continue;
            $ubah[] = $teks . ': ' . $rapi($sebelum) . ' -> ' . $rapi($sesudah);
        }

        // URL terlalu panjang untuk timeline.
        if ((string) $lama->link_file !== (string) ($baru['link_file'] ?? null)) {
            $ubah[] = 'Tautan berkas diperbarui';
        }
        if ((string) $lama->keterangan !== (string) ($baru['keterangan'] ?? null)) {
            $ubah[] = 'Keterangan diperbarui';
        }

        return implode(', ', $ubah);
    }

    public function destroy(Karyawan $karyawan, HistoryAssessment $historyAssessment)
    {
        $historyAssessment->delete();
        // Tanpa subjek: record-nya sudah dihapus.
        $this->log('hapus', 'Assessment', $karyawan->nama, 'Hapus data assessment');

        return redirect()
            ->route('history_assessment.index', $karyawan)
            ->with('success', 'History assessment berhasil dihapus!');
    }
}