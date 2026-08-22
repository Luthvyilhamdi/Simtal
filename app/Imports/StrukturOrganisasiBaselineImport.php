<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Parser mentah versi baseline struktur organisasi: kolom kode_sementara, nama_unit,
 * level, parent_kode_sementara, formasi. Validasi & commit dilakukan terpisah di
 * controller lewat alur upload -> preview -> konfirmasi, bukan langsung ditulis ke DB
 * saat parsing.
 *
 * Sengaja TIDAK pakai Maatwebsite\Excel\Concerns\ToCollection biasa: file transkripsi
 * riil ternyata punya lebih dari 1 sheet (data + sheet "Header Versi" terpisah), dan
 * import single-sheet default Maatwebsite mengikuti sheet yang sedang AKTIF saat file
 * terakhir disimpan di Excel — bukan selalu sheet index 0. Di sini kita scan semua
 * sheet dan pilih yang header-nya cocok dengan kolom yang kita butuhkan, supaya tidak
 * bergantung pada sheet mana yang kebetulan aktif.
 */
class StrukturOrganisasiBaselineImport
{
    public Collection $rows;

    // Kolom "formasi" adalah nama baku template — kolom referensi lain (grand total,
    // status, keterangan) opsional & diabaikan, tidak perlu ada di sini.
    private const REQUIRED_HEADERS = ['kode_sementara', 'nama_unit', 'level', 'parent_kode_sementara', 'formasi'];

    public function parse(string $path): void
    {
        $spreadsheet = IOFactory::load($path);
        $this->rows = collect();

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $data = $sheet->toArray(null, true, true, false);
            if (empty($data)) {
                continue;
            }

            $headerRow = array_map(fn ($h) => strtolower(trim((string) $h)), $data[0]);

            $hasRequiredHeaders = collect(self::REQUIRED_HEADERS)->every(fn ($h) => in_array($h, $headerRow, true));
            if (!$hasRequiredHeaders) {
                continue; // bukan sheet data kita — coba sheet lain (mis. sheet info/header SK)
            }

            $rows = collect();
            foreach (array_slice($data, 1) as $rawRow) {
                $assoc = [];
                foreach ($headerRow as $i => $key) {
                    if ($key === '') {
                        continue;
                    }
                    $assoc[$key] = $rawRow[$i] ?? null;
                }
                // Normalisasi ke 'mc_formasi' (nama kolom DB asli) supaya kode validasi/commit di
                // controller tetap satu nama, walau header Excel-nya baku "formasi".
                $assoc['mc_formasi'] = $assoc['formasi'] ?? null;
                $rows->push($assoc);
            }

            $this->rows = $rows;
            return;
        }
    }
}
