<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE unit_organisasi_snapshot MODIFY COLUMN level ENUM('direktorat','kompartemen','departemen','bagian','seksi','foreman','fungsional') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE unit_organisasi_snapshot MODIFY COLUMN level ENUM('direktorat','kompartemen','departemen','bagian','fungsional') NOT NULL");
    }
};
