<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kalibrasi_karyawans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->unsignedSmallInteger('tahun');
            $table->enum('nilai', ['FEE', 'EXE', 'MEE', 'BEE', 'FBE']);
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['karyawan_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalibrasi_karyawans');
    }
};