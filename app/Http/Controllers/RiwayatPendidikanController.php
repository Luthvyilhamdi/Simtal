<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\RiwayatPendidikan;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Kelola Riwayat Pendidikan per karyawan di halaman TERSENDIRI (mirip History
 * Assessment) — dilepas dari form Tambah/Edit Karyawan agar mudah dimaintain.
 * Setiap perubahan otomatis menyegarkan "Pendidikan Terakhir" (jenjang tertinggi).
 */
class RiwayatPendidikanController extends Controller
{
    use LogsActivity;

    public function index(Karyawan $karyawan)
    {
        $karyawan->load(['jabatan', 'jobGrade', 'personGrade']);

        $riwayat = $karyawan->riwayatPendidikan()
            ->get()
            ->sortBy(fn ($r) => array_search($r->jenjang, Karyawan::JENJANG_PENDIDIKAN))
            ->values();

        return view('riwayat_pendidikan.index', compact('karyawan', 'riwayat'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $data = $this->validated($request);

        $karyawan->riwayatPendidikan()->create($data);
        $karyawan->refreshPendidikanTerakhir();

        $this->log('tambah', 'History Pendidikan', $karyawan->nama, 'Jenjang: ' . $data['jenjang']);

        return redirect()
            ->route('riwayat_pendidikan.index', $karyawan)
            ->with('success', 'History pendidikan berhasil ditambahkan!');
    }

    public function update(Request $request, Karyawan $karyawan, RiwayatPendidikan $riwayatPendidikan)
    {
        abort_if($riwayatPendidikan->karyawan_id !== $karyawan->id, 404);

        $data = $this->validated($request);

        $riwayatPendidikan->update($data);
        $karyawan->refreshPendidikanTerakhir();

        $this->log('edit', 'History Pendidikan', $karyawan->nama, 'Jenjang: ' . $data['jenjang']);

        return redirect()
            ->route('riwayat_pendidikan.index', $karyawan)
            ->with('success', 'History pendidikan berhasil diperbarui!');
    }

    public function destroy(Karyawan $karyawan, RiwayatPendidikan $riwayatPendidikan)
    {
        abort_if($riwayatPendidikan->karyawan_id !== $karyawan->id, 404);

        $jenjang = $riwayatPendidikan->jenjang;
        $riwayatPendidikan->delete();
        $karyawan->refreshPendidikanTerakhir();

        $this->log('hapus', 'History Pendidikan', $karyawan->nama, 'Jenjang: ' . $jenjang);

        return redirect()
            ->route('riwayat_pendidikan.index', $karyawan)
            ->with('success', 'History pendidikan berhasil dihapus!');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'jenjang'   => ['required', Rule::in(Karyawan::JENJANG_PENDIDIKAN)],
            'jurusan'   => 'nullable|string|max:255',
            'institusi' => 'nullable|string|max:255',
        ], [
            'jenjang.required' => 'Jenjang pendidikan wajib dipilih.',
            'jenjang.in'       => 'Jenjang pendidikan tidak valid.',
        ]);
    }
}
