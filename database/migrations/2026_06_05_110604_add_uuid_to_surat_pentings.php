<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_pentings', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        // Isi uuid untuk data yang sudah ada
        \App\Models\SuratPenting::whereNull('uuid')->each(function($s) {
            $s->update(['uuid' => \Illuminate\Support\Str::uuid()]);
        });
    }

    public function down(): void
    {
        Schema::table('surat_pentings', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
