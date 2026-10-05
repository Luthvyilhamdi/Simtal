<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    /** @param  \Illuminate\Database\Eloquent\Model|null  $subjek  record yang disentuh */
    protected function log(
        string $aksi,
        string $modul,
        string $target = '',
        string $keterangan = '',
        $subjek = null
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
            'subjek_type' => $subjek ? $subjek::class : null,
            'subjek_id'   => $subjek?->getKey(),
        ]);
    }
}