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

    public function update(Request $request, Kompartemen $kompartemen)
    {
        $request->validate([
            'nama_kompartemen' => 'required|string|unique:kompartemen,nama_kompartemen,' . $kompartemen->id,
        ]);
        $kompartemen->update(['nama_kompartemen' => $request->nama_kompartemen]);
        return redirect()->route('master.kompartemen.index')
            ->with('success', 'Kompartemen berhasil diupdate!');
    }

    public function destroy(Kompartemen $kompartemen)
    {
        $kompartemen->delete();
        return redirect()->route('master.kompartemen.index')
            ->with('success', 'Kompartemen berhasil dihapus!');
    }
}