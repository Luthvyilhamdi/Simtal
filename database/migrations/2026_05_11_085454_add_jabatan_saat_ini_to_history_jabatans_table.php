<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('history_jabatans', function (Blueprint $table) {
            $table->string('jabatan_saat_ini')->nullable()->after('jabatan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_jabatans', function (Blueprint $table) {
            $table->dropColumn('jabatan_saat_ini');
        });
    }
};
