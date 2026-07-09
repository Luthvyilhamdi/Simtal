<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('toefls', function (Blueprint $table) {
            // Desimal agar mendukung skor IELTS (band 0–9, kelipatan 0.5) & TOEFL (bulat).
            $table->decimal('skor', 5, 1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('toefls', function (Blueprint $table) {
            $table->unsignedSmallInteger('skor')->change();
        });
    }
};
