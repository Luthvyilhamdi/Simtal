<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Backup database SiMental dengan PHP murni (tanpa mysqldump), agar jalan sama
 * baiknya di XAMPP lokal maupun shared hosting cPanel.
 *
 * Hasil: file .zip berisi satu dump .sql (CREATE TABLE + INSERT), disimpan di
 * storage/app/backups. Hanya menyimpan sejumlah backup terbaru (retensi).
 */
class DatabaseBackupService
{
    /** Folder penyimpanan relatif terhadap disk 'local' (storage/app). */
    private string $dir = 'backups';

    /** Jumlah backup terbaru yang dipertahankan; sisanya dihapus otomatis. */
    private int $keep = 10;

    /**
     * Jalankan backup. Mengembalikan info file yang dibuat.
     *
     * @return array{file: string, size: int, path: string}
     */
    public function run(): array
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $disk = Storage::disk('local');
        $disk->makeDirectory($this->dir);

        $dbName   = DB::getDatabaseName();
        $baseName = 'simental-backup-' . now()->format('Y-m-d_His');

        $sqlRel  = $this->dir . '/' . $baseName . '.sql';
        $sqlPath = $disk->path($sqlRel);

        $handle = @fopen($sqlPath, 'w');
        if (!$handle) {
            throw new RuntimeException('Tidak bisa menulis file backup (cek ruang disk & izin folder storage).');
        }

        try {
            $this->writeDump($handle, $dbName);
        } finally {
            fclose($handle);
        }

        // Kompres .sql menjadi .zip lalu hapus .sql mentah.
        $zipRel  = $this->dir . '/' . $baseName . '.zip';
        $zipPath = $disk->path($zipRel);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($sqlPath);
            throw new RuntimeException('Tidak bisa membuat file .zip.');
        }
        $zip->addFile($sqlPath, $baseName . '.sql');

        // Sertakan seluruh file upload (storage/app) agar backup lengkap —
        // kecuali folder backup itu sendiri (hindari menyertakan backup lama / rekursif).
        $excludeDir = $disk->path($this->dir); // storage/app/private/backups
        $fileCount  = $this->addDirToZip($zip, storage_path('app'), 'storage/app', [$excludeDir]);

        $zip->addFromString('RESTORE.txt',
            "SiMental — Backup Lengkap\n" .
            "Dibuat: " . now()->toDateTimeString() . "\n\n" .
            "Isi paket:\n" .
            "  - {$baseName}.sql  : dump database\n" .
            "  - storage/app/...   : semua file upload (foto, surat, dll) — {$fileCount} file\n\n" .
            "Cara restore:\n" .
            "  1. Import {$baseName}.sql ke database tujuan (phpMyAdmin / mysql).\n" .
            "  2. Salin isi folder 'storage/app' ke 'storage/app' pada project tujuan\n" .
            "     (timpa foto-karyawan/, surat-penting/, dll).\n" .
            "  3. Aktifkan symlink: php artisan storage:link\n"
        );

        $zip->close();
        @unlink($sqlPath);

        $this->cleanup($disk);

        return [
            'file'  => $baseName . '.zip',
            'size'  => $disk->size($zipRel),
            'path'  => $zipRel,
            'files' => $fileCount,
        ];
    }

    /**
     * Tambahkan seluruh isi $absDir ke $zip di bawah prefix $zipPrefix,
     * melewati path apa pun yang berada di dalam salah satu $excludeAbs.
     *
     * @return int jumlah file (bukan folder) yang ditambahkan
     */
    private function addDirToZip(ZipArchive $zip, string $absDir, string $zipPrefix, array $excludeAbs = []): int
    {
        if (!is_dir($absDir)) {
            return 0;
        }

        $norm = fn ($p) => str_replace('\\', '/', $p);
        $excl = array_map($norm, $excludeAbs);
        $base = $norm($absDir);
        $count = 0;

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $item) {
            $abs = $norm($item->getPathname());

            foreach ($excl as $ex) {
                if ($abs === $ex || str_starts_with($abs, $ex . '/')) {
                    continue 2; // lewati file/folder yang dikecualikan
                }
            }

            $rel     = ltrim(substr($abs, strlen($base)), '/');
            $zipPath = $zipPrefix . '/' . $rel;

            if ($item->isDir()) {
                $zip->addEmptyDir($zipPath);
            } else {
                $zip->addFile($item->getPathname(), $zipPath);
                $count++;
            }
        }

        return $count;
    }

    /** Tulis seluruh dump ke file handle. */
    private function writeDump($handle, string $dbName): void
    {
        fwrite($handle,
            "-- SiMental Database Backup\n" .
            "-- Database : {$dbName}\n" .
            "-- Dibuat   : " . now()->toDateTimeString() . "\n" .
            "-- ------------------------------------------------------------\n\n" .
            "SET FOREIGN_KEY_CHECKS=0;\n" .
            "SET NAMES utf8mb4;\n"
        );

        $tables = DB::select('SHOW FULL TABLES');
        $key    = 'Tables_in_' . $dbName;

        foreach ($tables as $row) {
            $arr   = (array) $row;
            $table = $arr[$key] ?? array_values($arr)[0];
            $type  = $arr['Table_type'] ?? 'BASE TABLE';

            if ($type === 'VIEW') {
                continue; // lewati view (bukan tabel data)
            }

            $this->dumpTable($handle, (string) $table);
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
    }

    /** Tulis struktur + data satu tabel. */
    private function dumpTable($handle, string $table): void
    {
        $create    = (array) DB::select("SHOW CREATE TABLE `{$table}`")[0];
        $createSql = $create['Create Table'] ?? '';

        fwrite($handle,
            "\n-- ----------------------------\n" .
            "-- Tabel `{$table}`\n" .
            "-- ----------------------------\n" .
            "DROP TABLE IF EXISTS `{$table}`;\n" .
            $createSql . ";\n\n"
        );

        $pdo   = DB::getPdo();
        $count = 0;

        foreach (DB::table($table)->cursor() as $rowObj) {
            $row  = (array) $rowObj;
            $vals = array_map(
                // Kutip semua nilai non-null sebagai string; MySQL meng-cast
                // otomatis ke tipe kolom saat import (aman untuk angka/tanggal).
                fn ($v) => is_null($v) ? 'NULL' : $pdo->quote((string) $v),
                array_values($row)
            );
            $cols = '`' . implode('`,`', array_keys($row)) . '`';
            fwrite($handle, "INSERT INTO `{$table}` ({$cols}) VALUES (" . implode(',', $vals) . ");\n");
            $count++;
        }

        if ($count === 0) {
            fwrite($handle, "-- (tabel kosong)\n");
        }
    }

    /** Hapus backup lama, sisakan $keep terbaru. */
    private function cleanup($disk): void
    {
        $this->files($disk)->slice($this->keep)->each(fn ($f) => $disk->delete($f['path']));
    }

    /**
     * Daftar file backup (.zip), terbaru dulu.
     *
     * @return Collection<int, array{name: string, size: int, time: int, path: string}>
     */
    public function files($disk = null): Collection
    {
        $disk = $disk ?: Storage::disk('local');

        if (!$disk->exists($this->dir)) {
            return collect();
        }

        return collect($disk->files($this->dir))
            ->filter(fn ($f) => str_ends_with($f, '.zip'))
            ->map(fn ($f) => [
                'name' => basename($f),
                'size' => $disk->size($f),
                'time' => $disk->lastModified($f),
                'path' => $f,
            ])
            ->sortByDesc('time')
            ->values();
    }

    /** Path relatif yang aman untuk nama file backup (cegah path traversal). */
    public function safePath(string $file): ?string
    {
        $file = basename($file);
        if (!str_ends_with($file, '.zip')) {
            return null;
        }
        return $this->dir . '/' . $file;
    }
}
