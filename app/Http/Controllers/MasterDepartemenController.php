<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use Illuminate\Http\Request;

class MasterDepartemenController extends Controller
{
    public function index()
    {
        $data = Departemen::orderBy('nama_departemen')->paginate(15);
        return view('master.departemen', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_departemen' => 'required|string|unique:departemen,nama_departemen',
        ]);
        Departemen::create(['nama_departemen' => $request->nama_departemen]);
        return redirect()->route('master.departemen.index')
            ->with('success', 'Departemen berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $departemen = Departemen::findOrFail($id);
        $request->validate([
            'nama_departemen' => 'required|string|unique:departemen,nama_departemen,' . $id,
        ]);
        $departemen->update(['nama_departemen' => $request->nama_departemen]);
        return redirect()->route('master.departemen.index')
            ->with('success', 'Departemen berhasil diupdate!');
    }

    public function destroy($id)
    {
    try {
        Departemen::findOrFail($id)->delete();
        return redirect()->route('master.departemen.index')
            ->with('success', 'Departemen berhasil dihapus!');
    } catch (\Illuminate\Database\QueryException $e) {
        return redirect()->route('master.departemen.index')
            ->with('error', 'Departemen tidak bisa dihapus karena masih digunakan di data lain!');
    }
    }
}