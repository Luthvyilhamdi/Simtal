<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TemplateTalentPoolExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            ['1234567', now()->year, 'longlist',  'Catatan opsional'],
            ['7654321', now()->year, 'shortlist', ''],
        ];
    }

    public function headings(): array
    {
        return ['NIK', 'Periode', 'Klasifikasi', 'Catatan'];
    }

    public function columnWidths(): array
    {
        return ['A' => 15, 'B' => 10, 'C' => 15, 'D' => 35];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Catatan petunjuk
        $sheet->getComment('C2')->getText()->createTextRun('Isi: longlist atau shortlist');

        return [];
    }
}