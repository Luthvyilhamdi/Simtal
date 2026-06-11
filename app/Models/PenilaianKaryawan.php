<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $karyawan_id
 * @property int    $tahun
 * @property string $periode
 * @property string $tipe
 * @property string $judul
 * @property float  $nilai
 * @property string|null $keterangan
 */
class PenilaianKaryawan extends Model
{
    protected $fillable = [
        'karyawan_id', 'tahun', 'periode', 'tipe',
        'judul', 'nilai', 'keterangan', 'created_by',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPeriodeLabelAttribute(): string
    {
        return match($this->periode) {
            'triwulan_1' => 'Triwulan I',
            'triwulan_2' => 'Triwulan II',
            'triwulan_3' => 'Triwulan III',
            'triwulan_4' => 'Triwulan IV',
            'tahunan'    => 'Tahunan',
            default      => '-',
        };
    }

    public function getNilaiFormatAttribute(): string
    {
        return number_format($this->nilai, 2, ',', '.');
    }
}