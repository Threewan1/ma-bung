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
    // Enum MySQL tidak bisa diubah lewat Schema::change(), jadi pakai raw ALTER TABLE untuk MySQL dan dicabangkan ke driverSqlite() untuk SQLite (testing only, lihat phpunit.xml) yang tidak mengenal sintaks ini.
    public function up(): void
    {
        if ($this->driverSqlite()) {
            $this->rebuildSqliteRoleColumn();
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'pelanggan', 'barber') NOT NULL DEFAULT 'pelanggan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->driverSqlite()) {
            $this->rebuildSqliteRoleColumn();
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan'");
    }

    private function driverSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }

    // SQLite cuma mensimulasikan ENUM lewat CHECK constraint yang tidak bisa diubah tanpa doctrine/dbal, jadi kolom "role" di-drop lalu ditambah ulang sebagai VARCHAR biasa (aman karena SQLite di proyek ini hanya dipakai untuk testing in-memory, dan validasi nilainya tetap dijaga di $request->validate()).
    private function rebuildSqliteRoleColumn(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('pelanggan')->after('no_hp');
        });
    }
};
