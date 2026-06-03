<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage di routes: middleware('role:super_admin') atau middleware('role:admin,super_admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || !in_array($user->role, $roles)) {
            if ($user && $user->role === 'user') {
                return redirect()->route('struktur-organisasi.index')
                    ->with('info', 'Akses Anda terbatas hanya pada Struktur Organisasi.');
            }
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}