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
        Schema::table('usulan_promosis', function (Blueprint $table) {
            $table->string('departemen_saat_ini')->nullable()->after('struktural_fungsional');
            $table->string('kompartemen_saat_ini')->nullable()->after('departemen_saat_ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usulan_promosis', function (Blueprint $table) {
            $table->dropColumn(['departemen_saat_ini', 'kompartemen_saat_ini']);
        });
    }
};
