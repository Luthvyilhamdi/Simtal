<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tautkan catatan aktivitas ke record yang disentuhnya. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('subjek_type')->nullable()->after('target');
            $table->unsignedBigInteger('subjek_id')->nullable()->after('subjek_type');

            // Index untuk menarik riwayat satu record.
            $table->index(['subjek_type', 'subjek_id'], 'activity_logs_subjek_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_subjek_index');
            $table->dropColumn(['subjek_type', 'subjek_id']);
        });
    }
};
