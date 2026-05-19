<?php

namespace App\Exports;

use App\Models\HistoryPejabat;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class HistoryPejabatAktifSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $jabatan;
    protected $search;

    public function __construct($jabatan = null, $search = null)
    {
        $this->jabatan = $jabatan;
        $this->search  = $search;
    }

    public function title(): string
    {
        return 'Pejabat Aktif';
    }

    public function query()
    {
        $query = HistoryPejabat::with('karyawan')
            ->whereNull('tanggal_selesai')
            ->orderBy('jabatan')
            ->orderBy('tanggal_mulai', 'desc');

        if ($this->jabatan) {
            $query->where('jabatan', $this->jabatan);
        }

        if ($this->search) {
            $query->whereHas('karyawan', function($q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                  ->orWhere('nik', 'like', '%'.$this->search.'%');
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama Karyawan',
            'Jabatan',
            'Jabatan Lengkap',
            'Direktorat',
            'Kompartemen',
            'Departemen',
            'Job Grade',
            'Person Grade',
            'No. SK',
            'Tanggal SK',
            'Tanggal Mulai',
            'Lama Menjabat',
            'Status',
        ];
    }

    protected $rowNo = 1;

    public function map($row): array
    {
        return [
            $this->rowNo++,
            $row->karyawan->nik ?? '-',
            $row->karyawan->nama ?? '-',
            $row->jabatan,
            $row->jabatan_saat_ini ?? '-',
            $row->direktorat ?? '-',
            $row->kompartemen ?? '-',
            $row->departemen ?? '-',
            $row->job_grade ?? '-',
            $row->person_grade ?? '-',
            $row->no_sk ?? '-',
            $row->tanggal_sk ? $row->tanggal_sk->format('d/m/Y') : '-',
            $row->tanggal_mulai->format('d/m/Y'),
            $row->durasi,
            'Aktif',
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