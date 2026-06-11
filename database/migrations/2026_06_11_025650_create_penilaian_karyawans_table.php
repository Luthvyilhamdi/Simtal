<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_karyawans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->unsignedSmallInteger('tahun');
            $table->enum('periode', ['triwulan_1', 'triwulan_2', 'triwulan_3', 'triwulan_4', 'tahunan']);
            $table->enum('tipe', ['KPI', '360']);
            $table->string('judul');
            $table->decimal('nilai', 5, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_karyawans');
    }
};