<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Menampilkan halaman kerja barber - reservasi 7 hari ke depan
    // (hari ini s.d. 6 hari ke depan) yang di-assign ke barber yang
    // sedang login, dikelompokkan per tanggal (label nama hari
    // Indonesia + tanggal) dan diurutkan berdasarkan jam. Hari yang
    // tidak punya reservasi sama sekali disembunyikan di view (lihat
    // barber/dashboard.blade.php) - filter kosongnya dilakukan di sana,
    // bukan di sini, supaya view masih bisa cek "apakah semua 7 hari
    // kosong" untuk menampilkan pesan umum.
    public function index()
    {
        $barber = Auth::user()->barber;

        $jadwal = collect(range(0, 6))->map(function ($mundur) use ($barber) {
            $tanggal = today()->addDays($mundur);

            $reservasi = $barber
                ? Reservation::with('user', 'service')
                    ->where('barber_id', $barber->id)
                    ->whereDate('tanggal', $tanggal->toDateString())
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('jam')
                    ->get()
                : collect();

            return [
                'tanggal' => $tanggal->toDateString(),
                // Nama hari Indonesia + tanggal, mis. "Senin, 01 September 2026"
                'label' => $tanggal->translatedFormat('l, d F Y'),
                'reservasi' => $reservasi,
            ];
        });

        // Dipakai view untuk menampilkan pesan umum kalau ketujuh hari
        // sama sekali tidak ada reservasi (bukan cuma "hari ini" saja).
        $adaJadwal = $jadwal->contains(fn ($hari) => $hari['reservasi']->isNotEmpty());

        return view('barber.dashboard', compact('barber', 'jadwal', 'adaJadwal'));
    }

    // AJAX: barber mengubah status reservasi yang di-assign ke dirinya
    // sendiri - "Mulai Layani" (Dikonfirmasi -> Sedang Dilayani) atau
    // "Selesai" (Sedang Dilayani -> Selesai). Tanpa reload halaman.
    public function updateStatus(Request $request, Reservation $reservasi)
    {
        $barber = Auth::user()->barber;

        // Barber cuma boleh mengubah reservasi yang di-assign ke
        // dirinya sendiri, bukan milik barber lain.
        if (! $barber || $reservasi->barber_id !== $barber->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:sedang_dilayani,done',
        ]);

        // Hanya 2 transisi maju yang sah dari halaman ini - cegah
        // barber "melompat" status dari luar alur seharusnya (mis.
        // langsung dari pending ke selesai).
        $statusAwalYangValid = [
            'sedang_dilayani' => 'confirmed',
            'done' => 'sedang_dilayani',
        ];

        if ($reservasi->status !== $statusAwalYangValid[$request->status]) {
            return response()->json([
                'success' => false,
                'message' => 'Status reservasi ini sudah berubah. Silakan muat ulang halaman.',
            ], 422);
        }

        // Barber cuma mengubah status PELAYANAN (sedang_dilayani/done) -
        // status PEMBAYARAN sengaja TIDAK ikut diubah di sini, beda dari
        // Admin\ReservationController::update(). Konfirmasi pembayaran
        // tetap wewenang admin (lewat modal bukti pembayaran untuk
        // Online, atau tombol/dropdown status di panel admin untuk COD),
        // supaya barber selesai melayani tidak otomatis dianggap "sudah
        // dibayar" sebelum benar-benar dikonfirmasi admin.
        $reservasi->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'status' => $reservasi->status,
        ]);
    }

    // Endpoint JSON - dipoll berkala oleh JS di halaman kerja barber
    // supaya status pelayanan/pembayaran ter-update otomatis tanpa
    // reload, terutama saat ADMIN yang mengonfirmasi pembayaran
    // (barber tidak bisa mengubah payment_status sendiri - lihat
    // updateStatus() di atas). Dibatasi HANYA reservasi milik barber
    // yang sedang login - tidak membocorkan data reservasi barber lain.
    public function statusUpdates()
    {
        $barber = Auth::user()->barber;

        $reservations = $barber
            ? Reservation::where('barber_id', $barber->id)->get(['id', 'status', 'payment_status'])
            : collect();

        return response()->json($reservations);
    }
}
