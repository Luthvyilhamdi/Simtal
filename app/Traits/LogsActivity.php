<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    protected function log(
        string $aksi,
        string $modul,
        string $target = '',
        string $keterangan = ''
    ): void {
        /** @var User|null $user */
        $user = Auth::user();

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'user_name'  => $user?->name ?? 'System',
            'aksi'       => $aksi,
            'modul'      => $modul,
            'target'     => $target,
            'keterangan' => $keterangan,
            'ip_address' => Request::ip(),
        ]);
    }
}