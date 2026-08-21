<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;

/**
 * "Masuk sebagai" — super admin menyamar sementara sebagai akun lain untuk
 * memverifikasi akses menu / troubleshooting, tanpa perlu password akun itu.
 *
 * Identitas super admin asli disimpan di session('impersonator_id'); selama
 * menyamar, super admin tunduk pada batasan akun tersebut. Route "leave"
 * berada di grup auth biasa (bukan super_admin) agar tetap bisa diakses saat
 * sedang menyamar.
 */
class ImpersonationController extends Controller
{
    use LogsActivity;

    /** Mulai menyamar sebagai $akun (hanya super admin asli). */
    public function start(User $akun)
    {
        /** @var User $current */
        $current = Auth::user();

        // Harus super admin asli & belum sedang menyamar.
        if (! $current->isSuperAdmin() || session()->has('impersonator_id')) {
            abort(403);
        }

        // Tidak boleh menyamar diri sendiri atau sesama super admin.
        if ($akun->id === $current->id || $akun->isSuperAdmin()) {
            return redirect()->route('akun.index')
                ->with('error', 'Tidak bisa masuk sebagai akun ini.');
        }

        // Catat SEBELUM berganti agar pelaku tercatat sebagai super admin.
        $this->log('impersonate', 'Akun', $akun->name, $current->name . ' masuk sebagai ' . $akun->name);

        session(['impersonator_id' => $current->id]);
        Auth::login($akun);

        return redirect()->route($akun->homeRoute())
            ->with('success', 'Anda sekarang masuk sebagai ' . $akun->name . '.');
    }

    /** Kembali ke akun super admin asli. */
    public function leave()
    {
        $impersonatorId = session('impersonator_id');

        if (! $impersonatorId) {
            return redirect()->route('dashboard');
        }

        $original          = User::find($impersonatorId);
        $impersonatedName  = Auth::user()?->name ?? '-';

        session()->forget('impersonator_id');

        if (! $original) {
            // Akun asli hilang — amankan dengan logout.
            Auth::logout();
            return redirect()->route('login');
        }

        Auth::login($original);
        // Catat SESUDAH kembali agar pelaku tercatat sebagai super admin.
        $this->log('impersonate_leave', 'Akun', $impersonatedName, 'Kembali dari akun ' . $impersonatedName);

        return redirect()->route('akun.index')
            ->with('success', 'Kembali ke akun Anda.');
    }
}
