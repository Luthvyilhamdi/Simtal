<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Toefl;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Kelola nilai TOEFL per karyawan (halaman tersendiri, mirip History Pendidikan).
 * Satu karyawan bisa punya beberapa hasil tes (riwayat).
 */
class ToeflController extends Controller
{
    use LogsActivity;

    public function index(Karyawan $karyawan)
    {
        $karyawan->load(['jabatan', 'jobGrade', 'personGrade']);

        $toefls = $karyawan->toefls()
            ->orderByDesc('tanggal_tes')
            ->orderByDesc('id')
            ->get();

        return view('toefl.index', compact('karyawan', 'toefls'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $data = $this->validated($request);

        $karyawan->toefls()->create($data);
        $this->log('tambah', 'TOEFL', $karyawan->nama, 'Skor: ' . $data['skor']);

        return redirect()->route('toefl.index', $karyawan)->with('success', 'Nilai TOEFL berhasil ditambahkan!');
    }

    public function update(Request $request, Karyawan $karyawan, Toefl $toefl)
    {
        abort_if($toefl->karyawan_id !== $karyawan->id, 404);

        $toefl->update($this->validated($request));
        $this->log('edit', 'TOEFL', $karyawan->nama, 'Skor: ' . $toefl->skor);

        return redirect()->route('toefl.index', $karyawan)->with('success', 'Nilai TOEFL berhasil diperbarui!');
    }

    public function destroy(Karyawan $karyawan, Toefl $toefl)
    {
        abort_if($toefl->karyawan_id !== $karyawan->id, 404);

        $skor = $toefl->skor;
        $toefl->delete();
        $this->log('hapus', 'TOEFL', $karyawan->nama, 'Skor: ' . $skor);

        return redirect()->route('toefl.index', $karyawan)->with('success', 'Nilai TOEFL berhasil dihapus!');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'skor'        => 'required|numeric|min:0|max:677',
            'jenis'       => ['nullable', Rule::in(Toefl::JENIS)],
            'tanggal_tes' => 'nullable|date',
            'lembaga'     => 'nullable|string|max:255',
            'keterangan'  => 'nullable|string|max:1000',
            'link_file'   => 'nullable|url|max:2048',
        ], [
            'skor.required' => 'Skor TOEFL wajib diisi.',
            'skor.max'      => 'Skor TOEFL maksimal 677 (ITP).',
        ]);
    }
}
