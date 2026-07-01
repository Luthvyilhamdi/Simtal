<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->string('no_hp', 30)->nullable()->after('tanggal_masuk');
            $table->string('email')->nullable()->after('no_hp');
            $table->string('jenjang_pendidikan', 20)->nullable()->after('email');
            $table->string('jurusan')->nullable()->after('jenjang_pendidikan');
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn(['no_hp', 'email', 'jenjang_pendidikan', 'jurusan']);
        });
    }
};
