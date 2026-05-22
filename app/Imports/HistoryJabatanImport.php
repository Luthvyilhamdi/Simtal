<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\HistoryJabatan;
use App\Models\Jabatan;
use App\Models\Direktorat;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobGrade;
use App\Models\PersonGrade;
use App\Models\KodeStruktur;
use App\Models\HistoryPejabat;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\Importable;
use Carbon\Carbon;

class HistoryJabatanImport implements
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

        // Cari atau buat master data
        $jabatan = Jabatan::firstOrCreate(
            ['nama_jabatan' => trim($row['jabatan'])]
        );
        $direktorat = Direktorat::firstOrCreate(
            ['nama_direktorat' => trim($row['direktorat'] ?? 'Belum Ditentukan')]
        );
        $kompartemen = Kompartemen::firstOrCreate(
            ['nama_kompartemen' => trim($row['kompartemen'] ?? 'Belum Ditentukan')]
        );
        $departemen = Departemen::firstOrCreate(
            ['nama_departemen' => trim($row['departemen'] ?? 'Belum Ditentukan')]
        );
        $jobGrade = JobGrade::firstOrCreate(
            ['job_grade' => trim($row['job_grade'] ?? '-')]
        );
        $personGrade = PersonGrade::firstOrCreate(
            ['person_grade' => trim($row['person_grade'] ?? '-')]
        );
        $kodeStruktur = isset($row['kode_struktur']) && $row['kode_struktur']
            ? KodeStruktur::firstOrCreate(['kode_struktur' => trim($row['kode_struktur'])])
            : null;

        $tanggalMulai   = $this->parseDate($row['tanggal_mulai']);
        $tanggalSelesai = isset($row['tanggal_selesai']) && $row['tanggal_selesai']
            ? $this->parseDate($row['tanggal_selesai'])
            : null;

        // Parse tipe
        $tipe = strtolower(trim($row['tipe'] ?? 'onboarding'));
        if (!in_array($tipe, ['promosi', 'mutasi', 'demosi', 'onboarding'])) {
            $tipe = 'onboarding';
        }

        // Tentukan is_current
        // Jika tanggal_selesai kosong = current
        $isCurrent = is_null($tanggalSelesai) ? 1 : 0;

        // Jika is_current, nonaktifkan history lain yang current
        if ($isCurrent) {
            HistoryJabatan::where('karyawan_id', $karyawan->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            // Update profil karyawan
            $karyawan->update([
                'jabatan_id'       => $jabatan->id,
                'jabatan_saat_ini' => $row['jabatan_saat_ini'] ?? $jabatan->nama_jabatan,
                'direktorat_id'    => $direktorat->id,
                'kompartemen_id'   => $kompartemen->id,
                'departemen_id'    => $departemen->id,
                'job_grade_id'     => $jobGrade->id,
                'person_grade_id'  => $personGrade->id,
                'kode_struktur_id' => $kodeStruktur?->id,
            ]);
        }

        $this->rows++;

        $historyBaru = new HistoryJabatan([
            'karyawan_id'      => $karyawan->id,
            'jabatan_id'       => $jabatan->id,
            'jabatan_saat_ini' => $row['jabatan_saat_ini'] ?? $jabatan->nama_jabatan,
            'direktorat_id'    => $direktorat->id,
            'kompartemen_id'   => $kompartemen->id,
            'departemen_id'    => $departemen->id,
            'job_grade_id'     => $jobGrade->id,
            'person_grade_id'  => $personGrade->id,
            'kode_struktur_id' => $kodeStruktur?->id,
            'tanggal_mulai'    => $tanggalMulai,
            'tanggal_selesai'  => $tanggalSelesai,
            'tipe'             => $tipe,
            'no_sk'            => $row['no_sk'] ?? null,
            'tanggal_sk'       => isset($row['tanggal_sk']) && $row['tanggal_sk']
                                    ? $this->parseDate($row['tanggal_sk'])
                                    : null,
            'keterangan'       => $row['keterangan'] ?? null,
            'is_current'       => $isCurrent,
        ]);

        // Cek History Pejabat otomatis
        if ($isCurrent && HistoryPejabat::isDipantau($jabatan->nama_jabatan)) {
            // Tutup history pejabat lama
            HistoryPejabat::where('karyawan_id', $karyawan->id)
                ->whereNull('tanggal_selesai')
                ->update(['tanggal_selesai' => $tanggalMulai]);
        }

        return $historyBaru;
    }

    public function rules(): array
    {
        return [
            'nik'           => 'required',
            'jabatan'       => 'required',
            'tanggal_mulai' => 'required',
            'tipe'          => 'nullable',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nik.required'           => 'Kolom NIK wajib diisi.',
            'jabatan.required'       => 'Kolom Jabatan wajib diisi.',
            'tanggal_mulai.required' => 'Kolom Tanggal Mulai wajib diisi.',
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

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd M Y', 'm/d/Y'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        return null;
    }

    public function getRowCount(): int     { return $this->rows; }
    public function getSkippedCount(): int { return $this->skipped; }
}