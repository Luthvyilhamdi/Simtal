<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('struktur_organisasi', function (Blueprint $table) {
            $table->id();
            $table->string('direktorat');
            $table->string('kompartemen')->nullable();
            $table->string('dept')->nullable();
            $table->string('bagian')->nullable();
            $table->string('fungsional')->nullable();
            $table->string('posisi');
            $table->string('job_grade')->nullable();
            $table->integer('mc_tko')->default(0);
            $table->integer('pengisian')->default(0);
            $table->integer('deviasi')->default(0);
            $table->enum('core', ['Core', 'Non Core', ''])->default('');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struktur_organisasi');
    }
};