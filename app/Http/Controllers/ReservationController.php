<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCreated;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// Digunakan untuk menyimpan file ke folder storage Laravel
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    // Menampilkan daftar reservasi milik pelanggan yang sedang login
    public function index()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->with('service', 'queue')
            ->latest()
            ->get();

        return view('reservasi.index', compact('reservations'));
    }

    // Menampilkan form buat reservasi baru
    public function create()
    {
        // Ambil semua layanan untuk ditampilkan di form
        $services = Service::all();
        return view('reservasi.create', compact('services'));
    }

    // Menyimpan data reservasi baru ke database
    // Menyimpan data reservasi baru ke database
public function store(Request $request)
{
    $request->validate([

        'service_id'      => 'required|exists:services,id',
        'tanggal'         => 'required|date',
        'jam'             => 'required',
        'catatan'         => 'nullable|string',

        'payment_method'  => 'required|in:online,cod',
        // Wajib memilih channel jika pembayaran online
        'payment_channel' => 'required_if:payment_method,online|nullable|in:qris,bca,dana,gopay,shopeepay',

        'payment_proof'   => 'required_if:payment_method,online|nullable|image|mimes:jpg,jpeg,png|max:2048',

    ]);

    // Default bukti pembayaran kosong.
    // Digunakan jika pelanggan memilih metode COD.
    $paymentProofPath = null;

    // Jika pelanggan mengupload bukti pembayaran,
    // simpan file ke folder storage/app/public/payment_proofs
    if ($request->hasFile('payment_proof')) {

        $paymentProofPath = $request->file('payment_proof')
            ->store('payment_proofs', 'public');

    }

    // Menentukan status pembayaran berdasarkan metode pembayaran.
    // Online  -> Menunggu verifikasi admin.
    // COD     -> Belum dibayar.
    $paymentStatus = $request->payment_method == 'online'
        ? 'waiting_verification'
        : 'unpaid';

    // Simpan reservasi dulu
    $reservation = Reservation::create([

        // ID pelanggan yang login
        'user_id'    => Auth::id(),

        // Layanan yang dipilih
        'service_id' => $request->service_id,

        // Tanggal reservasi
        'tanggal'    => $request->tanggal,

        // Jam reservasi
        'jam'        => $request->jam,

        // Catatan pelanggan
        'catatan' => $request->catatan,

        // Metode pembayaran yang dipilih.
        // Nilai:
        // online
        // atau
        // cod
        'payment_method' => $request->payment_method,

        // Channel pembayaran yang dipilih.
        // Jika COD maka nilainya NULL.
        'payment_channel' => $request->payment_method == 'online'
            ? $request->payment_channel
            : null,

        // Lokasi file bukti pembayaran.
        // Akan bernilai NULL jika pelanggan memilih COD.
        'payment_proof' => $paymentProofPath,

        // Status pembayaran.
        // Online  -> waiting_verification
        // COD     -> unpaid
        'payment_status' => $paymentStatus,

        // Status reservasi
        'status' => 'pending',
    ]);

    // Ambil semua reservasi aktif di tanggal yang sama, urutkan berdasarkan jam
    $reservasiAktif = Reservation::where('tanggal', $request->tanggal)
        ->where('status', '!=', 'cancelled') // tidak termasuk yang dibatalkan
        ->orderBy('jam', 'asc') // urutkan dari jam terkecil ke terbesar
        ->get();

    // Update nomor antrian semua reservasi di tanggal tersebut
    foreach ($reservasiAktif as $index => $res) {
        Queue::updateOrCreate(
            ['reservation_id' => $res->id], // cari berdasarkan reservation_id
            ['nomor_antrian'  => $index + 1, 'status_antrian' => 'menunggu'] // update nomornya
        );
    }

    // Ambil nomor antrian reservasi yang baru dibuat
    $nomorAntrian = Queue::where('reservation_id', $reservation->id)
        ->value('nomor_antrian');
    
    Mail::to(Auth::user()->email)
        ->send(new ReservationCreated($reservation, $nomorAntrian));

    return redirect()->route('reservasi.index')
        ->with('success', 'Reservasi berhasil dibuat! Nomor antrian kamu: ' . $nomorAntrian);
}

    // Menampilkan detail reservasi
    public function show(Reservation $reservasi)
    {
        if ($reservasi->user_id !== Auth::id()) {
            abort(403);
        }
        return view('reservasi.show', compact('reservasi'));
    }

    // Membatalkan reservasi
    public function destroy(Reservation $reservasi)
    {
        if ($reservasi->user_id !== Auth::id()) {
            abort(403);
        }
        $reservasi->update(['status' => 'cancelled']);
        return redirect()->route('reservasi.index')
            ->with('success', 'Reservasi berhasil dibatalkan!');
    }
}                                                                                                                         