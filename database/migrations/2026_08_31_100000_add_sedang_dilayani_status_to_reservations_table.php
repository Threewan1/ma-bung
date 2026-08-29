<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kolom "status" adalah ENUM MySQL - Schema builder Laravel tidak
     * bisa mengubah daftar nilai enum lewat ->change(), jadi pakai raw
     * SQL ALTER langsung. Urutan alur sekarang: pending -> confirmed ->
     * sedang_dilayani -> done (atau cancelled dari mana saja).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'confirmed', 'sedang_dilayani', 'cancelled', 'done') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'done') NOT NULL DEFAULT 'pending'");
    }
};
