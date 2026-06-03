<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property int|null    $user_id
 * @property string      $user_name
 * @property string      $aksi
 * @property string      $modul
 * @property string|null $target
 * @property string|null $keterangan
 * @property string|null $ip_address
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read string  $icon
 * @property-read array   $warna
 * @property-read string  $label_aksi
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'aksi',
        'modul', 'target', 'keterangan', 'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIconAttribute(): string
    {
        $icons = [
            'tambah'      => '➕',
            'edit'        => '✏️',
            'hapus'       => '🗑️',
            'import'      => '📥',
            'export'      => '📤',
            'login'       => '🔐',
            'logout'      => '🚪',
            'akhiri'      => '⏹',
            'assign'      => '👤',
            'salin'       => '📋',
            'edit_posisi' => '📝',
        ];

        return $icons[$this->aksi] ?? '📝';
    }

    public function getWarnaAttribute(): array
    {
        $warna = [
            'tambah'      => ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0'],
            'edit'        => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'hapus'       => ['bg' => '#fef2f2', 'text' => '#dc2626', 'border' => '#fecaca'],
            'import'      => ['bg' => '#f5f3ff', 'text' => '#7c3aed', 'border' => '#ddd6fe'],
            'export'      => ['bg' => '#fef3c7', 'text' => '#d97706', 'border' => '#fde68a'],
            'login'       => ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0'],
            'logout'      => ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0'],
            'akhiri'      => ['bg' => '#fef3c7', 'text' => '#d97706', 'border' => '#fde68a'],
            'assign'      => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
            'salin'       => ['bg' => '#f5f3ff', 'text' => '#7c3aed', 'border' => '#ddd6fe'],
            'edit_posisi' => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
        ];

        return $warna[$this->aksi] ?? ['bg' => '#f9fafb', 'text' => '#6b7280', 'border' => '#e5e7eb'];
    }

    public function getLabelAksiAttribute(): string
    {
        $labels = [
            'tambah'      => 'Tambah',
            'edit'        => 'Edit',
            'hapus'       => 'Hapus',
            'import'      => 'Import',
            'export'      => 'Export',
            'login'       => 'Login',
            'logout'      => 'Logout',
            'akhiri'      => 'Akhiri',
            'assign'      => 'Assign Karyawan',
            'salin'       => 'Salin Periode',
            'edit_posisi' => 'Edit Posisi',
        ];

        return $labels[$this->aksi] ?? ucfirst($this->aksi);
    }
}