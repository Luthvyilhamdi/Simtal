<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Toefl extends Model
{
    protected $table = 'toefls';

    protected $fillable = [
        'karyawan_id', 'skor', 'jenis', 'tanggal_tes', 'lembaga', 'keterangan', 'link_file',
    ];

    protected $casts = [
        'tanggal_tes' => 'date',
        'skor'        => 'float',   // dukung skor desimal (mis. IELTS band 6.5)
    ];

    /** Jenis tes. */
    public const JENIS = ['ITP', 'iBT', 'PBT', 'IELTS'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
