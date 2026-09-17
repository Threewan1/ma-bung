<?php

namespace Tests\Feature;

use App\Mail\ReservationReminder;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationsSendRemindersTest extends TestCase
{
    use RefreshDatabase;

    private function buatReservasi(User $user, Service $service, Carbon $jadwal, string $status): Reservation
    {
        return Reservation::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'tanggal' => $jadwal->toDateString(),
            'jam' => $jadwal->format('H:i:s'),
            'status' => $status,
            'payment_method' => 'cod',
        ]);
    }

    public function test_mengirim_reminder_untuk_reservasi_dalam_jendela_55_65_menit(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $service = Service::create(['nama_layanan' => 'Potong Rambut', 'harga' => 25000]);

        $reservasi = $this->buatReservasi($user, $service, Carbon::now()->addMinutes(60), 'confirmed');

        $this->artisan('reservations:send-reminders')->assertExitCode(0);

        Mail::assertQueued(ReservationReminder::class, function ($mail) use ($reservasi) {
            return $mail->reservation->id === $reservasi->id;
        });

        $this->assertNotNull($reservasi->fresh()->reminder_sent_at);
    }

    public function test_tidak_mengirim_reminder_di_luar_jendela_waktu(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $service = Service::create(['nama_layanan' => 'Potong Rambut', 'harga' => 25000]);

        $this->buatReservasi($user, $service, Carbon::now()->addMinutes(120), 'confirmed');

        $this->artisan('reservations:send-reminders')->assertExitCode(0);

        Mail::assertNothingQueued();
    }

    public function test_tidak_mengirim_reminder_dua_kali(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $service = Service::create(['nama_layanan' => 'Potong Rambut', 'harga' => 25000]);

        $reservasi = $this->buatReservasi($user, $service, Carbon::now()->addMinutes(60), 'confirmed');
        $reservasi->update(['reminder_sent_at' => Carbon::now()]);

        $this->artisan('reservations:send-reminders')->assertExitCode(0);

        Mail::assertNothingQueued();
    }

    public function test_mengirim_reminder_untuk_status_sedang_dilayani(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $service = Service::create(['nama_layanan' => 'Potong Rambut', 'harga' => 25000]);

        $this->buatReservasi($user, $service, Carbon::now()->addMinutes(58), 'sedang_dilayani');

        $this->artisan('reservations:send-reminders')->assertExitCode(0);

        Mail::assertQueued(ReservationReminder::class);
    }
}
