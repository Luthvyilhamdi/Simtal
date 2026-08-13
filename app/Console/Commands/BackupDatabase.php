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

        $this->info('✓ Backup dibuat: ' . $res['file'] . ' (' . number_format($res['size'] / 1048576, 2) . ' MB, ' . ($res['files'] ?? 0) . ' file upload)');
        return self::SUCCESS;
    }
}
