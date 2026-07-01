<?php

namespace App\Exports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KaryawanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $search;
    protected $status;

    public function __construct($search = null, $status = null)
    {
        $this->search = $search;
        $this->status = $status;
    }

    public function title(): string
    {
        return 'Data Karyawan';
    }

    public function collection()
    {
        $query = Karyawan::with([
            'jabatan', 'direktorat', 'kompartemen',
            'departemen', 'jobGrade', 'personGrade', 'kodeStruktur'
        ])->orderBy('nama');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                  ->orWhere('nik', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Usia',
            'Tanggal Masuk',
            'Masa Kerja (Tahun)',
            'No. HP',
            'Email',
            'Jenjang Pendidikan',
            'Jurusan',
            'Jabatan',
            'Jabatan Saat Ini',
            'Struktural/Fungsional',
            'Direktorat',
            'Kompartemen',
            'Departemen',
            'Job Grade',
            'Person Grade',
            'Kode Struktur',
            'Status',
        ];
    }

    protected $rowNo = 1;

    public function map($row): array
    {
        return [
            $this->rowNo++,
            $row->nik,
            $row->nama,
            $row->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $row->tempat_lahir ?? '-',
            $row->tanggal_lahir ? \Carbon\Carbon::parse($row->tanggal_lahir)->format('d/m/Y') : '-',
            $row->tanggal_lahir ? \Carbon\Carbon::parse($row->tanggal_lahir)->age : '-',
            $row->tanggal_masuk ? \Carbon\Carbon::parse($row->tanggal_masuk)->format('d/m/Y') : '-',
            $row->tanggal_masuk ? \Carbon\Carbon::parse($row->tanggal_masuk)->diffInYears(now()) : '-',
            $row->no_hp ?? '-',
            $row->email ?? '-',
            $row->jenjang_pendidikan ?? '-',
            $row->jurusan ?? '-',
            $row->jabatan->nama_jabatan ?? '-',
            $row->jabatan_saat_ini ?? '-',
            $row->struktural_fungsional ?? '-',
            $row->direktorat->nama_direktorat ?? '-',
            $row->kompartemen->nama_kompartemen ?? '-',
            $row->departemen->nama_departemen ?? '-',
            $row->jobGrade->job_grade ?? '-',
            $row->personGrade->person_grade ?? '-',
            $row->kodeStruktur->kode_struktur ?? '-',
            ucfirst($row->status),
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