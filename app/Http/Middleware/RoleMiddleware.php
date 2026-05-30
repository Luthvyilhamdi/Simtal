<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage di routes: middleware('role:super_admin') atau middleware('role:admin,super_admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role, $roles)) {
            // User role → redirect ke struktur organisasi
            if ($user && $user->role === 'user') {
                return redirect()->route('struktur-organisasi.index')
                    ->with('info', 'Akses Anda terbatas hanya pada Struktur Organisasi.');
            }
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}