<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasis';

    protected $fillable = [
        'judul', 'pesan', 'tipe', 'level',
        'notifiable_type', 'notifiable_id',
        'is_read', 'read_at',
    ];

    protected $casts = [
        'is_read'  => 'boolean',
        'read_at'  => 'datetime',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    // Icon berdasarkan tipe
    public function getIconAttribute(): string
    {
        return match($this->tipe) {
            'idp_expire'      => '📋',
            'masa_kerja'      => '🏆',
            'pensiun'         => '🎯',
            'pgs_pjs_berakhir'=> '⏰',
            'assessment'      => '📊',
            default           => '🔔',
        };
    }

    // Warna berdasarkan level
    public function getWarnaAttribute(): array
    {
        return match($this->level) {
            'danger'  => ['bg' => '#fef2f2', 'text' => '#dc2626', 'border' => '#fecaca'],
            'warning' => ['bg' => '#fffbeb', 'text' => '#d97706', 'border' => '#fde68a'],
            default   => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
        };
    }
}