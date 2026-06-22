<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usulan_mutasis', function (Blueprint $table) {
            if (!Schema::hasColumn('usulan_mutasis', 'jabatan_tujuan')) {
                $table->string('jabatan_tujuan')->nullable()->after('person_grade_saat_ini');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usulan_mutasis', function (Blueprint $table) {
            if (Schema::hasColumn('usulan_mutasis', 'jabatan_tujuan')) {
                $table->dropColumn('jabatan_tujuan');
            }
        });
    }
};