<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\Jabatan;
use App\Models\Direktorat;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobGrade;
use App\Models\PersonGrade;
use App\Models\KodeStruktur;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Concerns\Importable;
use Carbon\Carbon;

class KaryawanImport implements
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
        // Cek duplikat NIK
        if (Karyawan::where('nik', $row['nik'])->exists()) {
            $this->skipped++;
            return null;
        }

        $this->rows++;

        // Cari atau buat master data
        $jabatan = Jabatan::firstOrCreate(
            ['nama_jabatan' => $row['jabatan'] ?? 'Belum Ditentukan']
        );
        $direktorat = Direktorat::firstOrCreate(
            ['nama_direktorat' => $row['direktorat'] ?? 'Belum Ditentukan']
        );
        $kompartemen = Kompartemen::firstOrCreate(
            ['nama_kompartemen' => $row['kompartemen'] ?? 'Belum Ditentukan']
        );
        $departemen = Departemen::firstOrCreate(
            ['nama_departemen' => $row['departemen'] ?? 'Belum Ditentukan']
        );
        $jobGrade = JobGrade::firstOrCreate(
            ['job_grade' => $row['job_grade'] ?? '-']
        );
        $personGrade = PersonGrade::firstOrCreate(
            ['person_grade' => $row['person_grade'] ?? '-']
        );
        $kodeStruktur = isset($row['kode_struktur']) && $row['kode_struktur']
            ? KodeStruktur::firstOrCreate(['kode_struktur' => $row['kode_struktur']])
            : null;

        return new Karyawan([
            'nik'              => trim($row['nik']),
            'nama'             => trim($row['nama']),
            'jenis_kelamin'    => strtoupper($row['jenis_kelamin']) === 'P' ? 'P' : 'L',
            'tempat_lahir'     => $row['tempat_lahir'] ?? null,
            'tanggal_lahir'    => $this->parseDate($row['tanggal_lahir']),
            'tanggal_masuk'    => $this->parseDate($row['tanggal_masuk']),
            'jabatan_saat_ini' => $row['jabatan_saat_ini'] ?? $row['jabatan'] ?? null,
            'status'           => strtolower($row['status'] ?? 'aktif') === 'aktif' ? 'aktif' : 'tidak aktif',
            'jabatan_id'       => $jabatan->id,
            'direktorat_id'    => $direktorat->id,
            'kompartemen_id'   => $kompartemen->id,
            'departemen_id'    => $departemen->id,
            'job_grade_id'     => $jobGrade->id,
            'person_grade_id'  => $personGrade->id,
            'kode_struktur_id' => $kodeStruktur?->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'nik'  => 'required',
            'nama' => 'required',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nik.required'  => 'Kolom NIK wajib diisi.',
            'nama.required' => 'Kolom Nama wajib diisi.',
        ];
    }

    private function parseDate($value): ?string
    {
        if (!$value) return null;

        // Kalau angka (Excel date serial)
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        // Coba berbagai format tanggal
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd M Y', 'm/d/Y'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        return null;
    }

    public function getRowCount(): int    { return $this->rows; }
    public function getSkippedCount(): int { return $this->skipped; }
}