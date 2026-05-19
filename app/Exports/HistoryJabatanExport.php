<?php

namespace App\Exports;

use App\Models\HistoryJabatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class HistoryJabatanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'History Jabatan';
    }

    public function collection()
    {
        return HistoryJabatan::with([
            'karyawan', 'jabatan', 'direktorat',
            'kompartemen', 'departemen', 'jobGrade',
            'personGrade', 'kodeStruktur'
        ])
        ->orderBy('karyawan_id')
        ->orderBy('tanggal_mulai', 'desc')
        ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama Karyawan',
            'Tipe',
            'Jabatan',
            'Jabatan Saat Ini',
            'Direktorat',
            'Kompartemen',
            'Departemen',
            'Job Grade',
            'Person Grade',
            'Kode Struktur',
            'No. SK',
            'Tanggal SK',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status',
            'Keterangan',
        ];
    }

    protected $rowNo = 1;

    public function map($row): array
    {
        return [
            $this->rowNo++,
            $row->karyawan->nik ?? '-',
            $row->karyawan->nama ?? '-',
            ucfirst($row->tipe),
            $row->jabatan->nama_jabatan ?? '-',
            $row->jabatan_saat_ini ?? '-',
            $row->direktorat->nama_direktorat ?? '-',
            $row->kompartemen->nama_kompartemen ?? '-',
            $row->departemen->nama_departemen ?? '-',
            $row->jobGrade->job_grade ?? '-',
            $row->personGrade->person_grade ?? '-',
            $row->kodeStruktur->kode_struktur ?? '-',
            $row->no_sk ?? '-',
            $row->tanggal_sk
                ? \Carbon\Carbon::parse($row->tanggal_sk)->format('d/m/Y')
                : '-',
            \Carbon\Carbon::parse($row->tanggal_mulai)->format('d/m/Y'),
            $row->tanggal_selesai
                ? \Carbon\Carbon::parse($row->tanggal_selesai)->format('d/m/Y')
                : 'Sekarang',
            $row->is_current ? 'Aktif' : 'Selesai',
            $row->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }
}