<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $nomorAntrian;

    public function __construct($reservation, $nomorAntrian)
    {
        $this->reservation = $reservation;
        $this->nomorAntrian = $nomorAntrian;
    }

    public function build()
    {
        return $this->subject('Reservasi Barbershop Berhasil')
                    ->view('emails.reservation');
    }
}