<?php

namespace App\Imports;

use App\Models\Karyawan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

/**
 * Import TMT (Terhitung Mulai Tanggal) Band / Job Grade / Person Grade.
 * Kunci = NIK. Hanya kolom yang TERISI yang diperbarui (kolom kosong tidak
 * meng-null-kan nilai lama). NIK tak dikenal / semua kolom kosong → dilewati.
 */
class TmtImport implements ToCollection, WithHeadingRow
{
    private int $updated = 0;
    private int $skipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $nik = trim((string) ($row['nik'] ?? ''));
            if ($nik === '') {
                $this->skipped++;
                continue;
            }

            $karyawan = Karyawan::where('nik', $nik)->first();
            if (!$karyawan) {
                $this->skipped++;
                continue;
            }

            $data = [];
            if ($this->has($row, 'tmt_band')) $data['tanggal_mulai_band'] = $this->parseDate($row['tmt_band']);
            if ($this->has($row, 'tmt_jg'))   $data['tanggal_mulai_jg']   = $this->parseDate($row['tmt_jg']);
            if ($this->has($row, 'tmt_pg'))   $data['tanggal_mulai_pg']   = $this->parseDate($row['tmt_pg']);

            // Buang yang gagal di-parse (nilai ada tapi format tak dikenal).
            $data = array_filter($data, fn ($v) => $v !== null);

            if (empty($data)) {
                $this->skipped++;
                continue;
            }

            $karyawan->forceFill($data)->saveQuietly();
            $this->updated++;
        }
    }

    private function has($row, string $key): bool
    {
        return isset($row[$key]) && trim((string) $row[$key]) !== '';
    }

    private function parseDate($value): ?string
    {
        if (!$value) return null;

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd M Y'];
        foreach ($formats as $format) {
            if (Carbon::hasFormat((string) $value, $format)) {
                return Carbon::createFromFormat($format, (string) $value)->format('Y-m-d');
            }
        }

        return null;
    }

    public function getUpdatedCount(): int { return $this->updated; }
    public function getSkippedCount(): int { return $this->skipped; }
}
