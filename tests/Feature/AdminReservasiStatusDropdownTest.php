<?php

namespace Tests\Feature;

use App\Models\Barber;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi untuk bug dropdown status "Menunggu/Dikonfirmasi/Dibatalkan" di
 * halaman admin "Kelola Reservasi" (admin/reservasi/_list.blade.php) yang
 * muncul setelah penerapan pencegahan double-submit.
 *
 * Root cause: JS nonaktifkanSelectDanKirim() sebelumnya men-set
 * `select.disabled = true` SEBELUM memanggil `select.form.submit()`.
 * Sesuai aturan HTML standar, form control yang disabled TIDAK disertakan
 * dalam data yang dikirim ke server - jadi field "status" hilang total
 * dari request begitu select di-disable lebih dulu.
 *
 * PHP feature test TIDAK bisa mengeksekusi JS di browser sungguhan (tidak
 * ada Dusk/Playwright terpasang di project ini), jadi test ini tidak bisa
 * membuktikan urutan baris JS-nya secara langsung. Yang test ini buktikan
 * adalah SIMTOM di level HTTP: persis apa yang terjadi di server untuk
 * masing-masing dari 2 kemungkinan pengiriman form (field "status" hilang
 * vs field "status" ikut terkirim) - mewakili kondisi SEBELUM dan SESUDAH
 * perbaikan urutan JS tersebut.
 */
class AdminReservasiStatusDropdownTest extends TestCase
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

    private function reservasiPending(): Reservation
    {
        $customer = $this->customer();
        $barberUser = User::factory()->create(['role' => 'barber']);
        $barber = Barber::create(['user_id' => $barberUser->id, 'nama' => 'Iron', 'status_aktif' => true]);
        $service = Service::create(['nama_layanan' => 'Potong Rambut', 'harga' => 25000]);

        return Reservation::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'tanggal' => now()->addDay()->toDateString(),
            'jam' => '10:00',
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);
    }

    // ================= Kondisi SEBELUM perbaikan (bug) =================
    // Mensimulasikan persis apa yang browser kirim kalau <select> sudah
    // disabled SEBELUM form.submit() - field "status" hilang total dari
    // body request (bukan cuma kosong, TIDAK ADA sama sekali).
    public function test_update_gagal_dan_status_tidak_berubah_kalau_field_status_hilang_dari_request(): void
    {
        $admin = $this->admin();
        $reservasi = $this->reservasiPending();

        $response = $this->actingAs($admin)->post("/admin/reservasi/{$reservasi->id}", [
            '_method' => 'PUT',
            // Sengaja TIDAK mengirim 'status' sama sekali.
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame('pending', $reservasi->fresh()->status);
    }

    // ================= Kondisi SESUDAH perbaikan (benar) =================
    // Mensimulasikan pengiriman form yang benar - field "status" ikut
    // terkirim (karena select TIDAK disabled lagi saat submit() dipanggil,
    // sesuai urutan baru di nonaktifkanSelectDanKirim()).
    public function test_update_berhasil_dan_status_tersimpan_di_database_kalau_field_status_ikut_terkirim(): void
    {
        $admin = $this->admin();
        $reservasi = $this->reservasiPending();

        $response = $this->actingAs($admin)->post("/admin/reservasi/{$reservasi->id}", [
            '_method' => 'PUT',
            'status' => 'confirmed',
        ]);

        $response->assertRedirect(route('admin.reservasi.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reservations', [
            'id' => $reservasi->id,
            'status' => 'confirmed',
        ]);
    }

    // ================= Transisi ke "cancelled" juga diverifikasi ==========
    public function test_update_ke_cancelled_berhasil_dan_tersimpan_di_database(): void
    {
        $admin = $this->admin();
        $reservasi = $this->reservasiPending();

        $response = $this->actingAs($admin)->post("/admin/reservasi/{$reservasi->id}", [
            '_method' => 'PUT',
            'status' => 'cancelled',
        ]);

        $response->assertRedirect(route('admin.reservasi.index'));
        $this->assertDatabaseHas('reservations', [
            'id' => $reservasi->id,
            'status' => 'cancelled',
        ]);
    }

    // ================= Guard urutan kode JS (bukan test HTTP) =============
    // Test di atas TIDAK bisa mendeteksi kalau urutan baris JS di
    // nonaktifkanSelectDanKirim() dibalik lagi - keduanya cuma
    // mensimulasikan hasil AKHIR pengiriman form lewat HTTP langsung,
    // tidak lewat browser sungguhan. Test ini menutup celah itu secara
    // tekstual: membaca file Blade-nya langsung dan memastikan
    // `select.form.submit()` muncul SEBELUM `select.disabled = true` di
    // dalam fungsi tersebut, persis syarat yang menyebabkan bug kemarin.
    public function test_urutan_kode_submit_sebelum_disable_di_javascript(): void
    {
        $isi = file_get_contents(resource_path('views/admin/reservasi/index.blade.php'));

        $posFungsi = strpos($isi, 'window.nonaktifkanSelectDanKirim');
        $this->assertNotFalse($posFungsi, 'Fungsi nonaktifkanSelectDanKirim tidak ditemukan di index.blade.php.');

        $isiFungsi = substr($isi, $posFungsi, 300);

        $posSubmit = strpos($isiFungsi, 'select.form.submit()');
        $posDisabled = strpos($isiFungsi, 'select.disabled = true');

        $this->assertNotFalse($posSubmit, 'Baris select.form.submit() tidak ditemukan.');
        $this->assertNotFalse($posDisabled, 'Baris select.disabled = true tidak ditemukan.');
        $this->assertTrue(
            $posSubmit < $posDisabled,
            'select.form.submit() harus dipanggil SEBELUM select.disabled = true - kalau dibalik, field "status" tidak akan ikut terkirim (form control disabled tidak disertakan dalam data form).'
        );
    }
}
