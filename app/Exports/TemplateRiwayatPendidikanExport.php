<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateRiwayatPendidikanExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'History Pendidikan';
    }

    public function headings(): array
    {
        return ['nik', 'jenjang', 'jurusan', 'institusi'];
    }

    public function array(): array
    {
        // Contoh: satu karyawan (NIK sama) boleh punya beberapa baris jenjang.
        return [
            ['10001', 'SMA/SMK', 'IPA',       'SMAN 1 Jakarta'],
            ['10001', 'S1',      'Akuntansi',  'Universitas Indonesia'],
            ['10001', 'S2',      'Manajemen',  'Universitas Gadjah Mada'],
            ['10002', 'D3',      'Manajemen SDM', 'Politeknik Negeri Bandung'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $sheet->getStyle('A2:D5')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9ca3af']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ]);

        $sheet->freezePane('A2');

        return [];
    }
}
