<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
            $table->string('nama');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->date('tanggal_masuk');
            $table->string('foto')->nullable();
            $table->enum('status', ['aktif', 'tidak aktif'])->default('aktif');
            $table->foreignId('direktorat_id')->constrained('direktorat')->onDelete('restrict');
            $table->foreignId('kompartemen_id')->constrained('kompartemen')->onDelete('restrict');
            $table->foreignId('departemen_id')->constrained('departemen')->onDelete('restrict');
            $table->foreignId('job_grade_id')->constrained('job_grade')->onDelete('restrict');
            $table->foreignId('person_grade_id')->constrained('person_grade')->onDelete('restrict');
            $table->foreignId('jabatan_id')->constrained('jabatan')->onDelete('restrict');
            $table->foreignId('kode_struktur_id')->constrained('kode_struktur')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
