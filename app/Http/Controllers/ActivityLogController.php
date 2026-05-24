<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('user_name', 'like', '%'.$request->search.'%')
                  ->orWhere('target', 'like', '%'.$request->search.'%')
                  ->orWhere('keterangan', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->aksi) {
            $query->where('aksi', $request->aksi);
        }

        if ($request->modul) {
            $query->where('modul', $request->modul);
        }

        if ($request->tanggal) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $logs = $query->paginate(20);

        $moduls = ActivityLog::distinct()->pluck('modul')->sort()->values();

        return view('activity_log.index', compact('logs', 'moduls'));
    }

    public function destroy(Request $request)
    {
        // Hapus semua log (hanya super admin)
        ActivityLog::truncate();
        return redirect()->route('activity_log.index')
            ->with('success', 'Semua log aktivitas berhasil dihapus!');
    }
}