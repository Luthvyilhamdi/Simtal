<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplatePenilaianExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        // Contoh baris (boleh dihapus saat mengisi data asli)
        return [
            ['1234567', 'Contoh Karyawan', 2025, 'Triwulan I', 'KPI', 'KPI Triwulan I', '85.50', 'Catatan opsional'],
            ['1234567', 'Contoh Karyawan', 2025, 'Tahunan',    'KPI', 'KPI Tahunan',     '88.00', ''],
        ];
    }

    public function headings(): array
    {
        return ['NIK', 'Nama', 'Tahun', 'Periode', 'Tipe', 'Judul', 'Nilai', 'Keterangan'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('15803D');
        return [];
    }
}