<?php

namespace App\Mail;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// Dikirim ke pelanggan begitu admin menandai status pembayaran
// reservasinya menjadi "Lunas" - lihat
// Admin\ReservationController::updatePaymentStatus().
class PaymentConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->loadMissing(['user', 'service']);
    }

    public function build()
    {
        return $this->subject('Pembayaran kamu telah kami terima')
            ->view('emails.payment-confirmed')
            ->with(['ringkasan' => $this->ringkasan()]);
    }

    private function ringkasan(): array
    {
        $r = $this->reservation;
        $harga = $r->harga_snapshot ?? 0;

        return [
            'Layanan' => $r->service->nama_layanan ?? '-',
            'Tanggal' => Carbon::parse($r->tanggal)->translatedFormat('l, d F Y'),
            'Jam' => $r->jam,
            'Metode Pembayaran' => $r->payment_method === 'online' ? 'Online' : 'COD (Bayar di Tempat)',
            'Total Pembayaran' => 'Rp ' . number_format($harga, 0, ',', '.'),
        ];
    }
}
