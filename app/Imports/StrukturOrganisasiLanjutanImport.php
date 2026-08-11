<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Parser mentah versi LANJUTAN (bukan baseline) struktur organisasi: kolom
 * kode_sementara, nama_unit, level, parent_kode_sementara, formasi, jenis_transisi,
 * unit_versi_sebelumnya, keterangan_transisi. Validasi & resolusi transisi dilakukan
 * terpisah di controller lewat alur upload -> preview -> konfirmasi, bukan langsung
 * ditulis ke DB saat parsing.
 *
 * Parser ini HEADER-DRIVEN (baca semua kolom yg ADA di baris header, bukan skema tetap) —
 * jadi kolom opsional seperti unit_versi_sebelumnya_level (dipakai controller sbg override
 * disambiguasi level saat nama_unit tidak unik di Versi Dasar, lihat
 * StrukturOrganisasiVersiController::resolveVersiDasarUnitReference()) otomatis ikut
 * terbaca kalau ada di file, tanpa perlu ditambahkan ke REQUIRED_HEADERS di bawah.
 *
 * Sama seperti StrukturOrganisasiBaselineImport: sengaja TIDAK pakai Maatwebsite\Excel
 * ToCollection biasa (ikut sheet AKTIF terakhir, bukan selalu index 0) — di sini scan
 * semua sheet dan pilih yang header-nya cocok dengan kolom yang kita butuhkan.
 */
class StrukturOrganisasiLanjutanImport
{
    public Collection $rows;

    // Kolom referensi lain di template (grand_total_dari_bagan, status, keterangan) dan
    // kolom opsional (unit_versi_sebelumnya_level) tidak wajib ada di sini — cuma kolom yg
    // BENAR2 wajib utk sheet ini dikenali sbg sheet data yg valid.
    private const REQUIRED_HEADERS = [
        'kode_sementara', 'nama_unit', 'level', 'parent_kode_sementara', 'formasi',
        'jenis_transisi', 'unit_versi_sebelumnya', 'keterangan_transisi',
    ];

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
                    if ($key === '') continue;
                    $assoc[$key] = $rawRow[$i] ?? null;
                }
                $assoc['mc_formasi'] = $assoc['formasi'] ?? null;
                $rows->push($assoc);
            }

            $this->rows = $rows;
            return;
        }
    }
}
