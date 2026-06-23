<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\KalibrasiKaryawan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class KalibrasiImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $rowCount     = 0;
    protected int $skippedCount = 0;

    // Kalibrasi unik per (karyawan, tahun) → updateOrCreate
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $nik = trim((string) ($row['nik'] ?? ''));
            if ($nik === '') { $this->skippedCount++; continue; }

            $karyawan = Karyawan::where('nik', $nik)->first();
            if (!$karyawan) { $this->skippedCount++; continue; }

            $tahun = (int) ($row['tahun'] ?? 0);
            if ($tahun < 2000 || $tahun > 2100) { $this->skippedCount++; continue; }

            $nilai = strtoupper(trim((string) ($row['nilai'] ?? '')));
            if (!in_array($nilai, ['FEE', 'EXE', 'MEE', 'BEE', 'FBE'], true)) { $this->skippedCount++; continue; }

            KalibrasiKaryawan::updateOrCreate(
                ['karyawan_id' => $karyawan->id, 'tahun' => $tahun],
                [
                    'nilai'      => $nilai,
                    'keterangan' => trim((string) ($row['keterangan'] ?? '')) ?: null,
                    'created_by' => Auth::id(),
                ]
            );
            $this->rowCount++;
        }
    }

    public function chunkSize(): int { return 200; }
    public function getRowCount(): int { return $this->rowCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
}