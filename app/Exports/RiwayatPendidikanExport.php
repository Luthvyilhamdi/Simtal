<?php

namespace App\Exports;

use App\Models\Karyawan;
use App\Models\RiwayatPendidikan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Export History Pendidikan — 1 BARIS = 1 JENJANG (NIK boleh berulang).
 * Kolom: NIK, Nama, Jenjang Pendidikan, Jurusan, Institusi/Sekolah.
 * Urut: nama karyawan → jenjang (SD→S3). Mengikuti filter pencarian bila ada.
 */
class RiwayatPendidikanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function title(): string
    {
        return 'History Pendidikan';
    }

    public function collection()
    {
        $urutJenjang = "FIELD(jenjang,'" . implode("','", Karyawan::JENJANG_PENDIDIKAN) . "')";

        $query = RiwayatPendidikan::with(['karyawan:id,nik,nama'])
            ->join('karyawans', 'karyawans.id', '=', 'riwayat_pendidikans.karyawan_id')
            ->select('riwayat_pendidikans.*')
            ->orderBy('karyawans.nama')
            ->orderByRaw($urutJenjang)
            ->orderBy('riwayat_pendidikans.id');

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
        return ['NIK', 'Nama', 'Jenjang Pendidikan', 'Jurusan', 'Institusi/Sekolah'];
    }

    public function map($row): array
    {
        return [
            $row->karyawan->nik ?? '-',
            $row->karyawan->nama ?? '-',
            $row->jenjang,
            $row->jurusan ?: '-',
            $row->institusi ?: '-',
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
