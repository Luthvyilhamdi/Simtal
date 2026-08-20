<?php

use Illuminate\Support\Facades\Schedule;

// Notifikasi harian kini digenerate otomatis dari dalam aplikasi (tanpa cron),
// lihat App\Http\Middleware\GenerateDailyNotifikasi. Baris schedule cron dihapus
// karena server produksi tidak menjalankan `schedule:run`.

// Backup database otomatis tiap Minggu 02:00 (hanya jalan bila cron server aktif;
// bila cron tidak tersedia, gunakan tombol Backup manual di aplikasi).
Schedule::command('db:backup')->weeklyOn(0, '02:00');