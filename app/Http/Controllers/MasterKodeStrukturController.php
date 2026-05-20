<?php

namespace App\Http\Controllers;

use App\Models\KodeStruktur;
use Illuminate\Http\Request;

class MasterKodeStrukturController extends Controller
{
    public function index()
    {
        $data = KodeStruktur::orderBy('kode_struktur')->paginate(15);
        return view('master.kode_struktur', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_struktur' => 'required|string|unique:kode_struktur,kode_struktur',
        ]);
        KodeStruktur::create(['kode_struktur' => $request->kode_struktur]);
        return redirect()->route('master.kode-struktur.index')
            ->with('success', 'Kode Struktur berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $kodeStruktur = KodeStruktur::findOrFail($id);
        $request->validate([
            'kode_struktur' => 'required|string|unique:kode_struktur,kode_struktur,' . $id,
        ]);
        $kodeStruktur->update(['kode_struktur' => $request->kode_struktur]);
        return redirect()->route('master.kode-struktur.index')
            ->with('success', 'Kode Struktur berhasil diupdate!');
    }

    public function destroy($id)
{
    try {
        KodeStruktur::findOrFail($id)->delete();
        return redirect()->route('master.kode-struktur.index')
            ->with('success', 'Kode Struktur berhasil dihapus!');
    } catch (\Illuminate\Database\QueryException $e) {
        return redirect()->route('master.kode-struktur.index')
            ->with('error', 'Kode Struktur tidak bisa dihapus karena masih digunakan di data lain!');
    }
    }
}