<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kontrol akses menu per-admin.
 *
 * `menu_access` menyimpan daftar key menu yang boleh dibuka oleh seorang admin
 * (JSON array). Hanya berlaku untuk role 'admin':
 *  - super_admin → selalu bisa semua (kolom ini diabaikan)
 *  - user        → tetap hanya Struktur Organisasi
 *  - admin       → hanya menu yang tercantum di sini (default: kosong = tak ada)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('menu_access')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('menu_access');
        });
    }
};
