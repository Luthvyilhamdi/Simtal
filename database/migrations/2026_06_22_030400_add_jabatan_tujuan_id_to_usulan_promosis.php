<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usulan_promosis', function (Blueprint $table) {
            if (!Schema::hasColumn('usulan_promosis', 'jabatan_tujuan_id')) {
                $table->foreignId('jabatan_tujuan_id')->nullable()->after('jabatan_tujuan')
                    ->constrained('jabatan')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('usulan_promosis', function (Blueprint $table) {
            if (Schema::hasColumn('usulan_promosis', 'jabatan_tujuan_id')) {
                $table->dropConstrainedForeignId('jabatan_tujuan_id');
            }
        });
    }
};