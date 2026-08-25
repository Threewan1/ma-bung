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

        // Lokasi file bukti pembayaran
        'payment_proof',

        // Status pembayaran
        'payment_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function queue()
    {
        return $this->hasOne(Queue::class);
    }
}
