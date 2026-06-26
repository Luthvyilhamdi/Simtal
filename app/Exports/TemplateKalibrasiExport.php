<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateKalibrasiExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        // Nilai valid: FEE, EXE, PEE, MEE, ME, SME, PME, BEE, NME, FBE — satu baris per (karyawan, tahun)
        return [
            ['1234567', 'Contoh Karyawan', 2025, 'EXE', 'Catatan opsional'],
            ['1234567', 'Contoh Karyawan', 2024, 'MEE', ''],
        ];
    }

    public function headings(): array
    {
        return ['NIK', 'Nama', 'Tahun', 'Nilai', 'Keterangan'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:E1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('15803D');
        return [];
    }
}