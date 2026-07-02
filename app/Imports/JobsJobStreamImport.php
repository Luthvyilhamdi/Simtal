<?php

namespace App\Imports;

use App\Models\StrukturOrganisasi;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Row;

/**
 * Import Jobs & Job Stream ke Struktur Organisasi, dicocokkan per JOB TITLE (posisi).
 *
 * - Kolom: posisi, jobs, job_stream (heading baris pertama).
 * - Semua baris Struktur Organisasi dengan posisi yang sama (SEMUA periode)
 *   akan di-update sekaligus. Jadi jobs/job stream melekat pada nama job title.
 * - Kolom KOSONG tidak menimpa nilai lama (smart update). Bila jobs & job_stream
 *   dua-duanya kosong, baris dilewati.
 * - Posisi yang tidak ditemukan di Struktur Organisasi dicatat sebagai "tidak cocok".
 */
class JobsJobStreamImport implements OnEachRow, WithHeadingRow
{
    use Importable;

    private int $updatedTitles = 0;   // jumlah job title yang berhasil di-update
    private int $updatedRows   = 0;    // jumlah baris SO yang terpengaruh
    private int $skipped       = 0;    // baris template dilewati (posisi kosong / tanpa nilai)
    private array $unmatched   = [];   // posisi yang tidak ada di Struktur Organisasi

    public function onRow(Row $rowObj): void
    {
        $row = $rowObj->toArray();

        $posisi = isset($row['posisi']) ? trim((string) $row['posisi']) : '';
        if ($posisi === '' || $posisi === '-') {
            $this->skipped++;
            return;
        }

        $jobs      = $this->val($row, 'jobs');
        $jobStream = $this->val($row, 'job_stream');

        // Hanya kolom yang terisi yang diperbarui (kosong = biarkan nilai lama).
        $data = [];
        if ($jobs !== null)      $data['jobs'] = $jobs;
        if ($jobStream !== null) $data['job_stream'] = $jobStream;

        if (empty($data)) {
            $this->skipped++;
            return;
        }

        // Update semua baris SO dengan posisi ini, di seluruh periode.
        $affected = StrukturOrganisasi::where('posisi', $posisi)->update($data);

        if ($affected > 0) {
            $this->updatedTitles++;
            $this->updatedRows += $affected;
        } else {
            $this->unmatched[] = $posisi;
        }
    }

    /** Nilai kolom bila terisi (trim), atau null bila kosong. */
    private function val(array $row, string $key): ?string
    {
        if (!isset($row[$key])) return null;
        $v = trim((string) $row[$key]);
        return $v === '' ? null : $v;
    }

    public function getUpdatedTitles(): int { return $this->updatedTitles; }
    public function getUpdatedRows(): int   { return $this->updatedRows; }
    public function getSkippedCount(): int  { return $this->skipped; }
    public function getUnmatched(): array   { return array_values(array_unique($this->unmatched)); }
}
