<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kolom "role" adalah ENUM MySQL - Schema builder Laravel tidak bisa
     * mengubah daftar nilai enum lewat ->change(), jadi pakai raw SQL
     * ALTER TABLE langsung.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'pelanggan', 'barber') NOT NULL DEFAULT 'pelanggan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan'");
    }
};
