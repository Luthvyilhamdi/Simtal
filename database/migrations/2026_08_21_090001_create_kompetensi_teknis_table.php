<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kompetensi_teknis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kompetensi')->unique();
            $table->string('rumpun_asal')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kompetensi_teknis');
    }
};
