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

            // Jenis pembayaran online (qris/bca/dana/gopay/shopeepay), NULL kalau COD.
            $table->string('payment_channel')
                ->nullable()
                ->after('payment_method');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {

            // Menghapus kolom payment_channel
            // jika migration di-rollback
            $table->dropColumn('payment_channel');

        });
    }
};
