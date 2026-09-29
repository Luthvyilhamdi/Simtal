<?php

namespace App\Exports;

use App\Models\Toefl;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateToeflExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Nilai TOEFL';
    }

    public function headings(): array
    {
        return ['nik', 'skor', 'jenis', 'tanggal_tes', 'lembaga', 'keterangan'];
    }

    public function array(): array
    {
        // Contoh: satu karyawan (NIK sama) boleh punya beberapa tes.
        return [
            ['10001', '520',  'ITP',   '15/03/2026', 'Pusat Bahasa UI',  'Tes pertama'],
            ['10001', '547',  'ITP',   '20/08/2026', 'Pusat Bahasa UI',  'Tes ulang'],
            ['10002', '88',   'iBT',   '05/05/2026', 'ETS Jakarta',      ''],
            ['10003', '6.5',  'IELTS', '11/07/2026', 'IDP Bandung',      'Band 6.5'],
            ['10004', '480',  '',      '02/09/2026', 'Internal PIM',     'jenis boleh dikosongkan'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $sheet->getStyle('A2:F6')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9ca3af']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ]);

        // Petunjuk singkat di bawah contoh, supaya aturan pengisian ikut terbawa
        // dalam berkasnya dan tidak hilang saat template diteruskan ke orang lain.
        $sheet->setCellValue('A7', 'Petunjuk:');
        $sheet->setCellValue('A8', '1. Hapus baris contoh (baris 2-6) sebelum diisi data sebenarnya.');
        $sheet->setCellValue('A9', '2. Kolom wajib: nik, skor, tanggal_tes. Jenis, lembaga & keterangan boleh dikosongkan.');
        $sheet->setCellValue('A10', '3. jenis bebas diisi. Saran yang umum: ' . implode(', ', Toefl::JENIS) . '. Boleh juga dikosongkan.');
        $sheet->setCellValue('A11', '4. tanggal_tes format hari/bulan/tahun, contoh 15/03/2026.');
        $sheet->setCellValue('A12', '5. NIK harus sudah terdaftar di Profil Karyawan. Baris dengan NIK asing dilewati.');
        $sheet->setCellValue('A13', '6. Tes yang sama (NIK + tanggal + jenis) akan diperbarui, bukan digandakan.');
        $sheet->getStyle('A7')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => '15803D']]]);
        $sheet->getStyle('A8:A13')->applyFromArray(['font' => ['size' => 10, 'color' => ['rgb' => '6b7280']]]);

        $sheet->freezePane('A2');

        return [];
    }
}
