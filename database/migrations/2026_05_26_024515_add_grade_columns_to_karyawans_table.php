<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->date('tanggal_mulai_pg')->nullable()->after('person_grade_id');
            $table->date('tanggal_mulai_jg')->nullable()->after('tanggal_mulai_pg');
            $table->date('tanggal_mulai_band')->nullable()->after('tanggal_mulai_jg');
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn(['tanggal_mulai_pg', 'tanggal_mulai_jg', 'tanggal_mulai_band']);
        });
    }
};