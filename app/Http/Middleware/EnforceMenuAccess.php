<?php

namespace App\Http\Middleware;

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
            // Jangan buntu di 403 — arahkan ke halaman yang boleh dibuka
            // (menu pertama yang diizinkan, atau halaman "Belum ada akses").
            // homeRoute() selalu mengembalikan route yang lolos filter ini,
            // sehingga tidak akan terjadi redirect berulang.
            return redirect()->route($user->homeRoute())
                ->with('error', 'Anda tidak memiliki akses ke menu tersebut.');
        }

        return $next($request);
    }
}
