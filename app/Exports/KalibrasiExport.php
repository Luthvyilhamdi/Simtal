<?php

namespace App\Exports;

use App\Models\KalibrasiKaryawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KalibrasiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ?string $search = null,
        protected ?string $tahun  = null,
    ) {}

    public function collection()
    {
        $q = KalibrasiKaryawan::with('karyawan')->orderBy('tahun', 'desc');

        if ($this->tahun) {
            $q->where('tahun', $this->tahun);
        }
        if ($this->search) {
            $s = $this->search;
            $q->whereHas('karyawan', fn($k) => $k->where('nama', 'like', "%$s%")->orWhere('nik', 'like', "%$s%"));
        }

        return $q->get();
    }

    public function headings(): array
    {
        return ['NIK', 'Nama', 'Tahun', 'Nilai', 'Label Nilai', 'Keterangan'];
    }

    public function map($k): array
    {
        return [
            optional($k->karyawan)->nik,
            optional($k->karyawan)->nama,
            $k->tahun,
            $k->nilai,            // kode (FEE/EXE/...) — untuk re-import
            $k->nilai_label,      // label lengkap (read-only info)
            $k->keterangan,
        ];
    }
}