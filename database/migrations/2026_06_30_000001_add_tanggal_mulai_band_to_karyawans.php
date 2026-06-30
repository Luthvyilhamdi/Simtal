<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            // Tanggal masuk Band saat ini. Diisi & dijaga OTOMATIS dari Riwayat
            // Jabatan (lihat HistoryJabatan::syncTanggalMulaiBand). Tidak diinput
            // manual. Dipakai untuk menghitung MDG-Band (kelayakan promosi band).
            $table->date('tanggal_mulai_band')->nullable()->after('tanggal_mulai_jg');
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn('tanggal_mulai_band');
        });
    }
};
