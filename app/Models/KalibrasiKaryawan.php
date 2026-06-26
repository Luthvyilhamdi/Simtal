<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KalibrasiKaryawan extends Model
{
    protected $fillable = [
        'karyawan_id', 'tahun', 'nilai', 'keterangan', 'created_by',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function nilaiOptions(): array
    {
        return [
            'FEE' => 'Far Exceeded Expectation (FEE)',
            'EXE' => 'Exceeds Expectation (EXE)',
            'PEE' => 'Partially Exceeds Expectation (PEE)',
            'MEE' => 'Meet Expectation (MEE)',
            'ME'  => 'Meet Expectation (ME)',
            'SME' => 'Somewhat Meet Expectation (SME)',
            'PME' => 'Partially Meets Expectation (PME)',
            'BEE' => 'Below Expectation (BEE)',
            'NME' => 'Does Not Meet Expectation (NME)',
            'FBE' => 'Far Below Expectation (FBE)',
        ];
    }

    public function getNilaiLabelAttribute(): string
    {
        return self::nilaiOptions()[$this->nilai] ?? $this->nilai;
    }

    public function getNilaiBadgeColorAttribute(): array
    {
        return match($this->nilai) {
            'FEE' => ['bg' => '#dcfce7', 'text' => '#15803d'],
            'EXE' => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
            'PEE' => ['bg' => '#ecfeff', 'text' => '#0e7490'],
            'MEE' => ['bg' => '#f3f4f6', 'text' => '#374151'],
            'ME'  => ['bg' => '#f3f4f6', 'text' => '#4b5563'],
            'SME' => ['bg' => '#fef9c3', 'text' => '#ca8a04'],
            'PME' => ['bg' => '#ffedd5', 'text' => '#ea580c'],
            'BEE' => ['bg' => '#fef3c7', 'text' => '#d97706'],
            'NME' => ['bg' => '#fee2e2', 'text' => '#b91c1c'],
            'FBE' => ['bg' => '#fee2e2', 'text' => '#dc2626'],
            default => ['bg' => '#f3f4f6', 'text' => '#374151'],
        };
    }
}