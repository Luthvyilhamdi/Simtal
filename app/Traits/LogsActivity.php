<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    protected function log(
        string $aksi,
        string $modul,
        string $target = '',
        string $keterangan = ''
    ): void {
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'user_name'  => auth()->user()->name ?? 'System',
            'aksi'       => $aksi,
            'modul'      => $modul,
            'target'     => $target,
            'keterangan' => $keterangan,
            'ip_address' => Request::ip(),
        ]);
    }
}