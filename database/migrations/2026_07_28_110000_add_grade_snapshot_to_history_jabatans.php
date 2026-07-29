<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Snapshot teks Job Grade & Person Grade — sama seperti unit: merekam
        // nilai grade apa adanya saat itu. Skala grade lama yang tak ada di
        // master bisa disimpan tanpa mengotori master.
        Schema::table('history_jabatans', function (Blueprint $table) {
            $table->string('job_grade_nama', 50)->nullable()->after('job_grade_id');
            $table->string('person_grade_nama', 50)->nullable()->after('person_grade_id');
        });

        DB::statement('UPDATE history_jabatans h JOIN job_grade j ON h.job_grade_id = j.id SET h.job_grade_nama = j.job_grade');
        DB::statement('UPDATE history_jabatans h JOIN person_grade p ON h.person_grade_id = p.id SET h.person_grade_nama = p.person_grade');

        Schema::table('history_jabatans', function (Blueprint $table) {
            $table->unsignedBigInteger('job_grade_id')->nullable()->change();
            $table->unsignedBigInteger('person_grade_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('history_jabatans', function (Blueprint $table) {
            $table->dropColumn(['job_grade_nama', 'person_grade_nama']);
        });
    }
};
