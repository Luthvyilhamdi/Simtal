<?php

namespace App\Exports;

use App\Models\StrukturOrganisasiVersi;
use App\Models\UnitOrganisasiSnapshot;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StrukturOrganisasiVersiExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithTitle
{
    protected StrukturOrganisasiVersi $versi;
    protected Collection $units;
    protected Collection $namaByUnitId;
    protected array $totals;

    public function __construct(StrukturOrganisasiVersi $versi)
    {
        $this->versi = $versi;
        $this->units = $versi->unitOrganisasiSnapshots()->orderBy('level')->orderBy('nama_unit')->get();
        $this->namaByUnitId = $this->units->pluck('nama_unit', 'unit_organisasi_id');
        $this->totals = UnitOrganisasiSnapshot::totalFormasiBawahanBatch($this->units);
    }

    public function collection(): Collection
    {
        return $this->units;
    }

    public function map($unit): array
    {
        $totalBawahan = $this->totals[$unit->unit_organisasi_id] ?? null;

        return [
            $unit->nama_unit,
            ucfirst($unit->level),
            $unit->parent_unit_organisasi_id ? ($this->namaByUnitId[$unit->parent_unit_organisasi_id] ?? '-') : '-',
            $unit->mc_formasi,
            is_null($totalBawahan) ? '-' : $totalBawahan,
            $unit->mc_formasi + ($totalBawahan ?? 0),
            $unit->keterangan ?: '-',
        ];
    }

    public function headings(): array
    {
        return ['Nama Unit', 'Level', 'Parent', 'Formasi Unit', 'Total Bawahan', 'Grand Total', 'Keterangan'];
    }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 14, 'C' => 32, 'D' => 13, 'E' => 14, 'F' => 13, 'G' => 35];
    }

    public function title(): string
    {
        // Nama sheet Excel maks 31 karakter & tidak boleh mengandung / \ ? * [ ] :
        $bersih = str_replace(['/', '\\', '?', '*', '[', ']', ':'], '-', $this->versi->nomor_sk);
        return substr($bersih, 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(20);

        $lastRow = $this->units->count() + 1;
        $sheet->getStyle('D2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
