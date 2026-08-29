<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCreated;
use App\Models\Barber;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// Digunakan untuk menyimpan file ke folder storage Laravel
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    // Jam-jam operasional yang bisa dipilih pelanggan saat reservasi.
    private const JAM_SLOTS = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

    // Kapasitas per slot jam SEKARANG dihitung per-barber (satu barber
    // cuma bisa menangani 1 reservasi per jam) - bukan angka tetap "3"
    // lagi, tapi jumlah barber yang sedang aktif (status_aktif=true).
    // Jam dianggap benar-benar penuh hanya kalau SEMUA barber aktif
    // sudah terisi di jam itu.
    private function jumlahBarberAktif(): int
    {
        return Barber::where('status_aktif', true)->count();
    }

    // Menghitung barber_id mana saja yang sudah terisi (reservasi aktif,
    // bukan "cancelled") di setiap slot jam untuk satu tanggal tertentu -
    // dipakai bersama oleh endpoint AJAX jamTersedia() dan validasi
    // ulang di store().
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

    // Menampilkan daftar reservasi milik pelanggan yang sedang login
    // Bisa difilter berdasarkan status lewat query string, mis. ?status=done
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

    // Endpoint JSON - dipanggil berkala (polling) oleh JS di dashboard
    // dan halaman "Reservasi Saya" supaya status reservasi (dan status
    // pembayaran/nomor antrian) ter-update otomatis begitu admin
    // mengubahnya, tanpa pelanggan perlu reload halaman.
    //
    // "reservasiSelesaiBulanIni" DIHITUNG DI SERVER (bukan diserahkan ke
    // JS untuk dihitung ulang dari tanggal tiap reservasi) supaya selalu
    // konsisten dengan angka yang dipakai saat render awal halaman
    // (DashboardController) - kalau dihitung di JS pakai jam/tanggal
    // browser (new Date()), hasilnya bisa meleset kalau jam/zona waktu
    // perangkat pelanggan berbeda dari server.
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
                'nomor_antrian' => $reservasi->queue?->nomor_antrian,
            ])->values(),
            'reservasiSelesaiBulanIni' => $reservasiSelesaiBulanIni,
        ]);
    }

    // Menampilkan form buat reservasi baru
    public function create()
    {
        // Ambil semua layanan untuk ditampilkan di form
        $services = Service::all();

        // Barber aktif untuk pilihan awal (non-aktif sebenarnya tidak
        // pernah dikirim ke halaman ini - lihat query Barber::where
        // status_aktif di controller/endpoint lain juga)
        $barbers = Barber::where('status_aktif', true)->orderBy('nama')->get();

        return view('reservasi.create', compact('services', 'barbers'));
    }

    // Endpoint AJAX: mengembalikan sisa slot untuk setiap jam pada
    // tanggal tertentu, dipakai form buat reservasi supaya jam yang
    // sudah penuh langsung tampil disabled tanpa reload halaman.
    // Kapasitas per jam = jumlah barber yang sedang aktif (satu barber
    // cuma menangani 1 reservasi per jam) - bukan angka tetap lagi.
    public function jamTersedia(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ]);

        $totalBarberAktif = $this->jumlahBarberAktif();
        $barberTerpakai = $this->hitungBarberTerpakaiPerJam($request->tanggal);

        $slots = collect(self::JAM_SLOTS)->map(function ($jam) use ($barberTerpakai, $totalBarberAktif) {
            $jumlahTerpakai = count($barberTerpakai[$jam] ?? []);
            $sisa = max(0, $totalBarberAktif - $jumlahTerpakai);

            return [
                'jam' => $jam,
                'sisa' => $sisa,
                'penuh' => $sisa <= 0,
            ];
        });

        return response()->json($slots);
    }

    // Endpoint AJAX: mengembalikan status ketersediaan tiap barber aktif
    // untuk tanggal+jam tertentu - dipakai form buat reservasi supaya
    // barber yang sudah terisi di jam itu tampil disabled dengan label
    // "Sedang Bertugas".
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
                'tersedia' => ! $barberTerpakaiIds->contains($barber->id),
            ]);

        return response()->json($barbers);
    }

    // Menyimpan data reservasi baru ke database
    // Menyimpan data reservasi baru ke database
public function store(Request $request)
{
    $request->validate([

        'service_id'      => 'required|exists:services,id',
        'barber_id'       => 'required|exists:barbers,id',
        'tanggal'         => 'required|date',
        'jam'             => 'required',
        'catatan'         => 'nullable|string',

        'payment_method'  => 'required|in:online,cod',
        // Wajib memilih channel jika pembayaran online
        'payment_channel' => 'required_if:payment_method,online|nullable|in:qris,bca,dana,gopay,shopeepay',

        // Wajib upload bukti pembayaran kalau metode Online, tapi
        // nullable (boleh kosong) kalau metode-nya COD.
        'payment_proof'   => 'required_if:payment_method,online|nullable|image|mimes:jpg,jpeg,png|max:2048',

    ], [
        'barber_id.required' => 'Silakan pilih barber terlebih dahulu.',
        'barber_id.exists' => 'Barber yang dipilih tidak valid.',
        'payment_proof.required_if' => 'Bukti pembayaran wajib diunggah untuk metode pembayaran Online.',
        'payment_channel.required_if' => 'Silakan pilih salah satu metode pembayaran online (QRIS/Transfer Bank/DANA/GoPay/ShopeePay).',
    ]);

    // Cek ulang ketersediaan BARBER YANG DIPILIH di sisi backend (bukan
    // cuma andalkan AJAX di frontend) supaya tidak kena race condition -
    // misalnya dua pelanggan sama-sama submit barber & jam yang sama
    // nyaris bersamaan. Reservasi yang statusnya "cancelled" tidak
    // dihitung, jadi slot-nya kembali tersedia untuk pelanggan lain.
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

        // Barber yang dipilih pelanggan
        'barber_id' => $request->barber_id,

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

    // Menyimpan rating & review pelanggan untuk reservasi yang sudah selesai
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