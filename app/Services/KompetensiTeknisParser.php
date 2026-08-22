<?php

namespace App\Services;

use App\Models\JobFamily;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Parse file Excel "Profiling Kompetensi Teknis" mentah (format HC: header baris 1-2,
 * data mulai baris 4, kolom G dst = 1 kolom per kompetensi sampai kolom "Total Kompetensi
 * Teknis") jadi baris tidy siap-review. TIDAK menulis file apa pun — murni transformasi data.
 *
 * Diekstrak dari ParseKompetensiTeknisPreview::handle() (logic apa adanya, bukan logic
 * baru) supaya bisa dipakai bareng oleh command CLI (yg tetap nulis hasilnya ke Excel
 * preview seperti sebelumnya) DAN controller web (yg nyimpen hasilnya ke file temp/session
 * utk alur upload self-service) — tanpa duplikasi logic parsing.
 *
 * Rumpun jabatan (dulu string bebas ketik "rumpun_asal") SEKARANG job_family_id — mengacu
 * ke master job_family, TIDAK PERNAH auto-create baris baru di master itu:
 * - Kompetensi NATIVE (bukan generic): job_family_id = $jobFamilyIdNative (param, dikirim
 *   controller dari dropdown yg SUDAH divalidasi ada di master).
 * - Kompetensi GENERIC (suffix "- JF X" di header): X dicocokkan exact match
 *   (case-insensitive) ke job_family.nama. Ketemu -> job_family_id kolom itu. TIDAK ketemu
 *   -> job_family_id NULL utk semua baris kolom itu + 1 warning (bukan stop total proses,
 *   cukup baris itu ditandai perlu di-review manual sebelum bisa commit).
 *
 * asal/prioritas (dulu 1 kolom "tipe" primary/secondary/generic — DIHAPUS TOTAL, lihat
 * migration split_tipe_into_asal_prioritas): logic baca border cell (thick/hair/dst) utk
 * deteksi primary/secondary TERBUKTI TIDAK RELIABLE (beda file beda orang bikin, border
 * style tidak konsisten) — SUDAH DIHAPUS, tidak dibaca sama sekali lagi.
 * - asal ditentukan MURNI dari struktur header (ada suffix "- JF X" -> generic, tidak ->
 *   native) — ini TETAP reliable, bukan bagian yg bermasalah.
 * - prioritas SELALU default 'secondary' saat parsing — user pilih manual mana yg
 *   'primary' di step "Pilih Primary" (alur import web) SETELAH mapping unit, BUKAN
 *   ditentukan di sini.
 * - Tiap baris tidy dapat 'row_id' stabil ("{baris_excel}-{kolom_excel}", mis. "4-G") utk
 *   dirujuk balik di step Pilih Primary itu.
 */
class KompetensiTeknisParser
{
    // Urutan cek sengaja dari yang paling spesifik ke paling umum agar "Officer" tidak
    // salah tangkap baris "Junior Officer ..." / "Associate Officer ...".
    private const JENJANG_URUTAN = [
        'Junior Officer'       => 3,
        'Associate Officer'    => 4,
        'Senior Administrator' => 5,
        'Administrator'        => 6,
        'Officer'              => 2,
    ];

    private const KOL_LIST_JABATAN = 2;  // B
    private const KOL_GRADE        = 3;  // C
    private const KOL_JOBS         = 4;  // D
    private const KOL_MANAGERIAL   = 6;  // F
    private const KOL_KOMPETENSI_MULAI = 7; // G

    /**
     * @return array{tidyRows: array<int, array<string, mixed>>, warnings: array<int, array{baris_asal_excel: int|string, pesan: string}>, kompetensiColumnCount: int, batasKolomDitemukan: bool}
     *
     * @throws \InvalidArgumentException kalau file/sheet/job_family_id tidak ada
     * @throws \RuntimeException kalau tidak ada kolom kompetensi terdeteksi sama sekali
     */
    public static function parse(string $filePath, int $jobFamilyIdNative, ?string $sheetName = null): array
    {
        if (!is_file($filePath)) {
            throw new \InvalidArgumentException("File tidak ditemukan: {$filePath}");
        }

        if (!JobFamily::whereKey($jobFamilyIdNative)->exists()) {
            throw new \InvalidArgumentException("job_family_id {$jobFamilyIdNative} tidak ditemukan di master Job Family.");
        }

        // Lookup exact match (case-insensitive, trim) nama job_family -> id, dihitung
        // SEKALI di awal (bukan query per kolom generic).
        $jobFamilyByLowerName = JobFamily::pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $nama) => [mb_strtolower(trim($nama)) => $id])
            ->all();

        $warnings = [];

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $sheetName ? $spreadsheet->getSheetByName($sheetName) : $spreadsheet->getActiveSheet();

        if (!$sheet) {
            throw new \InvalidArgumentException("Sheet tidak ditemukan: {$sheetName}");
        }

        $highestColIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());

        // 1) Kumpulkan kolom-kolom kompetensi (dari kolom G) sampai ketemu "Total Kompetensi Teknis".
        $kompetensiColumns = [];
        $foundBatas        = false;

        for ($c = self::KOL_KOMPETENSI_MULAI; $c <= $highestColIndex; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            $headerRaw = $sheet->getCell($colLetter . '1')->getValue();
            $header    = self::normalizeText($headerRaw);

            if ($header === '') {
                continue;
            }

            if (mb_strtolower($header) === 'total kompetensi teknis') {
                $foundBatas = true;
                break;
            }

            [$namaKompetensi, $rumpunHeader, $isGeneric] = self::parseHeaderKompetensi($header);

            if ($isGeneric) {
                $jobFamilyIdKolom = $jobFamilyByLowerName[mb_strtolower(trim($rumpunHeader))] ?? null;

                if ($jobFamilyIdKolom === null) {
                    $warnings[] = [
                        'baris_asal_excel' => '',
                        'pesan' => "Rumpun jabatan \"{$rumpunHeader}\" pada header kompetensi \"{$namaKompetensi}\" tidak ditemukan di master Job Family — mohon periksa penulisan di file sumber atau tambahkan ke master terlebih dahulu.",
                    ];
                }
            } else {
                $jobFamilyIdKolom = $jobFamilyIdNative;
            }

            $kompetensiColumns[$colLetter] = [
                'nama_kompetensi' => $namaKompetensi,
                'is_generic'      => $isGeneric,
                'job_family_id'   => $jobFamilyIdKolom, // null hanya kalau generic & rumpun tidak ketemu di master
            ];
        }

        if (!$foundBatas) {
            $warnings[] = [
                'baris_asal_excel' => '',
                'pesan'            => "Kolom penanda \"Total Kompetensi Teknis\" tidak ditemukan sampai kolom terakhir ({$sheet->getHighestColumn()}). Semua kolom sejak G dianggap kolom kompetensi.",
            ];
        }

        if (empty($kompetensiColumns)) {
            throw new \RuntimeException('Tidak ada kolom kompetensi yang terdeteksi (kolom G dst). Cek kembali format file sumber.');
        }

        // 2) Iterasi baris data mulai baris 4, berhenti saat kolom B kosong.
        $tidyRows = [];
        $row      = 4;

        while (true) {
            $listJabatanRaw = $sheet->getCell(Coordinate::stringFromColumnIndex(self::KOL_LIST_JABATAN) . $row)->getValue();
            $listJabatan    = self::normalizeText($listJabatanRaw);

            if ($listJabatan === '') {
                break;
            }

            $grade    = self::normalizeText($sheet->getCell(Coordinate::stringFromColumnIndex(self::KOL_GRADE) . $row)->getValue());
            $namaJobs = self::normalizeText($sheet->getCell(Coordinate::stringFromColumnIndex(self::KOL_JOBS) . $row)->getValue());
            $fRaw     = self::normalizeText($sheet->getCell(Coordinate::stringFromColumnIndex(self::KOL_MANAGERIAL) . $row)->getValue());

            [$managerial, $managerialWarning] = self::parseManagerial($fRaw);
            if ($managerialWarning) {
                $warnings[] = ['baris_asal_excel' => $row, 'pesan' => $managerialWarning];
            }

            [$jenjangJabatan, $urutanJenjang, $kandidatNamaUnit, $jenjangWarning] =
                self::parseJenjang($listJabatan, $managerial);
            if ($jenjangWarning) {
                $warnings[] = ['baris_asal_excel' => $row, 'pesan' => $jenjangWarning];
            }

            foreach ($kompetensiColumns as $colLetter => $meta) {
                $rawVal = $sheet->getCell($colLetter . $row)->getValue();

                if ($rawVal === null || trim((string) $rawVal) === '') {
                    continue;
                }

                if (!is_numeric($rawVal) || (int) $rawVal < 1 || (int) $rawVal > 5) {
                    $warnings[] = [
                        'baris_asal_excel' => $row,
                        'pesan'            => "Nilai kompetensi tidak valid di kolom {$colLetter} (\"{$meta['nama_kompetensi']}\"): " . json_encode($rawVal),
                    ];
                    continue;
                }

                $level = (int) $rawVal;
                $asal  = $meta['is_generic'] ? 'generic' : 'native';

                $tidyRows[] = [
                    // "{baris_excel}-{kolom_excel}" (mis. "4-G") — stabil & unik dalam 1x
                    // parse (1 baris Excel x 1 kolom kompetensi = 1 tidy row), dipakai
                    // dirujuk balik di step "Pilih Primary" (bukan berbasis nama kompetensi,
                    // krn nama bisa duplikat lintas kolom generic dari job_family beda).
                    'row_id'           => "{$row}-{$colLetter}",
                    'baris_asal_excel' => $row,
                    'kandidat_nama_unit' => $kandidatNamaUnit,
                    'jenjang_jabatan'  => $jenjangJabatan,
                    'urutan_jenjang'   => $urutanJenjang,
                    'grade'            => $grade,
                    'nama_jobs'        => $namaJobs,
                    'managerial'       => $managerial === null ? '' : ($managerial ? 1 : 0),
                    'nama_kompetensi'  => $meta['nama_kompetensi'],
                    'job_family_id'    => $meta['job_family_id'],
                    'level'            => $level,
                    'asal'             => $asal,
                    // Default aman — belum ada penentuan primary otomatis sama sekali.
                    // Direvisi user manual di step "Pilih Primary" (Step baru, sebelum Review).
                    'prioritas'        => 'secondary',
                ];
            }

            $row++;
        }

        return [
            'tidyRows'               => $tidyRows,
            'warnings'               => $warnings,
            'kompetensiColumnCount'  => count($kompetensiColumns),
            'batasKolomDitemukan'    => $foundBatas,
        ];
    }

    private static function normalizeText($value): string
    {
        if ($value === null) {
            return '';
        }

        $clean = str_replace(["\r\n", "\r", "\n"], ' ', (string) $value);

        return trim(preg_replace('/\s+/', ' ', $clean));
    }

    /**
     * @return array{0: string, 1: ?string, 2: bool} [nama_kompetensi, rumpun_asal(header), is_generic]
     */
    private static function parseHeaderKompetensi(string $header): array
    {
        if (preg_match('/^(.*?)\s-\sJF\s+(.*)$/u', $header, $m)) {
            return [trim($m[1]), trim($m[2]), true];
        }

        return [$header, null, false];
    }

    /**
     * @return array{0: ?bool, 1: ?string} [managerial, warning]
     */
    private static function parseManagerial(string $raw): array
    {
        if ($raw === '') {
            return [null, "Kolom Managerial/Non Managerial kosong."];
        }

        $lower = mb_strtolower($raw);

        if (str_contains($lower, 'non')) {
            return [false, null];
        }

        if (str_contains($lower, 'manag')) {
            $warning = $raw !== 'Managerial'
                ? "Nilai kolom Managerial tidak standar: \"{$raw}\" — diasumsikan Managerial."
                : null;

            return [true, $warning];
        }

        return [null, "Nilai kolom Managerial/Non Managerial tidak dikenali: \"{$raw}\"."];
    }

    /**
     * @return array{0: ?string, 1: ?int, 2: string, 3: ?string} [jenjang_jabatan, urutan_jenjang, kandidat_nama_unit, warning]
     */
    private static function parseJenjang(string $listJabatan, ?bool $managerial): array
    {
        if ($managerial === true) {
            $firstSpace = strpos($listJabatan, ' ');

            if ($firstSpace === false) {
                return [$listJabatan, 1, '', "Nama unit tidak bisa diturunkan dari label managerial tunggal \"{$listJabatan}\"."];
            }

            $jenjang = substr($listJabatan, 0, $firstSpace);
            $unit    = trim(substr($listJabatan, $firstSpace + 1));

            return [$jenjang, 1, $unit, null];
        }

        foreach (self::JENJANG_URUTAN as $prefix => $urutan) {
            if (str_starts_with($listJabatan, $prefix . ' ')) {
                $unit = trim(substr($listJabatan, strlen($prefix)));

                return [$prefix, $urutan, $unit, null];
            }
        }

        return [
            null,
            null,
            $listJabatan,
            "Label jenjang tidak dikenali (bukan Officer/Junior Officer/Associate Officer/Senior Administrator/Administrator, dan bukan Managerial): \"{$listJabatan}\". Urutan jenjang tidak ditebak.",
        ];
    }
}
