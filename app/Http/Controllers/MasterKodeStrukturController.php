<?php

namespace App\Http\Controllers;

use App\Models\KodeStruktur;

class MasterKodeStrukturController extends MasterController
{
    protected string $model     = KodeStruktur::class;
    protected string $column    = 'kode_struktur';
    protected string $routeName = 'master.kode-struktur';
    protected string $view      = 'master.kode_struktur';
    protected string $label     = 'Kode Struktur';
}
