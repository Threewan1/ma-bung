<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Reservation;
use App\Models\Service;

class DashboardController extends Controller
{
    // Harus sama persis dengan ReservationController::JAM_SLOTS.
    private const JAM_SLOTS = ['10:00', '11:00', '12:00', '13:00', '14:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'];

    // Semua angka statistik kartu atas, dipisah dari index() biar bisa dipakai ulang endpoint stats() (polling).
    private function hitungStatistik(): array
    {
        $totalReservasi = Reservation::count();
        $totalLayanan = Service::count();

        // Reservasi aktif hari ini, bukan jumlah baris "queues" yang bisa mencampur tanggal lain.
        $antrianHariIni = Reservation::whereDate('tanggal', today())
            ->whereNotIn('status', ['cancelled', 'done'])
            ->count();

        $menungguKonfirmasi = Reservation::where('status', 'pending')->count();

        // Termasuk yang belum sempat upload bukti sama sekali.
        $perluVerifikasiPembayaran = Reservation::where('payment_method', 'online')
            ->whereIn('payment_status', ['waiting_verification', 'unpaid'])
            ->count();

        // Bulan & harga diambil dari payment_confirmed_at/harga_snapshot (bukan tanggal jadwal/harga layanan sekarang), biar laporan bulan lalu tidak ikut berubah.
        $totalPendapatanBulanIni = Reservation::where('status', 'done')
            ->where('payment_status', 'paid')
            ->whereMonth('payment_confirmed_at', now()->month)
            ->whereYear('payment_confirmed_at', now()->year)
            ->sum('harga_snapshot');

        // Kapasitas = jumlah jam operasional x jumlah barber aktif.
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

    // Halaman dashboard admin.
    public function index()
    {
        $statistik = $this->hitungStatistik();

        $reservasiTerbaru = Reservation::with('user', 'service')
            ->latest()
            ->take(5)
            ->get();

        // Section terpisah "Reservasi Hari Ini", diurutkan jam.
        $reservasiHariIni = Reservation::with('user', 'service')
            ->whereDate('tanggal', today())
            ->orderBy('jam')
            ->get();

        // 7 hari terakhir termasuk hari ini, buat grafik bar chart.
        $trenReservasi = collect(range(6, 0))->map(function ($mundur) {
            $tanggal = today()->subDays($mundur);

            return [
                'label' => $tanggal->translatedFormat('d/m'),
                'jumlah' => Reservation::whereDate('tanggal', $tanggal)->count(),
            ];
        })->values();

        return view('admin.dashboard', array_merge($statistik, compact(
            'reservasiTerbaru',
            'reservasiHariIni',
            'trenReservasi'
        )));
    }

    // Dipoll berkala oleh JS dashboard biar kartu statistik ter-update tanpa reload.
    public function stats()
    {
        return response()->json($this->hitungStatistik());
    }
}