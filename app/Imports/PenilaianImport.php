<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\PenilaianKaryawan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PenilaianImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $rowCount     = 0;
    protected int $skippedCount = 0;

    // Normalisasi label periode → key database
    protected array $periodeMap = [
        'triwulan_1' => 'triwulan_1', 'triwulan 1' => 'triwulan_1', 'triwulan i'   => 'triwulan_1', 'tw1' => 'triwulan_1', 'tw 1' => 'triwulan_1', 'q1' => 'triwulan_1',
        'triwulan_2' => 'triwulan_2', 'triwulan 2' => 'triwulan_2', 'triwulan ii'  => 'triwulan_2', 'tw2' => 'triwulan_2', 'tw 2' => 'triwulan_2', 'q2' => 'triwulan_2',
        'triwulan_3' => 'triwulan_3', 'triwulan 3' => 'triwulan_3', 'triwulan iii' => 'triwulan_3', 'tw3' => 'triwulan_3', 'tw 3' => 'triwulan_3', 'q3' => 'triwulan_3',
        'triwulan_4' => 'triwulan_4', 'triwulan 4' => 'triwulan_4', 'triwulan iv'  => 'triwulan_4', 'tw4' => 'triwulan_4', 'tw 4' => 'triwulan_4', 'q4' => 'triwulan_4',
        'tahunan'    => 'tahunan',
    ];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $nik = trim((string) ($row['nik'] ?? ''));
            if ($nik === '') { $this->skippedCount++; continue; }

            $karyawan = Karyawan::where('nik', $nik)->first();
            if (!$karyawan) { $this->skippedCount++; continue; }

            $tahun = (int) ($row['tahun'] ?? 0);
            if ($tahun < 2000 || $tahun > 2100) { $this->skippedCount++; continue; }

            $periodeRaw = strtolower(trim((string) ($row['periode'] ?? '')));
            $periode = $this->periodeMap[$periodeRaw] ?? null;
            if (!$periode) { $this->skippedCount++; continue; }

            $tipe = strtoupper(trim((string) ($row['tipe'] ?? 'KPI')));
            if (!in_array($tipe, ['KPI', '360'], true)) { $tipe = 'KPI'; }

            $judul = trim((string) ($row['judul'] ?? ''));
            if ($judul === '') { $this->skippedCount++; continue; }

            // Nilai bisa pakai koma desimal (Excel ID): 85,5 → 85.5
            $nilaiRaw = str_replace(',', '.', trim((string) ($row['nilai'] ?? '')));
            if ($nilaiRaw === '' || !is_numeric($nilaiRaw)) { $this->skippedCount++; continue; }

            PenilaianKaryawan::create([
                'karyawan_id' => $karyawan->id,
                'tahun'       => $tahun,
                'periode'     => $periode,
                'tipe'        => $tipe,
                'judul'       => $judul,
                'nilai'       => (float) $nilaiRaw,
                'keterangan'  => trim((string) ($row['keterangan'] ?? '')) ?: null,
                'created_by'  => Auth::id(),
            ]);
            $this->rowCount++;
        }
    }

    public function chunkSize(): int { return 200; }
    public function getRowCount(): int { return $this->rowCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
}