<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_pentings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->string('judul');
            $table->string('nomor_surat')->nullable();
            $table->enum('kategori', [
                'sk_jabatan',
                'sk_promosi',
                'sk_mutasi',
                'sk_pensiun',
                'surat_tugas',
                'surat_peringatan',
                'kontrak',
                'sertifikat',
                'lainnya',
            ]);
            $table->date('tanggal_surat');
            $table->date('tanggal_exp')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_size')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_pentings');
    }
};