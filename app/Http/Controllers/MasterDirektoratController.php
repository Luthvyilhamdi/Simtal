<?php

namespace App\Http\Controllers;

use App\Models\Direktorat;

class MasterDirektoratController extends MasterController
{
    protected string $model     = Direktorat::class;
    protected string $column    = 'nama_direktorat';
    protected string $routeName = 'master.direktorat';
    protected string $view      = 'master.direktorat';
    protected string $label     = 'Direktorat';
}
