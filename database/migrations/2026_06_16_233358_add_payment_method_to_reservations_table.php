<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * menambahkan kolom payment method.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {

            // Metode pembayaran: online (bayar sekarang) atau cod (bayar di tempat).
            $table->enum('payment_method', ['online', 'cod'])
                ->default('cod')
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {

            // Hapus kolom saat rollback
            $table->dropColumn('payment_method');
        });
    }
};
