<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE surat_pentings MODIFY COLUMN kategori ENUM(
            'sk_jabatan','sk_promosi','sk_mutasi','sk_pensiun',
            'surat_tugas','surat_peringatan','kontrak','sertifikat',
            'pedoman','prosedur','kebijakan','lainnya'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE surat_pentings MODIFY COLUMN kategori ENUM(
            'sk_jabatan','sk_promosi','sk_mutasi','sk_pensiun',
            'surat_tugas','surat_peringatan','kontrak','sertifikat','lainnya'
        ) NOT NULL");
    }
};