<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Satu baris di tabel "queues" = nomor antrian satu reservasi.
class Queue extends Model
{
    protected $fillable = [
        'reservation_id',
        'nomor_antrian',
        'waktu_dibuat',
    ];

    // Relasi ke reservasi pemilik nomor antrian ini.
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // Hitung ulang nomor antrian semua reservasi aktif di 1 tanggal, urut dari jam paling pagi (id jadi tie-breaker kalau jamnya sama).
    public static function aturUlangNomorAntrian(string $tanggal): void
    {
        // Ambil reservasi aktif di tanggal ini, urut dari jam paling pagi.
        $reservasiAktif = Reservation::where('tanggal', $tanggal)
            ->where('status', '!=', 'cancelled')
            ->orderBy('jam', 'asc')
            ->orderBy('id', 'asc') // tie-breaker untuk jam yang sama
            ->get(['id']);

        // Simpan ulang nomor antrian tiap reservasi sesuai urutannya.
        foreach ($reservasiAktif as $index => $reservasi) {
            self::updateOrCreate(
                ['reservation_id' => $reservasi->id],
                ['nomor_antrian' => $index + 1]
            );
        }

        // Hapus nomor antrian reservasi yang baru saja dibatalkan.
        self::whereHas('reservation', function ($query) use ($tanggal) {
            $query->where('tanggal', $tanggal)->where('status', 'cancelled');
        })->delete();
    }
}
