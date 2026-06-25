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

    protected array $karyawanIdByNik;
    protected array $existingCombos;

    public function __construct()
    {
        $this->karyawanIdByNik = Karyawan::pluck('id', 'nik')->all();
        $this->existingCombos  = TalentPool::select('karyawan_id', 'periode')
            ->get()
            ->mapWithKeys(fn ($t) => [$t->karyawan_id . '-' . $t->periode => true])
            ->all();
    }

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

        $karyawanId = $this->karyawanIdByNik[$nik] ?? null;
        if (!$karyawanId) {
            $this->skipped++;
            return null;
        }

        // Skip duplikat (sudah ada di DB, atau muncul dobel di file yang sama)
        $comboKey = $karyawanId . '-' . $periode;
        if (isset($this->existingCombos[$comboKey])) {
            $this->skipped++;
            return null;
        }
        $this->existingCombos[$comboKey] = true;

        $this->imported++;

        return new TalentPool([
            'karyawan_id' => $karyawanId,
            'periode'     => $periode,
            'klasifikasi' => $klasifikasi,
            'catatan'     => $catatan,
            'created_by'  => Auth::id(),
        ]);
    }

    public function getImported(): int { return $this->imported; }
    public function getSkipped(): int  { return $this->skipped; }
}