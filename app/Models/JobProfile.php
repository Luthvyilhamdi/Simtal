<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobProfile extends Model
{
    protected $table = 'job_profile';

    protected $fillable = [
        'unit_organisasi_id',
        'struktur_organisasi_versi_id',
        'nama_jabatan',
        'file_path',
        'file_original_name',
        'keterangan',
    ];

    public function unitOrganisasi()
    {
        return $this->belongsTo(UnitOrganisasi::class);
    }

    public function strukturOrganisasiVersi()
    {
        return $this->belongsTo(StrukturOrganisasiVersi::class);
    }
}
