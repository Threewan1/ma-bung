<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\DashboardController as CustomerDashboardController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\BarberController as AdminBarberController;
use App\Http\Controllers\Barber\DashboardController as BarberDashboardController;
use App\Models\Reservation;
use App\Models\Service;
use Illuminate\Support\Facades\Route;

// Halaman Utama
Route::get('/', function () {
    // Section "Layanan Kami" - langsung dari tabel services, biar ikut ter-update begitu admin ubah data lewat "Kelola Layanan".
    $layanan = Service::all();

    // Cuma testimoni rating 4-5 yang ada ulasannya, fallback ke 3 contoh di view kalau belum ada yang cocok.
    $testimoni = Reservation::with('user')
        ->whereNotNull('review')
        ->where('review', '!=', '')
        ->where('rating', '>=', 4)
        ->latest('tanggal')
        ->take(6)
        ->get();

    return view('welcome', compact('layanan', 'testimoni'));
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

// "verified" wajib di sini juga, bukan cuma di dashboard, biar email belum diverifikasi tidak bisa sentuh reservasi sama sekali.
Route::middleware(['auth', 'verified'])->group(function () {
    // Harus didaftarkan sebelum Route::resource, biar path literal ini tidak "ketangkap" jadi {reservasi}.
    Route::get('/reservasi/jam-tersedia', [ReservationController::class, 'jamTersedia'])
        ->name('reservasi.jam-tersedia');

    Route::get('/reservasi/status-updates', [ReservationController::class, 'statusUpdates'])
        ->name('reservasi.statusUpdates');

    Route::get('/reservasi/barber-tersedia', [ReservationController::class, 'barberTersedia'])
        ->name('reservasi.barberTersedia');

    Route::resource('/reservasi', ReservationController::class);

    Route::post(
        '/reservasi/{reservasi}/upload-bukti',
        [ReservationController::class, 'uploadBukti']
    )->name('reservasi.uploadBukti');

    Route::post(
        '/reservasi/{reservasi}/rating',
        [ReservationController::class, 'rate']
    )->name('reservasi.rate');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    Route::resource('layanan', ServiceController::class);

    // Harus didaftarkan sebelum Route::resource, biar tidak "ketangkap" jadi {reservasi}.
    Route::get('reservasi/status-updates', [AdminReservationController::class, 'statusUpdates'])
        ->name('reservasi.statusUpdates');
    Route::get('reservasi/tab-content', [AdminReservationController::class, 'tabContent'])
        ->name('reservasi.tabContent');

    Route::resource('reservasi', AdminReservationController::class);

    Route::patch('reservasi/{reservasi}/payment-status', [AdminReservationController::class, 'updatePaymentStatus'])
        ->name('reservasi.updatePaymentStatus');

    // CRUD sederhana - barber baru dibuat lewat seeder/tinker, admin cuma kelola yang sudah ada.
    Route::get('barber', [AdminBarberController::class, 'index'])->name('barber.index');
    Route::get('barber/{barber}/edit', [AdminBarberController::class, 'edit'])->name('barber.edit');
    Route::put('barber/{barber}', [AdminBarberController::class, 'update'])->name('barber.update');
    Route::patch('barber/{barber}/toggle-status', [AdminBarberController::class, 'toggleStatus'])->name('barber.toggleStatus');
});

// Halaman Kerja Barber
Route::middleware(['auth', 'barber'])->prefix('barber')->name('barber.')->group(function () {
    Route::get('/', [BarberDashboardController::class, 'index'])->name('dashboard');

    // Semua reservasi Selesai/Dibatalkan, dipisah dari halaman kerja utama.
    Route::get('/riwayat', [BarberDashboardController::class, 'riwayat'])->name('riwayat');

    Route::patch('/reservasi/{reservasi}/status', [BarberDashboardController::class, 'updateStatus'])
        ->name('updateStatus');

    Route::post('/reservasi/{reservasi}/transformasi', [BarberDashboardController::class, 'uploadTransformasi'])
        ->name('uploadTransformasi');

    // {slot} dibatasi "before"/"after" saja, nilai lain otomatis 404.
    Route::delete('/reservasi/{reservasi}/transformasi/{slot}', [BarberDashboardController::class, 'hapusTransformasi'])
        ->whereIn('slot', ['before', 'after'])
        ->name('hapusTransformasi');

    Route::get('/status-updates', [BarberDashboardController::class, 'statusUpdates'])
        ->name('statusUpdates');
});

require __DIR__.'/auth.php';