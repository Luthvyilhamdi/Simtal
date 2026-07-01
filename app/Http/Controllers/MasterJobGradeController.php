<?php

namespace App\Http\Controllers;

use App\Models\JobGrade;

class MasterJobGradeController extends MasterController
{
    protected string $model     = JobGrade::class;
    protected string $column    = 'job_grade';
    protected string $routeName = 'master.job-grade';
    protected string $view      = 'master.job_grade';
    protected string $label     = 'Job Grade';
}
