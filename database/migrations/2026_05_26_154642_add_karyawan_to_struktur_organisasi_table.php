<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struktur_organisasi', function (Blueprint $table) {
            $table->foreignId('karyawan_id')
                  ->nullable()
                  ->after('core')
                  ->constrained('karyawans')
                  ->nullOnDelete();
            $table->string('nik_karyawan')->nullable()->after('karyawan_id');
            $table->string('nama_karyawan')->nullable()->after('nik_karyawan');
        });
    }

    public function down(): void
    {
        Schema::table('struktur_organisasi', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->dropColumn(['karyawan_id', 'nik_karyawan', 'nama_karyawan']);
        });
    }
};