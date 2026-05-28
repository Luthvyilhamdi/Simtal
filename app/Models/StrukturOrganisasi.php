<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrukturOrganisasi extends Model
{
    protected $table = 'struktur_organisasi';

    protected $fillable = [
        'direktorat', 'kompartemen', 'dept', 'bagian',
        'fungsional', 'posisi', 'job_grade',
        'mc_tko', 'pengisian', 'deviasi', 'core',
        'karyawan_id', 'nik_karyawan', 'nama_karyawan',
    ];

    protected $casts = [
        'mc_tko'    => 'integer',
        'pengisian' => 'integer',
        'deviasi'   => 'integer',
    ];

    // Auto hitung deviasi
    protected static function booted(): void
    {
        static::saving(function ($so) {
            // Pengisian otomatis dari karyawan_id
            $so->pengisian = $so->karyawan_id ? 1 : 0;
            $so->deviasi   = $so->pengisian - $so->mc_tko;
        });
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function getWarnaDeviasiAttribute(): string
    {
        if ($this->deviasi > 0) return '#dc2626';
        if ($this->deviasi < 0) return '#d97706';
        return '#15803d';
    }

    public function getCoreWarnaAttribute(): array
    {
        return match($this->core) {
            'Core'     => ['bg' => '#f0fdf4', 'text' => '#15803d'],
            'Non Core' => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
            default    => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
        };
    }
}