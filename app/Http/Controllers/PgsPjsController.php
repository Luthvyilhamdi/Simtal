<?php

namespace App\Http\Controllers;

use App\Models\PgsPjs;
use App\Models\Karyawan;
use App\Exports\PgsPjsExport;
use Illuminate\Http\Request;

class PgsPjsController extends Controller
{
    public function index()
    {
        // Auto-update is_active berdasarkan tanggal_berakhir
        PgsPjs::where('is_active', true)
            ->where('tanggal_berakhir', '<', now())
            ->whereNotNull('tanggal_berakhir')
            ->update(['is_active' => false]);

        // Yang sedang berlangsung
        $aktif = PgsPjs::with('karyawan')
            ->where('is_active', true)
            ->orderBy('tanggal_berakhir', 'asc')
            ->get();

        // History (sudah berakhir)
        $history = PgsPjs::with('karyawan')
            ->where('is_active', false)
            ->orderBy('tanggal_berakhir', 'desc')
            ->paginate(10);

        return view('pgs_pjs.index', compact('aktif', 'history'));
    }

    public function create()
    {
        $karyawans = Karyawan::where('status', 'aktif')
            ->orderBy('nama')
            ->get();
        return view('pgs_pjs.create', compact('karyawans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id'     => 'required|exists:karyawans,id',
            'tipe'            => 'required|in:pgs,pjs',
            'jabatan_pgs_pjs' => 'required|string',
            'direktorat'      => 'nullable|string',
            'departemen'      => 'nullable|string',
            'no_sk'           => 'nullable|string',
            'tanggal_sk'      => 'nullable|date',
            'tanggal_mulai'   => 'required|date',
            'keterangan'      => 'nullable|string',
        ]);

        PgsPjs::create([
            'karyawan_id'      => $request->karyawan_id,
            'tipe'             => $request->tipe,
            'jabatan_pgs_pjs'  => $request->jabatan_pgs_pjs,
            'direktorat'       => $request->direktorat,
            'departemen'       => $request->departemen,
            'no_sk'            => $request->no_sk,
            'tanggal_sk'       => $request->tanggal_sk,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_berakhir' => null,
            'keterangan'       => $request->keterangan,
            'is_active'        => true,
        ]);

        return redirect()
            ->route('pgs_pjs.index')
            ->with('success', 'Data PGS/PJS berhasil ditambahkan!');
    }

    public function akhiri(Request $request, PgsPjs $pgsPjs)
    {
        $request->validate([
            'tanggal_berakhir' => 'required|date|after_or_equal:' . $pgsPjs->tanggal_mulai,
        ]);

        $pgsPjs->update([
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'is_active'        => false,
        ]);

        return redirect()
            ->route('pgs_pjs.index')
            ->with('success', 'PGS/PJS berhasil diakhiri!');
    }

    public function destroy(PgsPjs $pgsPjs)
    {
        $pgsPjs->delete();
        return redirect()
            ->route('pgs_pjs.index')
            ->with('success', 'Data PGS/PJS berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $tipe     = $request->tipe;
        $search   = $request->search;
        $filename = 'pgs-pjs-' . now()->format('d-m-Y') . '.xlsx';

        return (new PgsPjsExport($tipe, $search))->download($filename);
    }
}