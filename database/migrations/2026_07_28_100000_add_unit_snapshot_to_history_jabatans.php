<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom snapshot teks: merekam NAMA unit apa adanya saat itu.
        // History = potret masa lalu; nama direktorat/kompartemen/departemen
        // dulu bisa beda dan sudah tak ada di master. Disimpan sebagai teks
        // agar tidak mengotori master (yang hanya berisi struktur berlaku kini).
        Schema::table('history_jabatans', function (Blueprint $table) {
            $table->string('direktorat_nama')->nullable()->after('direktorat_id');
            $table->string('kompartemen_nama')->nullable()->after('kompartemen_id');
            $table->string('departemen_nama')->nullable()->after('departemen_id');
        });

        // Backfill dari FK yang sudah ada agar data lama tetap tampil.
        DB::statement('UPDATE history_jabatans h JOIN direktorat d ON h.direktorat_id = d.id SET h.direktorat_nama = d.nama_direktorat');
        DB::statement('UPDATE history_jabatans h JOIN kompartemen k ON h.kompartemen_id = k.id SET h.kompartemen_nama = k.nama_kompartemen');
        DB::statement('UPDATE history_jabatans h JOIN departemen dp ON h.departemen_id = dp.id SET h.departemen_nama = dp.nama_departemen');

        // FK unit dibuat nullable: nama historis yang tak ada di master
        // disimpan tanpa FK (id = null).
        Schema::table('history_jabatans', function (Blueprint $table) {
            $table->unsignedBigInteger('direktorat_id')->nullable()->change();
            $table->unsignedBigInteger('kompartemen_id')->nullable()->change();
            $table->unsignedBigInteger('departemen_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('history_jabatans', function (Blueprint $table) {
            $table->dropColumn(['direktorat_nama', 'kompartemen_nama', 'departemen_nama']);
        });
    }
};
