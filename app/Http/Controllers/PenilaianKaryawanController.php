<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\PenilaianKaryawan;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenilaianKaryawanController extends Controller
{
    use LogsActivity;

    public function index(Karyawan $karyawan, Request $request)
    {
        $query = PenilaianKaryawan::where('karyawan_id', $karyawan->id)
            ->orderBy('tahun', 'desc')
            ->orderBy('periode');

        if ($request->tahun) {
            $query->where('tahun', $request->tahun);
        }
        if ($request->tipe) {
            $query->where('tipe', $request->tipe);
        }

        $penilaians = $query->paginate(10)->appends(request()->query());

        $tahuns = PenilaianKaryawan::where('karyawan_id', $karyawan->id)
            ->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('penilaian_karyawan.index', compact('karyawan', 'penilaians', 'tahuns'));
    }

    public function create(Karyawan $karyawan)
    {
        return view('penilaian_karyawan.create', compact('karyawan'));
    }

    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'tahun'      => 'required|integer|min:2000|max:2100',
            'periode'    => 'required|in:triwulan_1,triwulan_2,triwulan_3,triwulan_4,tahunan',
            'tipe'       => 'required|in:KPI,360',
            'judul'      => 'required|string|max:255',
            'nilai'      => 'required|numeric|min:0|max:999.99',
            'keterangan' => 'nullable|string|max:500',
        ]);

        PenilaianKaryawan::create([
            'karyawan_id' => $karyawan->id,
            'tahun'       => $request->tahun,
            'periode'     => $request->periode,
            'tipe'        => $request->tipe,
            'judul'       => $request->judul,
            'nilai'       => $request->nilai,
            'keterangan'  => $request->keterangan,
            'created_by'  => Auth::id(),
        ]);

        $this->log('tambah', 'Penilaian Karyawan', $karyawan->nama,
            $request->tipe . ' | ' . $request->tahun . ' ' . $request->periode . ' | Nilai: ' . $request->nilai);

        return redirect()->route('penilaian_karyawan.index', $karyawan)
            ->with('success', 'Data penilaian berhasil ditambahkan!');
    }

    public function destroy(Karyawan $karyawan, PenilaianKaryawan $penilaian)
    {
        $judul = $penilaian->judul;
        $tipe  = $penilaian->tipe;
        $penilaian->delete();

        $this->log('hapus', 'Penilaian Karyawan', $karyawan->nama,
            $tipe . ' | ' . $judul);

        return redirect()->route('penilaian_karyawan.index', $karyawan)
            ->with('success', 'Data penilaian berhasil dihapus!');
    }
}