<?php

namespace App\Exports;

use App\Models\PenilaianKaryawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PenilaianExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ?string $search = null,
        protected ?string $tahun  = null,
    ) {}

    public function collection()
    {
        $q = PenilaianKaryawan::with('karyawan')
            ->orderBy('tahun', 'desc')
            ->orderBy('periode');

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
        return ['NIK', 'Nama', 'Tahun', 'Periode', 'Tipe', 'Judul', 'Nilai', 'Keterangan'];
    }

    public function map($p): array
    {
        return [
            optional($p->karyawan)->nik,
            optional($p->karyawan)->nama,
            $p->tahun,
            $p->periode_label,         // "Triwulan I" — saat re-import dikenali lagi
            $p->tipe,
            $p->judul,
            (float) $p->nilai,         // numerik mentah untuk round-trip aman
            $p->keterangan,
        ];
    }
}