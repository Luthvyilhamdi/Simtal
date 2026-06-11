<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('history_assessment_kompetensi', function (Blueprint $table) {
            $table->string('lembaga')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('history_assessment_kompetensi', function (Blueprint $table) {
            $table->dropColumn('lembaga');
        });
    }
};