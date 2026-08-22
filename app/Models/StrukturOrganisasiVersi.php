<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrukturOrganisasiVersi extends Model
{
    protected $table = 'struktur_organisasi_versi';

    protected $fillable = [
        'nomor_sk', 'tanggal_sk', 'tanggal_mulai_berlaku', 'keterangan', 'status', 'created_by',
    ];

    protected $casts = [
        'tanggal_sk'            => 'date',
        'tanggal_mulai_berlaku' => 'date',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unitOrganisasiSnapshots()
    {
        return $this->hasMany(UnitOrganisasiSnapshot::class);
    }

    public function unitOrganisasiTransisis()
    {
        return $this->hasMany(UnitOrganisasiTransisi::class);
    }

    public function jobProfiles()
    {
        return $this->hasMany(JobProfile::class);
    }

    // Versi berikutnya (berdasarkan tanggal_mulai_berlaku)
    public function versiBerikutnya()
    {
        return self::where('tanggal_mulai_berlaku', '>', $this->tanggal_mulai_berlaku)
            ->orderBy('tanggal_mulai_berlaku')
            ->first();
    }

    // Tanggal berakhir dihitung dari tanggal_mulai_berlaku versi berikutnya (H-1), null jika ini versi terakhir/masih berlaku
    public function getTanggalBerakhirAttribute()
    {
        $versiBerikutnya = $this->versiBerikutnya();

        return $versiBerikutnya ? $versiBerikutnya->tanggal_mulai_berlaku->copy()->subDay() : null;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }
}
