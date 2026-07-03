<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE history_jabatans MODIFY tipe ENUM('mutasi','rotasi','promosi','demosi','onboarding') NOT NULL DEFAULT 'mutasi'");
    }

    public function down(): void
    {
        // Kembalikan record rotasi ke mutasi agar tidak melanggar enum lama.
        DB::table('history_jabatans')->where('tipe', 'rotasi')->update(['tipe' => 'mutasi']);
        DB::statement("ALTER TABLE history_jabatans MODIFY tipe ENUM('mutasi','promosi','demosi','onboarding') NOT NULL DEFAULT 'mutasi'");
    }
};
