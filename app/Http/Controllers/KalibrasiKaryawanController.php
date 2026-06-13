<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KalibrasiKaryawan;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KalibrasiKaryawanController extends Controller
{
    use LogsActivity;

    public function index(Karyawan $karyawan)
    {
        $kalibrasis = KalibrasiKaryawan::where('karyawan_id', $karyawan->id)
            ->orderBy('tahun', 'desc')
            ->paginate(10);

        return view('kalibrasi_karyawan.index', compact('karyawan', 'kalibrasis'));
    }

    public function create(Karyawan $karyawan)
    {
        $tahunTersedia = KalibrasiKaryawan::where('karyawan_id', $karyawan->id)
            ->pluck('tahun')->toArray();

        return view('kalibrasi_karyawan.create', compact('karyawan', 'tahunTersedia'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'tahun'      => 'required|integer|min:2000|max:2100',
            'nilai'      => 'required|in:FEE,EXE,MEE,BEE,FBE',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $existing = KalibrasiKaryawan::where('karyawan_id', $karyawan->id)
            ->where('tahun', $request->tahun)->first();

        if ($existing) {
            return back()->withErrors(['tahun' => 'Kalibrasi tahun ' . $request->tahun . ' sudah ada. Gunakan tombol Edit.'])->withInput();
        }

        KalibrasiKaryawan::create([
            'karyawan_id' => $karyawan->id,
            'tahun'       => $request->tahun,
            'nilai'       => $request->nilai,
            'keterangan'  => $request->keterangan,
            'created_by'  => Auth::id(),
        ]);

        $this->log('tambah', 'Kalibrasi', $karyawan->nama,
            'Tahun: ' . $request->tahun . ' | Nilai: ' . $request->nilai);

        return redirect()->route('kalibrasi_karyawan.index', $karyawan)
            ->with('success', 'Data kalibrasi berhasil ditambahkan!');
    }

    public function edit(Karyawan $karyawan, KalibrasiKaryawan $kalibrasi)
    {
        return view('kalibrasi_karyawan.edit', compact('karyawan', 'kalibrasi'));
    }

    public function update(Request $request, Karyawan $karyawan, KalibrasiKaryawan $kalibrasi)
    {
        $request->validate([
            'nilai'      => 'required|in:FEE,EXE,MEE,BEE,FBE',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $nilaiLama = $kalibrasi->nilai;

        $kalibrasi->update([
            'nilai'      => $request->nilai,
            'keterangan' => $request->keterangan,
        ]);

        $this->log('edit', 'Kalibrasi', $karyawan->nama,
            'Tahun: ' . $kalibrasi->tahun . ' | ' . $nilaiLama . ' → ' . $request->nilai);

        return redirect()->route('kalibrasi_karyawan.index', $karyawan)
            ->with('success', 'Data kalibrasi berhasil diperbarui!');
    }

    public function destroy(Karyawan $karyawan, KalibrasiKaryawan $kalibrasi)
    {
        $tahun = $kalibrasi->tahun;
        $nilai = $kalibrasi->nilai;
        $kalibrasi->delete();

        $this->log('hapus', 'Kalibrasi', $karyawan->nama,
            'Tahun: ' . $tahun . ' | Nilai: ' . $nilai);

        return redirect()->route('kalibrasi_karyawan.index', $karyawan)
            ->with('success', 'Data kalibrasi berhasil dihapus!');
    }
}