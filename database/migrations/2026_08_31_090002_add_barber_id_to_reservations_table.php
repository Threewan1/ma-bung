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
            // Barber yang dipilih pelanggan saat reservasi. Nullable +
            // nullOnDelete supaya reservasi lama/riwayat tidak ikut
            // terhapus kalau data barber-nya suatu saat dihapus.
            $table->foreignId('barber_id')->nullable()->after('service_id')
                ->constrained('barbers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('barber_id');
        });
    }
};
