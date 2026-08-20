<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifikasiRead extends Model
{
    protected $table = 'notifikasi_reads';

    protected $fillable = [
        'notifikasi_id', 'user_id', 'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
