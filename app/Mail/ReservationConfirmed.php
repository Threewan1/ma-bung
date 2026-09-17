<?php

namespace App\Mail;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// Dikirim ke pelanggan begitu admin mengubah status reservasinya dari
// "Pending" menjadi "Dikonfirmasi" - lihat
// Admin\ReservationController::update().
class ReservationConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->loadMissing(['user', 'service', 'barber']);
    }

    public function build()
    {
        return $this->subject('Reservasi kamu telah dikonfirmasi!')
            ->view('emails.reservation-confirmed')
            ->with(['ringkasan' => $this->ringkasan()]);
    }

    private function ringkasan(): array
    {
        $r = $this->reservation;

        return [
            'Layanan' => $r->service->nama_layanan ?? '-',
            'Barber' => $r->barber->nama ?? 'Belum ditentukan',
            'Tanggal' => Carbon::parse($r->tanggal)->translatedFormat('l, d F Y'),
            'Jam' => $r->jam,
            'Metode Pembayaran' => $r->payment_method === 'online' ? 'Online' : 'COD (Bayar di Tempat)',
        ];
    }
}
