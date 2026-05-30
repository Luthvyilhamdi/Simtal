<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware untuk memblokir role 'user' dari halaman selain Struktur Organisasi.
 * Gunakan di group route: middleware('not_user_role')
 */
class UserRoleMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if ($user && $user->role === 'user') {
            return redirect()->route('struktur-organisasi.index')
                ->with('info', 'Akses Anda terbatas hanya pada Struktur Organisasi.');
        }

        return $next($request);
    }
}