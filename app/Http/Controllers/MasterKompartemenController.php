<?php

namespace App\Http\Controllers;

use App\Models\Kompartemen;

class MasterKompartemenController extends MasterController
{
    protected string $model     = Kompartemen::class;
    protected string $column    = 'nama_kompartemen';
    protected string $routeName = 'master.kompartemen';
    protected string $view      = 'master.kompartemen';
    protected string $label     = 'Kompartemen';
}
