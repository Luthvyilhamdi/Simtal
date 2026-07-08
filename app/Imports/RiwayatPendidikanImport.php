<?php

namespace App\Imports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Row;

/**
 * Import Riwayat Pendidikan massal (1 baris = 1 jenjang) dengan kunci NIK + Jenjang:
 * - NIK tidak ditemukan  → baris dilewati.
 * - Jenjang tidak valid   → baris dilewati.
 * - Pasangan (NIK, Jenjang) sudah ada → DIPERBARUI (jurusan/institusi ditimpa).
 * - Pasangan baru          → dibuat.
 *
 * Setelah selesai, "Pendidikan Terakhir" (jenjang_pendidikan/jurusan) tiap karyawan
 * yang tersentuh dihitung ulang otomatis (jenjang tertinggi).
 */
class RiwayatPendidikanImport implements OnEachRow, WithHeadingRow
{
    use Importable;

    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    /** @var array<int,int> id karyawan yang tersentuh (unik) */
    private array $affected = [];

    public function onRow(Row $rowObj): void
    {
        $row = $rowObj->toArray();

        $nik     = isset($row['nik']) ? trim((string) $row['nik']) : '';
        $jenjang = isset($row['jenjang']) ? trim((string) $row['jenjang']) : '';

        if ($nik === '' || $jenjang === '') {
            $this->skipped++;
            return;
        }

        // Cocokkan jenjang ke daftar resmi (case-insensitive), mis. "sma/smk" → "SMA/SMK".
        $jenjang = $this->normJenjang($jenjang);
        if ($jenjang === null) {
            $this->skipped++;
            return;
        }

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (! $karyawan) {
            $this->skipped++;
            return;
        }

        $jurusan   = isset($row['jurusan'])   ? (trim((string) $row['jurusan'])   ?: null) : null;
        $institusi = isset($row['institusi']) ? (trim((string) $row['institusi']) ?: null) : null;

        $entry = $karyawan->riwayatPendidikan()->updateOrCreate(
            ['jenjang' => $jenjang],
            ['jurusan' => $jurusan, 'institusi' => $institusi]
        );

        if ($entry->wasRecentlyCreated) {
            $this->created++;
        } else {
            $this->updated++;
        }

        $this->affected[$karyawan->id] = $karyawan->id;
    }

    /** Selesaikan: hitung ulang Pendidikan Terakhir untuk semua karyawan yang tersentuh. */
    public function refreshAffected(): void
    {
        if (! $this->affected) return;
        Karyawan::whereIn('id', array_values($this->affected))
            ->get()
            ->each->refreshPendidikanTerakhir();
    }

    private function normJenjang(string $value): ?string
    {
        foreach (Karyawan::JENJANG_PENDIDIKAN as $opt) {
            if (strcasecmp($opt, $value) === 0) return $opt;
        }
        return null;
    }

    public function getCreatedCount(): int { return $this->created; }
    public function getUpdatedCount(): int { return $this->updated; }
    public function getSkippedCount(): int { return $this->skipped; }
}
