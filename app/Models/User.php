<?php

namespace App\Models;

use App\Support\MenuAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property string|null $nik
 * @property array|null $menu_access
 * @property string $password
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'nik', 'menu_access',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'menu_access'       => 'array',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Apakah user boleh membuka menu tertentu (key dari MenuAccess).
     *  - super_admin → semua
     *  - user        → hanya Struktur Organisasi
     *  - admin       → hanya yang tercantum di menu_access
     */
    public function canAccessMenu(string $key): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($this->role === 'user') {
            return $key === 'struktur';
        }
        if ($this->role === 'admin') {
            return in_array($key, $this->menu_access ?? [], true);
        }
        return false;
    }

    /**
     * Nama route landing setelah login, disesuaikan dengan akses.
     * Admin diarahkan ke menu pertama yang boleh dibuka; bila tidak ada,
     * ke halaman pemberitahuan "belum ada akses".
     */
    public function homeRoute(): string
    {
        if ($this->isSuperAdmin()) {
            return 'dashboard';
        }
        if ($this->role === 'user') {
            return 'struktur-organisasi.index';
        }
        foreach (MenuAccess::menus() as $key => $def) {
            if ($this->canAccessMenu($key)) {
                return $def['home'];
            }
        }
        return 'no-access';
    }
}