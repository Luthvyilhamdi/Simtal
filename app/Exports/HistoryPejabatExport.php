<?php

namespace App\Exports;

use App\Models\HistoryPejabat;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class HistoryPejabatExport implements WithMultipleSheets
{
    protected $jabatan;
    protected $search;

    public function __construct($jabatan = null, $search = null)
    {
        $this->jabatan = $jabatan;
        $this->search  = $search;
    }

    public function sheets(): array
    {
        return [
            'Pejabat Aktif'   => new HistoryPejabatAktifSheet($this->jabatan, $this->search),
            'Pejabat Selesai' => new HistoryPejabatSelesaiSheet($this->jabatan, $this->search),
        ];
    }
}