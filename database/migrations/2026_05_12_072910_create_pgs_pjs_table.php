<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pgs_pjs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->enum('tipe', ['pgs', 'pjs']);
            $table->string('jabatan_pgs_pjs');        // jabatan yang diduduki sementara
            $table->string('direktorat')->nullable();
            $table->string('departemen')->nullable();
            $table->string('no_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir');
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true); // true = sedang berlangsung
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pgs_pjs');
    }
};