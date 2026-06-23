<?php

namespace App\Imports;

use App\Models\TalentPool;
use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Auth;

class TalentPoolImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected int $imported = 0;
    protected int $skipped  = 0;

    public function model(array $row)
    {
        $nik         = trim($row['nik'] ?? '');
        $periode     = (int) ($row['periode'] ?? now()->year);
        $klasifikasi = strtolower(trim($row['klasifikasi'] ?? ''));
        $catatan     = trim($row['catatan'] ?? '') ?: null;

        if (!$nik || !in_array($klasifikasi, ['longlist', 'shortlist'])) {
            $this->skipped++;
            return null;
        }

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) {
            $this->skipped++;
            return null;
        }

        // Skip duplikat
        if (TalentPool::where('karyawan_id', $karyawan->id)->where('periode', $periode)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return new TalentPool([
            'karyawan_id' => $karyawan->id,
            'periode'     => $periode,
            'klasifikasi' => $klasifikasi,
            'catatan'     => $catatan,
            'created_by'  => Auth::id(),
        ]);
    }

    public function getImported(): int { return $this->imported; }
    public function getSkipped(): int  { return $this->skipped; }
}