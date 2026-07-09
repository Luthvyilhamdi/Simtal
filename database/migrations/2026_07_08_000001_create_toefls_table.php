<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toefls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('skor');
            $table->string('jenis')->nullable();      // ITP / iBT / PBT
            $table->date('tanggal_tes')->nullable();
            $table->string('lembaga')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('link_file', 2048)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toefls');
    }
};
