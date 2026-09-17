<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Enum MySQL tidak bisa diubah lewat Schema::change(), jadi pakai raw ALTER TABLE untuk menambah status "sedang_dilayani" ke alur pending -> confirmed -> sedang_dilayani -> done/cancelled, dan dicabangkan ke driverSqlite() untuk SQLite (testing only) yang tidak mengenal sintaks ini.
    public function up(): void
    {
        if ($this->driverSqlite()) {
            $this->rebuildSqliteStatusColumn();
            return;
        }

        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'confirmed', 'sedang_dilayani', 'cancelled', 'done') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->driverSqlite()) {
            $this->rebuildSqliteStatusColumn();
            return;
        }

        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'done') NOT NULL DEFAULT 'pending'");
    }

    private function driverSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }

    // Sama seperti rebuildSqliteRoleColumn() - SQLite cuma mensimulasikan ENUM lewat CHECK constraint, jadi kolom "status" di-drop dan ditambah ulang sebagai VARCHAR biasa (aman karena hanya dipakai untuk testing in-memory, validasi tetap dijaga di $request->validate()).
    private function rebuildSqliteStatusColumn(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('jam');
        });
    }
};
