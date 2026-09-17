<?php

use App\Models\Reservation;
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

            // Snapshot harga layanan saat reservasi dibuat, biar laporan pendapatan lama tidak ikut berubah kalau harga layanan diedit belakangan.
            $table->decimal('harga_snapshot', 10, 2)->nullable()->after('service_id');

            // Kapan payment_status berubah jadi "paid", menggantikan kolom "tanggal" sebagai acuan bulan di laporan pendapatan.
            $table->timestamp('payment_confirmed_at')->nullable()->after('payment_status');

        });

        // Backfill data lama - pakai Eloquent (bukan raw JOIN UPDATE) biar tetap portable di SQLite test suite.
        // $timestamps = false sengaja dipasang biar updated_at tidak ikut berubah (penting karena backfill payment_confirmed_at di bawah justru mengambil nilai dari updated_at).

        // Isi dengan harga layanan yang berlaku saat ini, pendekatan terbaik yang tersedia karena tidak ada riwayat harga.
        Reservation::whereNull('harga_snapshot')
            ->with('service')
            ->get()
            ->each(function (Reservation $reservasi) {
                $reservasi->timestamps = false;
                $reservasi->update([
                    'harga_snapshot' => $reservasi->service->harga ?? 0,
                ]);
            });

        // Pakai updated_at sebagai perkiraan terbaik, bukan waktu konfirmasi yang pasti akurat (opsi A, dikonfirmasi user).
        Reservation::where('payment_status', 'paid')
            ->whereNull('payment_confirmed_at')
            ->get()
            ->each(function (Reservation $reservasi) {
                $nilaiAsli = $reservasi->updated_at;
                $reservasi->timestamps = false;
                $reservasi->update([
                    'payment_confirmed_at' => $nilaiAsli,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['harga_snapshot', 'payment_confirmed_at']);
        });
    }
};
