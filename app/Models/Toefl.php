<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Toefl extends Model
{
    protected $table = 'toefls';

    protected $fillable = [
        'karyawan_id', 'skor', 'jenis', 'tanggal_tes', 'lembaga', 'keterangan', 'link_file',
    ];

    protected $casts = [
        'tanggal_tes' => 'date',
        'skor'        => 'float',   // dukung skor desimal (mis. IELTS band 6.5)
    ];

    /**
     * Saran jenis tes untuk dropdown — BUKAN daftar tertutup.
     *
     * Di lapangan ada jenis lain (mis. "Prediction"), dan sebagian besar data
     * lama tidak mengisi kolom ini sama sekali. Karena itu kolom jenis boleh
     * dikosongkan dan boleh diisi bebas; nilai di sini hanya mempercepat
     * pengisian yang umum. Jangan dipakai sebagai aturan validasi.
     */
    public const JENIS = ['ITP', 'iBT', 'PBT', 'IELTS', 'Prediction'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
