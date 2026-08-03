<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struktur_organisasi', function (Blueprint $table) {
            $table->string('job_family')->nullable()->after('job_stream');
        });
    }

    public function down(): void
    {
        Schema::table('struktur_organisasi', function (Blueprint $table) {
            $table->dropColumn('job_family');
        });
    }
};
