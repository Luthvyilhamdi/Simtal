<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi kompetensi_teknis.rumpun_asal (varchar bebas ketik) -> job_family_id (FK).
 *
 * Backfill data existing (23 baris saat migration ini ditulis): rumpun_asal = "JF Human
 * Capital" adalah PENGECUALIAN yg sudah dikonfirmasi manual -> di-map ke job_family
 * "Human Capital & General Affair" (BUKAN exact match nama). 7 nilai rumpun_asal generic
 * lainnya (Finance & Accounting, Business Process & Innovation, Governance Risk &
 * Compliance, Legal, Corporate Affair, Sustainability & HSE, Corporate Strategy & Business
 * Development) SUDAH DIVERIFIKASI exact match (case-insensitive) ke job_family.nama
 * sebelum migration ini ditulis — up() tetap throw exception kalau ternyata ada baris yg
 * tidak ketemu mapping-nya (jaring pengaman, bukan diasumsikan aman buta).
 *
 * Kolom job_family_id ditambah NULLABLE dulu (perlu diisi backfill dulu), verifikasi 0
 * NULL tersisa, BARU diubah NOT NULL & rumpun_asal di-drop — supaya migration gagal dgn
 * jelas (bukan diam2 menyisakan data rusak) kalau ternyata ada baris yg tidak ke-cover.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kompetensi_teknis', function (Blueprint $table) {
            $table->foreignId('job_family_id')->nullable()->after('rumpun_asal')
                ->constrained('job_family')->restrictOnDelete();
        });

        // Pengecualian yg sudah dikonfirmasi: "JF Human Capital" -> "Human Capital & General Affair".
        $hcId = DB::table('job_family')->where('nama', 'Human Capital & General Affair')->value('id');

        if ($hcId === null) {
            throw new \RuntimeException('Migrasi dibatalkan: job_family "Human Capital & General Affair" tidak ditemukan.');
        }

        DB::table('kompetensi_teknis')
            ->where('rumpun_asal', 'JF Human Capital')
            ->update(['job_family_id' => $hcId]);

        // Sisanya: exact match (case-insensitive, trim) rumpun_asal -> job_family.nama.
        $sisa = DB::table('kompetensi_teknis')->whereNull('job_family_id')->select('id', 'rumpun_asal')->get();

        foreach ($sisa as $row) {
            $jobFamilyId = DB::table('job_family')
                ->whereRaw('LOWER(nama) = ?', [mb_strtolower(trim((string) $row->rumpun_asal))])
                ->value('id');

            if ($jobFamilyId === null) {
                throw new \RuntimeException(
                    "Migrasi dibatalkan: rumpun_asal \"{$row->rumpun_asal}\" (kompetensi_teknis.id={$row->id}) "
                    . 'tidak ketemu exact match di job_family. Periksa data sebelum migrate lagi.'
                );
            }

            DB::table('kompetensi_teknis')->where('id', $row->id)->update(['job_family_id' => $jobFamilyId]);
        }

        $remaining = DB::table('kompetensi_teknis')->whereNull('job_family_id')->count();

        if ($remaining > 0) {
            throw new \RuntimeException("Migrasi dibatalkan: masih ada {$remaining} baris kompetensi_teknis dgn job_family_id NULL setelah backfill.");
        }

        Schema::table('kompetensi_teknis', function (Blueprint $table) {
            $table->foreignId('job_family_id')->nullable(false)->change();
            $table->dropColumn('rumpun_asal');
        });
    }

    public function down(): void
    {
        Schema::table('kompetensi_teknis', function (Blueprint $table) {
            $table->string('rumpun_asal')->nullable()->after('nama_kompetensi');
        });

        DB::table('kompetensi_teknis')->update([
            'rumpun_asal' => DB::raw('(SELECT nama FROM job_family WHERE job_family.id = kompetensi_teknis.job_family_id)'),
        ]);

        Schema::table('kompetensi_teknis', function (Blueprint $table) {
            $table->dropForeign(['job_family_id']);
            $table->dropColumn('job_family_id');
        });
    }
};
