<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    // Menampilkan semua data antrian hari ini
    public function index()
    {
        // Ambil antrian hari ini beserta relasi reservation, user, dan service
        $queues = Queue::with('reservation.user', 'reservation.service')
            ->whereDate('created_at', today()) // filter antrian hari ini saja
            ->orderBy('nomor_antrian') // urutkan berdasarkan nomor antrian
            ->get();

        return view('admin.antrian.index', compact('queues'));
    }

    // Mengupdate status antrian
    public function update(Request $request, Queue $antrian)
    {
        // Validasi status yang boleh dipilih
        $request->validate([
            'status_antrian' => 'required|in:menunggu,diproses,selesai',
        ]);

        // Update status antrian
        $antrian->update(['status_antrian' => $request->status_antrian]);

        return redirect()->route('admin.antrian.index')
            ->with('success', 'Status antrian berhasil diupdate!');
    }

    // Menghapus data antrian
    public function destroy(Queue $antrian)
    {
        // Hapus antrian dari database
        $antrian->delete();

        return redirect()->route('admin.antrian.index')
            ->with('success', 'Antrian berhasil dihapus!');
    }
}