<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');

            // Data snapshot dari karyawan (terintegrasi, ikut update)
            $table->string('jabatan_saat_ini')->nullable();
            $table->string('job_grade')->nullable();
            $table->string('person_grade')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->integer('usia')->nullable();

            // Data assessment
            $table->string('job_stream')->nullable();
            $table->date('tanggal_pelaksanaan');
            $table->string('tingkat_pengukuran')->nullable();

            // Rekomendasi (dalam persen)
            $table->decimal('rekomendasi_inti', 5, 2)->nullable();
            $table->decimal('rekomendasi_primer', 5, 2)->nullable();
            $table->decimal('rekomendasi_skunder', 5, 2)->nullable();

            // Rekomendasi Final
            $table->enum('rekomendasi_final', ['ready', 'ready_with_development', 'not_ready'])->nullable();

            // Tanggal exp IDP (2 tahun dari tanggal assessment)
            $table->date('tanggal_exp_idp')->nullable();

            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_assessments');
    }
};