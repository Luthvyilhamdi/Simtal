<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name');           // nama user saat aksi (snapshot)
            $table->string('aksi');                // tambah, edit, hapus, import, export, login
            $table->string('modul');               // karyawan, history_jabatan, assessment, dll
            $table->string('target')->nullable();  // nama data yang diubah
            $table->text('keterangan')->nullable(); // detail tambahan
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};