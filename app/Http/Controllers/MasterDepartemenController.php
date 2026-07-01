<?php

namespace App\Http\Controllers;

use App\Models\Departemen;

class MasterDepartemenController extends MasterController
{
    protected string $model     = Departemen::class;
    protected string $column    = 'nama_departemen';
    protected string $routeName = 'master.departemen';
    protected string $view      = 'master.departemen';
    protected string $label     = 'Departemen';
}
