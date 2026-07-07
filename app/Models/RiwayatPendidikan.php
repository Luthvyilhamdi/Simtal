<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPendidikan extends Model
{
    protected $fillable = [
        'karyawan_id', 'jenjang', 'jurusan', 'institusi',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
