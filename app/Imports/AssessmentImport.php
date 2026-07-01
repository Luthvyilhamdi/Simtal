<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\HistoryAssessment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\Importable;
use Carbon\Carbon;

class AssessmentImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError
{
    use Importable, SkipsErrors;

    private $rows    = 0;
    private $skipped = 0;

    public function model(array $row)
    {
        // Cari karyawan berdasarkan NIK
        $karyawan = Karyawan::where('nik', trim($row['nik']))->first();

        if (!$karyawan) {
            $this->skipped++;
            return null;
        }

        $this->rows++;

        // Load relasi
        $karyawan->load(['jobGrade', 'personGrade']);

        // Hitung usia saat assessment
        $tanggalPelaksanaan = $this->parseDate($row['tanggal_pelaksanaan']);
        $usia = $karyawan->tanggal_lahir
            ? Carbon::parse($karyawan->tanggal_lahir)->age
            : null;

        // Tanggal exp IDP = tanggal pelaksanaan + 2 tahun
        $tanggalExpIdp = $tanggalPelaksanaan
            ? Carbon::parse($tanggalPelaksanaan)->addYears(2)->format('Y-m-d')
            : null;

        // Parse rekomendasi final
        $rekFinal = null;
        if (isset($row['rekomendasi_final'])) {
            $val = strtolower(trim($row['rekomendasi_final']));
            if (str_contains($val, 'ready with') || str_contains($val, 'rwd') || $val === 'ready_with_development') {
                $rekFinal = 'ready_with_development';
            } elseif (str_contains($val, 'not ready') || $val === 'not_ready') {
                $rekFinal = 'not_ready';
            } elseif (str_contains($val, 'ready') || $val === 'ready') {
                $rekFinal = 'ready';
            }
        }

        return new HistoryAssessment([
            'karyawan_id'          => $karyawan->id,
            // Data otomatis dari profil karyawan
            'jabatan_saat_ini'     => $karyawan->jabatan_saat_ini,
            'job_grade'            => $karyawan->jobGrade->job_grade ?? null,
            'person_grade'         => $karyawan->personGrade->person_grade ?? null,
            'jenis_kelamin'        => $karyawan->jenis_kelamin,
            'usia'                 => $usia,
            // Data dari Excel
            'job_stream'           => $row['job_stream'] ?? null,
            'tanggal_pelaksanaan'  => $tanggalPelaksanaan,
            'tingkat_pengukuran'   => $row['tingkat_pengukuran'] ?? null,
            'rekomendasi_inti'     => $this->parsePercent($row['rekomendasi_inti'] ?? null),
            'rekomendasi_primer'   => $this->parsePercent($row['rekomendasi_primer'] ?? null),
            'rekomendasi_skunder'  => $this->parsePercent($row['rekomendasi_skunder'] ?? null),
            'rekomendasi_final'    => $rekFinal,
            'tanggal_exp_idp'      => $tanggalExpIdp,
            'keterangan'           => $row['keterangan'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nik'                 => 'required',
            'tanggal_pelaksanaan' => 'required',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nik.required'                 => 'Kolom NIK wajib diisi.',
            'tanggal_pelaksanaan.required' => 'Kolom Tanggal Pelaksanaan wajib diisi.',
        ];
    }

    private function parseDate($value): ?string
    {
        if (!$value) return null;

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        // Semua format dd/mm/yyyy (tanpa mm/dd). hasFormat memvalidasi ketat
        // agar tanggal tak valid tidak "overflow" menjadi tanggal keliru.
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd M Y'];
        foreach ($formats as $format) {
            if (Carbon::hasFormat((string) $value, $format)) {
                return Carbon::createFromFormat($format, (string) $value)->format('Y-m-d');
            }
        }

        return null;
    }

    private function parsePercent($value): ?float
    {
        if ($value === null || $value === '') return null;
        $val = (float) str_replace(['%', ','], ['', '.'], $value);
        return $val > 100 ? 100 : ($val < 0 ? 0 : $val);
    }

    public function getRowCount(): int     { return $this->rows; }
    public function getSkippedCount(): int { return $this->skipped; }
}