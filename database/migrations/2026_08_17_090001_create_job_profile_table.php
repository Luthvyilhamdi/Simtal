<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_organisasi_id')->constrained('unit_organisasi')->cascadeOnDelete();
            $table->foreignId('struktur_organisasi_versi_id')->constrained('struktur_organisasi_versi')->cascadeOnDelete();
            $table->string('nama_jabatan');
            $table->string('file_path');
            $table->string('file_original_name');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['unit_organisasi_id', 'struktur_organisasi_versi_id', 'nama_jabatan'], 'job_profile_unit_versi_jabatan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_profile');
    }
};
