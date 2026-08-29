<?php

namespace App\Http\Controllers;

use App\Models\Service;

class LayananController extends Controller
{
    // Menampilkan semua layanan & harga untuk pelanggan
    public function index()
    {
        $services = Service::all();

        return view('layanan.index', compact('services'));
    }
}
