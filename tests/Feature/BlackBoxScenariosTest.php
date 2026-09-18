<?php

namespace Tests\Feature;

use App\Mail\PaymentConfirmed;
use App\Mail\ReservationCancelled;
use App\Mail\ReservationConfirmed;
use App\Mail\ReservationCreated;
use App\Models\Barber;
use App\Models\Queue;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pengujian Black Box - dieksekusi sungguhan lewat Laravel Feature Test
 * (HTTP request nyata ke route aplikasi, disimulasikan lewat TestCase).
 * Nomor test_scenario_XX mengacu ke nomor skenario pada dokumen pengujian.
 */
class BlackBoxScenariosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
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

    // ================= 1. Registrasi akun baru =================
    public function test_scenario_01_registrasi_akun_baru_dengan_data_valid(): void
    {
        $response = $this->post('/register', [
            'name' => 'Pelanggan Uji',
            'email' => 'pelanggan.uji@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'pelanggan.uji@example.com']);
        // Perilaku ACTUAL aplikasi: user langsung di-login (Auth::login)
        // dan diarahkan ke /dashboard, BUKAN ke halaman login seperti
        // disebut pada skenario.
        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    // ================= 2. Login & logout =================
    public function test_scenario_02_login_dan_logout_dengan_data_benar(): void
    {
        $user = $this->customer();

        $login = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $this->assertAuthenticated();
        // Pelanggan (role != admin/barber) diarahkan langsung ke
        // dashboard-nya - lihat AuthenticatedSessionController::store().
        $login->assertRedirect('/dashboard');

        $logout = $this->post('/logout');
        $this->assertGuest();
        $logout->assertRedirect('/');
    }

    // ================= 3. Halaman daftar layanan =================
    public function test_scenario_03_halaman_daftar_layanan_tampil_benar(): void
    {
        $user = $this->customer();
        $s1 = Service::create(['nama_layanan' => 'Potong Rambut', 'harga' => 25000]);
        $s2 = Service::create(['nama_layanan' => 'Cukur Jenggot', 'harga' => 15000]);

        $response = $this->actingAs($user)->get('/layanan');

        $response->assertStatus(200);
        $response->assertSee('Potong Rambut');
        $response->assertSee('Cukur Jenggot');
        $response->assertSee(number_format(25000, 0, ',', '.'));
        $response->assertSee(number_format(15000, 0, ',', '.'));
    }

    // ================= 4. Pilih tanggal -> jam tersedia/penuh =================
    public function test_scenario_04_jam_tersedia_dan_jam_penuh_dinonaktifkan(): void
    {
        $user = $this->customer();
        [$userBarber, $barber] = $this->barberUser('Iron'); // hanya 1 barber aktif -> kapasitas per jam = 1
        $service = $this->service();
        $tanggal = now()->addDay()->toDateString();

        // Sebelum ada reservasi, semua jam harus tampil "sisa=1, penuh=false"
        $before = $this->actingAs($user)->getJson('/reservasi/jam-tersedia?tanggal=' . $tanggal);
        $before->assertStatus(200);
        $slot1000Before = collect($before->json())->firstWhere('jam', '10:00');
        $this->assertSame(1, $slot1000Before['sisa']);
        $this->assertFalse($slot1000Before['penuh']);

        // Isi slot 10:00 dengan reservasi aktif
        Reservation::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => $tanggal,
            'jam' => '10:00',
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);

        $after = $this->actingAs($user)->getJson('/reservasi/jam-tersedia?tanggal=' . $tanggal);
        $slot1000After = collect($after->json())->firstWhere('jam', '10:00');
        $slot1100After = collect($after->json())->firstWhere('jam', '11:00');

        $this->assertSame(0, $slot1000After['sisa']);
        $this->assertTrue($slot1000After['penuh']); // jam 10:00 harus penuh
        $this->assertFalse($slot1100After['penuh']); // jam lain masih tersedia
    }

    // ================= 5. Pilih tanggal+jam -> daftar barber tersedia =================
    public function test_scenario_05_daftar_barber_tersedia_untuk_tanggal_dan_jam(): void
    {
        $user = $this->customer();
        [, $barberIron] = $this->barberUser('Iron');
        [, $barberRival] = $this->barberUser('Rival');
        $service = $this->service();
        $tanggal = now()->addDay()->toDateString();

        Reservation::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'barber_id' => $barberIron->id,
            'tanggal' => $tanggal,
            'jam' => '10:00',
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($user)->getJson('/reservasi/barber-tersedia?tanggal=' . $tanggal . '&jam=10:00');
        $response->assertStatus(200);

        $data = collect($response->json());
        $this->assertFalse($data->firstWhere('id', $barberIron->id)['tersedia']); // Iron sudah terisi
        $this->assertTrue($data->firstWhere('id', $barberRival->id)['tersedia']); // Rival masih kosong
    }

    // ================= 6. Membuat reservasi baru lengkap =================
    public function test_scenario_06_membuat_reservasi_baru_lengkap_tersimpan(): void
    {
        Mail::fake();
        $user = $this->customer();
        [, $barber] = $this->barberUser();
        $service = $this->service();
        $tanggal = now()->addDay()->toDateString();

        $response = $this->actingAs($user)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => $tanggal,
            'jam' => '09:00',
            'catatan' => 'Rapi seperti biasa',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect(route('reservasi.index'));
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => $tanggal,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);
        Mail::assertQueued(ReservationCreated::class);
    }

    // ================= 7. Payment online tanpa unggah bukti -> ditolak =================
    public function test_scenario_07_online_tanpa_bukti_ditolak_dengan_pesan(): void
    {
        $user = $this->customer();
        [, $barber] = $this->barberUser();
        $service = $this->service();

        $response = $this->actingAs($user)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '09:00',
            'payment_method' => 'online',
            'payment_channel' => 'qris',
            // payment_proof sengaja tidak dikirim
        ]);

        $response->assertSessionHasErrors('payment_proof');
        $this->assertDatabaseCount('reservations', 0);
    }

    // ================= 8. Nomor antrian sesuai urutan jadwal =================
    public function test_scenario_08_nomor_antrian_sesuai_urutan_jadwal(): void
    {
        Mail::fake();
        $userA = $this->customer();
        $userB = $this->customer();
        [, $barberA] = $this->barberUser('Iron');
        [, $barberB] = $this->barberUser('Rival');
        $service = $this->service();
        $tanggal = now()->addDay()->toDateString();

        // Buat reservasi jam 14:00 duluan
        $this->actingAs($userA)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barberA->id,
            'tanggal' => $tanggal,
            'jam' => '14:00',
            'payment_method' => 'cod',
        ]);

        // Lalu reservasi jam 08:00 (lebih pagi) oleh pelanggan lain
        $this->actingAs($userB)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barberB->id,
            'tanggal' => $tanggal,
            'jam' => '08:00',
            'payment_method' => 'cod',
        ]);

        $reservasiJam14 = Reservation::where('jam', '14:00')->first();
        $reservasiJam08 = Reservation::where('jam', '08:00')->first();

        // Nomor antrian harus mengikuti URUTAN JAM, bukan urutan pembuatan
        $this->assertSame(1, Queue::where('reservation_id', $reservasiJam08->id)->value('nomor_antrian'));
        $this->assertSame(2, Queue::where('reservation_id', $reservasiJam14->id)->value('nomor_antrian'));
    }

    // ================= 9. Halaman "Reservasi Saya" =================
    public function test_scenario_09_halaman_reservasi_saya_tampilkan_daftar_dan_detail(): void
    {
        $user = $this->customer();
        [, $barber] = $this->barberUser();
        $service = $this->service();

        $reservasi = Reservation::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '09:00',
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);
        Queue::create(['reservation_id' => $reservasi->id, 'nomor_antrian' => 1]);

        $index = $this->actingAs($user)->get('/reservasi');
        $index->assertStatus(200);
        $index->assertSee('Potong Rambut');

        $show = $this->actingAs($user)->get(route('reservasi.show', $reservasi->id));
        $show->assertStatus(200);
        $show->assertSee('1'); // nomor antrian tampil di halaman detail

        // Pelanggan lain tidak boleh melihat detail reservasi ini (403)
        $lain = $this->customer();
        $this->actingAs($lain)->get(route('reservasi.show', $reservasi->id))->assertForbidden();
    }

    // ================= 10. Batalkan reservasi belum "Selesai" =================
    public function test_scenario_10_batalkan_reservasi_yang_belum_selesai(): void
    {
        Mail::fake();
        $user = $this->customer();
        $service = $this->service();

        $reservasi = Reservation::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '09:00',
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($user)->delete(route('reservasi.destroy', $reservasi->id));

        $response->assertRedirect(route('reservasi.index'));
        $this->assertSame('cancelled', $reservasi->fresh()->status);
        Mail::assertQueued(ReservationCancelled::class);

        // Reservasi yang statusnya "done" TIDAK boleh dibatalkan
        $selesai = Reservation::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'tanggal' => now()->toDateString(),
            'jam' => '10:00',
            'status' => 'done',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
        ]);
        $this->actingAs($user)->delete(route('reservasi.destroy', $selesai->id));
        $this->assertSame('done', $selesai->fresh()->status);
    }

    // ================= 10b. Batalkan reservasi yang sudah cancelled tidak kirim email dobel =================
    public function test_scenario_10b_batalkan_reservasi_dua_kali_tidak_kirim_email_dobel(): void
    {
        Mail::fake();
        $user = $this->customer();
        $service = $this->service();

        $reservasi = Reservation::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '09:00',
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($user)->delete(route('reservasi.destroy', $reservasi->id));
        $this->actingAs($user)->delete(route('reservasi.destroy', $reservasi->id));

        $this->assertSame('cancelled', $reservasi->fresh()->status);
        Mail::assertQueued(ReservationCancelled::class, 1);
    }

    // ================= 11. Ubah data profil pelanggan =================
    public function test_scenario_11_ubah_profil_pelanggan_tersimpan(): void
    {
        $user = $this->customer();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Nama Baru',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertSame('Nama Baru', $user->fresh()->name);
    }

    // ================= 12. Rating & ulasan pada reservasi selesai =================
    public function test_scenario_12_rating_dan_ulasan_pada_reservasi_selesai(): void
    {
        $user = $this->customer();
        $service = $this->service();
        $selesai = Reservation::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'tanggal' => now()->subDay()->toDateString(),
            'jam' => '09:00',
            'status' => 'done',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($user)->post(route('reservasi.rate', $selesai->id), [
            'rating' => 5,
            'review' => 'Rapi dan cepat!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertSame(5, $selesai->fresh()->rating);
        $this->assertSame('Rapi dan cepat!', $selesai->fresh()->review);

        // Reservasi yang BELUM selesai tidak boleh dirating
        $pending = Reservation::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '09:00',
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);
        $this->actingAs($user)->post(route('reservasi.rate', $pending->id), ['rating' => 4]);
        $this->assertNull($pending->fresh()->rating);
    }

    // ================= 13. Dashboard admin -> statistik =================
    public function test_scenario_13_dashboard_admin_tampilkan_statistik(): void
    {
        $admin = $this->admin();
        $user = $this->customer();
        [, $barber] = $this->barberUser();
        $service = $this->service();

        Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id, 'barber_id' => $barber->id,
            'tanggal' => now()->toDateString(), 'jam' => '09:00', 'status' => 'done',
            'payment_method' => 'cod', 'payment_status' => 'paid',
            // harga_snapshot & payment_confirmed_at HARUS diisi manual di
            // sini - kedua kolom ini normalnya diisi otomatis oleh
            // ReservationController::store() dan
            // Admin\ReservationController::updatePaymentStatus(), tapi
            // test ini membuat reservasi langsung lewat Eloquent
            // (melewati controller), jadi tidak ikut ke-trigger.
            'harga_snapshot' => $service->harga,
            'payment_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertViewHas('totalReservasi', 1);
        $response->assertViewHas('totalLayanan', 1);
        $response->assertViewHas('totalPendapatanBulanIni', 25000);

        $stats = $this->actingAs($admin)->getJson('/admin/stats');
        $stats->assertStatus(200)->assertJson(['totalReservasi' => 1]);

        // Non-admin ditolak akses (redirect, bukan 200)
        $this->actingAs($user)->get('/admin')->assertRedirect(route('dashboard'));
    }

    // ================= 14. Tambah/ubah/hapus layanan =================
    public function test_scenario_14_crud_layanan_oleh_admin(): void
    {
        $admin = $this->admin();

        $create = $this->actingAs($admin)->post(route('admin.layanan.store'), [
            'nama_layanan' => 'Creambath',
            'harga' => 50000,
        ]);
        $create->assertRedirect(route('admin.layanan.index'));
        $this->assertDatabaseHas('services', ['nama_layanan' => 'Creambath', 'harga' => 50000]);

        $service = Service::where('nama_layanan', 'Creambath')->first();
        $update = $this->actingAs($admin)->put(route('admin.layanan.update', $service->id), [
            'nama_layanan' => 'Creambath Premium',
            'harga' => 60000,
        ]);
        $update->assertRedirect(route('admin.layanan.index'));
        $this->assertDatabaseHas('services', ['id' => $service->id, 'nama_layanan' => 'Creambath Premium', 'harga' => 60000]);

        $delete = $this->actingAs($admin)->delete(route('admin.layanan.destroy', $service->id));
        $delete->assertRedirect(route('admin.layanan.index'));
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    // ================= 15. Ubah data & status aktif barber =================
    public function test_scenario_15_ubah_data_dan_status_aktif_barber(): void
    {
        $admin = $this->admin();
        [, $barber] = $this->barberUser('Iron');

        $update = $this->actingAs($admin)->put(route('admin.barber.update', $barber->id), [
            'nama' => 'Iron Updated',
        ]);
        $update->assertRedirect(route('admin.barber.index'));
        $this->assertSame('Iron Updated', $barber->fresh()->nama);
        // Nama akun login ikut disamakan
        $this->assertSame('Iron Updated', $barber->fresh()->user->name);

        $this->assertTrue($barber->fresh()->status_aktif);
        $toggle = $this->actingAs($admin)->patch(route('admin.barber.toggleStatus', $barber->id));
        $toggle->assertRedirect(route('admin.barber.index'));
        $this->assertFalse($barber->fresh()->status_aktif);
    }

    // ================= 16. Filter reservasi berdasarkan status & metode bayar =================
    public function test_scenario_16_data_reservasi_terkelompok_benar_per_status_dan_metode_bayar(): void
    {
        // CATATAN METODOLOGI: filter status (tab) & metode pembayaran di
        // halaman admin/reservasi diimplementasikan MURNI di sisi
        // client (Bootstrap tab + atribut data-payment-filter yang
        // dibaca JavaScript - lihat resources/views/admin/reservasi/_tab-content.blade.php).
        // PHPUnit/Feature Test tidak mengeksekusi JavaScript, sehingga
        // interaksi klik-tab/klik-dropdown TIDAK BISA diverifikasi
        // otomatis dari sisi backend. Yang BISA diverifikasi di sini
        // adalah bahwa backend mengirim & mengelompokkan data reservasi
        // dengan benar per status dan payment_method, sehingga JS filter
        // tersebut punya data yang benar untuk difilter.
        $admin = $this->admin();
        $user = $this->customer();
        $service = $this->service();

        $pendingCod = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id,
            'tanggal' => now()->toDateString(), 'jam' => '09:00', 'status' => 'pending',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);
        $confirmedOnline = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id,
            'tanggal' => now()->toDateString(), 'jam' => '10:00', 'status' => 'confirmed',
            'payment_method' => 'online', 'payment_status' => 'paid', 'payment_proof' => 'x.jpg',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reservasi.index'));
        $response->assertStatus(200);

        $html = $response->getContent();
        // Tiap card reservasi punya form action unik "/admin/reservasi/{id}"
        // (dropdown status) - dipakai sebagai penanda reservasi mana yang
        // muncul di potongan HTML tab tertentu.
        $tabPending = substr($html, strpos($html, 'id="tab-pending"'), 4000);
        $this->assertStringContainsString('action="/admin/reservasi/' . $pendingCod->id . '"', $tabPending);

        $tabConfirmed = substr($html, strpos($html, 'id="tab-confirmed"'), 4000);
        $this->assertStringContainsString('action="/admin/reservasi/' . $confirmedOnline->id . '"', $tabConfirmed);
    }

    // ================= 17. Buka bukti pembayaran =================
    public function test_scenario_17_bukti_pembayaran_dapat_diakses(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $user = $this->customer();
        $service = $this->service();

        // ->create() dipakai (bukan ->image()) karena ekstensi GD tidak
        // terpasang di lingkungan pengujian ini - ->image() butuh GD
        // untuk benar-benar merender bitmap. ->create() dengan mimeType
        // eksplisit tetap lolos validasi 'image' Laravel untuk UploadedFile
        // hasil fake (mime dibaca dari yang di-set, bukan dari isi file).
        $file = UploadedFile::fake()->create('bukti.jpg', 10, 'image/jpeg');
        $path = $file->store('payment_proofs', 'public');

        $reservasi = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id,
            'tanggal' => now()->toDateString(), 'jam' => '09:00', 'status' => 'pending',
            'payment_method' => 'online', 'payment_status' => 'waiting_verification',
            'payment_proof' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $show = $this->actingAs($user)->get(route('reservasi.show', $reservasi->id));
        $show->assertStatus(200);
        $show->assertSee('storage/' . $path, false);
    }

    // ================= 18. Konfirmasi online tanpa bukti -> ditolak =================
    public function test_scenario_18_konfirmasi_online_tanpa_bukti_ditolak(): void
    {
        $admin = $this->admin();
        $user = $this->customer();
        $service = $this->service();

        $reservasi = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id,
            'tanggal' => now()->toDateString(), 'jam' => '09:00', 'status' => 'pending',
            'payment_method' => 'online', 'payment_status' => 'unpaid', 'payment_proof' => null,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.reservasi.update', $reservasi->id), [
            'status' => 'confirmed',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame('pending', $reservasi->fresh()->status);
    }

    // ================= 19. Tandai pembayaran "Lunas" =================
    public function test_scenario_19_tandai_pembayaran_lunas(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $user = $this->customer();
        $service = $this->service();

        $reservasi = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id,
            'tanggal' => now()->toDateString(), 'jam' => '09:00', 'status' => 'confirmed',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.reservasi.updatePaymentStatus', $reservasi->id), [
            'payment_status' => 'paid',
        ]);

        $response->assertRedirect(route('admin.reservasi.index'));
        $this->assertSame('paid', $reservasi->fresh()->payment_status);
        Mail::assertQueued(PaymentConfirmed::class);
    }

    // ================= 20. Tolak bukti pembayaran yang tidak sesuai =================
    public function test_scenario_20_tolak_bukti_pembayaran_yang_tidak_sesuai(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $user = $this->customer();
        $service = $this->service();
        $path = UploadedFile::fake()->create('bukti-salah.jpg', 10, 'image/jpeg')->store('payment_proofs', 'public');

        $reservasi = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id,
            'tanggal' => now()->toDateString(), 'jam' => '09:00', 'status' => 'pending',
            'payment_method' => 'online', 'payment_status' => 'waiting_verification',
            'payment_proof' => $path,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.reservasi.updatePaymentStatus', $reservasi->id), [
            'payment_status' => 'rejected',
        ]);

        $response->assertRedirect(route('admin.reservasi.index'));
        $reservasi->refresh();
        $this->assertSame('rejected', $reservasi->payment_status);
        $this->assertNull($reservasi->payment_proof);
        Storage::disk('public')->assertMissing($path);
    }

    // ================= 21. Halaman Kerja barber -> urut waktu terdekat =================
    public function test_scenario_21_halaman_kerja_barber_urut_jadwal_terdekat(): void
    {
        [$userBarber, $barber] = $this->barberUser('Iron');
        $user = $this->customer();
        $service = $this->service();

        $jauh = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id, 'barber_id' => $barber->id,
            'tanggal' => now()->addDays(2)->toDateString(), 'jam' => '09:00', 'status' => 'confirmed',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);
        $dekat = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id, 'barber_id' => $barber->id,
            'tanggal' => now()->toDateString(), 'jam' => '17:00', 'status' => 'confirmed',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($userBarber)->get(route('barber.dashboard'));
        $response->assertStatus(200);

        $list = $response->viewData('reservasiPerluDitindak');
        $this->assertSame($dekat->id, $list->first()->id);
        $this->assertSame($jauh->id, $list->last()->id);
    }

    // ================= 22. Ubah status Sedang Dilayani -> Selesai =================
    public function test_scenario_22_ubah_status_sedang_dilayani_lalu_selesai_berurutan(): void
    {
        [$userBarber, $barber] = $this->barberUser('Iron');
        $user = $this->customer();
        $service = $this->service();

        $reservasi = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id, 'barber_id' => $barber->id,
            'tanggal' => now()->toDateString(), 'jam' => '09:00', 'status' => 'confirmed',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);

        $step1 = $this->actingAs($userBarber)->patchJson(route('barber.updateStatus', $reservasi->id), [
            'status' => 'sedang_dilayani',
        ]);
        $step1->assertStatus(200)->assertJson(['success' => true, 'status' => 'sedang_dilayani']);
        $this->assertSame('sedang_dilayani', $reservasi->fresh()->status);

        $step2 = $this->actingAs($userBarber)->patchJson(route('barber.updateStatus', $reservasi->id), [
            'status' => 'done',
        ]);
        $step2->assertStatus(200)->assertJson(['success' => true, 'status' => 'done']);
        $this->assertSame('done', $reservasi->fresh()->status);

        // Melompat langsung dari "confirmed" ke "done" tanpa "sedang_dilayani" harus ditolak
        $reservasiLain = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id, 'barber_id' => $barber->id,
            'tanggal' => now()->toDateString(), 'jam' => '10:00', 'status' => 'confirmed',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);
        $lompat = $this->actingAs($userBarber)->patchJson(route('barber.updateStatus', $reservasiLain->id), [
            'status' => 'done',
        ]);
        $lompat->assertStatus(422);
        $this->assertSame('confirmed', $reservasiLain->fresh()->status);
    }

    // ================= 23. Halaman "Riwayat Saya" barber =================
    public function test_scenario_23_halaman_riwayat_saya_barber(): void
    {
        [$userBarber, $barber] = $this->barberUser('Iron');
        $user = $this->customer();
        $service = $this->service();

        $done = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id, 'barber_id' => $barber->id,
            'tanggal' => now()->subDay()->toDateString(), 'jam' => '09:00', 'status' => 'done',
            'payment_method' => 'cod', 'payment_status' => 'paid',
        ]);
        $cancelled = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id, 'barber_id' => $barber->id,
            'tanggal' => now()->subDays(2)->toDateString(), 'jam' => '09:00', 'status' => 'cancelled',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);
        $aktif = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id, 'barber_id' => $barber->id,
            'tanggal' => now()->addDay()->toDateString(), 'jam' => '09:00', 'status' => 'confirmed',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($userBarber)->get(route('barber.riwayat'));
        $response->assertStatus(200);

        $riwayat = $response->viewData('reservasiRiwayat')->pluck('id');
        $this->assertTrue($riwayat->contains($done->id));
        $this->assertTrue($riwayat->contains($cancelled->id));
        $this->assertFalse($riwayat->contains($aktif->id));
    }

    // ================= 24. Email notifikasi otomatis sesuai perubahan =================
    public function test_scenario_24_email_notifikasi_terkirim_sesuai_perubahan_status(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $user = $this->customer();
        $service = $this->service();
        [, $barber] = $this->barberUser();

        // (a) Reservasi dibuat -> ReservationCreated
        $this->actingAs($user)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '09:00',
            'payment_method' => 'cod',
        ]);
        Mail::assertQueued(ReservationCreated::class, 1);

        $reservasi = Reservation::first();

        // (b) Dikonfirmasi admin -> ReservationConfirmed
        $this->actingAs($admin)->patch(route('admin.reservasi.update', $reservasi->id), ['status' => 'confirmed']);
        Mail::assertQueued(ReservationConfirmed::class, 1);

        // (c) Dibatalkan -> ReservationCancelled
        $this->actingAs($admin)->patch(route('admin.reservasi.update', $reservasi->id), ['status' => 'cancelled']);
        Mail::assertQueued(ReservationCancelled::class, 1);

        // (d) Pembayaran lunas -> PaymentConfirmed (pakai reservasi baru, COD)
        $reservasi2 = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id,
            'tanggal' => now()->toDateString(), 'jam' => '10:00', 'status' => 'confirmed',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);
        $this->actingAs($admin)->patch(route('admin.reservasi.updatePaymentStatus', $reservasi2->id), ['payment_status' => 'paid']);
        Mail::assertQueued(PaymentConfirmed::class, 1);
    }

    // ================= 25. Dua pelanggan pesan barber sama, jam sama =================
    public function test_scenario_25_dua_pelanggan_pesan_barber_sama_jam_sama_hanya_satu_diproses(): void
    {
        // CATATAN METODOLOGI: ini mensimulasikan DUA REQUEST BERURUTAN
        // (bukan concurrent request sungguhan) yang menguji guard
        // "double booking" di ReservationController::store() (query cek
        // barberSudahTerisi SEBELUM insert). Ini membuktikan logika guard
        // bekerja benar untuk kasus non-concurrent, TAPI TIDAK
        // membuktikan tidak ada race condition murni pada request yang
        // benar-benar bersamaan (dua proses PHP-FPM berbeda, dua koneksi
        // DB berbeda, dieksekusi dalam window waktu yang sama persis
        // antara SELECT cek dan INSERT). Pengujian race condition murni
        // butuh load-testing tool (mis. k6/Apache Bench dengan concurrency
        // > 1) yang mengirim request BENAR-BENAR bersamaan ke server
        // sungguhan (bukan lewat PHPUnit yang single-threaded/sequential),
        // idealnya di atas MySQL (bukan SQLite in-memory testing) karena
        // locking/transaction behavior keduanya berbeda.
        $userA = $this->customer();
        $userB = $this->customer();
        [, $barber] = $this->barberUser('Iron');
        $service = $this->service();
        $tanggal = now()->addDay()->toDateString();

        $respA = $this->actingAs($userA)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => $tanggal,
            'jam' => '09:00',
            'payment_method' => 'cod',
        ]);
        $respA->assertRedirect(route('reservasi.index'));

        $respB = $this->actingAs($userB)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => $tanggal,
            'jam' => '09:00',
            'payment_method' => 'cod',
        ]);
        $respB->assertSessionHasErrors('barber_id');

        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseHas('reservations', ['user_id' => $userA->id, 'barber_id' => $barber->id]);
        $this->assertDatabaseMissing('reservations', ['user_id' => $userB->id]);
    }

    // ================= 26. Nomor antrian diperbarui otomatis =================
    public function test_scenario_26_nomor_antrian_diperbarui_otomatis_saat_reservasi_baru_atau_batal(): void
    {
        Mail::fake();
        $user = $this->customer();
        [, $barberA] = $this->barberUser('Iron');
        [, $barberB] = $this->barberUser('Rival');
        [, $barberC] = $this->barberUser('Amos');
        $service = $this->service();
        $tanggal = now()->addDay()->toDateString();

        $r1 = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id, 'barber_id' => $barberA->id,
            'tanggal' => $tanggal, 'jam' => '08:00', 'status' => 'pending',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);
        $r2 = Reservation::create([
            'user_id' => $user->id, 'service_id' => $service->id, 'barber_id' => $barberB->id,
            'tanggal' => $tanggal, 'jam' => '09:00', 'status' => 'pending',
            'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);
        Queue::aturUlangNomorAntrian($tanggal);

        $this->assertSame(1, Queue::where('reservation_id', $r1->id)->value('nomor_antrian'));
        $this->assertSame(2, Queue::where('reservation_id', $r2->id)->value('nomor_antrian'));

        // Reservasi baru di antara jam 08:00 dan 09:00 -> nomor antrian bergeser otomatis
        $this->actingAs($user)->post('/reservasi', [
            'service_id' => $service->id,
            'barber_id' => $barberC->id,
            'tanggal' => $tanggal,
            'jam' => '08:30',
            'payment_method' => 'cod',
        ]);
        $r3 = Reservation::where('jam', '08:30')->first();
        $this->assertSame(1, Queue::where('reservation_id', $r1->id)->value('nomor_antrian'));
        $this->assertSame(2, Queue::where('reservation_id', $r3->id)->value('nomor_antrian'));
        $this->assertSame(3, Queue::where('reservation_id', $r2->id)->value('nomor_antrian'));

        // Batalkan r1 (nomor 1) -> sisa nomor antrian dirapikan ulang, tanpa bolong
        $this->actingAs($user)->delete(route('reservasi.destroy', $r1->id));
        $this->assertNull(Queue::where('reservation_id', $r1->id)->value('nomor_antrian')); // dihapus
        $this->assertSame(1, Queue::where('reservation_id', $r3->id)->value('nomor_antrian'));
        $this->assertSame(2, Queue::where('reservation_id', $r2->id)->value('nomor_antrian'));
    }
}
