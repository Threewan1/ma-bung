<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Queue;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // Menampilkan semua data reservasi untuk admin
    public function index()
    {
        // Ambil semua reservasi beserta relasi user dan service
        $reservations = Reservation::with('user', 'service', 'queue')
            ->latest() // urutkan dari yang terbaru
            ->get();

        return view('admin.reservasi.index', compact('reservations'));
    }

    // Mengupdate status reservasi
    public function update(Request $request, Reservation $reservasi)
    {
        // Validasi status yang boleh dipilih
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,done',
        ]);

        // Update status reservasi
        $reservasi->update(['status' => $request->status]);

        return redirect()->route('admin.reservasi.index')
            ->with('success', 'Status reservasi berhasil diupdate!');
    }

    // Menghapus data reservasi
    public function destroy(Reservation $reservasi)
    {
        // Hapus reservasi dari database
        $reservasi->delete();

        return redirect()->route('admin.reservasi.index')
            ->with('success', 'Reservasi berhasil dihapus!');
    }
}