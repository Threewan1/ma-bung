<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PaymentConfirmed;
use App\Mail\ReservationCancelled;
use App\Mail\ReservationConfirmed;
use App\Models\Queue;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    // Semua reservasi untuk halaman Kelola Reservasi.
    public function index()
    {
        $reservations = Reservation::with('user', 'service', 'queue', 'barber')
            ->latest()
            ->get();

        return view('admin.reservasi.index', compact('reservations'));
    }

    // Dipoll tiap ~15 detik, cuma kirim signature ringan biar tabel tidak dirender ulang kalau tidak ada yang berubah.
    public function statusUpdates()
    {
        $reservations = Reservation::orderBy('id')->get(['id', 'status', 'payment_status']);

        return response()->json([
            'signature' => $reservations->map(fn ($r) => "{$r->id}:{$r->status}:{$r->payment_status}")->implode('|'),
        ]);
    }

    // Render ulang tab + tabel buat live-sync tanpa reload halaman penuh.
    public function tabContent()
    {
        $reservations = Reservation::with('user', 'service', 'queue', 'barber')
            ->latest()
            ->get();

        return view('admin.reservasi._tab-content', compact('reservations'));
    }

    // Admin cuma boleh ubah 3 status ini; "Sedang Dilayani"/"Selesai" wewenang barber lewat halaman kerjanya sendiri.
    public function update(Request $request, Reservation $reservasi)
    {
        // Reservasi online butuh bukti pembayaran dulu sebelum bisa dikonfirmasi, dicek di backend juga (bukan cuma disable di tampilan).
        $request->validate([
            'status' => [
                'required',
                'in:pending,confirmed,cancelled',
                function ($attribute, $value, $fail) use ($reservasi) {
                    if ($value === 'confirmed'
                        && $reservasi->payment_method === 'online'
                        && ! $reservasi->payment_proof) {
                        $fail('Reservasi online ini belum bisa dikonfirmasi karena bukti pembayaran belum diunggah pelanggan.');
                    }
                },
            ],
        ]);

        // Simpan status lama dulu, dipakai nentuin email mana yang perlu dikirim berdasarkan transisinya.
        $statusLama = $reservasi->status;

        $reservasi->update(['status' => $request->status]);

        // Cuma transisi Pending -> Dikonfirmasi yang dapat email, dibungkus try-catch biar gagal kirim tidak bikin request ini error.
        if ($statusLama === 'pending' && $reservasi->status === 'confirmed') {
            try {
                Mail::to($reservasi->user->email)
                    ->queue(new ReservationConfirmed($reservasi));
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email ReservationConfirmed', [
                    'reservasi_id' => $reservasi->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Guard biar tidak dobel kalau memang sudah cancelled sebelumnya.
        if ($statusLama !== 'cancelled' && $reservasi->status === 'cancelled') {
            Queue::aturUlangNomorAntrian($reservasi->tanggal);

            try {
                Mail::to($reservasi->user->email)
                    ->queue(new ReservationCancelled($reservasi));
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email ReservationCancelled', [
                    'reservasi_id' => $reservasi->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Tombol "Konfirmasi" AJAX di dashboard minta JSON, bukan redirect.
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $reservasi->status,
            ]);
        }

        return redirect()->route('admin.reservasi.index')
            ->with('success', 'Status reservasi berhasil diupdate!');
    }

    public function destroy(Reservation $reservasi)
    {
        // Simpan tanggalnya dulu, karena setelah dihapus reservasi lain di tanggal yang sama masih perlu dihitung ulang nomor antriannya.
        $tanggal = $reservasi->tanggal;

        $reservasi->delete();

        Queue::aturUlangNomorAntrian($tanggal);

        return redirect()->route('admin.reservasi.index')
            ->with('success', 'Reservasi berhasil dihapus!');
    }

    // Dipakai modal Konfirmasi/Tolak bukti online DAN tombol "Tandai Lunas" COD - terpisah dari update() karena "Selesai" tidak otomatis berarti "Lunas".
    public function updatePaymentStatus(Request $request, Reservation $reservasi)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,rejected',
        ]);

        $paymentStatusLama = $reservasi->payment_status;

        // Kosongkan payment_proof kalau ditolak, biar form upload ulang otomatis muncul lagi di halaman pelanggan.
        $updateData = ['payment_status' => $request->payment_status];

        if ($request->payment_status === 'rejected') {
            if ($reservasi->payment_proof) {
                Storage::disk('public')->delete($reservasi->payment_proof);
            }
            $updateData['payment_proof'] = null;
        }

        // Dipakai acuan bulan di laporan "Total Pendapatan Bulan Ini".
        if ($request->payment_status === 'paid') {
            $updateData['payment_confirmed_at'] = now();
        }

        $reservasi->update($updateData);

        // Cuma kirim email kalau memang baru berubah jadi "paid".
        if ($paymentStatusLama !== 'paid' && $reservasi->payment_status === 'paid') {
            try {
                Mail::to($reservasi->user->email)
                    ->queue(new PaymentConfirmed($reservasi));
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email PaymentConfirmed', [
                    'reservasi_id' => $reservasi->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $pesan = $request->payment_status === 'paid'
            ? 'Pembayaran berhasil dikonfirmasi (Lunas)!'
            : 'Bukti pembayaran ditolak.';

        return redirect()->route('admin.reservasi.index')
            ->with('success', $pesan);
    }

    // Upload foto before/after sudah dipindah ke Barber\DashboardController::uploadTransformasi().
}