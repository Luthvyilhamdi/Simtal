<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('history_jabatans', function (Blueprint $table) {
            if (!Schema::hasColumn('history_jabatans', 'jabatan_saat_ini')) {
                $table->string('jabatan_saat_ini')->nullable()->after('jabatan_id');
            }
            if (!Schema::hasColumn('history_jabatans', 'no_sk')) {
                $table->string('no_sk')->nullable()->after('keterangan');
            }
            if (!Schema::hasColumn('history_jabatans', 'tanggal_sk')) {
                $table->date('tanggal_sk')->nullable()->after('no_sk');
            }
        });
    }

    public function down(): void
    {
        Schema::table('history_jabatans', function (Blueprint $table) {
            foreach (['jabatan_saat_ini', 'no_sk', 'tanggal_sk'] as $col) {
                if (Schema::hasColumn('history_jabatans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};