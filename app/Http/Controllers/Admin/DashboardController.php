<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Queue;

class DashboardController extends Controller
{
    // Menampilkan halaman dashboard admin dengan data statistik
    public function index()
    {
        // Hitung total semua reservasi
        $totalReservasi = Reservation::count();

        // Hitung total semua layanan
        $totalLayanan = Service::count();

        // Hitung antrian hari ini
        $antrianHariIni = Queue::whereDate('created_at', today())->count();

        // Ambil 5 reservasi terbaru beserta relasi user dan service
        $reservasiTerbaru = Reservation::with('user', 'service')
            ->latest()
            ->take(5)
            ->get();

        // Kirim semua data ke view dashboard admin
        return view('admin.dashboard', compact(
            'totalReservasi',
            'totalLayanan',
            'antrianHariIni',
            'reservasiTerbaru'
        ));
    }
}