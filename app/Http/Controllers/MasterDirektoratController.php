<?php

namespace App\Http\Controllers;

use App\Models\Direktorat;
use Illuminate\Http\Request;

class MasterDirektoratController extends Controller
{
    public function index()
    {
        $data = Direktorat::orderBy('nama_direktorat')->paginate(15);
        return view('master.direktorat', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_direktorat' => 'required|string|unique:direktorat,nama_direktorat',
        ]);
        Direktorat::create(['nama_direktorat' => $request->nama_direktorat]);
        return redirect()->route('master.direktorat.index')
            ->with('success', 'Direktorat berhasil ditambahkan!');
    }

    public function update(Request $request, Direktorat $direktorat)
    {
        $request->validate([
            'nama_direktorat' => 'required|string|unique:direktorat,nama_direktorat,' . $direktorat->id,
        ]);
        $direktorat->update(['nama_direktorat' => $request->nama_direktorat]);
        return redirect()->route('master.direktorat.index')
            ->with('success', 'Direktorat berhasil diupdate!');
    }

    public function destroy(Direktorat $direktorat)
    {
        $direktorat->delete();
        return redirect()->route('master.direktorat.index')
            ->with('success', 'Direktorat berhasil dihapus!');
    }
}