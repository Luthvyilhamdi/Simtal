<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateTmtExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Template TMT';
    }

    public function headings(): array
    {
        return ['nik', 'nama', 'tmt_band', 'tmt_jg', 'tmt_pg'];
    }

    public function array(): array
    {
        return [
            // Format tanggal dd/mm/yyyy. Kolom kosong = tidak diubah.
            ['10001', 'Budi Santoso', '01/01/2020', '01/01/2022', '01/01/2022'],
            ['10002', 'Siti Aminah', '15/06/2019', '', '01/03/2021'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $sheet->getStyle('A2:E3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9ca3af']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ]);
        $sheet->freezePane('A2');
        return [];
    }
}
