<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_organisasi_snapshot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_organisasi_id')->constrained('unit_organisasi')->cascadeOnDelete();
            $table->foreignId('struktur_organisasi_versi_id')->constrained('struktur_organisasi_versi')->cascadeOnDelete();
            $table->string('nama_unit');
            $table->enum('level', ['direktorat', 'kompartemen', 'departemen', 'bagian', 'fungsional']);
            $table->foreignId('parent_unit_organisasi_id')->nullable()->constrained('unit_organisasi')->nullOnDelete();
            // Formasi milik unit ini sendiri di level ini (bukan akumulasi turunan)
            $table->unsignedInteger('mc_formasi')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['unit_organisasi_id', 'struktur_organisasi_versi_id'], 'unit_organisasi_snapshot_unit_versi_unique');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_organisasi_snapshot');
    }
};
