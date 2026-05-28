<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struktur_organisasi', function (Blueprint $table) {
            $table->unsignedTinyInteger('bulan')->default(1)->after('id');
            $table->unsignedSmallInteger('tahun')->default(2025)->after('bulan');
            $table->index(['bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::table('struktur_organisasi', function (Blueprint $table) {
            $table->dropIndex(['bulan', 'tahun']);
            $table->dropColumn(['bulan', 'tahun']);
        });
    }
};