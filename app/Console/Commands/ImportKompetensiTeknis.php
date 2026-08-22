<?php

namespace App\Console\Commands;

use App\Services\KompetensiTeknisImporter;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Logic insert (resolve/create kompetensi_teknis, cek duplikat unique constraint, insert
 * unit_kompetensi_teknis, hitung laporan) SUDAH DIPINDAH ke App\Services\
 * KompetensiTeknisImporter (dipakai bareng dgn controller web review/commit) — command
 * ini SEKARANG cuma tanggung jawab CLI-nya saja: baca argumen/option, baca file Excel
 * tidy, pakai dictionary UNIT_MAP hardcode punya command ini sendiri, panggil Service,
 * & cetak laporan ke console. Behaviour/laporan CLI TIDAK berubah dari sebelumnya (sudah
 * diverifikasi identik stlh refactor ini).
 */
class ImportKompetensiTeknis extends Command
{
    protected $signature = 'komtek:import
        {file : Path file Excel hasil tidy (sheet "Tidy") dari komtek:parse-preview}
        {--versi=38 : struktur_organisasi_versi_id acuan untuk semua baris}
        {--commit : Commit beneran. Tanpa flag ini, semua perubahan di-rollback di akhir (dry-run).}';

    protected $description = 'Import file tidy Kompetensi Teknis ke kompetensi_teknis & unit_kompetensi_teknis (default dry-run)';

    // Dictionary hasil verifikasi manual kandidat_nama_unit -> unit_organisasi_id (versi 38).
    // Sengaja hardcode, JANGAN auto-match ulang di sini.
    private const UNIT_MAP = [
        'Organisasi & Manajemen Talenta'   => 1449,
        'Remunerasi & Hubungan Industrial' => 1438,
        'Hubungan Industrial'              => 1636,
        'Knowledge Management & Budaya'    => 1660,
        'Learning Development'             => 1640,
        'Manajemen Talenta & Kinerja'      => 1670,
        'Organisasi'                       => 1669,
        'Remunerasi'                       => 1635,
    ];

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!is_file($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");

            return self::FAILURE;
        }

        $versiId = (int) $this->option('versi');
        $commit  = (bool) $this->option('commit');

        $rows = $this->readTidySheet($filePath);

        if ($rows === null) {
            return self::FAILURE;
        }

        $result = KompetensiTeknisImporter::import($rows, self::UNIT_MAP, $versiId, $commit);

        $this->printReport($result, $commit);

        return count($result['errors']) > 0 || $result['stoppedEarly'] ? self::FAILURE : self::SUCCESS;
    }

    private function readTidySheet(string $filePath): ?array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getSheetByName('Tidy');

        if (!$sheet) {
            $this->error('Sheet "Tidy" tidak ditemukan di file: ' . $filePath);

            return null;
        }

        $data   = $sheet->toArray(null, true, true, false);
        $header = array_map('trim', $data[0] ?? []);
        $expected = [
            'row_id', 'baris_asal_excel', 'kandidat_nama_unit', 'jenjang_jabatan', 'urutan_jenjang',
            'grade', 'nama_jobs', 'managerial', 'nama_kompetensi', 'job_family_id', 'level', 'asal', 'prioritas',
        ];

        if ($header !== $expected) {
            $this->error('Header sheet "Tidy" tidak sesuai format yang diharapkan.');

            return null;
        }

        $rows = [];
        for ($i = 1; $i < count($data); $i++) {
            $raw = $data[$i];
            if (($raw[0] ?? null) === null || trim((string) $raw[0]) === '') {
                continue;
            }
            $raw = array_slice($raw, 0, count($expected));
            $raw = array_pad($raw, count($expected), null);
            $rows[] = array_combine($expected, $raw);
        }

        return $rows;
    }

    private function printReport(array $result, bool $commit): void
    {
        $this->newLine();
        $this->info('=== LAPORAN IMPORT KOMPETENSI TEKNIS ===');
        $this->line("Total baris tidy diproses : {$result['totalDiproses']}");
        $this->line("Total berhasil di-insert  : {$result['totalInsert']}");
        $this->line("Total duplikat di-skip    : {$result['totalDuplikat']}");
        $this->line('Total error               : ' . count($result['errors']));

        $this->newLine();
        if (count($result['kompetensiBaru']) > 0) {
            $this->info('Kompetensi teknis BARU dibuat (' . count($result['kompetensiBaru']) . '):');
            foreach ($result['kompetensiBaru'] as $k) {
                $this->line('  - ' . $k);
            }
        } else {
            $this->info('Tidak ada kompetensi teknis baru (semua sudah ada di master).');
        }

        $this->newLine();
        if (count($result['konflikRumpun']) > 0) {
            $this->warn('Warning konflik job_family (' . count($result['konflikRumpun']) . ') — TIDAK di-overwrite, putuskan manual:');
            foreach ($result['konflikRumpun'] as $k) {
                $this->line('  - ' . $k);
            }
        } else {
            $this->info('Tidak ada konflik job_family.');
        }

        $this->newLine();
        $this->info('Breakdown jumlah baris per unit_organisasi_id:');
        $perUnit = $result['perUnit'];
        ksort($perUnit);
        foreach ($perUnit as $unitId => $count) {
            $this->line("  - unit_organisasi_id {$unitId}: {$count} baris");
        }

        $this->newLine();
        $this->info('Tally asal (dari seluruh baris tidy yang diproses):');
        foreach (['native', 'generic'] as $a) {
            $this->line("  - {$a}: {$result['tallyAsal'][$a]}");
        }

        $this->newLine();
        $this->info('Tally prioritas (CLI ini belum punya opsi pilih Primary — semua baris otomatis secondary, lihat catatan class):');
        foreach (['primary', 'secondary'] as $p) {
            $this->line("  - {$p}: {$result['tallyPrioritas'][$p]}");
        }

        if (count($result['errors']) > 0) {
            $this->newLine();
            $this->error('Daftar error:');
            foreach ($result['errors'] as $e) {
                $this->line('  - ' . $e);
            }
        }

        $this->newLine();
        if ($result['stoppedEarly'] || count($result['errors']) > 0) {
            $this->error('IMPORT DIHENTIKAN KARENA ERROR — TRANSAKSI DI-ROLLBACK, TIDAK ADA PERUBAHAN PERMANEN.');
        } elseif ($commit) {
            $this->info('COMMITTED — DATA TERSIMPAN');
        } else {
            $this->info('DRY-RUN — TIDAK ADA PERUBAHAN PERMANEN');
        }
    }
}
