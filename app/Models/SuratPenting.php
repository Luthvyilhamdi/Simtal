<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property int         $karyawan_id
 * @property int         $uploaded_by
 * @property string      $judul
 * @property string|null $nomor_surat
 * @property string      $kategori
 * @property string      $file_path
 * @property string      $file_name
 * @property string|null $file_size
 * @property string|null $keterangan
 * @property \Carbon\Carbon      $tanggal_surat
 * @property \Carbon\Carbon|null $tanggal_exp
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 */
class SuratPenting extends Model
{
    protected $fillable = [
        'karyawan_id', 'judul', 'nomor_surat', 'kategori',
        'tanggal_surat', 'tanggal_exp', 'file_path',
        'file_name', 'file_size', 'keterangan', 'uploaded_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_exp'   => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getKategoriLabelAttribute(): string
    {
        return match($this->kategori) {
            'sk_jabatan'       => 'SK Jabatan',
            'sk_promosi'       => 'SK Promosi',
            'sk_mutasi'        => 'SK Mutasi',
            'sk_pensiun'       => 'SK Pensiun',
            'surat_tugas'      => 'Surat Tugas',
            'surat_peringatan' => 'Surat Peringatan',
            'kontrak'          => 'Kontrak',
            'sertifikat'       => 'Sertifikat',
            default            => 'Lainnya',
        };
    }

    public function getKategoriWarnaAttribute(): array
    {
        return match($this->kategori) {
            'sk_jabatan','sk_promosi','sk_mutasi' => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
            'sk_pensiun'                          => ['bg' => '#fef3c7', 'text' => '#d97706'],
            'surat_tugas'                         => ['bg' => '#f0fdf4', 'text' => '#15803d'],
            'surat_peringatan'                    => ['bg' => '#fee2e2', 'text' => '#dc2626'],
            'kontrak'                             => ['bg' => '#f5f3ff', 'text' => '#7c3aed'],
            'sertifikat'                          => ['bg' => '#fdf4ff', 'text' => '#a21caf'],
            default                               => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->tanggal_exp && $this->tanggal_exp->isPast();
    }

    public function getIsPdfAttribute(): bool
    {
        return str_ends_with(strtolower($this->file_name), '.pdf');
    }

    public function getIsImageAttribute(): bool
    {
        return in_array(strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']);
    }
}