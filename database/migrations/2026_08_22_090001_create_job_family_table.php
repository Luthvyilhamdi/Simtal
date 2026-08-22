<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master data "Rumpun Jabatan" (Job Family) — 15 baris tetap, diisi LANGSUNG di migration
 * ini (bukan Seeder terpisah) supaya urutan eksekusi terjamin: migration
 * 2026_08_22_090002 (backfill kompetensi_teknis.rumpun_asal -> job_family_id) butuh
 * ke-15 baris ini SUDAH ADA saat migration itu jalan. Kalau dipisah ke Seeder class,
 * urutan konvensional Laravel (migrate SEMUA dulu, baru db:seed) akan bikin migration
 * backfill jalan SEBELUM job_family terisi — gagal. Data referensi kecil & tetap spt ini
 * (bukan data transaksional bulk spt StrukturOrganisasiSeeder/StrukturOrganisasiMei2026Seeder
 * yg memang cocok jadi Seeder terpisah) lazim ditaruh langsung di migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_family', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        // Urutan & penulisan (termasuk koma dan "&") HARUS PERSIS — sudah dikonfirmasi.
        $namaList = [
            'Commercial',
            'Engineering & Construction',
            'Maintenance',
            'Operations',
            'R&D And Product Management',
            'Supply Chain',
            'Sustainability & HSE',
            'Business Process & Innovation',
            'Corporate Affair',
            'Corporate Strategy & Business Development',
            'Finance & Accounting',
            'Governance, Risk & Compliance',
            'Human Capital & General Affair',
            'IT & Data Analytics',
            'Legal',
        ];

        $now = now();
        DB::table('job_family')->insert(array_map(
            fn ($nama) => ['nama' => $nama, 'created_at' => $now, 'updated_at' => $now],
            $namaList
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('job_family');
    }
};
