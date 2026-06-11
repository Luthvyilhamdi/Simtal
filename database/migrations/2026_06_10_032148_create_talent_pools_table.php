<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_pools', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('periode'); // tahun, misal 2025
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->enum('klasifikasi', ['longlist', 'shortlist']);
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Satu karyawan hanya bisa masuk 1x per periode
            $table->unique(['karyawan_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_pools');
    }
};