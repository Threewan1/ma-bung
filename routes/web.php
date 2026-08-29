<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\DashboardController as CustomerDashboardController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\BarberController as AdminBarberController;
use App\Http\Controllers\Barber\DashboardController as BarberDashboardController;
use App\Models\Reservation;
use Illuminate\Support\Facades\Route;

// Halaman Utama
Route::get('/', function () {
    // Testimoni asli dari pelanggan - hanya yang rating-nya bagus (4-5
    // bintang) DAN nulis ulasan (bukan cuma kasih bintang tanpa teks),
    // supaya section "Apa Kata Pelanggan Kami" berisi kutipan yang layak
    // ditampilkan ke publik. Kalau belum ada satupun yang memenuhi
    // syarat, view-nya fallback ke 3 testimoni contoh (lihat welcome.blade.php).
    $testimoni = Reservation::with('user')
        ->whereNotNull('review')
        ->where('review', '!=', '')
        ->where('rating', '>=', 4)
        ->latest('tanggal')
        ->take(6)
        ->get();

    return view('welcome', compact('testimoni'));
});

// Dashboard Pelanggan
Route::get('/dashboard', [CustomerDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Semua Layanan & Harga (halaman customer)
Route::get('/layanan', [LayananController::class, 'index'])
    ->middleware('auth')
    ->name('layanan.index');

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Reservasi Pelanggan
Route::middleware('auth')->group(function () {
    // Cek jam yang masih tersedia untuk tanggal tertentu (dipakai AJAX
    // di form buat reservasi) - HARUS didaftarkan sebelum Route::resource
    // di bawah, supaya path literal ini tidak "ketangkap" duluan oleh
    // route resource "/reservasi/{reservasi}" (yang akan mencoba
    // menginterpretasikan "jam-tersedia" sebagai ID reservasi).
    Route::get('/reservasi/jam-tersedia', [ReservationController::class, 'jamTersedia'])
        ->name('reservasi.jam-tersedia');

    // Endpoint JSON status reservasi (polling) - HARUS didaftarkan
    // sebelum Route::resource di bawah dengan alasan yang sama seperti
    // "jam-tersedia" di atas.
    Route::get('/reservasi/status-updates', [ReservationController::class, 'statusUpdates'])
        ->name('reservasi.statusUpdates');

    // Cek ketersediaan barber untuk tanggal+jam tertentu (dipakai AJAX
    // di form buat reservasi) - HARUS didaftarkan sebelum Route::resource
    // dengan alasan yang sama seperti "jam-tersedia" di atas.
    Route::get('/reservasi/barber-tersedia', [ReservationController::class, 'barberTersedia'])
        ->name('reservasi.barberTersedia');

    // Resource CRUD reservasi
    Route::resource('/reservasi', ReservationController::class);

    //Route upload bukti pembayaran
    Route::post(
        '/reservasi/{reservasi}/upload-bukti',
        [ReservationController::class, 'uploadBukti']
    )->name('reservasi.uploadBukti');

    // Rating & review reservasi yang sudah selesai
    Route::post(
        '/reservasi/{reservasi}/rating',
        [ReservationController::class, 'rate']
    )->name('reservasi.rate');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard admin menggunakan DashboardController
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Endpoint JSON statistik dashboard - dipoll berkala oleh JS supaya
    // kartu statistik ter-update otomatis tanpa reload halaman.
    Route::get('/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    Route::resource('layanan', ServiceController::class);

    // Endpoint live-sync "Kelola Reservasi" (polling status + render
    // ulang tab via AJAX) - HARUS didaftarkan sebelum Route::resource
    // di bawah, supaya path literal ini tidak "ketangkap" duluan oleh
    // route resource "reservasi/{reservasi}".
    Route::get('reservasi/status-updates', [AdminReservationController::class, 'statusUpdates'])
        ->name('reservasi.statusUpdates');
    Route::get('reservasi/tab-content', [AdminReservationController::class, 'tabContent'])
        ->name('reservasi.tabContent');

    Route::resource('reservasi', AdminReservationController::class);

    // Konfirmasi/tolak bukti pembayaran reservasi
    Route::patch('reservasi/{reservasi}/payment-status', [AdminReservationController::class, 'updatePaymentStatus'])
        ->name('reservasi.updatePaymentStatus');

    // Upload foto before & after (reservasi yang sudah selesai)
    Route::post('reservasi/{reservasi}/transformasi', [AdminReservationController::class, 'uploadTransformasi'])
        ->name('reservasi.uploadTransformasi');

    Route::resource('antrian', QueueController::class);

    // Kelola Barber: lihat daftar, aktifkan/nonaktifkan, edit nama/foto.
    // "CRUD sederhana" - tanpa create/destroy penuh, barber baru dibuat
    // lewat seeder/tinker (lihat BarberSeeder), admin cukup kelola yang
    // sudah ada.
    Route::get('barber', [AdminBarberController::class, 'index'])->name('barber.index');
    Route::get('barber/{barber}/edit', [AdminBarberController::class, 'edit'])->name('barber.edit');
    Route::put('barber/{barber}', [AdminBarberController::class, 'update'])->name('barber.update');
    Route::patch('barber/{barber}/toggle-status', [AdminBarberController::class, 'toggleStatus'])->name('barber.toggleStatus');
});

// Halaman Kerja Barber
Route::middleware(['auth', 'barber'])->prefix('barber')->name('barber.')->group(function () {
    Route::get('/', [BarberDashboardController::class, 'index'])->name('dashboard');

    // AJAX: "Mulai Layani" (confirmed -> sedang_dilayani) & "Selesai"
    // (sedang_dilayani -> done) - tanpa reload halaman.
    Route::patch('/reservasi/{reservasi}/status', [BarberDashboardController::class, 'updateStatus'])
        ->name('updateStatus');

    // Endpoint JSON live-sync (polling) - supaya status pelayanan/
    // pembayaran ter-update otomatis di halaman ini tanpa reload,
    // terutama saat admin mengonfirmasi pembayaran.
    Route::get('/status-updates', [BarberDashboardController::class, 'statusUpdates'])
        ->name('statusUpdates');
});

require __DIR__.'/auth.php';