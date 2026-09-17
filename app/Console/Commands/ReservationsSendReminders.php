<?php

namespace App\Console\Commands;

use App\Mail\ReservationReminder;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ReservationsSendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email pengingat ke pelanggan yang reservasinya dimulai sekitar 1 jam lagi';

    /**
     * Rentang 55-65 menit (bukan tepat 60) karena scheduler jalan berkala, bukan presisi detik.
     */
    private const WINDOW_START_MINUTES = 55;
    private const WINDOW_END_MINUTES = 65;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        $windowStart = $now->copy()->addMinutes(self::WINDOW_START_MINUTES);
        $windowEnd = $now->copy()->addMinutes(self::WINDOW_END_MINUTES);

        // Saring longgar dulu di DB (tanggal & jam kolom terpisah), filter presisi tanggal+jam gabungan dilakukan di PHP di bawah.
        $reservations = Reservation::with(['user', 'service', 'barber'])
            ->whereIn('status', ['confirmed', 'sedang_dilayani'])
            ->whereNull('reminder_sent_at')
            ->whereIn('tanggal', [$now->toDateString(), $now->copy()->addDay()->toDateString()])
            ->get();

        $terkirim = 0;

        foreach ($reservations as $reservation) {
            $jadwal = Carbon::parse($reservation->tanggal.' '.$reservation->jam);

            if ($jadwal->betweenIncluded($windowStart, $windowEnd)) {
                Mail::to($reservation->user->email)->send(new ReservationReminder($reservation));

                $reservation->update(['reminder_sent_at' => $now]);

                $terkirim++;
            }
        }

        $this->info("Reminder terkirim untuk {$terkirim} reservasi.");

        return self::SUCCESS;
    }
}
