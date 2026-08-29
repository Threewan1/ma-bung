<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Queue;

class DashboardController extends Controller
{
    // Jam-jam operasional - HARUS sama dengan
    // App\Http\Controllers\ReservationController::JAM_SLOTS (dipakai
    // untuk menghitung total slot maksimal per hari di card "Kapasitas
    // Hari Ini"). Kapasitas per jam SEKARANG dihitung dinamis dari
    // jumlah barber aktif (satu barber = satu slot per jam), bukan
    // angka tetap lagi.
    private const JAM_SLOTS = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

    // Menghitung semua angka statistik di baris kartu paling atas -
    // dipisah dari index() supaya bisa dipakai ulang oleh endpoint
    // JSON stats() (dipoll berkala oleh JS di dashboard supaya
    // angkanya ter-update otomatis tanpa reload halaman).
    private function hitungStatistik(): array
    {
        // Hitung total semua reservasi
        $totalReservasi = Reservation::count();

        // Hitung total semua layanan
        $totalLayanan = Service::count();

        // Hitung antrian hari ini
        $antrianHariIni = Queue::whereDate('created_at', today())->count();

        // Reservasi yang menunggu konfirmasi admin
        $menungguKonfirmasi = Reservation::where('status', 'pending')->count();

        // Reservasi online yang bukti pembayarannya belum diverifikasi
        // (termasuk yang belum sempat upload bukti sama sekali)
        $perluVerifikasiPembayaran = Reservation::where('payment_method', 'online')
            ->whereIn('payment_status', ['waiting_verification', 'unpaid'])
            ->count();

        // Total pendapatan bulan berjalan - dari reservasi yang sudah
        // selesai DAN pembayarannya lunas
        $totalPendapatanBulanIni = Reservation::where('status', 'done')
            ->where('payment_status', 'paid')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->with('service')
            ->get()
            ->sum(fn ($reservasi) => $reservasi->service->harga ?? 0);

        // Kapasitas hari ini - jumlah slot terisi (reservasi aktif, di
        // luar yang dibatalkan) vs total slot maksimal (jumlah jam
        // operasional x jumlah barber yang sedang aktif)
        $totalSlotHariIni = count(self::JAM_SLOTS) * Barber::where('status_aktif', true)->count();
        $slotTerisiHariIni = Reservation::whereDate('tanggal', today())
            ->where('status', '!=', 'cancelled')
            ->count();

        return [
            'totalReservasi' => $totalReservasi,
            'totalLayanan' => $totalLayanan,
            'antrianHariIni' => $antrianHariIni,
            'menungguKonfirmasi' => $menungguKonfirmasi,
            'perluVerifikasiPembayaran' => $perluVerifikasiPembayaran,
            'totalPendapatanBulanIni' => $totalPendapatanBulanIni,
            'totalPendapatanBulanIniFormatted' => 'Rp ' . number_format($totalPendapatanBulanIni, 0, ',', '.'),
            'totalSlotHariIni' => $totalSlotHariIni,
            'slotTerisiHariIni' => $slotTerisiHariIni,
        ];
    }

    // Menampilkan halaman dashboard admin dengan data statistik
    public function index()
    {
        $statistik = $this->hitungStatistik();

        // Ambil 5 reservasi terbaru beserta relasi user dan service
        $reservasiTerbaru = Reservation::with('user', 'service')
            ->latest()
            ->take(5)
            ->get();

        // Semua reservasi untuk hari ini, diurutkan berdasarkan jam
        // (section terpisah "Reservasi Hari Ini")
        $reservasiHariIni = Reservation::with('user', 'service')
            ->whereDate('tanggal', today())
            ->orderBy('jam')
            ->get();

        // Tren jumlah reservasi per hari untuk 7 hari terakhir (termasuk
        // hari ini) - dipakai grafik bar chart
        $trenReservasi = collect(range(6, 0))->map(function ($mundur) {
            $tanggal = today()->subDays($mundur);

            return [
                'label' => $tanggal->translatedFormat('d/m'),
                'jumlah' => Reservation::whereDate('tanggal', $tanggal)->count(),
            ];
        })->values();

        // Kirim semua data ke view dashboard admin
        return view('admin.dashboard', array_merge($statistik, compact(
            'reservasiTerbaru',
            'reservasiHariIni',
            'trenReservasi'
        )));
    }

    // Endpoint JSON - dipanggil berkala (polling) oleh JS di dashboard
    // supaya kartu statistik ter-update otomatis tanpa reload halaman
    // saat ada reservasi baru masuk.
    public function stats()
    {
        return response()->json($this->hitungStatistik());
    }
}