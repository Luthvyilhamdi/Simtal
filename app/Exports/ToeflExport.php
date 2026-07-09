<?php

namespace App\Exports;

use App\Models\Toefl;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Export nilai TOEFL — 1 BARIS = 1 TES (NIK boleh berulang).
 * Kolom: NIK, Nama, Skor, Jenis, Tanggal Tes, Lembaga, Keterangan.
 */
class ToeflExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function title(): string
    {
        return 'Nilai TOEFL';
    }

    public function collection()
    {
        $query = Toefl::with(['karyawan:id,nik,nama'])
            ->join('karyawans', 'karyawans.id', '=', 'toefls.karyawan_id')
            ->select('toefls.*')
            ->orderBy('karyawans.nama')
            ->orderByDesc('toefls.tanggal_tes')
            ->orderByDesc('toefls.id');

        if ($this->search) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('karyawans.nama', 'like', "%{$s}%")
                  ->orWhere('karyawans.nik', 'like', "%{$s}%");
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['NIK', 'Nama', 'Skor', 'Jenis', 'Tanggal Tes', 'Lembaga', 'Keterangan'];
    }

    public function map($row): array
    {
        return [
            $row->karyawan->nik ?? '-',
            $row->karyawan->nama ?? '-',
            $row->skor,
            $row->jenis ?: '-',
            $row->tanggal_tes ? $row->tanggal_tes->format('d/m/Y') : '-',
            $row->lembaga ?: '-',
            $row->keterangan ?: '-',
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
