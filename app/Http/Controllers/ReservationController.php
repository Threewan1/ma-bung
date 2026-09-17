<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCreated;
use App\Mail\ReservationCancelled;
use App\Models\Barber;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    // Jam operasional: 10.00-22.00, istirahat 15.00-16.00.
    private const JAM_SLOTS = ['10:00', '11:00', '12:00', '13:00', '14:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'];

    // Kapasitas per jam = jumlah barber aktif (satu barber, satu reservasi per jam).
    private function jumlahBarberAktif(): int
    {
        return Barber::where('status_aktif', true)->count();
    }

    // Cari barber_id yang sudah terisi per jam di satu tanggal, dipakai jamTersedia() dan validasi ulang di store().
    private function hitungBarberTerpakaiPerJam(string $tanggal): array
    {
        return Reservation::where('tanggal', $tanggal)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('barber_id')
            ->get(['jam', 'barber_id'])
            ->groupBy(fn ($r) => substr($r->jam, 0, 5))
            ->map(fn ($grup) => $grup->pluck('barber_id')->all())
            ->toArray();
    }

    // Daftar reservasi pelanggan yang login, bisa difilter lewat ?status=done.
    public function index(Request $request)
    {
        $query = Reservation::where('user_id', Auth::id())
            ->with('service', 'queue')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->get();

        return view('reservasi.index', compact('reservations'));
    }

    // Endpoint polling buat dashboard & "Reservasi Saya", biar status/pembayaran/antrian ter-update tanpa reload.
    // reservasiSelesaiBulanIni dihitung di server (bukan di JS) biar tidak meleset gara-gara jam/zona waktu browser beda-beda.
    public function statusUpdates()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->with('queue')
            ->get();

        $reservasiSelesaiBulanIni = $reservations
            ->where('status', 'done')
            ->filter(fn ($reservasi) => \Carbon\Carbon::parse($reservasi->tanggal)->isSameMonth(now()))
            ->count();

        return response()->json([
            'reservations' => $reservations->map(fn ($reservasi) => [
                'id' => $reservasi->id,
                'status' => $reservasi->status,
                'payment_status' => $reservasi->payment_status,
                'payment_badge' => $reservasi->payment_badge,
                'nomor_antrian' => $reservasi->queue?->nomor_antrian,
            ])->values(),
            'reservasiSelesaiBulanIni' => $reservasiSelesaiBulanIni,
        ]);
    }

    // Form buat reservasi baru.
    public function create()
    {
        $services = Service::all();

        // Cuma barber aktif yang boleh dipilih.
        $barbers = Barber::where('status_aktif', true)->orderBy('nama')->get();

        return view('reservasi.create', compact('services', 'barbers'));
    }

    // Endpoint AJAX: sisa slot tiap jam di 1 tanggal, dipakai form reservasi biar jam penuh langsung tampil disabled.
    public function jamTersedia(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ]);

        $totalBarberAktif = $this->jumlahBarberAktif();
        $barberTerpakai = $this->hitungBarberTerpakaiPerJam($request->tanggal);
        $tanggal = $request->tanggal;
        $sekarang = now();

        $slots = collect(self::JAM_SLOTS)->map(function ($jam) use ($barberTerpakai, $totalBarberAktif, $tanggal, $sekarang) {
            $jumlahTerpakai = count($barberTerpakai[$jam] ?? []);
            $sisa = max(0, $totalBarberAktif - $jumlahTerpakai);

            // Jam yang sudah lewat (kalau tanggalnya hari ini) tidak boleh dipilih lagi.
            $sudahLewat = \Carbon\Carbon::parse($tanggal . ' ' . $jam)->lessThanOrEqualTo($sekarang);

            return [
                'jam' => $jam,
                'sisa' => $sisa,
                'penuh' => $sisa <= 0,
                'sudah_lewat' => $sudahLewat,
            ];
        });

        return response()->json($slots);
    }

    // Endpoint AJAX: ketersediaan tiap barber aktif di 1 tanggal+jam, dipakai form reservasi buat nandain "Sedang Bertugas".
    public function barberTersedia(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam' => 'required',
        ]);

        $barberTerpakaiIds = Reservation::where('tanggal', $request->tanggal)
            ->where('jam', $request->jam)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('barber_id')
            ->pluck('barber_id');

        $barbers = Barber::where('status_aktif', true)
            ->orderBy('nama')
            ->get()
            ->map(fn ($barber) => [
                'id' => $barber->id,
                'nama' => $barber->nama,
                'foto' => $barber->foto ? asset('storage/' . $barber->foto) : null,
                'tersedia' => ! $barberTerpakaiIds->contains($barber->id),
            ]);

        return response()->json($barbers);
    }

    // Menyimpan reservasi baru.
public function store(Request $request)
{
    $request->validate([

        'service_id'      => 'required|exists:services,id',
        'barber_id'       => 'required|exists:barbers,id',
        'tanggal'         => 'required|date',
        'jam'             => 'required',
        'catatan'         => 'nullable|string',

        'payment_method'  => 'required|in:online,cod',
        // Wajib pilih channel kalau metodenya online.
        'payment_channel' => 'required_if:payment_method,online|nullable|in:qris,bri',

        // Wajib upload bukti kalau online, boleh kosong kalau COD.
        'payment_proof'   => 'required_if:payment_method,online|nullable|image|mimes:jpg,jpeg,png|max:2048',

    ], [
        'barber_id.required' => 'Silakan pilih barber terlebih dahulu.',
        'barber_id.exists' => 'Barber yang dipilih tidak valid.',
        'payment_proof.required_if' => 'Bukti pembayaran wajib diunggah untuk metode pembayaran Online.',
        'payment_channel.required_if' => 'Silakan pilih salah satu metode pembayaran online (QRIS/Transfer Bank).',
    ]);

    // Cek ulang di backend (bukan cuma andalkan AJAX) biar tidak race condition kalau 2 pelanggan submit barber+jam yang sama bersamaan.
    $barberSudahTerisi = Reservation::where('tanggal', $request->tanggal)
        ->where('jam', $request->jam)
        ->where('barber_id', $request->barber_id)
        ->where('status', '!=', 'cancelled')
        ->exists();

    if ($barberSudahTerisi) {
        return back()->withErrors([
            'barber_id' => 'Maaf, barber ini baru saja dipesan pelanggan lain di jam yang sama. Silakan pilih barber lain atau jam lain.',
        ])->withInput();
    }

    // Cek ulang di backend, jangan cuma andalkan disable di frontend yang bisa ketinggalan waktu.
    if (\Carbon\Carbon::parse($request->tanggal . ' ' . $request->jam)->lessThanOrEqualTo(now())) {
        return back()->withErrors([
            'jam' => 'Jam yang dipilih sudah lewat. Silakan pilih jam lain.',
        ])->withInput();
    }

    // COD tidak upload bukti, jadi default-nya kosong.
    $paymentProofPath = null;

    if ($request->hasFile('payment_proof')) {
        $paymentProofPath = $request->file('payment_proof')
            ->store('payment_proofs', 'public');
    }

    // Online nunggu diverifikasi admin, COD dianggap belum bayar dulu.
    $paymentStatus = $request->payment_method == 'online'
        ? 'waiting_verification'
        : 'unpaid';

    $reservation = Reservation::create([
        'user_id'    => Auth::id(),
        'service_id' => $request->service_id,

        // Simpan harga saat ini biar tidak ikut berubah kalau harga layanan diedit belakangan.
        'harga_snapshot' => Service::find($request->service_id)?->harga,

        'barber_id' => $request->barber_id,
        'tanggal'    => $request->tanggal,
        'jam'        => $request->jam,
        'catatan' => $request->catatan,
        'payment_method' => $request->payment_method,

        // NULL kalau COD, karena cuma pembayaran online yang punya channel.
        'payment_channel' => $request->payment_method == 'online'
            ? $request->payment_channel
            : null,

        'payment_proof' => $paymentProofPath,
        'payment_status' => $paymentStatus,
        'status' => 'pending',
    ]);

    // Hitung ulang nomor antrian di tanggal ini (termasuk reservasi baru ini).
    Queue::aturUlangNomorAntrian($request->tanggal);

    $nomorAntrian = Queue::where('reservation_id', $reservation->id)
        ->value('nomor_antrian');

    // Butuh queue worker jalan supaya emailnya benar-benar terkirim, dibungkus try-catch biar reservasinya tetap tersimpan walau pengiriman gagal.
    try {
        Mail::to(Auth::user()->email)
            ->queue(new ReservationCreated($reservation, $nomorAntrian));
    } catch (\Throwable $e) {
        Log::error('Gagal mengirim email ReservationCreated', [
            'reservasi_id' => $reservation->id,
            'error' => $e->getMessage(),
        ]);
    }

    return redirect()->route('reservasi.index')
        ->with('success', 'Reservasi berhasil dibuat! Nomor antrian kamu: ' . $nomorAntrian);
}

    // Detail satu reservasi.
    public function show(Reservation $reservasi)
    {
        if ($reservasi->user_id !== Auth::id()) {
            abort(403);
        }
        return view('reservasi.show', compact('reservasi'));
    }

    // Membatalkan reservasi.
    public function destroy(Reservation $reservasi)
    {
        if ($reservasi->user_id !== Auth::id()) {
            abort(403);
        }

        // Yang sudah "done" tidak boleh dibatalkan lagi.
        if ($reservasi->status === 'done') {
            return redirect()->route('reservasi.index')
                ->with('error', 'Reservasi yang sudah selesai tidak bisa dibatalkan.');
        }

        // Cek dulu sebelum update, biar tidak kirim email pembatalan dobel kalau ternyata sudah cancelled.
        $sudahDibatalkan = $reservasi->status === 'cancelled';

        $reservasi->update(['status' => 'cancelled']);

        // Hitung ulang nomor antrian sisa reservasi aktif di tanggal yang sama.
        Queue::aturUlangNomorAntrian($reservasi->tanggal);

        if (! $sudahDibatalkan) {
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

        return redirect()->route('reservasi.index')
            ->with('success', 'Reservasi berhasil dibatalkan!');
    }

    // Upload (ulang) bukti pembayaran, dipakai reservasi online yang buktinya masih kosong atau baru saja ditolak admin.
    public function uploadBukti(Request $request, Reservation $reservasi)
    {
        if ($reservasi->user_id !== Auth::id()) {
            abort(403);
        }

        if ($reservasi->payment_method !== 'online') {
            abort(403);
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($reservasi->payment_proof) {
            Storage::disk('public')->delete($reservasi->payment_proof);
        }

        $reservasi->update([
            'payment_proof' => $request->file('payment_proof')->store('payment_proofs', 'public'),
            'payment_status' => 'waiting_verification',
        ]);

        return redirect()->route('reservasi.show', $reservasi->id)
            ->with('success', 'Bukti pembayaran berhasil diunggah, menunggu verifikasi admin.');
    }

    // Simpan rating & review untuk reservasi yang sudah selesai.
    public function rate(Request $request, Reservation $reservasi)
    {
        if ($reservasi->user_id !== Auth::id()) {
            abort(403);
        }

        if ($reservasi->status !== 'done') {
            return redirect()->route('dashboard')
                ->with('error', 'Rating hanya bisa diberikan untuk reservasi yang sudah selesai.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $reservasi->update([
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Terima kasih atas rating dan ulasan kamu!');
    }
}                                                                                                                         