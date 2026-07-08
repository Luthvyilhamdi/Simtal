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
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Row;
use Carbon\Carbon;

/**
 * Import karyawan dengan mode "update pintar":
 * - NIK sudah ada  → DATA DIPERBARUI (bukan dilewati).
 * - NIK baru       → data baru dibuat (butuh data lengkap: nama, tempat & tanggal lahir,
 *                    tanggal masuk; kolom master diberi default bila kosong).
 * - Kolom KOSONG   → TIDAK diubah (nilai lama dipertahankan).
 *
 * Jadi untuk update massal cukup upload kolom yang ingin diubah saja
 * (mis. nik + struktural_fungsional); kolom lain yang dikosongkan tidak menimpa data lama.
 */
class KaryawanImport implements OnEachRow, WithHeadingRow
{
    use Importable;

    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    public function onRow(Row $rowObj): void
    {
        $row = $rowObj->toArray();

        $nik = isset($row['nik']) ? trim((string) $row['nik']) : '';
        if ($nik === '') {
            $this->skipped++;
            return;
        }

        try {
            $existing = Karyawan::where('nik', $nik)->first();

            // Bangun data HANYA dari kolom yang terisi (kolom kosong tidak diubah).
            $data = [];

            if ($this->filled($row, 'nama'))             $data['nama'] = trim($row['nama']);
            if ($this->filled($row, 'tempat_lahir'))     $data['tempat_lahir'] = trim($row['tempat_lahir']);
            if ($this->filled($row, 'jabatan_saat_ini')) $data['jabatan_saat_ini'] = trim($row['jabatan_saat_ini']);
            if ($this->filled($row, 'jenis_kelamin'))    $data['jenis_kelamin'] = strtoupper(trim($row['jenis_kelamin'])) === 'P' ? 'P' : 'L';
            if ($this->filled($row, 'tanggal_lahir'))    $data['tanggal_lahir'] = $this->parseDate($row['tanggal_lahir']);
            if ($this->filled($row, 'tanggal_masuk'))    $data['tanggal_masuk'] = $this->parseDate($row['tanggal_masuk']);
            if ($this->filled($row, 'status'))           $data['status'] = strtolower(trim($row['status'])) === 'aktif' ? 'aktif' : 'tidak aktif';
            if ($this->filled($row, 'status_kepegawaian')) $data['status_kepegawaian'] = $this->normStatusKepegawaian($row['status_kepegawaian']);
            if ($this->filled($row, 'no_hp'))            $data['no_hp'] = trim((string) $row['no_hp']);
            if ($this->filled($row, 'email'))            $data['email'] = trim((string) $row['email']);
            // Pendidikan (jenjang_pendidikan/jurusan) TIDAK diimport di sini —
            // dikelola khusus lewat History Pendidikan (riwayat_pendidikan_all.*).
            if ($this->filled($row, 'struktural_fungsional')) {
                $data['struktural_fungsional'] = ucfirst(strtolower(trim($row['struktural_fungsional'])));
            }

            // Relasi (master data) — hanya diproses kalau kolom teksnya terisi.
            if ($this->filled($row, 'jabatan'))       $data['jabatan_id']       = Jabatan::firstOrCreate(['nama_jabatan' => trim($row['jabatan'])])->id;
            if ($this->filled($row, 'direktorat'))    $data['direktorat_id']    = Direktorat::firstOrCreate(['nama_direktorat' => trim($row['direktorat'])])->id;
            if ($this->filled($row, 'kompartemen'))   $data['kompartemen_id']   = Kompartemen::firstOrCreate(['nama_kompartemen' => trim($row['kompartemen'])])->id;
            if ($this->filled($row, 'departemen'))    $data['departemen_id']    = Departemen::firstOrCreate(['nama_departemen' => trim($row['departemen'])])->id;
            if ($this->filled($row, 'job_grade'))     $data['job_grade_id']     = JobGrade::firstOrCreate(['job_grade' => trim((string) $row['job_grade'])])->id;
            if ($this->filled($row, 'person_grade'))  $data['person_grade_id']  = PersonGrade::firstOrCreate(['person_grade' => trim((string) $row['person_grade'])])->id;
            if ($this->filled($row, 'kode_struktur')) $data['kode_struktur_id'] = KodeStruktur::firstOrCreate(['kode_struktur' => trim((string) $row['kode_struktur'])])->id;

            if ($existing) {
                if (! empty($data)) {
                    $existing->update($data);
                }
                $this->updated++;
                return;
            }

            // ── Data baru ── minimal wajib ada 'nama'.
            if (empty($data['nama'])) {
                $this->skipped++;
                return;
            }

            $data['nik']           = $nik;
            $data['jenis_kelamin'] = $data['jenis_kelamin'] ?? 'L';
            $data['status']        = $data['status'] ?? 'aktif';

            // Kolom relasi WAJIB (NOT NULL) — beri default bila kosong (seperti import lama).
            $data['jabatan_saat_ini'] = $data['jabatan_saat_ini']
                ?? ($this->filled($row, 'jabatan') ? trim($row['jabatan']) : null);
            $data['jabatan_id']       = $data['jabatan_id']       ?? Jabatan::firstOrCreate(['nama_jabatan' => 'Belum Ditentukan'])->id;
            $data['direktorat_id']    = $data['direktorat_id']    ?? Direktorat::firstOrCreate(['nama_direktorat' => 'Belum Ditentukan'])->id;
            $data['kompartemen_id']   = $data['kompartemen_id']   ?? Kompartemen::firstOrCreate(['nama_kompartemen' => 'Belum Ditentukan'])->id;
            $data['departemen_id']    = $data['departemen_id']    ?? Departemen::firstOrCreate(['nama_departemen' => 'Belum Ditentukan'])->id;
            $data['job_grade_id']     = $data['job_grade_id']     ?? JobGrade::firstOrCreate(['job_grade' => '-'])->id;
            $data['person_grade_id']  = $data['person_grade_id']  ?? PersonGrade::firstOrCreate(['person_grade' => '-'])->id;
            $data['kode_struktur_id'] = $data['kode_struktur_id'] ?? KodeStruktur::firstOrCreate(['kode_struktur' => '-'])->id;

            Karyawan::create($data);
            $this->created++;
        } catch (\Throwable $e) {
            // Baris gagal (mis. data baru tidak lengkap: tempat/tanggal lahir/masuk kosong).
            $this->skipped++;
        }
    }

    /** Kolom dianggap terisi bila ada & tidak kosong setelah di-trim. */
    private function filled(array $row, string $key): bool
    {
        return isset($row[$key]) && trim((string) $row[$key]) !== '';
    }

    /** Cocokkan status kepegawaian ke opsi resmi (case-insensitive); bila tak cocok, pakai apa adanya. */
    private function normStatusKepegawaian($value): string
    {
        $value = trim((string) $value);
        foreach (Karyawan::STATUS_KEPEGAWAIAN as $opt) {
            if (strcasecmp($opt, $value) === 0) return $opt;
        }
        return $value;
    }

    private function parseDate($value): ?string
    {
        if (! $value) return null;

        // Kalau angka (Excel date serial)
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

    public function getCreatedCount(): int { return $this->created; }
    public function getUpdatedCount(): int { return $this->updated; }
    public function getSkippedCount(): int { return $this->skipped; }
    public function getRowCount(): int     { return $this->created + $this->updated; }
}
