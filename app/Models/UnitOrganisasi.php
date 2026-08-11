<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitOrganisasi extends Model
{
    protected $table = 'unit_organisasi';

    public function snapshots()
    {
        return $this->hasMany(UnitOrganisasiSnapshot::class);
    }

    public function transisiSebagaiAsal()
    {
        return $this->hasMany(UnitOrganisasiTransisi::class, 'unit_asal_id');
    }

    public function transisiSebagaiBaru()
    {
        return $this->hasMany(UnitOrganisasiTransisi::class, 'unit_baru_id');
    }
}
