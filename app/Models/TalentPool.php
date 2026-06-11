<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $periode
 * @property int    $karyawan_id
 * @property string $klasifikasi  longlist|shortlist
 * @property string|null $catatan
 * @property int|null $created_by
 */
class TalentPool extends Model
{
    protected $fillable = [
        'periode', 'karyawan_id', 'klasifikasi', 'catatan', 'created_by',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getKlasifikasiLabelAttribute(): string
    {
        return match($this->klasifikasi) {
            'longlist'  => 'Longlist',
            'shortlist' => 'Shortlist',
            default     => '-',
        };
    }

    public function getKlasifikasiWarnaAttribute(): array
    {
        return match($this->klasifikasi) {
            'longlist'  => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
            'shortlist' => ['bg' => '#dcfce7', 'text' => '#15803d'],
            default     => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
        };
    }
}