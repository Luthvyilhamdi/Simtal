<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateAssessmentExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Template Assessment';
    }

    public function headings(): array
    {
        return [
            'nik',
            'tanggal_pelaksanaan',
            'job_stream',
            'tingkat_pengukuran',
            'rekomendasi_inti',
            'rekomendasi_primer',
            'rekomendasi_skunder',
            'rekomendasi_final',
            'keterangan',
        ];
    }

    public function array(): array
    {
        return [
            [
                '10001',
                '15/03/2024',
                'Technical',
                'Level 2',
                '85',
                '72',
                '60',
                'Ready',
                'Siap untuk promosi',
            ],
            [
                '10002',
                '20/04/2024',
                'Managerial',
                'Level 1',
                '65',
                '55',
                '70',
                'Ready with Development',
                'Perlu pengembangan leadership',
            ],
            [
                '10003',
                '10/05/2024',
                'Technical',
                'Level 1',
                '40',
                '45',
                '50',
                'Not Ready',
                'Butuh pelatihan teknis lebih lanjut',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7c3aed']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $sheet->getStyle('A2:I4')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9ca3af']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ]);

        $sheet->freezePane('A2');
        return [];
    }
}