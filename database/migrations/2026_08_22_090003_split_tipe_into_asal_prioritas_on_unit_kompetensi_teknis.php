<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deteksi primary/secondary via border cell Excel TERBUKTI TIDAK RELIABLE (beda file beda
 * orang bikin, border style tidak konsisten) — kolom `tipe` (enum primary/secondary/generic)
 * dipecah jadi 2 kolom independen:
 * - `asal` (native/generic): dari struktur kolom header Excel (ada suffix "- JF X" atau
 *   tidak) — INI TETAP RELIABLE, bukan bagian yg bermasalah.
 * - `prioritas` (primary/secondary): SEKARANG dipilih manual oleh user di step "Pilih
 *   Primary" (alur import web), BUKAN dideteksi otomatis dari border lagi.
 *
 * Backfill 268 baris existing dari `tipe` lama:
 *   tipe='primary'   -> asal='native',  prioritas='primary'
 *   tipe='secondary' -> asal='native',  prioritas='secondary'
 *   tipe='generic'   -> asal='generic', prioritas='secondary' (default AMAN krn border lama
 *                        tidak bisa dipercaya, BUKAN klaim bahwa semua generic itu memang
 *                        secondary — revisi manual by user kalau ada yg ternyata primary
 *                        adalah kebutuhan terpisah, TIDAK dikerjakan di migration ini).
 *
 * MODIFY ... NOT NULL pakai DB::statement() raw (bukan Blueprint::change()) sengaja utk
 * hindari ketergantungan doctrine/dbal thd tipe kolom ENUM — sama pertimbangannya dgn
 * migration job_family_id sebelumnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_kompetensi_teknis', function (Blueprint $table) {
            $table->enum('asal', ['native', 'generic'])->nullable()->after('tipe');
            $table->enum('prioritas', ['primary', 'secondary'])->nullable()->after('asal');
        });

        DB::table('unit_kompetensi_teknis')->where('tipe', 'primary')
            ->update(['asal' => 'native', 'prioritas' => 'primary']);
        DB::table('unit_kompetensi_teknis')->where('tipe', 'secondary')
            ->update(['asal' => 'native', 'prioritas' => 'secondary']);
        DB::table('unit_kompetensi_teknis')->where('tipe', 'generic')
            ->update(['asal' => 'generic', 'prioritas' => 'secondary']);

        $remaining = DB::table('unit_kompetensi_teknis')
            ->where(fn ($q) => $q->whereNull('asal')->orWhereNull('prioritas'))
            ->count();

        if ($remaining > 0) {
            throw new \RuntimeException(
                "Migrasi dibatalkan: masih ada {$remaining} baris unit_kompetensi_teknis dgn asal/prioritas NULL "
                . 'setelah backfill (kemungkinan nilai tipe lama di luar primary/secondary/generic).'
            );
        }

        DB::statement("ALTER TABLE unit_kompetensi_teknis MODIFY asal ENUM('native','generic') NOT NULL");
        DB::statement("ALTER TABLE unit_kompetensi_teknis MODIFY prioritas ENUM('primary','secondary') NOT NULL");

        Schema::table('unit_kompetensi_teknis', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
    }

    public function down(): void
    {
        Schema::table('unit_kompetensi_teknis', function (Blueprint $table) {
            $table->enum('tipe', ['primary', 'secondary', 'generic'])->nullable()->after('prioritas');
        });

        DB::table('unit_kompetensi_teknis')->where('asal', 'native')->where('prioritas', 'primary')
            ->update(['tipe' => 'primary']);
        DB::table('unit_kompetensi_teknis')->where('asal', 'native')->where('prioritas', 'secondary')
            ->update(['tipe' => 'secondary']);
        DB::table('unit_kompetensi_teknis')->where('asal', 'generic')
            ->update(['tipe' => 'generic']);

        DB::statement("ALTER TABLE unit_kompetensi_teknis MODIFY tipe ENUM('primary','secondary','generic') NOT NULL");

        Schema::table('unit_kompetensi_teknis', function (Blueprint $table) {
            $table->dropColumn(['asal', 'prioritas']);
        });
    }
};
