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
        Schema::table('reservations', function (Blueprint $table) {
            // Foto before/after - diisi admin untuk reservasi yang sudah selesai,
            // dipakai untuk galeri "Transformasi Kamu" di dashboard pelanggan.
            $table->string('foto_before')->nullable()->after('review');
            $table->string('foto_after')->nullable()->after('foto_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['foto_before', 'foto_after']);
        });
    }
};
