<?php

namespace App\Http\Controllers;

use App\Models\TalentPool;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TalentPoolController extends Controller
{
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

        $talents = $query->orderBy('klasifikasi')->get();

        $stats = [
            'total'     => $talents->count(),
            'longlist'  => $talents->where('klasifikasi', 'longlist')->count(),
            'shortlist' => $talents->where('klasifikasi', 'shortlist')->count(),
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

        // Cek duplikasi
        $exists = TalentPool::where('karyawan_id', $request->karyawan_id)
            ->where('periode', $request->periode)->exists();

        if ($exists) {
            return back()->withErrors(['karyawan_id' => 'Karyawan ini sudah ada di Talent Pool periode '.$request->periode.'.'])->withInput();
        }

        TalentPool::create([
            'karyawan_id' => $request->karyawan_id,
            'periode'     => $request->periode,
            'klasifikasi' => $request->klasifikasi,
            'catatan'     => $request->catatan,
            'created_by'  => Auth::id(),
        ]);

        return redirect()->route('talent_pool.index', ['periode' => $request->periode])
            ->with('success', 'Karyawan berhasil ditambahkan ke Talent Pool '.$request->periode.'!');
    }

    public function update(Request $request, TalentPool $talentPool)
    {
        $request->validate([
            'klasifikasi' => 'required|in:longlist,shortlist',
            'catatan'     => 'nullable|string|max:500',
        ]);

        $talentPool->update([
            'klasifikasi' => $request->klasifikasi,
            'catatan'     => $request->catatan,
        ]);

        return redirect()->route('talent_pool.index', ['periode' => $talentPool->periode])
            ->with('success', 'Klasifikasi berhasil diupdate!');
    }

    public function destroy(TalentPool $talentPool)
    {
        $periode = $talentPool->periode;
        $talentPool->delete();
        return redirect()->route('talent_pool.index', ['periode' => $periode])
            ->with('success', 'Karyawan berhasil dihapus dari Talent Pool!');
    }
}