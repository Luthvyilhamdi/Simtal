<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_organisasi_transisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('struktur_organisasi_versi_id')->constrained('struktur_organisasi_versi')->cascadeOnDelete();
            $table->enum('jenis_transisi', ['rename', 'pecah', 'gabung', 'pindah_induk', 'bubar', 'baru']);
            $table->foreignId('unit_asal_id')->nullable()->constrained('unit_organisasi')->nullOnDelete();
            $table->foreignId('unit_baru_id')->nullable()->constrained('unit_organisasi')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_organisasi_transisi');
    }
};
