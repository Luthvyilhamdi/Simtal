<?php

namespace App\Exports;

use App\Models\PgsPjs;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PgsPjsAktifSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $tipe;
    protected $search;

    public function __construct($tipe = null, $search = null)
    {
        $this->tipe   = $tipe;
        $this->search = $search;
    }

    public function title(): string
    {
        return 'PGS & PJS Aktif';
    }

    public function query()
    {
        $query = PgsPjs::with('karyawan')
            ->where('is_active', true)
            ->orderBy('tipe')
            ->orderBy('tanggal_mulai', 'desc');

        if ($this->tipe) {
            $query->where('tipe', $this->tipe);
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
            'Tipe',
            'NIK',
            'Nama Karyawan',
            'Jabatan PGS/PJS',
            'Direktorat',
            'Departemen',
            'No. SK',
            'Tanggal SK',
            'Tanggal Mulai',
            'Tanggal Berakhir',
            'Keterangan',
            'Status',
        ];
    }

    protected $rowNo = 1;

    public function map($row): array
    {
        return [
            $this->rowNo++,
            strtoupper($row->tipe),
            $row->karyawan->nik ?? '-',
            $row->karyawan->nama ?? '-',
            $row->jabatan_pgs_pjs,
            $row->direktorat ?? '-',
            $row->departemen ?? '-',
            $row->no_sk ?? '-',
            $row->tanggal_sk ? $row->tanggal_sk->format('d/m/Y') : '-',
            $row->tanggal_mulai->format('d/m/Y'),
            $row->tanggal_berakhir ? $row->tanggal_berakhir->format('d/m/Y') : 'Belum ditentukan',
            $row->keterangan ?? '-',
            'Aktif',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }
}