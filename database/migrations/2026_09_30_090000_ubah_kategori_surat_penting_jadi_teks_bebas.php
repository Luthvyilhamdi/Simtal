<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Kategori surat: ENUM -> teks bebas (VARCHAR). */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE surat_pentings MODIFY COLUMN kategori VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        // Kategori ketikan sendiri tak muat di ENUM; kembalikan ke 'lainnya'.
        DB::statement("UPDATE surat_pentings SET kategori = 'lainnya' WHERE kategori NOT IN (
            'sk_jabatan','sk_promosi','sk_mutasi','sk_pensiun',
            'surat_tugas','surat_peringatan','kontrak','sertifikat',
            'pedoman','prosedur','kebijakan','lainnya'
        )");

        DB::statement("ALTER TABLE surat_pentings MODIFY COLUMN kategori ENUM(
            'sk_jabatan','sk_promosi','sk_mutasi','sk_pensiun',
            'surat_tugas','surat_peringatan','kontrak','sertifikat',
            'pedoman','prosedur','kebijakan','lainnya'
        ) NOT NULL");
    }
};
