<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsulanPromosi extends Model
{
    protected $fillable = [
        'karyawan_id',
        'jabatan_saat_ini', 'job_grade_saat_ini', 'person_grade_saat_ini',
        'band_saat_ini', 'struktural_fungsional', 'departemen_saat_ini', 'kompartemen_saat_ini',
        'jabatan_tujuan', 'jabatan_tujuan_id', 'job_grade_promosi', 'person_grade_promosi',
        'direktorat_tujuan_id', 'kompartemen_tujuan_id', 'departemen_tujuan_id',
        'assessment_id', 'hasil_assessment', 'tanggal_berlaku_assessment', 'level_ukur',
        'tanggal_usulan',
        'mdg_band_ok', 'mdg_jg_ok', 'mdg_pg_ok',
        'talent_pool_id', 'talent_pool_periode', 'talent_pool_klasifikasi',
        'kpi_snapshot', 'kalibrasi_snapshot',
        'absensi', 'kehadiran', 'periode_penilaian', 'tata_kelola',
        'mc_tersedia', 'hasil_evaluasi',
        'tindak_lanjut', 'tanggal_sidang', 'hasil_sidang',
        'status', 'catatan', 'created_by',
        'no_sk', 'tmt', 'sk_diproses',
    ];

    protected $casts = [
        'tanggal_usulan'            => 'date',
        'tanggal_berlaku_assessment' => 'date',
        'tanggal_sidang'            => 'date',
        'tmt'                       => 'date',
        'mdg_band_ok'               => 'boolean',
        'mdg_jg_ok'                 => 'boolean',
        'mdg_pg_ok'                 => 'boolean',
        'mc_tersedia'               => 'boolean',
        'sk_diproses'               => 'boolean',
        'kpi_snapshot'              => 'array',
        'kalibrasi_snapshot'        => 'array',
    ];

    public function karyawan()   { return $this->belongsTo(Karyawan::class); }
    public function assessment() { return $this->belongsTo(HistoryAssessment::class, 'assessment_id'); }
    public function talentPool() { return $this->belongsTo(TalentPool::class, 'talent_pool_id'); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }

    // Jabatan master tujuan
    public function jabatanTujuan() { return $this->belongsTo(Jabatan::class, 'jabatan_tujuan_id'); }

    // Unit tujuan promosi
    public function direktoratTujuan()  { return $this->belongsTo(Direktorat::class, 'direktorat_tujuan_id'); }
    public function kompartemenTujuan() { return $this->belongsTo(Kompartemen::class, 'kompartemen_tujuan_id'); }
    public function departemenTujuan()  { return $this->belongsTo(Departemen::class, 'departemen_tujuan_id'); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'        => 'Draft',
            'verif_berkas' => 'Verifikasi Berkas',
            'sidang'       => 'Sidang',
            'lulus'        => 'Lulus',
            'tidak_lulus'  => 'Tidak Lulus',
            'ditolak'      => 'Ditolak',
            default        => '-',
        };
    }

    public function getStatusColorAttribute(): array
    {
        return match($this->status) {
            'draft'        => ['bg' => '#f3f4f6', 'text' => '#374151'],
            'verif_berkas' => ['bg' => '#fef3c7', 'text' => '#d97706'],
            'sidang'       => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
            'lulus'        => ['bg' => '#dcfce7', 'text' => '#15803d'],
            'tidak_lulus'  => ['bg' => '#fee2e2', 'text' => '#dc2626'],
            'ditolak'      => ['bg' => '#fce7f3', 'text' => '#be185d'],
            default        => ['bg' => '#f3f4f6', 'text' => '#374151'],
        };
    }

    public function getHasilAssessmentLabelAttribute(): string
    {
        return match($this->hasil_assessment) {
            'ready'                  => 'Ready',
            'ready_with_development' => 'Ready with Development',
            'not_ready'              => 'Not Ready',
            default                  => $this->hasil_assessment ?? '-',
        };
    }
}