<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_pentings', function (Blueprint $table) {
            $table->unsignedBigInteger('karyawan_id')->nullable()->change();
            $table->string('tipe')->default('personal')->after('karyawan_id'); // personal | umum
        });
    }

    public function down(): void
    {
        Schema::table('surat_pentings', function (Blueprint $table) {
            $table->unsignedBigInteger('karyawan_id')->nullable(false)->change();
            $table->dropColumn('tipe');
        });
    }
};