<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitOrganisasiTransisi extends Model
{
    protected $table = 'unit_organisasi_transisi';

    protected $fillable = [
        'struktur_organisasi_versi_id', 'jenis_transisi',
        'unit_asal_id', 'unit_baru_id', 'keterangan',
    ];

    public function strukturOrganisasiVersi()
    {
        return $this->belongsTo(StrukturOrganisasiVersi::class);
    }

    public function unitAsal()
    {
        return $this->belongsTo(UnitOrganisasi::class, 'unit_asal_id');
    }

    public function unitBaru()
    {
        return $this->belongsTo(UnitOrganisasi::class, 'unit_baru_id');
    }
}
