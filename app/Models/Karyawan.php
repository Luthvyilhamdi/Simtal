<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    // Tabel karyawans (default Laravel sudah benar, tidak perlu $table)
    protected $fillable = [
        'nik',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'tanggal_masuk',
        'foto',
        'status',
        'direktorat_id',
        'kompartemen_id',
        'departemen_id',
        'job_grade_id',
        'person_grade_id',
        'jabatan_id',
        'kode_struktur_id',
        'jabatan_saat_ini',
    ];

    public function direktorat()
    {
        return $this->belongsTo(Direktorat::class);
    }

    public function kompartemen()
    {
        return $this->belongsTo(Kompartemen::class);
    }

    public function departemen()
    {
        return $this->belongsTo(Departemen::class);
    }

    public function jobGrade()
    {
        return $this->belongsTo(JobGrade::class);
    }

    public function personGrade()
    {
        return $this->belongsTo(PersonGrade::class);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function kodeStruktur()
    {
        return $this->belongsTo(KodeStruktur::class);
    }
    public function historyJabatan()
    {
        return $this->hasMany(HistoryJabatan::class)->orderBy('tanggal_mulai', 'desc');
    }

    public function jabatanSekarang()
    {
        return $this->hasOne(HistoryJabatan::class)->where('is_current', true);
    }

    public function historyAssessment()
    {
        return $this->hasMany(HistoryAssessment::class)->orderBy('tanggal_pelaksanaan', 'desc');
    }
    
    public function pgsPjs()
    {
        return $this->hasMany(PgsPjs::class)->orderBy('tanggal_mulai', 'desc');
    }

    public function pgsPjsAktif()
    {
        return $this->hasMany(PgsPjs::class)->where('is_active', true);
    }

    public function historyPejabat()
    {
        return $this->hasMany(HistoryPejabat::class)->orderBy('tanggal_mulai', 'desc');
    }

    public function pejabatAktif()
    {
        return $this->hasOne(HistoryPejabat::class)->whereNull('tanggal_selesai');
    }

    public function suratPenting()
    {
    return $this->hasMany(SuratPenting::class)->orderBy('tanggal_surat', 'desc');
    }
}
