<?php

namespace App\Http\Controllers;

use App\Models\PgsPjs;
use App\Models\Karyawan;
use App\Traits\LogsActivity;
use App\Exports\PgsPjsExport;
use Illuminate\Http\Request;

class PgsPjsController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        PgsPjs::where('is_active', true)
            ->where('tanggal_berakhir', '<', now())
            ->whereNotNull('tanggal_berakhir')
            ->update(['is_active' => false]);

        // Filter jenis penugasan: 'pgs' / 'pjs'. Null = tampilkan keduanya.
        $tipe = in_array($request->tipe, ['pgs', 'pjs'], true) ? $request->tipe : null;

        $aktif = PgsPjs::with('karyawan')
            ->where('is_active', true)
            ->when($tipe, fn ($q) => $q->where('tipe', $tipe))
            ->orderBy('tanggal_berakhir', 'asc')
            ->get();

        $history = PgsPjs::with('karyawan')
            ->where('is_active', false)
            ->when($tipe, fn ($q) => $q->where('tipe', $tipe))
            ->orderBy('tanggal_berakhir', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pgs_pjs.index', compact('aktif', 'history', 'tipe'));
    }

    public function create(Request $request)
    {
        // Jenis dibawa dari menu (PJS / PGS) agar form langsung terpilih.
        $tipe = in_array($request->tipe, ['pgs', 'pjs'], true) ? $request->tipe : null;

        $karyawans = Karyawan::where('status', 'aktif')
            ->orderBy('nama')
            ->get();
        return view('pgs_pjs.create', compact('karyawans', 'tipe'));
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

        $karyawan = Karyawan::find($request->karyawan_id);

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

        $this->log('tambah', 'PGS/PJS', $karyawan->nama, strtoupper($request->tipe) . ': ' . $request->jabatan_pgs_pjs);

        return redirect()
            ->route('pgs_pjs.index', ['tipe' => $request->tipe])
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

        $this->log('akhiri', 'PGS/PJS', $pgsPjs->karyawan->nama, 'Diakhiri: ' . $request->tanggal_berakhir);

        return redirect()
            ->route('pgs_pjs.index', ['tipe' => $pgsPjs->tipe])
            ->with('success', 'PGS/PJS berhasil diakhiri!');
    }

    public function destroy(PgsPjs $pgsPjs)
    {
        $nama = $pgsPjs->karyawan->nama ?? '-';
        $tipe = $pgsPjs->tipe;
        $pgsPjs->delete();

        $this->log('hapus', 'PGS/PJS', $nama, 'Hapus data PGS/PJS');

        return redirect()
            ->route('pgs_pjs.index', ['tipe' => $tipe])
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