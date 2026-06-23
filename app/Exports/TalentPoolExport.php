<?php

namespace App\Exports;

use App\Models\TalentPool;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TalentPoolExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    protected ?string $periode;
    protected ?string $klasifikasi;
    protected ?string $search;

    public function __construct(?string $periode = null, ?string $klasifikasi = null, ?string $search = null)
    {
        $this->periode     = $periode;
        $this->klasifikasi = $klasifikasi;
        $this->search      = $search;
    }

    public function collection()
    {
        $query = TalentPool::with(['karyawan.jobGrade', 'karyawan.personGrade'])
            ->orderBy('klasifikasi')
            ->orderBy('periode', 'desc');

        if ($this->periode) {
            $query->where('periode', $this->periode);
        }
        if ($this->klasifikasi) {
            $query->where('klasifikasi', $this->klasifikasi);
        }
        if ($this->search) {
            $query->whereHas('karyawan', function($q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                  ->orWhere('nik', 'like', '%'.$this->search.'%');
            });
        }

        return $query->get();
    }

    public function map($row): array
    {
        $k = $row->karyawan;
        return [
            $k->nik ?? '-',
            $k->nama ?? '-',
            $row->periode,
            ucfirst($row->klasifikasi),
            $k->jobGrade->job_grade ?? '-',
            $k->personGrade->person_grade ?? '-',
            $k->band ?? '-',
            $row->catatan ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Nama Karyawan',
            'Periode',
            'Klasifikasi',
            'Job Grade',
            'Person Grade',
            'Band',
            'Catatan',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 30,
            'C' => 10,
            'D' => 15,
            'E' => 12,
            'F' => 14,
            'G' => 10,
            'H' => 35,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header style
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(20);

        // Warnai Longlist & Shortlist
        $lastRow = $sheet->getHighestRow();
        for ($i = 2; $i <= $lastRow; $i++) {
            $klasifikasi = $sheet->getCell('D'.$i)->getValue();
            if ($klasifikasi === 'Shortlist') {
                $sheet->getStyle('A'.$i.':H'.$i)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
                ]);
            } elseif ($klasifikasi === 'Longlist') {
                $sheet->getStyle('A'.$i.':H'.$i)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                ]);
            }
        }

        return [];
    }
}