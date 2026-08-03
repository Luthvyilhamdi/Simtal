<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah nilai ENUM 'tipe' yang dipakai kode tapi belum terdaftar:
        // - eligible_grade : dari GenerateNotifikasi (karyawan eligible naik grade)
        // - so_assign      : dari StrukturOrganisasiController (assign karyawan ke posisi)
        // Tanpa ini, insert notif bertipe tersebut GAGAL ("Data truncated for column 'tipe'")
        // dan command notifikasi:generate berhenti di tengah.
        DB::statement("ALTER TABLE notifikasis MODIFY COLUMN tipe ENUM(
            'idp_expire',
            'masa_kerja',
            'pensiun',
            'pgs_pjs_berakhir',
            'assessment',
            'eligible_grade',
            'so_assign'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notifikasis MODIFY COLUMN tipe ENUM(
            'idp_expire',
            'masa_kerja',
            'pensiun',
            'pgs_pjs_berakhir',
            'assessment'
        ) NOT NULL");
    }
};
