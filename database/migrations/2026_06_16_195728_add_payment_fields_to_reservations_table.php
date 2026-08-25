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

            // Menyimpan nama file bukti pembayaran yang diupload pelanggan
            $table->string('payment_proof')->nullable();

            // Menyimpan status pembayaran pelanggan
            // unpaid = belum bayar
            // waiting_verification = menunggu verifikasi admin
            // paid = pembayaran diterima
            // rejected = pembayaran ditolak
            $table->enum('payment_status', [
                'unpaid',
                'waiting_verification',
                'paid',
                'rejected'
            ])->default('unpaid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {

            // Menghapus kolom pembayaran jika migration di-rollback
            $table->dropColumn([
                'payment_proof',
                'payment_status'
            ]);
        });
    }
};
