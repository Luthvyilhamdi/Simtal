<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\Toefl;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Import nilai TOEFL massal — 1 baris = 1 tes.
 *
 * Kunci pencocokan: NIK + tanggal tes + jenis.
 * Seorang karyawan boleh punya banyak tes, jadi ketiganya dipakai bersama:
 *   - pasangan yang sama sudah ada → DIPERBARUI (skor/lembaga/keterangan ditimpa)
 *   - pasangan baru                → dibuat
 *
 * Baris dilewati bila: NIK kosong/tidak terdaftar, skor kosong/bukan angka,
 * atau tanggal tes kosong/tidak terbaca. Jenis TIDAK pernah menggugurkan baris —
 * kolom itu bebas diisi dan boleh dikosongkan.
 * Semua yang dilewati dihitung dan alasannya dilaporkan ke pengguna.
 */
class ToeflImport implements OnEachRow, WithHeadingRow
{
    use Importable;

    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    /** @var array<int,string> alasan baris dilewati, untuk ditampilkan ke pengguna */
    private array $alasan = [];

    public function onRow(Row $rowObj): void
    {
        $row    = $rowObj->toArray();
        $nomor  = $rowObj->getIndex();

        $nik = isset($row['nik']) ? trim((string) $row['nik']) : '';
        if ($nik === '') {
            $this->lewati($nomor, 'NIK kosong');
            return;
        }

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (! $karyawan) {
            $this->lewati($nomor, "NIK {$nik} tidak terdaftar");
            return;
        }

        // Skor wajib. Koma desimal ala Indonesia (6,5) diterima — IELTS memakai band.
        $skorMentah = isset($row['skor']) ? trim((string) $row['skor']) : '';
        $skorMentah = str_replace(',', '.', $skorMentah);
        if ($skorMentah === '' || ! is_numeric($skorMentah)) {
            $this->lewati($nomor, 'skor kosong atau bukan angka');
            return;
        }

        $tanggal = $this->bacaTanggal($row['tanggal_tes'] ?? null);
        if ($tanggal === null) {
            $this->lewati($nomor, 'tanggal tes kosong atau tidak terbaca');
            return;
        }

        // Jenis BEBAS dan boleh kosong — di lapangan ada yang di luar daftar
        // saran (mis. "Prediction"). Yang cocok dengan saran hanya dirapikan
        // huruf besar-kecilnya ("itp" -> "ITP") supaya tidak lahir varian kembar.
        $jenis = $this->rapikanJenis(isset($row['jenis']) ? trim((string) $row['jenis']) : '');

        $entry = Toefl::updateOrCreate(
            [
                'karyawan_id' => $karyawan->id,
                'tanggal_tes' => $tanggal->toDateString(),
                'jenis'       => $jenis,   // boleh null
            ],
            [
                'skor'       => (float) $skorMentah,
                'lembaga'    => isset($row['lembaga'])    ? (trim((string) $row['lembaga'])    ?: null) : null,
                'keterangan' => isset($row['keterangan']) ? (trim((string) $row['keterangan']) ?: null) : null,
            ]
        );

        $entry->wasRecentlyCreated ? $this->created++ : $this->updated++;
    }

    /**
     * Terima tanggal dari Excel (angka serial) maupun teks.
     * Format Indonesia dd/mm/yyyy didahulukan agar 03/09/2026 dibaca 3 September,
     * bukan 9 Maret seperti tafsiran Amerika.
     */
    private function bacaTanggal($nilai): ?Carbon
    {
        if ($nilai === null || trim((string) $nilai) === '') {
            return null;
        }

        if (is_numeric($nilai)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $nilai));
            } catch (\Throwable) {
                return null;
            }
        }

        $teks = trim((string) $nilai);
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y'] as $format) {
            try {
                $t = Carbon::createFromFormat($format, $teks);
                if ($t !== false) {
                    return $t->startOfDay();
                }
            } catch (\Throwable) {
                // coba format berikutnya
            }
        }

        try {
            return Carbon::parse($teks)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Rapikan penulisan jenis. Bila cocok dengan salah satu saran (tanpa peduli
     * besar-kecil huruf), pakai ejaan bakunya: "itp" -> "ITP". Bila tidak cocok,
     * nilainya dipakai apa adanya — daftar saran bukan pembatas.
     */
    private function rapikanJenis(string $value): ?string
    {
        if ($value === '') {
            return null;
        }
        foreach (Toefl::JENIS as $opsi) {
            if (strcasecmp($opsi, $value) === 0) {
                return $opsi;
            }
        }
        return $value;
    }

    private function lewati(int $nomor, string $sebab): void
    {
        $this->skipped++;
        if (count($this->alasan) < 5) {
            $this->alasan[] = "baris {$nomor}: {$sebab}";
        }
    }

    public function getCreatedCount(): int { return $this->created; }
    public function getUpdatedCount(): int { return $this->updated; }
    public function getSkippedCount(): int { return $this->skipped; }

    /** @return array<int,string> */
    public function getAlasan(): array { return $this->alasan; }
}
