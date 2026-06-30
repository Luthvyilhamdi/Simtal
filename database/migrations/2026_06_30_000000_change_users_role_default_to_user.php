<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Default kolom role diturunkan dari 'admin' menjadi 'user'. Default
        // 'admin' sebelumnya berbahaya: setiap baris user yang dibuat tanpa
        // menyetel role secara eksplisit akan menjadi admin.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'user') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'user') NOT NULL DEFAULT 'admin'");
    }
};
