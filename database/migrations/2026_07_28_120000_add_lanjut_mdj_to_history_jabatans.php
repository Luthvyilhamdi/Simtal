<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penanda kelangsungan Masa Dinas Jabatan (MDJ).
        // true  = jabatan ini SAMA dengan sebelumnya (mis. hanya ganti nama
        //         karena perubahan SO) → MDJ dilanjutkan (tidak reset).
        // false = jabatan baru (promosi / pindah band / beda ranah) → MDJ
        //         mulai periode baru. Default false (entri = jabatan baru).
        Schema::table('history_jabatans', function (Blueprint $table) {
            $table->boolean('lanjut_mdj')->default(false)->after('is_current');
        });
    }

    public function down(): void
    {
        Schema::table('history_jabatans', function (Blueprint $table) {
            $table->dropColumn('lanjut_mdj');
        });
    }
};
