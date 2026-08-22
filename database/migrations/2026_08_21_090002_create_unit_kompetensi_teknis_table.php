<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_kompetensi_teknis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_organisasi_id')->constrained('unit_organisasi')->cascadeOnDelete();
            $table->foreignId('struktur_organisasi_versi_id')->constrained('struktur_organisasi_versi')->cascadeOnDelete();
            $table->string('jenjang_jabatan');
            $table->unsignedTinyInteger('urutan_jenjang');
            $table->string('grade')->nullable();
            $table->string('nama_jobs');
            $table->boolean('managerial');
            $table->foreignId('kompetensi_teknis_id')->constrained('kompetensi_teknis')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->enum('tipe', ['primary', 'secondary', 'generic']);
            $table->timestamps();

            $table->unique(
                ['unit_organisasi_id', 'struktur_organisasi_versi_id', 'jenjang_jabatan', 'kompetensi_teknis_id'],
                'unit_kompetensi_teknis_unit_versi_jenjang_kompetensi_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_kompetensi_teknis');
    }
};
