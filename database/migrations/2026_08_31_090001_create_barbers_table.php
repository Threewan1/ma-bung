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
        Schema::create('barbers', function (Blueprint $table) {
            $table->id();

            // Akun login barber (role 'barber') yang terhubung ke data ini
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('nama');

            // Foto profil barber - opsional
            $table->string('foto')->nullable();

            // Barber non-aktif tidak muncul sebagai pilihan saat pelanggan reservasi
            $table->boolean('status_aktif')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barbers');
    }
};
