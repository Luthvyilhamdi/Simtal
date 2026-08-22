<?php

namespace App\Http\Controllers;

use App\Models\JobFamily;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

/**
 * Kelola master data Rumpun Jabatan (Job Family) — dipakai dropdown WAJIB pilih di form
 * Import Kompetensi Teknis (KompetensiTeknisImportController::create()) & relasi
 * kompetensi_teknis.job_family_id (FK, NOT NULL). Minimal (index + store) dulu sesuai
 * kebutuhan saat ini — form tambah baru inline di halaman index (bukan halaman create
 * terpisah), lihat resources/views/job_family/index.blade.php. Edit/delete belum dibuat:
 * job_family sudah dipakai sbg FK di kompetensi_teknis, ubah/hapus baris butuh
 * pertimbangan lebih hati2 (dampak ke data yg sudah ada) drpd sekadar tambah baris baru.
 */
class JobFamilyController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $jobFamilies = JobFamily::withCount('kompetensiTeknis')->orderBy('nama')->get();

        return view('job_family.index', compact('jobFamilies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:job_family,nama',
        ], [
            'nama.required' => 'Nama rumpun jabatan wajib diisi.',
            'nama.unique'   => 'Nama rumpun jabatan ini sudah ada di master.',
        ]);

        $nama      = trim($data['nama']);
        $jobFamily = JobFamily::create(['nama' => $nama]);

        $this->log('tambah', 'Job Family', $jobFamily->nama, "Tambah rumpun jabatan baru ke master: \"{$jobFamily->nama}\".");

        return redirect()->route('organisasi.job-family.index')
            ->with('success', "Rumpun jabatan \"{$nama}\" berhasil ditambahkan.");
    }
}
