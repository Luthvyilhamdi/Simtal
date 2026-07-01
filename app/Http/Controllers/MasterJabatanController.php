<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;

class MasterJabatanController extends MasterController
{
    protected string $model     = Jabatan::class;
    protected string $column    = 'nama_jabatan';
    protected string $routeName = 'master.jabatan';
    protected string $view      = 'master.jabatan';
    protected string $label     = 'Jabatan';
}
