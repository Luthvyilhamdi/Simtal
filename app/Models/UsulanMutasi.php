<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsulanMutasi extends Model
{
    protected $fillable = [
        'karyawan_id', 'jenis',
        'jabatan_saat_ini', 'direktorat_saat_ini', 'kompartemen_saat_ini', 'departemen_saat_ini',
        'job_grade_saat_ini', 'person_grade_saat_ini',
        'jabatan_tujuan_id', 'direktorat_tujuan_id', 'kompartemen_tujuan_id', 'departemen_tujuan_id',
        'kode_struktur_tujuan_id',
        'alasan', 'tanggal_usulan',
        'status', 'no_sk', 'tmt', 'sk_diproses',
        'created_by',
    ];

    protected $casts = [
        'tanggal_usulan' => 'date',
        'tmt'            => 'date',
        'sk_diproses'    => 'boolean',
    ];

    public function karyawan()         { return $this->belongsTo(Karyawan::class); }
    public function createdBy()        { return $this->belongsTo(User::class, 'created_by'); }
    public function jabatanTujuan()    { return $this->belongsTo(Jabatan::class, 'jabatan_tujuan_id'); }
    public function direktoratTujuan() { return $this->belongsTo(Direktorat::class, 'direktorat_tujuan_id'); }
    public function kompartemenTujuan(){ return $this->belongsTo(Kompartemen::class, 'kompartemen_tujuan_id'); }
    public function departemenTujuan() { return $this->belongsTo(Departemen::class, 'departemen_tujuan_id'); }
    public function kodeStrukturTujuan(){ return $this->belongsTo(KodeStruktur::class, 'kode_struktur_tujuan_id'); }

    public function getJenisLabelAttribute(): string
    {
        return match($this->jenis) {
            'rotasi' => 'Rotasi',
            'mutasi' => 'Mutasi',
            default  => ucfirst($this->jenis ?? '-'),
        };
    }

    public function getJenisColorAttribute(): array
    {
        return match($this->jenis) {
            'rotasi' => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
            'mutasi' => ['bg' => '#f5f3ff', 'text' => '#7c3aed'],
            default  => ['bg' => '#f1f5f9', 'text' => '#475569'],
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->sk_diproses ? 'Selesai' : 'Menunggu SK';
    }
}