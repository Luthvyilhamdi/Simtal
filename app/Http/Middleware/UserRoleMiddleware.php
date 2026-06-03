<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware untuk memblokir role 'user' dari halaman selain Struktur Organisasi.
 * Gunakan di group route: middleware('not_user_role')
 */
class UserRoleMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user && $user->role === 'user') {
            return redirect()->route('struktur-organisasi.index')
                ->with('info', 'Akses Anda terbatas hanya pada Struktur Organisasi.');
        }

        return $next($request);
    }
}