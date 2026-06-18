<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usulan_promosis', function (Blueprint $table) {
            if (!Schema::hasColumn('usulan_promosis', 'direktorat_tujuan_id')) {
                $table->foreignId('direktorat_tujuan_id')->nullable()->after('person_grade_promosi')
                      ->constrained('direktorat')->nullOnDelete();
            }
            if (!Schema::hasColumn('usulan_promosis', 'kompartemen_tujuan_id')) {
                $table->foreignId('kompartemen_tujuan_id')->nullable()->after('direktorat_tujuan_id')
                      ->constrained('kompartemen')->nullOnDelete();
            }
            if (!Schema::hasColumn('usulan_promosis', 'departemen_tujuan_id')) {
                $table->foreignId('departemen_tujuan_id')->nullable()->after('kompartemen_tujuan_id')
                      ->constrained('departemen')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('usulan_promosis', function (Blueprint $table) {
            foreach (['direktorat_tujuan_id', 'kompartemen_tujuan_id', 'departemen_tujuan_id'] as $col) {
                if (Schema::hasColumn('usulan_promosis', $col)) {
                    $table->dropConstrainedForeignId($col);
                }
            }
        });
    }
};