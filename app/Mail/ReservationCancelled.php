<?php

namespace App\Mail;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// Dikirim ke pelanggan begitu reservasinya dibatalkan - baik oleh
// pelanggan sendiri (ReservationController::destroy()) maupun oleh
// admin (Admin\ReservationController::update()).
class ReservationCancelled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->loadMissing(['user', 'service', 'barber']);
    }

    public function build()
    {
        return $this->subject('Reservasi kamu telah dibatalkan')
            ->view('emails.reservation-cancelled')
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
        ];
    }
}
