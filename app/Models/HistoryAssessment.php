<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryAssessment extends Model
{
    protected $fillable = [
        'karyawan_id',
        'jabatan_saat_ini',
        'job_grade',
        'person_grade',
        'jenis_kelamin',
        'usia',
        'job_stream',
        'tanggal_pelaksanaan',
        'tingkat_pengukuran',
        'rekomendasi_inti',
        'rekomendasi_primer',
        'rekomendasi_skunder',
        'rekomendasi_final',
        'tanggal_exp_idp',
        'keterangan',
        'lembaga',
        'link_file',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'date',
        'tanggal_exp_idp'     => 'date',
        'rekomendasi_inti'    => 'float',
        'rekomendasi_primer'  => 'float',
        'rekomendasi_skunder' => 'float',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function getRekomendasiFinalLabelAttribute()
    {
        return match($this->rekomendasi_final) {
            'ready'                  => 'Ready',
            'ready_with_development' => 'Ready with Development',
            'not_ready'              => 'Not Ready',
            default                  => '-',
        };
    }

    public function getRekomendasiFinalWarnaAttribute()
    {
        return match($this->rekomendasi_final) {
            'ready'                  => ['bg' => '#dcfce7', 'text' => '#15803d'],
            'ready_with_development' => ['bg' => '#fef3c7', 'text' => '#d97706'],
            'not_ready'              => ['bg' => '#fee2e2', 'text' => '#dc2626'],
            default                  => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
        };
    }

    public function getIsExpiredAttribute()
    {
        return $this->tanggal_exp_idp && $this->tanggal_exp_idp->isPast();
    }
}