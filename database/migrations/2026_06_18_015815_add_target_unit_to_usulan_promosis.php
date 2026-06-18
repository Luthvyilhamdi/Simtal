<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usulan_promosis', function (Blueprint $table) {
            if (!Schema::hasColumn('usulan_promosis', 'no_sk')) {
                $table->string('no_sk')->nullable()->after('catatan');
            }
            if (!Schema::hasColumn('usulan_promosis', 'tmt')) {
                $table->date('tmt')->nullable()->after('no_sk');
            }
            if (!Schema::hasColumn('usulan_promosis', 'sk_diproses')) {
                $table->boolean('sk_diproses')->default(false)->after('tmt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usulan_promosis', function (Blueprint $table) {
            foreach (['no_sk', 'tmt', 'sk_diproses'] as $col) {
                if (Schema::hasColumn('usulan_promosis', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};