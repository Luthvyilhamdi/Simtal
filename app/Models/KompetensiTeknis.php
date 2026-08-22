<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KompetensiTeknis extends Model
{
    protected $table = 'kompetensi_teknis';

    protected $fillable = [
        'nama_kompetensi',
        'job_family_id',
        'keterangan',
    ];

    public function jobFamily()
    {
        return $this->belongsTo(JobFamily::class);
    }

    public function unitKompetensiTeknis()
    {
        return $this->hasMany(UnitKompetensiTeknis::class);
    }
}
