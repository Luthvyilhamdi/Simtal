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

    public function update(Request $request, $id)
    {
        $direktorat = Direktorat::findOrFail($id);
        $request->validate([
            'nama_direktorat' => 'required|string|unique:direktorat,nama_direktorat,' . $id,
        ]);
        $direktorat->update(['nama_direktorat' => $request->nama_direktorat]);
        return redirect()->route('master.direktorat.index')
            ->with('success', 'Direktorat berhasil diupdate!');
    }

    public function destroy($id)
    {
    try {
        Direktorat::findOrFail($id)->delete();
        return redirect()->route('master.direktorat.index')
            ->with('success', 'Direktorat berhasil dihapus!');
    } catch (\Illuminate\Database\QueryException $e) {
        return redirect()->route('master.direktorat.index')
            ->with('error', 'Direktorat tidak bisa dihapus karena masih digunakan di data lain!');
    }
    }
}