<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    // Ambil notifikasi untuk topbar (JSON)
    public function fetch()
    {
        $notifikasis = Notifikasi::orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($n) {
                return [
                    'id'       => $n->id,
                    'judul'    => $n->judul,
                    'pesan'    => $n->pesan,
                    'tipe'     => $n->tipe,
                    'level'    => $n->level,
                    'icon'     => $n->icon,
                    'warna'    => $n->warna,
                    'is_read'  => $n->is_read,
                    'waktu'    => $n->created_at->diffForHumans(),
                ];
            });

        $unread = Notifikasi::where('is_read', false)->count();

        return response()->json([
            'notifikasis' => $notifikasis,
            'unread'      => $unread,
        ]);
    }

    // Tandai semua sudah dibaca
    public function readAll()
    {
        Notifikasi::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // Tandai 1 notifikasi sudah dibaca
    public function read(Notifikasi $notifikasi)
    {
        $notifikasi->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // Hapus notifikasi
    public function destroy(Notifikasi $notifikasi)
    {
        $notifikasi->delete();
        return response()->json(['success' => true]);
    }

    // Halaman semua notifikasi
    public function index(Request $request)
    {
        $notifikasis = Notifikasi::when($request->tipe, fn($q, $tipe) => $q->where('tipe', $tipe))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        $unread = Notifikasi::where('is_read', false)->count();
        return view('notifikasi.index', compact('notifikasis', 'unread'));
    }
}