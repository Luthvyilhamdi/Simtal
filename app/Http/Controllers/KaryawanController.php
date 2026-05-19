<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Direktorat;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobGrade;
use App\Models\PersonGrade;
use App\Models\Jabatan;
use App\Models\KodeStruktur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::with(['direktorat','kompartemen','departemen','jobGrade','personGrade','jabatan','kodeStruktur']);

        if ($request->search) {
            $query->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nik', 'like', '%'.$request->search.'%');
        }

        $karyawans = $query->latest()->paginate(10);
        return view('karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        return view('karyawan.create', [
            'direktorats'   => Direktorat::all(),
            'kompartemens'  => Kompartemen::all(),
            'departemens'   => Departemen::all(),
            'jobGrades'     => JobGrade::all(),
            'personGrades'  => PersonGrade::all(),
            'jabatans'      => Jabatan::all(),
            'kodeStrukturs' => KodeStruktur::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik'              => 'required|unique:karyawans,nik',
            'nama'             => 'required',
            'jenis_kelamin'    => 'required|in:L,P',
            'tempat_lahir'     => 'required',
            'tanggal_lahir'    => 'required|date',
            'tanggal_masuk'    => 'required|date',
            'jabatan_id'       => 'required',
            'direktorat_id'    => 'required',
            'kompartemen_id'   => 'required',
            'departemen_id'    => 'required',
            'job_grade_id'     => 'required',
            'person_grade_id'  => 'required',
            'kode_struktur_id' => 'required',
            'status'           => 'required',
            'jabatan_saat_ini' => 'required|string',
            'foto'             => 'nullable|image|max:2048',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto-karyawan', 'public');
        }

        Karyawan::create($data);
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan!');
    }

    public function show(Karyawan $karyawan)
    {
        $karyawan->load(['direktorat','kompartemen','departemen','jobGrade','personGrade','jabatan','kodeStruktur']);
        return view('karyawan.show', compact('karyawan'));
    }

    public function edit(Karyawan $karyawan)
    {
        return view('karyawan.edit', [
            'karyawan'      => $karyawan,
            'direktorats'   => Direktorat::all(),
            'kompartemens'  => Kompartemen::all(),
            'departemens'   => Departemen::all(),
            'jobGrades'     => JobGrade::all(),
            'personGrades'  => PersonGrade::all(),
            'jabatans'      => Jabatan::all(),
            'kodeStrukturs' => KodeStruktur::all(),
        ]);
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nik'              => 'required|unique:karyawans,nik,'.$karyawan->id,
            'nama'             => 'required',
            'jenis_kelamin'    => 'required|in:L,P',
            'tempat_lahir'     => 'required',
            'tanggal_lahir'    => 'required|date',
            'tanggal_masuk'    => 'required|date',
            'jabatan_id'       => 'required',
            'direktorat_id'    => 'required',
            'kompartemen_id'   => 'required',
            'departemen_id'    => 'required',
            'job_grade_id'     => 'required',
            'person_grade_id'  => 'required',
            'kode_struktur_id' => 'required',
            'status'           => 'required',
            'jabatan_saat_ini' => 'required|string',
            'foto'             => 'nullable|image|max:2048',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            if ($karyawan->foto) Storage::disk('public')->delete($karyawan->foto);
            $data['foto'] = $request->file('foto')->store('foto-karyawan', 'public');
        }

        $karyawan->update($data);
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diupdate!');
    }

    public function destroy(Karyawan $karyawan)
    {
        if ($karyawan->foto) Storage::disk('public')->delete($karyawan->foto);
        $karyawan->delete();
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil dihapus!');
    }
}