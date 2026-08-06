<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notifikasi:generate')->dailyAt('07:00');

// Backup database otomatis tiap Minggu 02:00 (hanya jalan bila cron server aktif).
Schedule::command('db:backup')->weeklyOn(0, '02:00');