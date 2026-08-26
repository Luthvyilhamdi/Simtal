<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->daftarAkunSamaran();
    }

    /**
     * Sediakan $akunSamaran untuk menu "Masuk sebagai" di topbar.
     *
     * Hanya diisi bila yang login super admin DAN belum sedang menyamar —
     * di luar itu koleksi dikirim kosong, supaya layout tidak perlu tahu
     * aturannya dan tidak ada query yang jalan sia-sia di tiap halaman.
     */
    private function daftarAkunSamaran(): void
    {
        View::composer('layouts.app', function ($view) {
            $user  = Auth::user();
            $boleh = $user instanceof User
                && $user->isSuperAdmin()
                && ! session()->has('impersonator_id');

            $view->with('akunSamaran', $boleh
                ? User::where('role', '!=', 'super_admin')
                    ->orderBy('name')
                    ->get(['id', 'name', 'email', 'role'])
                : collect());
        });
    }
}
