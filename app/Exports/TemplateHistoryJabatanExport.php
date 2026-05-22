<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateHistoryJabatanExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Template History Jabatan';
    }

    public function headings(): array
    {
        return [
            'nik',
            'jabatan',
            'jabatan_saat_ini',
            'direktorat',
            'kompartemen',
            'departemen',
            'job_grade',
            'person_grade',
            'kode_struktur',
            'tipe',
            'tanggal_mulai',
            'tanggal_selesai',
            'no_sk',
            'tanggal_sk',
            'keterangan',
        ];
    }

    public function array(): array
    {
        return [
            // Contoh 1 karyawan 5 jabatan
            ['10001','Staff','Staff Keuangan','Direktorat Keuangan','Kompartemen Akuntansi','Departemen Anggaran','JG-3','PG-1','A.1.1','onboarding','01/01/2010','31/12/2012','SK/001/2010','01/01/2010','Jabatan awal masuk'],
            ['10001','Senior Staff','Senior Staff Keuangan','Direktorat Keuangan','Kompartemen Akuntansi','Departemen Anggaran','JG-4','PG-2','A.1.1','promosi','01/01/2013','31/12/2015','SK/002/2013','01/01/2013','Promosi pertama'],
            ['10001','Supervisor','Supervisor Keuangan','Direktorat Keuangan','Kompartemen Akuntansi','Departemen Anggaran','JG-5','PG-2','A.1.1','promosi','01/01/2016','31/12/2018','SK/003/2016','01/01/2016','Naik ke supervisor'],
            ['10001','Manager','Manager Keuangan','Direktorat Keuangan','Kompartemen Akuntansi','Departemen Anggaran','JG-7','PG-3','A.1.1','promosi','01/01/2019','31/12/2021','SK/004/2019','01/01/2019','Promosi manager'],
            ['10001','Senior Manager','Senior Manager Keuangan','Direktorat Keuangan','Kompartemen Akuntansi','Departemen Anggaran','JG-8','PG-3','A.1.1','promosi','01/01/2022','','SK/005/2022','01/01/2022','Jabatan saat ini - kosongkan tanggal_selesai'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Contoh data
        $sheet->getStyle('A2:O6')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9ca3af']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ]);

        // Highlight baris terakhir (is_current)
        $sheet->getStyle('A6:O6')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '15803d']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0fdf4']],
        ]);

        $sheet->freezePane('A2');
        return [];
    }
}