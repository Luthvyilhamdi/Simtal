<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\HistoryAssessmentKompetensi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AssessmentKompetensiImport implements
    ToModel,
    WithHeadingRow,
    SkipsOnError,
    WithBatchInserts,
    WithChunkReading
{
    use SkipsErrors;

    protected int $rowCount     = 0;
    protected int $skippedCount = 0;

    // Baris petunjuk & label (baris 2 & 3) di-skip otomatis oleh WithHeadingRow
    // karena heading ada di baris 1, data mulai baris 4
    // Kita skip baris yang bukan data nyata (petunjuk & label nama)
    protected int $skipRows = 2; // skip 2 baris setelah heading (petunjuk + label)
    protected int $currentRow = 0;

    public function model(array $row): mixed
    {
        $this->currentRow++;

        // Skip 2 baris pertama (petunjuk & label nama kompetensi)
        if ($this->currentRow <= $this->skipRows) {
            $this->skippedCount++;
            return null;
        }

        // Ambil NIK — bisa string atau integer dari Excel
        $nik = trim((string) ($row['nik'] ?? ''));
        if (empty($nik)) {
            $this->skippedCount++;
            return null;
        }

        // Cari karyawan
        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) {
            $this->skippedCount++;
            return null;
        }

        // Tanggal assessment
        $tanggalRaw = trim((string) ($row['tanggal_assessment'] ?? ''));
        if (empty($tanggalRaw)) {
            $this->skippedCount++;
            return null;
        }

        try {
            // Support format dd/mm/yyyy atau yyyy-mm-dd atau serial Excel
            if (is_numeric($tanggalRaw)) {
                $tanggal = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $tanggalRaw));
            } elseif (str_contains($tanggalRaw, '/')) {
                $tanggal = Carbon::createFromFormat('d/m/Y', $tanggalRaw);
            } else {
                $tanggal = Carbon::parse($tanggalRaw);
            }
        } catch (\Exception $e) {
            $this->skippedCount++;
            return null;
        }

        // Ambil semua key kompetensi & qualification dari model
        $kompKeys = array_keys(HistoryAssessmentKompetensi::competencies());
        $qualKeys = array_keys(HistoryAssessmentKompetensi::qualifications());

        // Validasi & ambil nilai kompetensi (1-4)
        $data = [];
        foreach ($kompKeys as $key) {
            $val = isset($row[$key]) ? (int) $row[$key] : null;
            if ($val === null || $val < 1 || $val > 4) {
                $this->skippedCount++;
                return null; // wajib semua diisi dengan nilai valid
            }
            $data[$key] = $val;
        }

        // Validasi & ambil nilai qualification (1-4)
        foreach ($qualKeys as $key) {
            $val = isset($row[$key]) ? (int) $row[$key] : null;
            if ($val === null || $val < 1 || $val > 4) {
                $this->skippedCount++;
                return null;
            }
            $data[$key] = $val;
        }

        // ===== HITUNG KESIMPULAN (logika sesuai tabel kriteria) =====
        $compR1    = 0; // rating 1 pada kompetensi (kritis)
        $compR2    = 0; // rating 2 pada kompetensi
        $compUnder = 0; // total kompetensi < 3 (rating 1 + rating 2)
        $qualUnder = 0; // qualification nilai < 2

        foreach ($kompKeys as $key) {
            if ($data[$key] === 1) { $compR1++; $compUnder++; }
            if ($data[$key] === 2) { $compR2++; $compUnder++; }
        }
        foreach ($qualKeys as $key) {
            if ($data[$key] < 2) $qualUnder++;
        }

        $kesimpulan = ($compR1 === 0 && $compR2 <= 3 && $qualUnder === 0)
            ? 'QUALIFIED'
            : 'NOT QUALIFIED';

        $this->rowCount++;

        return new HistoryAssessmentKompetensi(array_merge($data, [
            'karyawan_id'               => $karyawan->id,
            'tanggal_assessment'        => $tanggal->format('Y-m-d'),
            'periode'                   => trim((string) ($row['periode'] ?? '')) ?: null,
            'keterangan'                => trim((string) ($row['keterangan'] ?? '')) ?: null,
            'total_competency_under'    => $compUnder,
            'total_qualification_under' => $qualUnder,
            'kesimpulan'                => $kesimpulan,
        ]));
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}