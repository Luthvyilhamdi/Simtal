<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan 'penempatan' dulu, tanpa menghapus 'onboarding',
        //    supaya data lama tidak truncated.
        DB::statement("ALTER TABLE history_jabatans MODIFY tipe ENUM('mutasi','rotasi','promosi','demosi','onboarding','penempatan') NOT NULL DEFAULT 'mutasi'");

        // 2. Migrasikan data lama.
        DB::table('history_jabatans')
            ->where('tipe', 'onboarding')
            ->update(['tipe' => 'penempatan']);

        // 3. Baru hapus 'onboarding' dari enum setelah data aman.
        DB::statement("ALTER TABLE history_jabatans MODIFY tipe ENUM('mutasi','rotasi','promosi','demosi','penempatan') NOT NULL DEFAULT 'mutasi'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE history_jabatans MODIFY tipe ENUM('mutasi','rotasi','promosi','demosi','onboarding','penempatan') NOT NULL DEFAULT 'mutasi'");

        DB::table('history_jabatans')
            ->where('tipe', 'penempatan')
            ->update(['tipe' => 'onboarding']);

        DB::statement("ALTER TABLE history_jabatans MODIFY tipe ENUM('mutasi','rotasi','promosi','demosi','onboarding') NOT NULL DEFAULT 'mutasi'");
    }
};