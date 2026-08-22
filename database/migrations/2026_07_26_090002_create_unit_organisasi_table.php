<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Identitas permanen unit organisasi ("nomor punggung").
        // Sengaja tipis: nama/level/parent disimpan per versi di unit_organisasi_snapshot.
        Schema::create('unit_organisasi', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_organisasi');
    }
};
