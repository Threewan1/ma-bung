<?php

namespace Tests\Feature;

use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Memverifikasi SUNGGUHAN (Feature Test, request HTTP nyata) bahwa fitur
 * verifikasi email yang baru diaktifkan benar-benar menahan akses
 * pelanggan yang belum verifikasi ke /dashboard & /reservasi, sementara
 * admin/barber sama sekali tidak terpengaruh.
 */
class EmailVerificationEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelanggan_belum_verifikasi_diblokir_dari_dashboard_dan_reservasi(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'pelanggan']);

        $this->actingAs($user)->get('/dashboard')
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)->get('/reservasi')
            ->assertRedirect(route('verification.notice'));

        $service = Service::create(['nama_layanan' => 'Potong Rambut', 'harga' => 25000]);

        $this->actingAs($user)->post('/reservasi', [
            'service_id' => $service->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '09:00',
            'payment_method' => 'cod',
        ])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_pelanggan_sudah_verifikasi_bisa_akses_reservasi_normal(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']); // factory default: email_verified_at = now()

        $this->actingAs($user)->get('/reservasi')->assertStatus(200);
        $this->actingAs($user)->get('/dashboard')->assertStatus(200);
    }

    public function test_klik_link_verifikasi_menandai_email_terverifikasi_dan_membuka_akses(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'pelanggan']);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Klik link verifikasi langsung (belum pernah dilempar dari
        // halaman lain sebelumnya dalam sesi ini) -> fallback ke dashboard.
        $response = $this->actingAs($user)->get($url);
        $response->assertRedirect(route('dashboard', absolute: false) . '?verified=1');

        $this->assertNotNull($user->fresh()->email_verified_at);

        // Setelah verifikasi: akses ke /reservasi terbuka
        $this->actingAs($user)->get('/reservasi')->assertStatus(200);
    }

    public function test_klik_link_verifikasi_mengembalikan_ke_halaman_yang_tadinya_dituju(): void
    {
        // Skenario: pelanggan mencoba buka /reservasi lebih dulu (belum
        // verifikasi) -> dilempar ke halaman verifikasi, Laravel menyimpan
        // "/reservasi" sebagai intended URL (Redirect::guest() di dalam
        // EnsureEmailIsVerified). Begitu link verifikasi di email diklik,
        // Laravel mengembalikannya ke /reservasi (bukan ke dashboard) -
        // ini perilaku redirect()->intended() bawaan Laravel, UX yang
        // memang diinginkan (kembali ke halaman yang tadinya dituju).
        $user = User::factory()->unverified()->create(['role' => 'pelanggan']);

        $this->actingAs($user)->get('/reservasi')->assertRedirect(route('verification.notice'));

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($url);
        $response->assertRedirect('/reservasi');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_admin_dan_barber_tidak_terpengaruh_middleware_verified(): void
    {
        // Sengaja dibuat BELUM verifikasi - membuktikan admin/barber tidak
        // pernah dicek status verifikasinya sama sekali (route group
        // mereka memang tidak memakai middleware 'verified').
        $admin = User::factory()->unverified()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin')->assertStatus(200);

        $barberUser = User::factory()->unverified()->create(['role' => 'barber']);
        Barber::create(['user_id' => $barberUser->id, 'nama' => 'Iron', 'status_aktif' => true]);
        $this->actingAs($barberUser)->get('/barber')->assertStatus(200);
    }

    public function test_notifikasi_verifikasi_memakai_view_custom_bertema_dark_gold(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['role' => 'pelanggan']);
        event(new \Illuminate\Auth\Events\Registered($user));

        Notification::assertSentTo($user, VerifyEmail::class, function (VerifyEmail $notification) use ($user) {
            $mail = $notification->toMail($user);

            return $mail->view === 'emails.verify-email'
                && ($mail->viewData['user'] ?? null) === $user
                && ! empty($mail->viewData['url']);
        });

        // Render langsung view-nya (dengan $message tiruan supaya
        // x-email-layout, yang butuh ->embed(), tidak error) untuk
        // membuktikan isi HTML-nya memang memakai tema dark+gold yang
        // sama seperti email lain, bukan template default Laravel.
        $html = view('emails.verify-email', [
            'user' => $user,
            'url' => 'https://example.test/verify/1/deadbeef',
            'message' => new \Illuminate\Mail\Message(new \Symfony\Component\Mime\Email()),
        ])->render();

        $this->assertStringContainsString("MA'BUNG BARBERSHOP", $html);
        $this->assertStringContainsString('Verifikasi Alamat Email', $html);
        $this->assertStringContainsString('Verifikasi Email Saya', $html);
        $this->assertStringContainsString('#d4af37', $html); // warna gold khas tema email lain
    }
}
