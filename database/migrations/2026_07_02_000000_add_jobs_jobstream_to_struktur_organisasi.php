<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struktur_organisasi', function (Blueprint $table) {
            $table->string('jobs')->nullable()->after('posisi');
            $table->string('job_stream')->nullable()->after('jobs');
        });
    }

    public function down(): void
    {
        Schema::table('struktur_organisasi', function (Blueprint $table) {
            $table->dropColumn(['jobs', 'job_stream']);
        });
    }
};
