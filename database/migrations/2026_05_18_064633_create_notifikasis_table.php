<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('pesan');
            $table->enum('tipe', [
                'idp_expire',
                'masa_kerja',
                'pensiun',
                'pgs_pjs_berakhir',
                'assessment',
            ]);
            $table->enum('level', ['info', 'warning', 'danger'])->default('info');
            $table->morphs('notifiable'); // polymorphic — bisa karyawan, assessment, dll
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};