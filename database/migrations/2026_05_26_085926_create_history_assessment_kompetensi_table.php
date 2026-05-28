<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_assessment_kompetensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->date('tanggal_assessment');
            $table->string('periode')->nullable(); // cth: 2024, Q1-2024

            // ===== COMPETENCIES =====
            $table->unsignedTinyInteger('digital_leadership')->nullable();
            $table->unsignedTinyInteger('global_business_savvy')->nullable();
            $table->unsignedTinyInteger('customer_focus')->nullable();
            $table->unsignedTinyInteger('building_strategic_partnership')->nullable();
            $table->unsignedTinyInteger('strategic_orientation')->nullable();
            $table->unsignedTinyInteger('driving_execution')->nullable();
            $table->unsignedTinyInteger('driving_innovation')->nullable();
            $table->unsignedTinyInteger('developing_organizational_capabilities')->nullable();
            $table->unsignedTinyInteger('leading_change')->nullable();
            $table->unsignedTinyInteger('managing_diversity')->nullable();

            // ===== PROFESSIONAL QUALIFICATION =====
            $table->unsignedTinyInteger('financial')->nullable();
            $table->unsignedTinyInteger('commercial')->nullable();
            $table->unsignedTinyInteger('people')->nullable();
            $table->unsignedTinyInteger('operation')->nullable();
            $table->unsignedTinyInteger('technology')->nullable();

            // ===== HASIL =====
            $table->unsignedTinyInteger('total_competency_under')->default(0);
            $table->unsignedTinyInteger('total_qualification_under')->default(0);
            $table->enum('kesimpulan', ['QUALIFIED', 'NOT QUALIFIED'])->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_assessment_kompetensi');
    }
};