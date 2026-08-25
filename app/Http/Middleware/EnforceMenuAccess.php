<?php

namespace App\Http\Middleware;

use App\Models\Karyawan;
use App\Models\User;
use App\Support\MenuAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Blokir admin membuka menu yang tidak diizinkan — termasuk bila URL diketik
 * langsung (bukan sekadar disembunyikan di sidebar).
 *
 * Hanya role 'admin' yang dibatasi di sini. super_admin bebas; 'user' dan tamu
 * ditangani oleh middleware role yang sudah ada. Route yang tidak terdaftar di
 * registry (notifikasi, profil, logout, dsb.) tidak dijaga.
 */
class EnforceMenuAccess
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'admin') {
            return $next($request);
        }

        $menu = MenuAccess::menuForRoute($request->route()?->getName());

        if ($menu !== null && ! $user->canAccessMenu($menu)) {
            // Pengecualian: siapa pun boleh membuka catatan karyawan DIRINYA
            // SENDIRI, walau menu "Profil Karyawan" tidak diberikan. Pembatasan
            // menu mengatur akses ke data karyawan LAIN — bukan data diri.
            // Dipakai oleh tautan "Lihat Profil" di halaman Profil Saya.
            if ($this->melihatCatatanSendiri($request, $user)) {
                return $next($request);
            }

            // Jangan buntu di 403 — arahkan ke halaman yang boleh dibuka
            // (menu pertama yang diizinkan, atau halaman "Belum ada akses").
            // homeRoute() selalu mengembalikan route yang lolos filter ini,
            // sehingga tidak akan terjadi redirect berulang.
            return redirect()->route($user->homeRoute())
                ->with('error', 'Anda tidak memiliki akses ke menu tersebut.');
        }

        return $next($request);
    }

    /**
     * Benar hanya bila permintaan ini membuka HALAMAN LIHAT (bukan ubah/hapus)
     * atas catatan karyawan milik pengguna itu sendiri, dicocokkan lewat NIK.
     */
    private function melihatCatatanSendiri(Request $request, User $user): bool
    {
        if ($request->route()?->getName() !== 'karyawan.show') {
            return false;
        }

        // Akun tanpa NIK tak punya catatan karyawan — jangan sampai cocok
        // dengan baris ber-NIK kosong.
        if (blank($user->nik)) {
            return false;
        }

        $karyawan = $request->route('karyawan');

        return $karyawan instanceof Karyawan
            && (string) $karyawan->nik === (string) $user->nik;
    }
}
