<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // "Kelola Antrian" sudah dihapus, digantikan alur status reservasi - nomor_antrian tetap dipertahankan karena masih dipakai aktif.
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('status_antrian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->enum('status_antrian', ['menunggu', 'diproses', 'selesai'])->default('menunggu');
        });
    }
};
