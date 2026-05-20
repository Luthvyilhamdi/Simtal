<?php

namespace App\Http\Controllers;

use App\Models\Kompartemen;
use Illuminate\Http\Request;

class MasterKompartemenController extends Controller
{
    public function index()
    {
        $data = Kompartemen::orderBy('nama_kompartemen')->paginate(15);
        return view('master.kompartemen', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kompartemen' => 'required|string|unique:kompartemen,nama_kompartemen',
        ]);
        Kompartemen::create(['nama_kompartemen' => $request->nama_kompartemen]);
        return redirect()->route('master.kompartemen.index')
            ->with('success', 'Kompartemen berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $kompartemen = Kompartemen::findOrFail($id);
        $request->validate([
            'nama_kompartemen' => 'required|string|unique:kompartemen,nama_kompartemen,' . $id,
        ]);
        $kompartemen->update(['nama_kompartemen' => $request->nama_kompartemen]);
        return redirect()->route('master.kompartemen.index')
            ->with('success', 'Kompartemen berhasil diupdate!');
    }

    public function destroy($id)
    {
    try {
        Kompartemen::findOrFail($id)->delete();
        return redirect()->route('master.kompartemen.index')
            ->with('success', 'Kompartemen berhasil dihapus!');
    } catch (\Illuminate\Database\QueryException $e) {
        return redirect()->route('master.kompartemen.index')
            ->with('error', 'Kompartemen tidak bisa dihapus karena masih digunakan di data lain!');
    }
    }
}