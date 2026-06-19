<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usulan_mutasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->enum('jenis', ['rotasi', 'mutasi'])->default('rotasi');

            // Snapshot posisi awal (saat usulan dibuat) — grade tetap, hanya untuk tampilan
            $table->string('jabatan_saat_ini')->nullable();
            $table->string('direktorat_saat_ini')->nullable();
            $table->string('kompartemen_saat_ini')->nullable();
            $table->string('departemen_saat_ini')->nullable();
            $table->string('job_grade_saat_ini')->nullable();
            $table->string('person_grade_saat_ini')->nullable();

            // Posisi tujuan (FK master) — dipilih saat buat usulan
            $table->foreignId('jabatan_tujuan_id')->nullable()->constrained('jabatan')->nullOnDelete();
            $table->foreignId('direktorat_tujuan_id')->nullable()->constrained('direktorat')->nullOnDelete();
            $table->foreignId('kompartemen_tujuan_id')->nullable()->constrained('kompartemen')->nullOnDelete();
            $table->foreignId('departemen_tujuan_id')->nullable()->constrained('departemen')->nullOnDelete();
            $table->foreignId('kode_struktur_tujuan_id')->nullable()->constrained('kode_struktur')->nullOnDelete();

            $table->text('alasan')->nullable();
            $table->date('tanggal_usulan')->nullable();

            // Alur sederhana: diajukan -> selesai (setelah SK terbit)
            $table->enum('status', ['diajukan', 'selesai'])->default('diajukan');
            $table->string('no_sk')->nullable();
            $table->date('tmt')->nullable();
            $table->boolean('sk_diproses')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulan_mutasis');
    }
};