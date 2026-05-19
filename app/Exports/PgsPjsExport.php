<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PgsPjsExport implements WithMultipleSheets
{
    protected $tipe;
    protected $search;

    public function __construct($tipe = null, $search = null)
    {
        $this->tipe   = $tipe;
        $this->search = $search;
    }

    public function sheets(): array
    {
        return [
            'Aktif'   => new PgsPjsAktifSheet($this->tipe, $this->search),
            'History' => new PgsPjsHistorySheet($this->tipe, $this->search),
        ];
    }
}