<?php

namespace App\Exports;

use App\Models\HistoryAssessment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class HistoryAssessmentExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $search;
    protected $rekomendasi;
    protected $tahun;

    public function __construct($search = null, $rekomendasi = null, $tahun = null)
    {
        $this->search      = $search;
        $this->rekomendasi = $rekomendasi;
        $this->tahun       = $tahun;
    }

    public function title(): string
    {
        return 'History Assessment';
    }

    public function collection()
    {
        $query = HistoryAssessment::with('karyawan')
            ->orderBy('tanggal_pelaksanaan', 'desc');

        if ($this->search) {
            $query->whereHas('karyawan', function($q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                  ->orWhere('nik', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->rekomendasi) {
            $query->where('rekomendasi_final', $this->rekomendasi);
        }

        if ($this->tahun) {
            $query->whereYear('tanggal_pelaksanaan', $this->tahun);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama Karyawan',
            'Jabatan Saat Ini',
            'Job Grade',
            'Person Grade',
            'Jenis Kelamin',
            'Usia',
            'Job Stream',
            'Tanggal Pelaksanaan',
            'Tingkat Pengukuran',
            'Rekomendasi Inti (%)',
            'Rekomendasi Primer (%)',
            'Rekomendasi Sekunder (%)',
            'Rekomendasi Final',
            'Tanggal Exp IDP',
            'Status IDP',
            'Keterangan',
        ];
    }

    protected $rowNo = 1;

    public function map($row): array
    {
        $rekomendasiLabel = match($row->rekomendasi_final) {
            'ready'                  => 'Ready',
            'ready_with_development' => 'Ready with Development',
            'not_ready'              => 'Not Ready',
            default                  => '-',
        };

        $statusIdp = '-';
        if ($row->tanggal_exp_idp) {
            $statusIdp = \Carbon\Carbon::parse($row->tanggal_exp_idp)->isPast()
                ? 'Expired'
                : 'Aktif';
        }

        return [
            $this->rowNo++,
            $row->karyawan->nik ?? '-',
            $row->karyawan->nama ?? '-',
            $row->jabatan_saat_ini ?? '-',
            $row->job_grade ?? '-',
            $row->person_grade ?? '-',
            $row->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $row->usia ?? '-',
            $row->job_stream ?? '-',
            \Carbon\Carbon::parse($row->tanggal_pelaksanaan)->format('d/m/Y'),
            $row->tingkat_pengukuran ?? '-',
            $row->rekomendasi_inti ?? '-',
            $row->rekomendasi_primer ?? '-',
            $row->rekomendasi_skunder ?? '-',
            $rekomendasiLabel,
            $row->tanggal_exp_idp
                ? \Carbon\Carbon::parse($row->tanggal_exp_idp)->format('d/m/Y')
                : '-',
            $statusIdp,
            $row->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7c3aed']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }
}