<?php

namespace App\Mail;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// Dikirim ke pelanggan begitu reservasi baru berhasil dibuat (masih
// berstatus "pending", menunggu konfirmasi admin) - lihat
// ReservationController::store().
class ReservationCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public ?int $nomorAntrian;

    public function __construct(Reservation $reservation, ?int $nomorAntrian = null)
    {
        // Load relasi di sini (bukan di controller), karena job queue diproses di proses terpisah yang tidak punya konteks request.
        $this->reservation = $reservation->loadMissing(['user', 'service', 'barber']);
        $this->nomorAntrian = $nomorAntrian;
    }

    public function build()
    {
        return $this->subject('Reservasi kamu berhasil dibuat')
            ->view('emails.reservation-created')
            ->with(['ringkasan' => $this->ringkasan()]);
    }

    private function ringkasan(): array
    {
        $r = $this->reservation;

        $ringkasan = [
            'Layanan' => $r->service->nama_layanan ?? '-',
            'Barber' => $r->barber->nama ?? 'Belum ditentukan',
            'Tanggal' => Carbon::parse($r->tanggal)->translatedFormat('l, d F Y'),
            'Jam' => $r->jam,
            'Metode Pembayaran' => $r->payment_method === 'online' ? 'Online' : 'COD (Bayar di Tempat)',
        ];

        if ($this->nomorAntrian) {
            $ringkasan['No. Antrian'] = '#' . $this->nomorAntrian;
        }

        return $ringkasan;
    }
}
