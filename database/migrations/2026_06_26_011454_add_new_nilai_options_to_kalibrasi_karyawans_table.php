<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE kalibrasi_karyawans MODIFY nilai ENUM('FEE','EXE','MEE','BEE','FBE','SME','NME','PME','ME','PEE') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE kalibrasi_karyawans MODIFY nilai ENUM('FEE','EXE','MEE','BEE','FBE') NOT NULL");
    }
};
