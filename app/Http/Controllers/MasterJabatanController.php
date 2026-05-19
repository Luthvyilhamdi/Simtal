<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\Request;

class MasterJabatanController extends Controller
{
    public function index()
    {
        $data = Jabatan::orderBy('nama_jabatan')->paginate(15);
        return view('master.jabatan', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|unique:jabatan,nama_jabatan',
        ]);
        Jabatan::create(['nama_jabatan' => $request->nama_jabatan]);
        return redirect()->route('master.jabatan.index')
            ->with('success', 'Jabatan berhasil ditambahkan!');
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|unique:jabatan,nama_jabatan,' . $jabatan->id,
        ]);
        $jabatan->update(['nama_jabatan' => $request->nama_jabatan]);
        return redirect()->route('master.jabatan.index')
            ->with('success', 'Jabatan berhasil diupdate!');
    }

    public function destroy(Jabatan $jabatan)
    {
        $jabatan->delete();
        return redirect()->route('master.jabatan.index')
            ->with('success', 'Jabatan berhasil dihapus!');
    }
}