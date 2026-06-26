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
    protected array $skipReasons = [];

    // Kalibrasi unik per (karyawan, tahun) → updateOrCreate
    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $baris = $i + 2; // +1 untuk index ke-1, +1 lagi karena baris 1 adalah header

            // Bersihkan seluruh whitespace (termasuk non-breaking space hasil copy-paste), bukan cuma ujungnya
            $nikRaw = (string) ($row['nik'] ?? '');
            $nik = preg_replace('/[\x{00A0}\s]+/u', '', $nikRaw);

            if ($nik === '') { $this->skip($baris, "NIK kosong"); continue; }

            $karyawan = Karyawan::where('nik', $nik)->first();
            if (!$karyawan) { $this->skip($baris, "NIK \"{$nik}\" tidak ditemukan di Data Karyawan"); continue; }

            $tahun = (int) ($row['tahun'] ?? 0);
            if ($tahun < 2000 || $tahun > 2100) { $this->skip($baris, "Tahun \"{$tahun}\" tidak valid (NIK {$nik})"); continue; }

            $nilai = strtoupper(trim((string) ($row['nilai'] ?? '')));
            if (!in_array($nilai, ['FEE', 'EXE', 'PEE', 'MEE', 'ME', 'SME', 'PME', 'BEE', 'NME', 'FBE'], true)) {
                $this->skip($baris, "Nilai \"{$nilai}\" tidak valid (NIK {$nik})"); continue;
            }

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

    protected function skip(int $baris, string $alasan): void
    {
        $this->skippedCount++;
        if (count($this->skipReasons) < 5) {
            $this->skipReasons[] = "Baris {$baris}: {$alasan}";
        }
    }

    public function chunkSize(): int { return 200; }
    public function getRowCount(): int { return $this->rowCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
    public function getSkipReasons(): array { return $this->skipReasons; }
}