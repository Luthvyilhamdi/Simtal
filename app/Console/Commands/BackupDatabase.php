<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature   = 'db:backup';
    protected $description = 'Backup database SIMTAL ke file .zip (berisi dump .sql)';

    public function handle(DatabaseBackupService $service): int
    {
        $this->info('Membuat backup database...');

        try {
            $res = $service->run();
        } catch (\Throwable $e) {
            $this->error('Backup gagal: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('✓ Backup dibuat: ' . $res['file'] . ' (' . number_format($res['size'] / 1024, 1) . ' KB)');
        return self::SUCCESS;
    }
}
