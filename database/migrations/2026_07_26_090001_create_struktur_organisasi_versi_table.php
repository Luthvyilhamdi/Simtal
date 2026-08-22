<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('struktur_organisasi_versi', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sk', 100)->unique();
            $table->date('tanggal_sk');
            $table->date('tanggal_mulai_berlaku')->unique();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struktur_organisasi_versi');
    }
};
