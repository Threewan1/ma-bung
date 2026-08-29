<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Tips grooming harian - satu tips ditampilkan secara acak tapi
    // konsisten sepanjang hari yang sama (lihat pemilihan index di
    // bawah, berdasarkan hari-ke-berapa dalam setahun).
    private const GROOMING_TIPS = [
        'Cuci rambut maksimal 2-3 hari sekali agar tidak merusak kelembapan alami kulit kepala.',
        'Gunakan air hangat kuku (bukan panas) saat keramas supaya minyak alami rambut tidak hilang total.',
        'Sisir jenggot searah pertumbuhannya setiap pagi agar terlihat lebih rapi seharian.',
        'Oleskan minyak jenggot (beard oil) setelah mandi selagi pori-pori masih terbuka, hasilnya lebih maksimal.',
        'Potong rambut rutin setiap 3-4 minggu supaya bentuk potongan tetap terjaga.',
        'Keringkan rambut dengan handuk secara ditepuk-tepuk, bukan digosok kasar, untuk mencegah rambut rontok.',
        'Gunakan pomade secukupnya - terlalu banyak justru membuat rambut terlihat lepek dan berminyak.',
        'Rutin membersihkan sisir dan sikat rambut agar tidak jadi sarang kotoran dan minyak.',
        'Konsumsi cukup protein dan air putih, karena kesehatan rambut juga dipengaruhi dari dalam tubuh.',
        'Hindari keramas dengan air terlalu panas, karena bisa membuat kulit kepala kering dan gatal.',
        'Gunakan sampo khusus sesuai jenis rambut/kulit kepala (berminyak, kering, atau ketombean).',
        'Jemur handuk dan alat cukur setelah dipakai supaya tidak lembap dan jadi sarang bakteri.',
        'Trim ujung jenggot secara berkala meski sedang dipanjangkan, supaya bentuknya tetap rapi.',
        'Pijat kulit kepala beberapa menit saat keramas untuk melancarkan sirkulasi darah dan pertumbuhan rambut.',
        'Ganti sarung bantal secara rutin - sarung bantal kotor bisa memicu iritasi kulit kepala dan wajah.',
    ];


    // Menampilkan dashboard pelanggan
    public function index()
    {
        $user = Auth::user();

        // Reservasi aktif dengan tanggal terdekat
        $reservasiTerdekat = $user->reservations()
            ->with(['service', 'queue'])
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->first();

        // Reminder: reservasi aktif untuk hari ini atau besok
        $reservasiSegera = $user->reservations()
            ->with('service')
            ->whereIn('tanggal', [now()->toDateString(), now()->addDay()->toDateString()])
            ->whereNotIn('status', ['cancelled', 'done'])
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->first();

        // Reminder: reservasi online yang pembayarannya belum kelar
        // (ambil satu yang tanggalnya paling dekat, supaya gaya banner-nya
        // konsisten dengan banner "Reservasi Segera" - satu item, satu
        // tombol "Lihat Detail").
        $reservasiPerluBayar = $user->reservations()
            ->with('service')
            ->where('payment_method', 'online')
            ->whereIn('payment_status', ['unpaid', 'waiting_verification'])
            ->whereNotIn('status', ['cancelled', 'done'])
            ->orderBy('tanggal')
            ->first();

        // Ajakan rating: reservasi selesai terbaru yang belum diberi rating
        $reservasiPerluRating = $user->reservations()
            ->with('service')
            ->where('status', 'done')
            ->whereNull('rating')
            ->orderByDesc('tanggal')
            ->first();

        // Jumlah reservasi yang sudah selesai bulan ini (untuk card Aksi Cepat)
        $reservasiSelesaiBulanIni = $user->reservations()
            ->where('status', 'done')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->count();

        // =============================================================
        // KARTU MEMBER DIGITAL
        // Level ditentukan dari total riwayat reservasi berstatus
        // "done" (selesai): Bronze 0-4, Silver 5-9, Gold 10+.
        // =============================================================
        $totalKunjungan = $user->reservations()->where('status', 'done')->count();

        $memberLevel = match (true) {
            $totalKunjungan >= 10 => 'gold',
            $totalKunjungan >= 5 => 'silver',
            default => 'bronze',
        };

        // Nomor member dari ID user, diformat mis. "MB-00001"
        $nomorMember = 'MB-' . str_pad($user->id, 5, '0', STR_PAD_LEFT);

        // =============================================================
        // BADGE / ACHIEVEMENT
        // =============================================================
        $jumlahJenisLayanan = $user->reservations()->distinct('service_id')->count('service_id');
        $bergabungBulanIni = $user->created_at->isSameMonth(now()) && $user->created_at->isSameYear(now());

        $badges = [];

        if ($totalKunjungan >= 5) {
            $badges[] = [
                'icon' => 'fa-heart',
                'label' => 'Pelanggan Setia',
                'deskripsi' => 'Sudah 5+ kali kunjungan selesai',
            ];
        }

        if ($jumlahJenisLayanan >= 3) {
            $badges[] = [
                'icon' => 'fa-fire',
                'label' => 'Trendsetter',
                'deskripsi' => 'Pernah coba 3+ jenis layanan berbeda',
            ];
        }

        if ($bergabungBulanIni) {
            $badges[] = [
                'icon' => 'fa-seedling',
                'label' => 'Member Baru',
                'deskripsi' => 'Bergabung bulan ini',
            ];
        }

        // =============================================================
        // "WAKTUNYA POTONG LAGI!"
        // Rata-rata interval hari antar kunjungan (dari riwayat "done"),
        // dibandingkan dengan jumlah hari sejak kunjungan terakhir.
        // Di-skip kalau riwayat selesai belum sampai 2 kali, atau kalau
        // pelanggan sudah punya reservasi aktif yang dijadwalkan.
        // =============================================================
        $waktunyaPotongLagi = null;

        $tanggalSelesai = $user->reservations()
            ->where('status', 'done')
            ->orderBy('tanggal')
            ->pluck('tanggal')
            ->map(fn ($tanggal) => Carbon::parse($tanggal));

        if ($tanggalSelesai->count() >= 2 && ! $reservasiTerdekat) {
            $selisihHari = [];

            foreach ($tanggalSelesai->slice(1)->values() as $index => $tanggal) {
                $selisihHari[] = $tanggalSelesai[$index]->diffInDays($tanggal);
            }

            $rataRataInterval = array_sum($selisihHari) / count($selisihHari);
            $hariSejakTerakhir = $tanggalSelesai->last()->diffInDays(now());

            if ($hariSejakTerakhir > $rataRataInterval) {
                $waktunyaPotongLagi = (int) round($hariSejakTerakhir);
            }
        }

        // =============================================================
        // TIPS GROOMING HARIAN
        // Index tips dipilih dari hari-ke-berapa dalam setahun, supaya
        // tipsnya konsisten sepanjang hari yang sama tapi biasanya
        // berbeda dari hari ke hari.
        // =============================================================
        $tipHariIni = self::GROOMING_TIPS[now()->dayOfYear % count(self::GROOMING_TIPS)];

        // =============================================================
        // UCAPAN ULANG TAHUN
        // Cocokkan bulan & tanggal saja (tahun diabaikan).
        // =============================================================
        $ulangTahunHariIni = $user->tanggal_lahir
            && $user->tanggal_lahir->format('m-d') === now()->format('m-d');

        // =============================================================
        // GALERI TRANSFORMASI (before & after)
        // Hanya reservasi selesai yang sudah punya KEDUA foto.
        // =============================================================
        $galeriTransformasi = $user->reservations()
            ->with('service')
            ->where('status', 'done')
            ->whereNotNull('foto_before')
            ->whereNotNull('foto_after')
            ->orderByDesc('tanggal')
            ->get();

        return view('dashboard', compact(
            'user',
            'reservasiTerdekat',
            'reservasiSegera',
            'reservasiPerluBayar',
            'reservasiPerluRating',
            'reservasiSelesaiBulanIni',
            'totalKunjungan',
            'memberLevel',
            'nomorMember',
            'badges',
            'waktunyaPotongLagi',
            'tipHariIni',
            'ulangTahunHariIni',
            'galeriTransformasi'
        ));
    }
}
