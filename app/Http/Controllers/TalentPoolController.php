<?php

namespace App\Http\Controllers;

use App\Models\TalentPool;
use App\Models\Karyawan;
use App\Exports\TalentPoolExport;
use App\Exports\TemplateTalentPoolExport;
use App\Imports\TalentPoolImport;
use App\Traits\LogsActivity;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TalentPoolController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $periodeList = TalentPool::selectRaw('periode')
            ->distinct()->orderBy('periode', 'desc')->pluck('periode');

        $periode = $request->periode ?? now()->year;

        $query = TalentPool::with(['karyawan.jobGrade', 'karyawan.personGrade'])
            ->where('periode', $periode);

        if ($request->search) {
            $query->whereHas('karyawan', function($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nik',  'like', '%'.$request->search.'%');
            });
        }

        if ($request->klasifikasi) {
            $query->where('klasifikasi', $request->klasifikasi);
        }

        $talents = $query->orderBy('klasifikasi')->paginate(10)->appends(request()->query());

        $stats = [
            'total'     => TalentPool::where('periode', $periode)->count(),
            'longlist'  => TalentPool::where('periode', $periode)->where('klasifikasi', 'longlist')->count(),
            'shortlist' => TalentPool::where('periode', $periode)->where('klasifikasi', 'shortlist')->count(),
        ];

        return view('talent_pool.index', compact('talents', 'periode', 'periodeList', 'stats'));
    }

    public function create()
    {
        $karyawans = Karyawan::orderBy('nama')->get();
        $periode   = now()->year;
        return view('talent_pool.create', compact('karyawans', 'periode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'periode'     => 'required|integer|min:2000|max:2100',
            'klasifikasi' => 'required|in:longlist,shortlist',
            'catatan'     => 'nullable|string|max:500',
        ]);

        $exists = TalentPool::where('karyawan_id', $request->karyawan_id)
            ->where('periode', $request->periode)->exists();

        if ($exists) {
            return back()->withErrors(['karyawan_id' => 'Karyawan ini sudah ada di Talent Pool periode '.$request->periode.'.'])->withInput();
        }

        $karyawan = Karyawan::find($request->karyawan_id);

        TalentPool::create([
            'karyawan_id' => $request->karyawan_id,
            'periode'     => $request->periode,
            'klasifikasi' => $request->klasifikasi,
            'catatan'     => $request->catatan,
            'created_by'  => Auth::id(),
        ]);

        $this->log('tambah', 'Talent Pool', $karyawan->nama,
            'Periode: ' . $request->periode . ' | ' . ucfirst($request->klasifikasi));

        return redirect()->route('talent_pool.index', ['periode' => $request->periode])
            ->with('success', 'Karyawan berhasil ditambahkan ke Talent Pool '.$request->periode.'!');
    }

    public function update(Request $request, TalentPool $talentPool)
    {
        $request->validate([
            'klasifikasi' => 'required|in:longlist,shortlist',
            'catatan'     => 'nullable|string|max:500',
        ]);

        $klasifikasiLama = $talentPool->klasifikasi;

        $talentPool->update([
            'klasifikasi' => $request->klasifikasi,
            'catatan'     => $request->catatan,
        ]);

        $this->log('edit', 'Talent Pool', $talentPool->karyawan->nama ?? '-',
            'Periode: ' . $talentPool->periode . ' | ' . $klasifikasiLama . ' → ' . $request->klasifikasi);

        return redirect()->route('talent_pool.index', ['periode' => $talentPool->periode])
            ->with('success', 'Klasifikasi berhasil diupdate!');
    }

    public function destroy(TalentPool $talentPool)
    {
        $periode     = $talentPool->periode;
        $nama        = $talentPool->karyawan->nama ?? '-';
        $klasifikasi = $talentPool->klasifikasi;
        $talentPool->delete();

        $this->log('hapus', 'Talent Pool', $nama,
            'Periode: ' . $periode . ' | ' . ucfirst($klasifikasi));

        return redirect()->route('talent_pool.index', ['periode' => $periode])
            ->with('success', 'Karyawan berhasil dihapus dari Talent Pool!');
    }

    // ===== EXPORT =====
    public function export(Request $request)
    {
        $filename = 'talent-pool-' . ($request->periode ?? now()->year) . '-' . now()->format('d-m-Y') . '.xlsx';
        return Excel::download(
            new TalentPoolExport(
                $request->periode,
                $request->klasifikasi,
                $request->search
            ),
            $filename
        );
    }

    // ===== IMPORT =====
    public function importPage()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }
        return view('talent_pool.import');
    }

    public function import(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'File wajib dipilih.',
            'file.mimes'    => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $import = new TalentPoolImport();
            Excel::import($import, $request->file('file'));

            $msg = "Berhasil mengimport {$import->getImported()} data talent pool.";
            if ($import->getSkipped() > 0) {
                $msg .= " {$import->getSkipped()} data dilewati (NIK tidak ditemukan / duplikat / format salah).";
            }

            $this->log('import', 'Talent Pool', 'Import Excel', "Berhasil import {$import->getImported()} data");

            return redirect()->route('talent_pool.index')->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }

        return Excel::download(
            new TemplateTalentPoolExport(),
            'template-import-talent-pool.xlsx'
        );
    }
}