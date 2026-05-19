<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_jabatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->foreignId('jabatan_id')->constrained('jabatan')->onDelete('restrict');
            $table->foreignId('direktorat_id')->constrained('direktorat')->onDelete('restrict');
            $table->foreignId('kompartemen_id')->constrained('kompartemen')->onDelete('restrict');
            $table->foreignId('departemen_id')->constrained('departemen')->onDelete('restrict');
            $table->foreignId('job_grade_id')->constrained('job_grade')->onDelete('restrict');
            $table->foreignId('person_grade_id')->constrained('person_grade')->onDelete('restrict');
            $table->foreignId('kode_struktur_id')->constrained('kode_struktur')->onDelete('restrict');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('tipe', ['mutasi', 'promosi', 'demosi', 'onboarding'])->default('mutasi');
            $table->text('keterangan')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_jabatans');
    }
};