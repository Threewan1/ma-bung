<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    // Menampilkan semua data reservasi untuk admin
    public function index()
    {
        // Ambil semua reservasi beserta relasi user, service, dan barber
        $reservations = Reservation::with('user', 'service', 'queue', 'barber')
            ->latest() // urutkan dari yang terbaru
            ->get();

        return view('admin.reservasi.index', compact('reservations'));
    }

    // Endpoint JSON ringan - dipoll berkala oleh JS di halaman "Kelola
    // Reservasi" (tiap ~15 detik) supaya admin tahu ada perubahan status
    // pelayanan (barber menekan "Mulai Layani"/"Selesai") atau status
    // pembayaran, TANPA perlu me-render ulang seluruh tabel tiap kali
    // poll - "signature" dibandingkan di JS, HTML tab cuma diambil
    // ulang (lewat tabContent() di bawah) kalau memang ada yang beda.
    public function statusUpdates()
    {
        $reservations = Reservation::orderBy('id')->get(['id', 'status', 'payment_status']);

        return response()->json([
            'signature' => $reservations->map(fn ($r) => "{$r->id}:{$r->status}:{$r->payment_status}")->implode('|'),
        ]);
    }

    // Endpoint AJAX - mengembalikan HTML tab status + isi tabel yang
    // sudah dirender ulang (partial _tab-content.blade.php, SAMA persis
    // dengan yang dipakai render awal index()), dipakai JS di
    // index.blade.php untuk live-sync tanpa reload halaman penuh.
    public function tabContent()
    {
        $reservations = Reservation::with('user', 'service', 'queue', 'barber')
            ->latest()
            ->get();

        return view('admin.reservasi._tab-content', compact('reservations'));
    }

    // Mengupdate status reservasi
    public function update(Request $request, Reservation $reservasi)
    {
        // Validasi status yang boleh dipilih
        $request->validate([
            'status' => 'required|in:pending,confirmed,sedang_dilayani,cancelled,done',
        ]);

        $data = ['status' => $request->status];

        // Reservasi COD otomatis dianggap lunas begitu ditandai "Selesai" -
        // COD dibayar tunai langsung di tempat saat layanan selesai
        // dikerjakan, jadi tidak perlu langkah konfirmasi pembayaran
        // terpisah seperti Online (yang tetap lewat verifikasi bukti
        // transfer via modal, terlepas dari status reservasinya).
        if ($request->status === 'done' && $reservasi->payment_method !== 'online') {
            $data['payment_status'] = 'paid';
        }

        // Update status reservasi (+ status pembayaran untuk COD di atas)
        $reservasi->update($data);

        // Dipakai tombol "Konfirmasi" AJAX di dashboard admin (fetch
        // dengan Accept: application/json) - balas JSON tanpa redirect,
        // supaya baris tabelnya bisa diupdate langsung tanpa reload.
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $reservasi->status,
            ]);
        }

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

    // Mengonfirmasi atau menolak bukti pembayaran online yang diunggah
    // pelanggan (via modal). Pembayaran COD tidak lewat method ini -
    // lihat update() di atas, payment_status-nya otomatis jadi "paid"
    // begitu status reservasi ditandai "Selesai".
    public function updatePaymentStatus(Request $request, Reservation $reservasi)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,rejected',
        ]);

        $reservasi->update(['payment_status' => $request->payment_status]);

        $pesan = $request->payment_status === 'paid'
            ? 'Pembayaran berhasil dikonfirmasi (Lunas)!'
            : 'Bukti pembayaran ditolak.';

        return redirect()->route('admin.reservasi.index')
            ->with('success', $pesan);
    }

    // Upload foto before & after untuk reservasi yang sudah selesai
    // (dipakai galeri "Transformasi Kamu" di dashboard pelanggan)
    public function uploadTransformasi(Request $request, Reservation $reservasi)
    {
        if ($reservasi->status !== 'done') {
            return redirect()->route('admin.reservasi.index')
                ->with('error', 'Foto before/after hanya bisa diunggah untuk reservasi yang sudah selesai.');
        }

        $request->validate([
            'foto_before' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'foto_after' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto_before')) {
            if ($reservasi->foto_before) {
                Storage::disk('public')->delete($reservasi->foto_before);
            }
            $reservasi->foto_before = $request->file('foto_before')->store('transformasi', 'public');
        }

        if ($request->hasFile('foto_after')) {
            if ($reservasi->foto_after) {
                Storage::disk('public')->delete($reservasi->foto_after);
            }
            $reservasi->foto_after = $request->file('foto_after')->store('transformasi', 'public');
        }

        $reservasi->save();

        return redirect()->route('admin.reservasi.index')
            ->with('success', 'Foto before/after berhasil disimpan!');
    }
}