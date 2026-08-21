<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\NotifikasiRead;
use App\Support\MenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Batasi notifikasi sesuai akses menu user.
     *  - super_admin → semua (tanpa filter)
     *  - lainnya     → hanya jenis yang menunya boleh diakses; jenis yang tidak
     *                  terpetakan (umum) tetap tampil untuk semua.
     */
    private function applyTipeFilter($query)
    {
        $user = Auth::user();
        if (! $user || $user->isSuperAdmin()) {
            return $query;
        }

        $map         = MenuAccess::notifikasiTypeMenu();
        $mappedTipes = array_keys($map);
        $allowed     = [];
        foreach ($map as $tipe => $menu) {
            if ($user->canAccessMenu($menu)) {
                $allowed[] = $tipe;
            }
        }

        return $query->where(function ($q) use ($allowed, $mappedTipes) {
            // Jenis umum (tak terpetakan) selalu tampil …
            $q->whereNotIn('tipe', $mappedTipes);
            // … plus jenis yang menunya boleh diakses user ini.
            if (! empty($allowed)) {
                $q->orWhereIn('tipe', $allowed);
            }
        });
    }

    /** Query dasar notifikasi yang sudah difilter sesuai akses user. */
    private function baseQuery()
    {
        return $this->applyTipeFilter(Notifikasi::query());
    }

    /**
     * Subquery: notifikasi yang BELUM dibaca oleh user tertentu
     * (tidak ada baris pasangan di notifikasi_reads).
     */
    private function scopeUnreadFor($query, int $userId)
    {
        return $query->whereNotExists(function ($q) use ($userId) {
            $q->selectRaw('1')
              ->from('notifikasi_reads')
              ->whereColumn('notifikasi_reads.notifikasi_id', 'notifikasis.id')
              ->where('notifikasi_reads.user_id', $userId);
        });
    }

    /** Id notifikasi yang sudah dibaca user (dibatasi pada kumpulan id tertentu). */
    private function readIdsFor(int $userId, $notifikasiIds): array
    {
        return NotifikasiRead::where('user_id', $userId)
            ->whereIn('notifikasi_id', $notifikasiIds)
            ->pluck('notifikasi_id')
            ->all();
    }

    // Ambil notifikasi untuk topbar (JSON) — status baca per-user
    public function fetch()
    {
        $userId = Auth::id();

        $notifikasis = $this->baseQuery()->orderBy('created_at', 'desc')->take(10)->get();
        $readIds     = $this->readIdsFor($userId, $notifikasis->pluck('id'));

        $mapped = $notifikasis->map(function ($n) use ($readIds) {
            return [
                'id'       => $n->id,
                'judul'    => $n->judul,
                'pesan'    => $n->pesan,
                'tipe'     => $n->tipe,
                'level'    => $n->level,
                'icon'     => $n->icon,
                'warna'    => $n->warna,
                'is_read'  => in_array($n->id, $readIds, true),
                'waktu'    => $n->created_at->diffForHumans(),
            ];
        });

        $unread = $this->scopeUnreadFor($this->baseQuery(), $userId)->count();

        return response()->json([
            'notifikasis' => $mapped,
            'unread'      => $unread,
        ]);
    }

    // Tandai semua sudah dibaca — hanya untuk user ini
    public function readAll()
    {
        $userId = Auth::id();

        $unreadIds = $this->scopeUnreadFor($this->baseQuery(), $userId)->pluck('id');

        if ($unreadIds->isNotEmpty()) {
            $now  = now();
            $rows = $unreadIds->map(fn ($id) => [
                'notifikasi_id' => $id,
                'user_id'       => $userId,
                'read_at'       => $now,
                'created_at'    => $now,
                'updated_at'    => $now,
            ])->all();

            // insertOrIgnore agar aman terhadap unique constraint bila ada balapan
            NotifikasiRead::insertOrIgnore($rows);
        }

        return response()->json(['success' => true]);
    }

    // Tandai 1 notifikasi sudah dibaca — hanya untuk user ini
    public function read(Notifikasi $notifikasi)
    {
        NotifikasiRead::firstOrCreate(
            ['notifikasi_id' => $notifikasi->id, 'user_id' => Auth::id()],
            ['read_at' => now()],
        );

        return response()->json(['success' => true]);
    }

    // Hapus notifikasi (global untuk semua admin)
    public function destroy(Notifikasi $notifikasi)
    {
        $notifikasi->delete(); // baris read pasangannya ikut terhapus (cascade)
        return response()->json(['success' => true]);
    }

    // Halaman semua notifikasi — status baca per-user
    public function index(Request $request)
    {
        $userId = Auth::id();

        $notifikasis = $this->baseQuery()
            ->when($request->tipe, fn ($q, $tipe) => $q->where('tipe', $tipe))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $readIds = $this->readIdsFor($userId, $notifikasis->pluck('id'));
        $notifikasis->getCollection()->transform(function ($n) use ($readIds) {
            $n->is_read = in_array($n->id, $readIds, true);
            return $n;
        });

        $unread = $this->scopeUnreadFor($this->baseQuery(), $userId)->count();

        return view('notifikasi.index', compact('notifikasis', 'unread'));
    }
}
