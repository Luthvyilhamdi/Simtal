<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usulan_promosis', function (Blueprint $table) {
            $table->id();

            // Karyawan
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');

            // Data karyawan saat usulan (snapshot)
            $table->string('jabatan_saat_ini')->nullable();
            $table->string('job_grade_saat_ini')->nullable();
            $table->string('person_grade_saat_ini')->nullable();
            $table->string('band_saat_ini')->nullable();
            $table->string('struktural_fungsional')->nullable();

            // Usulan promosi
            $table->string('jabatan_tujuan');
            $table->string('job_grade_promosi')->nullable();
            $table->string('person_grade_promosi')->nullable();

            // Assessment (dari rekomendasi)
            $table->foreignId('assessment_id')->nullable()->constrained('history_assessments')->nullOnDelete();
            $table->string('hasil_assessment')->nullable();      // rekomendasi_final
            $table->date('tanggal_berlaku_assessment')->nullable(); // tanggal_exp_idp
            $table->string('level_ukur')->nullable();            // tingkat_pengukuran

            // Tanggal usulan
            $table->date('tanggal_usulan')->nullable();

            // MDG check (snapshot Yes/No)
            $table->boolean('mdg_band_ok')->default(false);
            $table->boolean('mdg_jg_ok')->default(false);
            $table->boolean('mdg_pg_ok')->default(false);

            // Talent Pool
            $table->foreignId('talent_pool_id')->nullable()->constrained('talent_pools')->nullOnDelete();
            $table->string('talent_pool_periode')->nullable();
            $table->string('talent_pool_klasifikasi')->nullable();

            // KPI & Kalibrasi (JSON snapshot)
            $table->json('kpi_snapshot')->nullable();        // 3 tahun terakhir
            $table->json('kalibrasi_snapshot')->nullable();  // 2 tahun terakhir

            // Penilaian lainnya
            $table->text('absensi')->nullable();
            $table->text('kehadiran')->nullable();
            $table->text('periode_penilaian')->nullable();
            $table->text('tata_kelola')->nullable();
            $table->boolean('mc_tersedia')->default(false);
            $table->text('hasil_evaluasi')->nullable();

            // Tindak lanjut & sidang
            $table->enum('tindak_lanjut', ['sidang', 'ditolak'])->nullable();
            $table->date('tanggal_sidang')->nullable();
            $table->enum('hasil_sidang', ['lulus', 'tidak_lulus', 'tanpa_sidang'])->nullable();

            // Status alur
            $table->enum('status', ['draft', 'verif_berkas', 'sidang', 'lulus', 'tidak_lulus', 'ditolak'])
                  ->default('draft');

            // Catatan per tahap
            $table->text('catatan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulan_promosis');
    }
};