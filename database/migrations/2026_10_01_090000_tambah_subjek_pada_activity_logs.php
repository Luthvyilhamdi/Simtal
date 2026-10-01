<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tautkan catatan aktivitas ke RECORD yang disentuhnya.
 *
 * Sebelumnya satu-satunya petunjuk adalah kolom `target` yang berisi teks bebas
 * (biasanya nama karyawan). Itu cukup untuk daftar Log Aktivitas, tapi tidak
 * bisa menjawab "riwayat perubahan record INI": nama bisa kembar, nama bisa
 * diperbaiki, dan dari sekian baris milik satu karyawan tidak ketahuan baris
 * mana yang disunting.
 *
 * Dua kolom ini MENAMBAH dan boleh kosong. Baris lama dibiarkan null dan
 * halaman Log Aktivitas yang ada tidak berubah perilakunya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('subjek_type')->nullable()->after('target');
            $table->unsignedBigInteger('subjek_id')->nullable()->after('subjek_type');

            // Dipakai untuk menarik riwayat satu record; tanpa index, tiap
            // pembukaan panel akan memindai seluruh tabel log.
            $table->index(['subjek_type', 'subjek_id'], 'activity_logs_subjek_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_subjek_index');
            $table->dropColumn(['subjek_type', 'subjek_id']);
        });
    }
};
