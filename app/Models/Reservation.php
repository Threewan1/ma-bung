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

        // Snapshot harga layanan saat reservasi dibuat, biar tidak ikut berubah kalau harga layanan diedit belakangan.
        'harga_snapshot',

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

        // Channel pembayaran online (qris, bri)
        'payment_channel',

        // Lokasi file bukti pembayaran
        'payment_proof',

        // Status pembayaran
        'payment_status',

        // Kapan payment_status berubah jadi "paid", dipakai acuan bulan di laporan pendapatan.
        'payment_confirmed_at',

        // Rating bintang 1-5 dari pelanggan
        'rating',

        // Ulasan/komentar dari pelanggan
        'review',

        // Foto before/after (diisi admin untuk reservasi yang sudah selesai)
        'foto_before',
        'foto_after',

        // Waktu email reminder H-1 jam dikirim (null = belum dikirim)
        'reminder_sent_at',
    ];

    protected $casts = [
        'reminder_sent_at' => 'datetime',
        'payment_confirmed_at' => 'datetime',
        'harga_snapshot' => 'decimal:2',
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

    // Satu-satunya sumber pemetaan payment_status ke tampilan badge, dipakai di semua halaman biar tidak ditulis ulang-ulang.
    public function getPaymentBadgeAttribute(): array
    {
        return match ($this->payment_status) {
            'unpaid' => ['danger', 'fa-exclamation-circle', 'Belum Bayar'],
            'waiting_verification' => ['warning', 'fa-clock', 'Menunggu Verifikasi'],
            'paid' => ['success', 'fa-check-circle', 'Lunas'],
            'rejected' => ['danger', 'fa-times-circle', 'Ditolak'],
            default => ['secondary', 'fa-info-circle', ucfirst($this->payment_status ?? 'unpaid')],
        };
    }
}
