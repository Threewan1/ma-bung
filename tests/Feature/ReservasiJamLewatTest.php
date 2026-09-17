<?php

namespace Tests\Feature;

use App\Models\Barber;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Menguji validasi "jam sudah lewat" untuk reservasi tanggal hari ini -
 * baik di endpoint tampilan (jamTersedia) maupun validasi backend saat
 * reservasi disimpan (store()). Waktu "sekarang" dibekukan lewat
 * Carbon::setTestNow() supaya hasil test tidak tergantung jam sungguhan
 * saat test dijalankan.
 */
class ReservasiJamLewatTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Selalu reset waktu palsu supaya tidak bocor ke test lain di
        // luar file ini (test class lain dijalankan di proses yang sama).
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function customer(): User
    {
        return User::factory()->create(['role' => 'pelanggan']);
    }

    private function barberUser(string $nama = 'Iron'): array
    {
        $user = User::factory()->create(['role' => 'barber', 'name' => $nama]);
        $barber = Barber::create(['user_id' => $user->id, 'nama' => $nama, 'status_aktif' => true]);

        return [$user, $barber];
    }

    private function service(): Service
    {
        return Service::create(['nama_layanan' => 'Potong Rambut', 'harga' => 25000]);
    }

    // ================= 1. Reservasi hari ini, jam sudah lewat -> ditolak =================
    public function test_reservasi_hari_ini_jam_sudah_lewat_ditolak(): void
    {
        $user = $this->customer();
        [, $barber] = $this->barberUser();
        $service = $this->service();

        $hariIni = Carbon::today();
        Carbon::setTestNow($hariIni->copy()->setTime(14, 30));

        $response = $this->actingAs($user)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => $hariIni->toDateString(),
            'jam' => '10:00', // jam 10:00 sudah lewat dibanding "sekarang" (14:30)
            'payment_method' => 'cod',
        ]);

        $response->assertSessionHasErrors('jam');
        $this->assertDatabaseCount('reservations', 0);
    }

    // ================= 2. Reservasi hari ini, jam belum lewat -> berhasil =================
    public function test_reservasi_hari_ini_jam_belum_lewat_berhasil(): void
    {
        $user = $this->customer();
        [, $barber] = $this->barberUser();
        $service = $this->service();

        $hariIni = Carbon::today();
        Carbon::setTestNow($hariIni->copy()->setTime(14, 30));

        $response = $this->actingAs($user)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => $hariIni->toDateString(),
            'jam' => '16:00', // belum lewat dibanding "sekarang" (14:30)
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect(route('reservasi.index'));
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'tanggal' => $hariIni->toDateString(),
            'jam' => '16:00',
        ]);
    }

    // ================= 3. Reservasi untuk besok, jam manapun -> tetap normal =================
    public function test_reservasi_besok_jam_manapun_tetap_normal(): void
    {
        $user = $this->customer();
        [, $barber] = $this->barberUser();
        $service = $this->service();

        $hariIni = Carbon::today();
        Carbon::setTestNow($hariIni->copy()->setTime(14, 30));

        $besok = $hariIni->copy()->addDay();

        // Jam 10:00 "sudah lewat" KALAU dibandingkan ke tanggal HARI INI,
        // tapi ini reservasi untuk BESOK - harus tetap lolos, membuktikan
        // validasi jam-lewat tidak ikut menyasar tanggal selain hari ini.
        $response = $this->actingAs($user)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => $besok->toDateString(),
            'jam' => '10:00',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect(route('reservasi.index'));
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'tanggal' => $besok->toDateString(),
            'jam' => '10:00',
        ]);
    }

    // ================= 4. Endpoint jamTersedia() menandai jam lewat utk hari ini =================
    public function test_endpoint_jam_tersedia_menandai_jam_lewat_untuk_hari_ini(): void
    {
        $user = $this->customer();
        $this->barberUser(); // minimal 1 barber aktif supaya endpoint mengembalikan slot
        $this->service();

        $hariIni = Carbon::today();
        Carbon::setTestNow($hariIni->copy()->setTime(14, 30));

        $response = $this->actingAs($user)->getJson('/reservasi/jam-tersedia?tanggal=' . $hariIni->toDateString());
        $response->assertStatus(200);

        $slots = collect($response->json());
        $slot1000 = $slots->firstWhere('jam', '10:00');
        $slot1400 = $slots->firstWhere('jam', '14:00');
        $slot1600 = $slots->firstWhere('jam', '16:00');

        $this->assertTrue($slot1000['sudah_lewat']); // 10:00 < 14:30 -> sudah lewat
        $this->assertTrue($slot1400['sudah_lewat']); // 14:00 < 14:30 -> sudah lewat
        $this->assertFalse($slot1600['sudah_lewat']); // 16:00 > 14:30 -> belum lewat
    }

    // ================= 5. Endpoint jamTersedia() untuk besok -> tidak ada yang lewat =================
    public function test_endpoint_jam_tersedia_untuk_besok_tidak_ada_yang_lewat(): void
    {
        $user = $this->customer();
        $this->barberUser();
        $this->service();

        $hariIni = Carbon::today();
        Carbon::setTestNow($hariIni->copy()->setTime(14, 30));

        $besok = $hariIni->copy()->addDay();

        $response = $this->actingAs($user)->getJson('/reservasi/jam-tersedia?tanggal=' . $besok->toDateString());
        $response->assertStatus(200);

        $slots = collect($response->json());

        $this->assertTrue($slots->every(fn ($slot) => $slot['sudah_lewat'] === false));
    }
}
