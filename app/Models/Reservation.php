<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    // Kolom yang boleh diisi secara mass assignment
    protected $fillable = [

        // ID pelanggan
        'user_id',

        // ID layanan yang dipilih
        'service_id',

        // ID barber yang dipilih pelanggan (nullable)
        'barber_id',

        // Tanggal reservasi
        'tanggal',

        // Jam reservasi
        'jam',

        // Catatan tambahan dari pelanggan
        'catatan',

        // Status reservasi
        'status',

        // metode pembayaran
        'payment_method',

        // Channel pembayaran online (qris, bca, dana, gopay, shopeepay)
        'payment_channel',

        // Lokasi file bukti pembayaran
        'payment_proof',

        // Status pembayaran
        'payment_status',

        // Rating bintang 1-5 dari pelanggan
        'rating',

        // Ulasan/komentar dari pelanggan
        'review',

        // Foto before/after (diisi admin untuk reservasi yang sudah selesai)
        'foto_before',
        'foto_after',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }

    public function queue()
    {
        return $this->hasOne(Queue::class);
    }
}
