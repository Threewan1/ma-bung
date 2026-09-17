<?php

namespace App\Mail;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// Dikirim ke pelanggan sekitar 1 jam sebelum jadwal reservasinya - lihat
// App\Console\Commands\ReservationsSendReminders.
class ReservationReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->loadMissing(['user', 'service', 'barber']);
    }

    public function build()
    {
        return $this->subject('Pengingat: Reservasi kamu sebentar lagi dimulai')
            ->view('emails.reservation-reminder')
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
