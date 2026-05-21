<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateKaryawanExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Data Karyawan';
    }

    public function headings(): array
    {
        return [
            'nik',
            'nama',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'tanggal_masuk',
            'jabatan',
            'jabatan_saat_ini',
            'direktorat',
            'kompartemen',
            'departemen',
            'job_grade',
            'person_grade',
            'kode_struktur',
            'status',
        ];
    }

    public function array(): array
    {
        // Contoh data
        return [
            [
                '10001',
                'Ahmad Fauzi',
                'L',
                'Jakarta',
                '15/08/1990',
                '01/03/2015',
                'Senior Manager',
                'Senior Manager Keuangan',
                'Direktorat Keuangan',
                'Kompartemen Akuntansi',
                'Departemen Anggaran',
                'JG-8',
                'PG-3',
                'A.1.1',
                'aktif',
            ],
            [
                '10002',
                'Siti Rahma',
                'P',
                'Bandung',
                '22/04/1993',
                '15/06/2018',
                'Staff',
                'Staff HR',
                'Direktorat SDM',
                'Kompartemen Rekrutmen',
                'Departemen HR',
                'JG-5',
                'PG-2',
                'B.2.1',
                'aktif',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Style header
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Style contoh data (italic abu)
        $sheet->getStyle('A2:O3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9ca3af']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ]);

        // Freeze row header
        $sheet->freezePane('A2');

        return [];
    }
}