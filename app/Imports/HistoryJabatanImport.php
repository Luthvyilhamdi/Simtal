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

    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    /** karyawan_id yang tersentuh, untuk hitung ulang current & profil. */
    private array $affected = [];

    /** [karyawan_id][tanggal_mulai(Y-m-d)] baris yg kolom lanjut_mdj-nya KOSONG
     *  → penanda MDJ ditentukan otomatis dari band saat finalize(). */
    private array $autoMdj = [];

    /**
     * SMART-UPDATE: kunci unik 1 periode = karyawan_id + tanggal_mulai.
     * Baris cocok → diperbarui; tidak ada → ditambah. Re-import file yang sama
     * tidak menggandakan baris. is_current & profil TIDAK diset per-baris —
     * dihitung ulang sekali per karyawan di finalize() setelah semua baris masuk.
     *
     * Persistensi dilakukan manual (updateOrCreate) lalu return null agar
     * Maatwebsite tidak meng-insert ganda.
     */
    public function model(array $row)
    {
        $karyawan = Karyawan::where('nik', trim((string) ($row['nik'] ?? '')))->first();
        if (!$karyawan) {
            $this->skipped++;
            return null;
        }

        $tanggalMulai = $this->parseDate($row['tanggal_mulai'] ?? null);
        if (!$tanggalMulai) {
            // Tanpa tanggal_mulai, kunci periode tak bisa ditentukan.
            $this->skipped++;
            return null;
        }

        // Jabatan & Kode Struktur = master level (boleh dibuat bila belum ada).
        $jabatan = Jabatan::firstOrCreate(['nama_jabatan' => trim($row['jabatan'])]);
        $kodeStruktur = isset($row['kode_struktur']) && $row['kode_struktur']
            ? KodeStruktur::firstOrCreate(['kode_struktur' => trim($row['kode_struktur'])])
            : null;

        // Unit & grade = SNAPSHOT TEKS apa adanya; resolve FK master HANYA bila
        // namanya cocok (tanpa create) → nama historis yang beda tidak mengotori
        // master (tersimpan sebagai teks, FK null).
        $dirNama  = trim($row['direktorat'] ?? '');
        $kompNama = trim($row['kompartemen'] ?? '');
        $depNama  = trim($row['departemen'] ?? '');
        $jgNama   = trim((string) ($row['job_grade'] ?? ''));
        $pgNama   = trim((string) ($row['person_grade'] ?? ''));

        $direktoratId  = $dirNama  !== '' ? Direktorat::where('nama_direktorat', $dirNama)->value('id') : null;
        $kompartemenId = $kompNama !== '' ? Kompartemen::where('nama_kompartemen', $kompNama)->value('id') : null;
        $departemenId  = $depNama  !== '' ? Departemen::where('nama_departemen', $depNama)->value('id') : null;
        $jobGradeId    = $jgNama   !== '' ? JobGrade::where('job_grade', $jgNama)->value('id') : null;
        $personGradeId = $pgNama   !== '' ? PersonGrade::where('person_grade', $pgNama)->value('id') : null;

        $tanggalSelesai = isset($row['tanggal_selesai']) && $row['tanggal_selesai']
            ? $this->parseDate($row['tanggal_selesai'])
            : null;

        $tipe = strtolower(trim($row['tipe'] ?? 'penempatan'));
        if (!in_array($tipe, ['promosi', 'mutasi', 'rotasi', 'demosi', 'penempatan'])) {
            $tipe = 'penempatan';
        }

        // Penanda kelangsungan MDJ:
        //   'ya'    → jabatan sama/kelanjutan (lanjut)
        //   'tidak' → jabatan baru (reset)
        //   kosong  → AUTO: ditentukan dari band di finalize() (band sama = lanjut)
        $lanjutRaw  = strtolower(trim((string) ($row['lanjut_mdj'] ?? $row['jabatan_sama'] ?? '')));
        $explicitYa = in_array($lanjutRaw, ['1', 'ya', 'yes', 'true', 'y', 'sama'], true);
        $lanjutMdj  = $explicitYa; // 'tidak' & kosong → false dulu; kosong dikoreksi di finalize()
        if ($lanjutRaw === '') {
            $this->autoMdj[$karyawan->id][$tanggalMulai] = true;
        }

        // Atribut isi (tanpa kunci & tanpa is_current).
        $attrs = [
            'jabatan_id'        => $jabatan->id,
            'jabatan_saat_ini'  => $row['jabatan_saat_ini'] ?? $jabatan->nama_jabatan,
            'direktorat_id'     => $direktoratId,
            'kompartemen_id'    => $kompartemenId,
            'departemen_id'     => $departemenId,
            'direktorat_nama'   => $dirNama  !== '' ? $dirNama  : null,
            'kompartemen_nama'  => $kompNama !== '' ? $kompNama : null,
            'departemen_nama'   => $depNama  !== '' ? $depNama  : null,
            'job_grade_id'      => $jobGradeId,
            'person_grade_id'   => $personGradeId,
            'job_grade_nama'    => $jgNama   !== '' ? $jgNama   : null,
            'person_grade_nama' => $pgNama   !== '' ? $pgNama   : null,
            'kode_struktur_id'  => $kodeStruktur?->id,
            'tanggal_selesai'   => $tanggalSelesai,
            'tipe'              => $tipe,
            'no_sk'             => $row['no_sk'] ?? null,
            'tanggal_sk'        => isset($row['tanggal_sk']) && $row['tanggal_sk']
                                    ? $this->parseDate($row['tanggal_sk'])
                                    : null,
            'keterangan'        => $row['keterangan'] ?? null,
            'lanjut_mdj'        => $lanjutMdj,
        ];

        // Upsert berdasarkan kunci NIK (karyawan_id) + tanggal_mulai.
        $existing = HistoryJabatan::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal_mulai', $tanggalMulai)
            ->first();

        if ($existing) {
            $existing->update($attrs);
            $this->updated++;
        } else {
            // create() memicu event model (sync Pejabat Definitif & TMT band).
            HistoryJabatan::create($attrs + [
                'karyawan_id'  => $karyawan->id,
                'tanggal_mulai' => $tanggalMulai,
            ]);
            $this->created++;
        }

        $this->affected[$karyawan->id] = $karyawan->id;

        return null; // persistensi sudah manual
    }

    /**
     * Setelah semua baris masuk: untuk tiap karyawan tersentuh, tetapkan
     * jabatan dengan tanggal_mulai TERBARU sebagai is_current (paling atas di
     * list), sisanya non-current, lalu sinkronkan profil dari baris current itu.
     */
    public function finalize(): void
    {
        foreach ($this->affected as $karyawanId) {
            $karyawan = Karyawan::find($karyawanId);
            if (!$karyawan) {
                continue;
            }

            $histories = HistoryJabatan::where('karyawan_id', $karyawanId)
                ->orderBy('tanggal_mulai', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            if ($histories->isEmpty()) {
                continue;
            }

            $current = $histories->first(); // tanggal_mulai terbaru = jabatan saat ini

            foreach ($histories as $h) {
                $shouldCurrent = $h->id === $current->id;
                if ((bool) $h->is_current !== $shouldCurrent) {
                    $h->is_current = $shouldCurrent;
                    $h->saveQuietly(); // jangan picu ulang event
                }
            }

            // AUTO lanjut_mdj untuk baris yang kolomnya KOSONG: band sama dengan
            // baris sebelumnya (kronologis) → lanjut; band beda / baris pertama → reset.
            if (!empty($this->autoMdj[$karyawanId])) {
                $autoDates = $this->autoMdj[$karyawanId];
                $asc = $histories->sortBy([['tanggal_mulai', 'asc'], ['id', 'asc']])->values();
                $prevBand = null;
                foreach ($asc as $h) {
                    $band = HistoryJabatan::bandDariRow($h);
                    $key  = optional($h->tanggal_mulai)->format('Y-m-d');

                    if (isset($autoDates[$key])) {
                        $lanjut = ($prevBand !== null && $band !== null && $band === $prevBand);
                        if ((bool) $h->lanjut_mdj !== $lanjut) {
                            $h->lanjut_mdj = $lanjut;
                            $h->saveQuietly();
                        }
                    }

                    if ($band !== null) {
                        $prevBand = $band;
                    }
                }
            }

            // Sinkron profil dari baris current. Pakai ?? agar nilai historis
            // (FK null) tidak meng-null-kan profil aktif.
            $karyawan->update([
                'jabatan_id'       => $current->jabatan_id       ?? $karyawan->jabatan_id,
                'jabatan_saat_ini' => $current->jabatan_saat_ini ?? $karyawan->jabatan_saat_ini,
                'direktorat_id'    => $current->direktorat_id    ?? $karyawan->direktorat_id,
                'kompartemen_id'   => $current->kompartemen_id   ?? $karyawan->kompartemen_id,
                'departemen_id'    => $current->departemen_id    ?? $karyawan->departemen_id,
                'job_grade_id'     => $current->job_grade_id     ?? $karyawan->job_grade_id,
                'person_grade_id'  => $current->person_grade_id  ?? $karyawan->person_grade_id,
                'kode_struktur_id' => $current->kode_struktur_id ?? $karyawan->kode_struktur_id,
            ]);
        }
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

    /** Kompat lama: total baris tertulis (create + update). */
    public function getRowCount(): int { return $this->created + $this->updated; }
}
