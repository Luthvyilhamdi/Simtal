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
            'lanjut_mdj',
        ];
    }

    public function array(): array
    {
        return [
            // Contoh 1 karyawan. Kolom lanjut_mdj: ya = kelanjutan, tidak = jabatan baru, kosong = auto ikut band.
            ['10001','Staff','Staff Keuangan','Direktorat Keuangan','Kompartemen Akuntansi','Departemen Anggaran','12','1','A.1.1','penempatan','01/01/2010','31/12/2012','SK/001/2010','01/01/2010','Jabatan awal (kosong = auto)',''],
            ['10001','Staff','Staff Anggaran & Perbendaharaan','Direktorat Keuangan','Kompartemen Akuntansi','Departemen Anggaran','12','1','A.1.1','mutasi','01/01/2013','31/12/2015','SK/002/2013','01/01/2013','Ganti nama karena SO, band sama -> ya','ya'],
            ['10001','Staff','Staff Perencanaan','Direktorat Keuangan','Kompartemen Perencanaan','Departemen Perencanaan','12','1','A.1.1','rotasi','01/01/2016','31/12/2018','SK/003/2016','01/01/2016','Band sama TAPI beda ranah (jabatan baru) -> tidak','tidak'],
            ['10001','Manager','Manager Keuangan','Direktorat Keuangan','Kompartemen Akuntansi','Departemen Anggaran','9','3','A.1.1','promosi','01/01/2019','31/12/2021','SK/004/2019','01/01/2019','Promosi naik band (kosong = auto reset)',''],
            ['10001','Senior Manager','Senior Manager Keuangan','Direktorat Keuangan','Kompartemen Akuntansi','Departemen Anggaran','8','3','A.1.1','promosi','01/01/2022','','SK/005/2022','01/01/2022','Jabatan saat ini - kosongkan tanggal_selesai',''],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Contoh data
        $sheet->getStyle('A2:P6')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '9ca3af']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f9fafb']],
        ]);

        // Highlight baris terakhir (is_current)
        $sheet->getStyle('A6:P6')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '15803d']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0fdf4']],
        ]);

        $sheet->freezePane('A2');
        return [];
    }
}