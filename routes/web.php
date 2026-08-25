<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\QueueController;
use Illuminate\Support\Facades\Route;

// Halaman Utama
Route::get('/', function () {
    return view('welcome');
});

// Dashboard Pelanggan
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Reservasi Pelanggan
Route::middleware('auth')->group(function () {
    Route::resource('/reservasi', ReservationController::class);
});

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard admin menggunakan DashboardController
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('layanan', ServiceController::class);
    Route::resource('reservasi', AdminReservationController::class);
    Route::resource('antrian', QueueController::class);
});

require __DIR__.'/auth.php';