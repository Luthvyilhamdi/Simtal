<?php

namespace App\Console\Commands;

use App\Models\JobFamily;
use App\Services\KompetensiTeknisParser;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Logic parsing (baca border cell, deteksi generic dari suffix "- JF ...", derive
 * jenjang/urutan/kandidat unit) SUDAH DIPINDAH ke App\Services\KompetensiTeknisParser
 * (dipakai bareng dgn controller web upload) — command ini SEKARANG cuma tanggung jawab
 * CLI-nya saja: baca argumen/option, resolve nama rumpun (--rumpun, string, tetap ramah CLI)
 * ke job_family_id (Service-nya butuh ID, bukan string bebas lagi — lihat migrasi
 * rumpun_asal -> job_family_id), panggil Service, tulis hasilnya ke file Excel preview,
 * & cetak ringkasan ke console.
 */
class ParseKompetensiTeknisPreview extends Command
{
    protected $signature = 'komtek:parse-preview
        {file : Path file Excel "Profiling Kompetensi Teknis" mentah}
        {--rumpun=Human Capital & General Affair : Nama rumpun jabatan (job_family.nama, exact match case-insensitive) yang sedang diprofilkan}
        {--sheet= : Nama sheet yang dibaca (default: sheet aktif/pertama)}
        {--output= : Path file xlsx hasil tidy (default: storage/app/imports/Kompetensi Teknis/preview/tidy_preview_<timestamp>.xlsx)}';

    protected $description = 'Parse file Excel Profiling Kompetensi Teknis mentah menjadi draft tidy siap-review (tanpa insert ke database)';

    public function handle(): int
    {
        $filePath   = $this->argument('file');
        $rumpunNama = trim((string) $this->option('rumpun'));

        $jobFamily = JobFamily::whereRaw('LOWER(nama) = ?', [mb_strtolower($rumpunNama)])->first();

        if (!$jobFamily) {
            $this->error("Rumpun jabatan \"{$rumpunNama}\" tidak ditemukan di master Job Family. Nama yang tersedia:");
            foreach (JobFamily::orderBy('nama')->pluck('nama') as $nama) {
                $this->line('  - ' . $nama);
            }

            return self::FAILURE;
        }

        try {
            $result = KompetensiTeknisParser::parse(
                $filePath,
                $jobFamily->id,
                $this->option('sheet')
            );
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Kolom kompetensi terdeteksi: ' . $result['kompetensiColumnCount']);

        $tidyRows = $result['tidyRows'];
        $warnings = $result['warnings'];

        // Tulis output tidy + warnings ke xlsx.
        $outputPath = $this->option('output') ?: $this->defaultOutputPath();
        $this->writeOutput($outputPath, $tidyRows, $warnings);

        // Ringkasan ke console.
        $this->printSummary($tidyRows, $warnings, $outputPath);

        return self::SUCCESS;
    }

    private function defaultOutputPath(): string
    {
        $dir = storage_path('app/imports/Kompetensi Teknis/preview');

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir . DIRECTORY_SEPARATOR . 'tidy_preview_' . now()->format('Ymd_His') . '.xlsx';
    }

    private function writeOutput(string $outputPath, array $tidyRows, array $warnings): void
    {
        $out = new Spreadsheet();

        $tidySheet = $out->getActiveSheet();
        $tidySheet->setTitle('Tidy');
        $headers = [
            'row_id', 'baris_asal_excel', 'kandidat_nama_unit', 'jenjang_jabatan', 'urutan_jenjang',
            'grade', 'nama_jobs', 'managerial', 'nama_kompetensi', 'job_family_id', 'level', 'asal', 'prioritas',
        ];
        // strictNullComparison=true wajib -- default PhpSpreadsheet pakai loose comparison
        // sehingga integer 0 (managerial=false) ke-treat sama seperti null dan cell-nya dilewati kosong.
        $tidySheet->fromArray($headers, null, 'A1', true);
        $r = 2;
        foreach ($tidyRows as $row) {
            $tidySheet->fromArray(array_values($row), null, 'A' . $r, true);
            $r++;
        }

        $warnSheet = $out->createSheet();
        $warnSheet->setTitle('Warnings');
        $warnSheet->fromArray(['baris_asal_excel', 'pesan'], null, 'A1', true);
        $r = 2;
        foreach ($warnings as $w) {
            $warnSheet->fromArray([$w['baris_asal_excel'], $w['pesan']], null, 'A' . $r, true);
            $r++;
        }

        $out->setActiveSheetIndex(0);

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        (new Xlsx($out))->save($outputPath);
    }

    private function printSummary(array $tidyRows, array $warnings, string $outputPath): void
    {
        $this->newLine();
        $this->info('=== RINGKASAN ===');
        $this->line('Total baris tidy dihasilkan: ' . count($tidyRows));
        $this->line('File output: ' . $outputPath);

        $units = collect($tidyRows)->pluck('kandidat_nama_unit')->unique()->filter()->sort()->values();
        $this->newLine();
        $this->info('Kandidat nama unit unik (' . $units->count() . ') — cocokkan manual ke unit_organisasi:');
        foreach ($units as $u) {
            $this->line('  - ' . $u);
        }

        // Resolve job_family_id -> nama SEKALI di sini (bukan per baris) utk tampilan console saja.
        $jobFamilyNames = JobFamily::pluck('nama', 'id');

        $generic = collect($tidyRows)
            ->where('asal', 'generic')
            ->map(function ($r) use ($jobFamilyNames) {
                $namaRumpun = $r['job_family_id'] !== null
                    ? ($jobFamilyNames[$r['job_family_id']] ?? "id={$r['job_family_id']}")
                    : 'TIDAK DIKENALI (lihat Warnings)';

                return $r['nama_kompetensi'] . ' || ' . $namaRumpun;
            })
            ->unique()
            ->sort()
            ->values();
        $this->newLine();
        $this->info('Kompetensi generic unik (' . $generic->count() . ') — nama_kompetensi || rumpun jabatan:');
        foreach ($generic as $g) {
            $this->line('  - ' . $g);
        }

        $tally = collect($tidyRows)->countBy('asal');
        $this->newLine();
        $this->info('Tally asal (prioritas belum relevan — semua default secondary, dipilih manual di web step "Pilih Primary"):');
        foreach (['native', 'generic'] as $a) {
            $this->line("  - {$a}: " . ($tally[$a] ?? 0));
        }

        $this->newLine();
        if (count($warnings) > 0) {
            $this->warn('Warnings (' . count($warnings) . ') — lihat juga sheet "Warnings" di file output:');
            foreach ($warnings as $w) {
                $prefix = $w['baris_asal_excel'] !== '' ? "Baris {$w['baris_asal_excel']}: " : '';
                $this->line('  - ' . $prefix . $w['pesan']);
            }
        } else {
            $this->info('Tidak ada warning.');
        }
    }
}
