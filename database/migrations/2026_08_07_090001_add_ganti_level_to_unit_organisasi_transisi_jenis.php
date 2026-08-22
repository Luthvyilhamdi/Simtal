<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE unit_organisasi_transisi MODIFY COLUMN jenis_transisi ENUM('rename','pecah','gabung','pindah_induk','bubar','baru','ganti_level') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE unit_organisasi_transisi MODIFY COLUMN jenis_transisi ENUM('rename','pecah','gabung','pindah_induk','bubar','baru') NOT NULL");
    }
};
