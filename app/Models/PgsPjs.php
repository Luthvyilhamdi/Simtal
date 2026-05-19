<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PgsPjs extends Model
{
    protected $table = 'pgs_pjs';

    protected $fillable = [
        'karyawan_id', 'tipe', 'jabatan_pgs_pjs',
        'direktorat', 'departemen',
        'no_sk', 'tanggal_sk',
        'tanggal_mulai', 'tanggal_berakhir',
        'keterangan', 'is_active',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date',
        'tanggal_berakhir' => 'date',
        'tanggal_sk'       => 'date',
        'is_active'        => 'boolean',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    // Cek apakah sudah expired
    public function getIsExpiredAttribute()
    {
        if (!$this->tanggal_berakhir) return false;
        return $this->tanggal_berakhir->isPast();
    }

    // Sisa hari
    public function getSisaHariAttribute()
    {
        if (!$this->tanggal_berakhir) return null;
        if ($this->isExpired) return 0;
        return now()->diffInDays($this->tanggal_berakhir);
    }

    // Label tipe
    public function getTipeLabelAttribute()
    {
        return strtoupper($this->tipe);
    }

    // Warna tipe
    public function getTipeWarnaAttribute()
    {
        return match($this->tipe) {
            'pgs' => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'pjs' => ['bg' => '#f5f3ff', 'text' => '#7c3aed', 'border' => '#ddd6fe'],
        };
    }
}