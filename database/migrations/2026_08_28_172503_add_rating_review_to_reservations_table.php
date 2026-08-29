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

            // Rating bintang 1-5 dari pelanggan untuk reservasi yang sudah selesai
            $table->unsignedTinyInteger('rating')->nullable()->after('payment_status');

            // Ulasan/komentar opsional dari pelanggan
            $table->text('review')->nullable()->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['rating', 'review']);
        });
    }
};
