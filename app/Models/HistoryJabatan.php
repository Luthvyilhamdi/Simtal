<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryJabatan extends Model
{
    protected $fillable = [
        'karyawan_id', 'jabatan_id', 'jabatan_saat_ini', 'direktorat_id',
        'kompartemen_id', 'departemen_id', 'job_grade_id',
        'person_grade_id', 'kode_struktur_id',
        'tanggal_mulai', 'tanggal_selesai',
        'tipe', 'keterangan',
        'no_sk', 'tanggal_sk', 
        'is_current',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_sk'      => 'date',
        'is_current'      => 'boolean',
    ];

    public function karyawan()    { return $this->belongsTo(Karyawan::class); }
    public function jabatan()     { return $this->belongsTo(Jabatan::class); }
    public function direktorat()  { return $this->belongsTo(Direktorat::class); }
    public function kompartemen() { return $this->belongsTo(Kompartemen::class); }
    public function departemen()  { return $this->belongsTo(Departemen::class); }
    public function jobGrade()    { return $this->belongsTo(JobGrade::class); }
    public function personGrade() { return $this->belongsTo(PersonGrade::class); }
    public function kodeStruktur(){ return $this->belongsTo(KodeStruktur::class); }
}