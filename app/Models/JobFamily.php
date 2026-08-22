<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobFamily extends Model
{
    protected $table = 'job_family';

    protected $fillable = [
        'nama',
    ];

    public function kompetensiTeknis()
    {
        return $this->hasMany(KompetensiTeknis::class);
    }
}
