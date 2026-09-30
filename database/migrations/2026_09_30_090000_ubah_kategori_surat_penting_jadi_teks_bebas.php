<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Kategori surat diubah dari ENUM menjadi teks bebas.
 *
 * Alasan: daftar kategori di lapangan tidak pernah selesai (berita acara, nota
 * dinas, dan sebagainya). Dengan ENUM, nilai di luar daftar ditolak MySQL
 * ("Data truncated for column 'kategori'"), sehingga kategori baru hanya bisa
 * ditambah lewat migrasi. VARCHAR membuat pengguna bisa mengetik sendiri,
 * sementara daftar bawaan tetap hidup sebagai SARAN di SuratPenting::KATEGORI.
 *
 * Slug lama ('sk_jabatan' dst.) tidak diutak-atik - nilainya tetap sama,
 * hanya tipe kolomnya yang melonggar.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE surat_pentings MODIFY COLUMN kategori VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        // Kategori ketikan sendiri tidak muat di ENUM. Kembalikan dulu ke
        // 'lainnya' supaya ALTER-nya tidak gagal / memotong data diam-diam.
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
