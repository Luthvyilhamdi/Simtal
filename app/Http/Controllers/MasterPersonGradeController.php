<?php

namespace App\Http\Controllers;

use App\Models\PersonGrade;

class MasterPersonGradeController extends MasterController
{
    protected string $model     = PersonGrade::class;
    protected string $column    = 'person_grade';
    protected string $routeName = 'master.person-grade';
    protected string $view      = 'master.person_grade';
    protected string $label     = 'Person Grade';
}
