<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_pejabats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->foreignId('history_jabatan_id')->nullable()->constrained('history_jabatans')->onDelete('set null');

            // Snapshot data saat menjabat
            $table->string('jabatan');           // nama jabatan (SVP, VP, SPM, PM)
            $table->string('jabatan_saat_ini')->nullable(); // jabatan lengkap
            $table->string('direktorat')->nullable();
            $table->string('kompartemen')->nullable();
            $table->string('departemen')->nullable();
            $table->string('job_grade')->nullable();
            $table->string('person_grade')->nullable();
            $table->string('no_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable(); // null = masih menjabat
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_pejabats');
    }
};