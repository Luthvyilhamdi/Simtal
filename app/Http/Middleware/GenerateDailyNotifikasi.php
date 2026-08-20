<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Auto-scheduler notifikasi tanpa cron server.
 *
 * Karena server produksi tidak dikelola sendiri (tidak bisa memasang cron
 * `schedule:run`), notifikasi harian digenerate langsung dari aplikasi:
 * sekali per hari, saat ada admin yang membuka aplikasi.
 *
 * Eksekusi dilakukan di terminate() — SETELAH response dikirim ke browser —
 * sehingga tidak menambah waktu muat halaman. Command `notifikasi:generate`
 * bersifat idempotent, jadi aman meski (jarang) terpicu dua kali.
 */
class GenerateDailyNotifikasi
{
    private const CACHE_KEY = 'notifikasi_generated_date';

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        // Hanya untuk sesi yang sudah login
        if (!Auth::check()) {
            return;
        }

        $today = now()->toDateString();

        // Sudah dibuat hari ini? lewati (biaya cuma 1 query cache)
        if (Cache::get(self::CACHE_KEY) === $today) {
            return;
        }

        // Tandai dulu agar request lain di hari yang sama tidak ikut menjalankan
        Cache::put(self::CACHE_KEY, $today, now()->endOfDay());

        try {
            Artisan::call('notifikasi:generate');
        } catch (\Throwable $e) {
            // Jangan ganggu pengguna bila gagal — cukup catat di log
            Log::warning('Auto-generate notifikasi gagal: ' . $e->getMessage());
        }
    }
}
