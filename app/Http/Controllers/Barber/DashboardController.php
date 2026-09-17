<?php

namespace App\Http\Controllers\Barber;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    // Halaman kerja barber: cuma reservasi yang masih perlu ditindak (Dikonfirmasi/Sedang Dilayani, hari ini ke depan), urut jadwal paling dekat.
    public function index()
    {
        $barber = Auth::user()->barber;

        $reservasiPerluDitindak = $barber
            ? Reservation::with('user', 'service')
                ->where('barber_id', $barber->id)
                ->whereIn('status', ['confirmed', 'sedang_dilayani'])
                ->whereDate('tanggal', '>=', today())
                ->orderBy('tanggal')
                ->orderBy('jam')
                ->get()
            : collect();

        // Query terpisah dari $reservasiPerluDitindak - progress harian butuh SEMUA status hari ini termasuk yang sudah "done".
        $reservasiHariIni = $barber
            ? Reservation::where('barber_id', $barber->id)
                ->whereDate('tanggal', today())
                ->where('status', '!=', 'cancelled')
                ->get(['id', 'status'])
            : collect();

        $dilayaniBulanIni = $barber
            ? Reservation::where('barber_id', $barber->id)
                ->where('status', 'done')
                ->whereMonth('tanggal', today()->month)
                ->whereYear('tanggal', today()->year)
                ->count()
            : 0;

        $totalHariIni = $reservasiHariIni->count();
        $selesaiHariIni = $reservasiHariIni->where('status', 'done')->count();
        $progressHariIniPersen = $totalHariIni > 0
            ? intdiv($selesaiHariIni * 100, $totalHariIni)
            : 0;

        // Reservasi "Dikonfirmasi" hari ini yang jamnya paling dekat dan belum lewat.
        $sekarang = now();
        $pelangganBerikutnya = $reservasiPerluDitindak
            ->filter(fn ($r) => $r->status === 'confirmed' && Carbon::parse($r->tanggal)->isToday())
            ->first(fn ($r) => Carbon::parse($r->jam)->format('H:i:s') >= $sekarang->format('H:i:s'));

        return view('barber.dashboard', compact(
            'barber',
            'reservasiPerluDitindak',
            'dilayaniBulanIni',
            'totalHariIni',
            'selesaiHariIni',
            'progressHariIniPersen',
            'pelangganBerikutnya'
        ));
    }

    // Halaman "Riwayat Saya": semua reservasi Selesai/Dibatalkan milik barber ini, terbaru dulu.
    public function riwayat()
    {
        $barber = Auth::user()->barber;

        $reservasiRiwayat = $barber
            ? Reservation::with('user', 'service')
                ->where('barber_id', $barber->id)
                ->whereIn('status', ['done', 'cancelled'])
                ->orderByDesc('tanggal')
                ->orderByDesc('jam')
                ->get()
            : collect();

        return view('barber.riwayat', compact('barber', 'reservasiRiwayat'));
    }

    // AJAX "Mulai Layani"/"Selesai" buat reservasi milik barber sendiri, tanpa reload.
    public function updateStatus(Request $request, Reservation $reservasi)
    {
        $barber = Auth::user()->barber;

        if (! $barber || $reservasi->barber_id !== $barber->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:sedang_dilayani,done',
        ]);

        // Cuma 2 transisi maju yang sah, cegah barber "melompat" status.
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

        // Cuma status pelayanan yang berubah, status pembayaran tetap wewenang admin.
        $reservasi->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'status' => $reservasi->status,
        ]);
    }

    // Upload foto before/after, dipakai galeri "Transformasi Kamu" - syaratnya reservasi milik barber ini DAN sudah "done".
    public function uploadTransformasi(Request $request, Reservation $reservasi)
    {
        $barber = Auth::user()->barber;

        if (! $barber || $reservasi->barber_id !== $barber->id) {
            abort(403);
        }

        if ($reservasi->status !== 'done') {
            return back()->with('error', 'Foto before/after hanya bisa diunggah untuk reservasi yang sudah selesai.');
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

        return back()->with('success', 'Foto before/after berhasil disimpan!');
    }

    // Hapus foto before/after per-slot, dipakai kalau barber mau ganti foto yang sudah terlanjur diunggah.
    public function hapusTransformasi(Request $request, Reservation $reservasi, string $slot)
    {
        $barber = Auth::user()->barber;

        if (! $barber || $reservasi->barber_id !== $barber->id) {
            abort(403);
        }

        if ($reservasi->status !== 'done') {
            return back()->with('error', 'Foto before/after hanya bisa dihapus untuk reservasi yang sudah selesai.');
        }

        $kolom = 'foto_' . $slot;

        if ($reservasi->{$kolom}) {
            Storage::disk('public')->delete($reservasi->{$kolom});
            $reservasi->{$kolom} = null;
            $reservasi->save();
        }

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    // Dipoll JS halaman kerja barber biar status ter-update tanpa reload, dibatasi cuma reservasi milik barber yang login.
    public function statusUpdates()
    {
        $barber = Auth::user()->barber;

        $reservations = $barber
            ? Reservation::where('barber_id', $barber->id)->get(['id', 'status', 'payment_status'])->append('payment_badge')
            : collect();

        return response()->json($reservations);
    }
}
