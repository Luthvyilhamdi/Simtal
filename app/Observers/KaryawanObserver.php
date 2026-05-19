<?php

namespace App\Observers;

use App\Models\Karyawan;

class KaryawanObserver
{
    public function updated(Karyawan $karyawan)
    {
        // Cek apakah ada perubahan di field yang diintegrasikan
        if ($karyawan->wasChanged(['jabatan_saat_ini', 'job_grade_id', 'person_grade_id'])) {

            $jobGrade    = $karyawan->jobGrade->job_grade ?? null;
            $personGrade = $karyawan->personGrade->person_grade ?? null;

            // Update semua history assessment karyawan ini
            $karyawan->historyAssessment()->update([
                'jabatan_saat_ini' => $karyawan->jabatan_saat_ini,
                'job_grade'        => $jobGrade,
                'person_grade'     => $personGrade,
            ]);
        }
    }
}