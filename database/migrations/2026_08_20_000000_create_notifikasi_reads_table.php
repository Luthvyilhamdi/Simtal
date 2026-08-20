<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status "sudah dibaca" per-user.
 *
 * Notifikasi bersifat global (satu baris dilihat semua admin), tetapi status
 * baca harus per-akun: satu user menandai dibaca TIDAK boleh ikut menandai
 * dibaca di akun lain. Baris di tabel ini menandakan user X sudah membaca
 * notifikasi Y.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notifikasi_id')->constrained('notifikasis')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['notifikasi_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_reads');
    }
};
