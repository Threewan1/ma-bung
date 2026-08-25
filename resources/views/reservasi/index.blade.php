<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Saya - Ma'bung Barbershop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white">

    {{-- Navbar --}}
    <nav class="bg-gray-800 shadow-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-yellow-400">
                <i class="fas fa-cut"></i> Ma'bung Barbershop
            </div>
            <div class="space-x-4">
                <a href="/dashboard" class="text-white hover:text-yellow-400">Dashboard</a>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="text-white hover:text-yellow-400">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Konten --}}
    <div class="max-w-5xl mx-auto pt-24 pb-10 px-4">
        <h2 class="text-2xl font-bold text-yellow-400 mb-6">
            <i class="fas fa-list"></i> Reservasi Saya
        </h2>

        {{-- Pesan Sukses --}}
        @if(session('success'))
            <div class="bg-green-500 text-white p-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tombol Buat Reservasi --}}
        <a href="/reservasi/create"
            class="bg-yellow-400 text-gray-900 px-6 py-2 rounded-lg font-bold hover:bg-yellow-500 inline-block mb-6">
            <i class="fas fa-plus"></i> Buat Reservasi Baru
        </a>

        {{-- Tabel Reservasi --}}
        @if($reservations->isEmpty())
            <div class="bg-gray-800 p-6 rounded-lg text-center text-gray-400">
                <i class="fas fa-calendar-times text-5xl mb-4"></i>
                <p>Belum ada reservasi. Buat reservasi sekarang!</p>
            </div>
        @else
            <div class="bg-gray-800 rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-700">

                        <tr>

                            {{-- Nomor --}}
                            <th class="px-4 py-3 text-left">No</th>

                            {{-- Nama layanan --}}
                            <th class="px-4 py-3 text-left">Layanan</th>

                            {{-- Tanggal reservasi --}}
                            <th class="px-4 py-3 text-left">Tanggal</th>

                            {{-- Jam reservasi --}}
                            <th class="px-4 py-3 text-left">Jam</th>

                            {{-- Metode pembayaran --}}
                            <th class="px-4 py-3 text-left">Pembayaran</th>

                            {{-- Status pembayaran --}}
                            <th class="px-4 py-3 text-left">Status Bayar</th>

                            {{-- Nomor antrean --}}
                            <th class="px-4 py-3 text-left">No. Antrian</th>

                            {{-- Status reservasi --}}
                            <th class="px-4 py-3 text-left">Status Reservasi</th>

                            {{-- Tombol aksi --}}
                            <th class="px-4 py-3 text-left">Aksi</th>

                        </tr>

                    </thead>
                    <tbody>
                        {{-- Melakukan perulangan terhadap seluruh data reservasi.
                            Nama $reservations harus sama dengan variabel
                            yang dikirim oleh ReservasiController. --}}
                        @foreach($reservations as $reservasi)

                            {{-- Baris untuk satu reservasi --}}
                            <tr class="border-b border-gray-700 hover:bg-gray-800">

                                {{-- ========================================= --}}
                                {{-- NOMOR --}}
                                {{-- ========================================= --}}
                                <td class="px-4 py-3">
                                    {{-- Menampilkan nomor urut --}}
                                    {{ $loop->iteration }}
                                </td>


                                {{-- ========================================= --}}
                                {{-- LAYANAN --}}
                                {{-- ========================================= --}}
                                <td class="px-4 py-3">
                                    {{-- Menampilkan nama layanan --}}
                                    {{ $reservasi->service->nama_layanan ?? '-' }}
                                </td>


                                {{-- ========================================= --}}
                                {{-- TANGGAL --}}
                                {{-- ========================================= --}}
                                <td class="px-4 py-3">
                                    {{-- Format tanggal reservasi --}}
                                    {{ \Carbon\Carbon::parse($reservasi->tanggal)->format('d/m/Y') }}
                                </td>


                                {{-- ========================================= --}}
                                {{-- JAM --}}
                                {{-- ========================================= --}}
                                <td class="px-4 py-3">
                                    {{-- Menampilkan jam reservasi --}}
                                    {{ $reservasi->jam ?? '-' }}
                                </td>


                                {{-- ========================================= --}}
                                {{-- PEMBAYARAN --}}
                                {{-- ========================================= --}}
                                <td class="px-4 py-3">

                                    {{-- 
                                        Menampilkan metode pembayaran.
                                        Contoh:
                                        - cash
                                        - transfer
                                        - qris
                                    --}}
                                    <div class="font-medium">
                                        {{ ucfirst($reservasi->payment_method ?? '-') }}
                                    </div>

                                    {{-- 
                                        Menampilkan channel pembayaran.
                                        Contoh:
                                        - QRIS
                                        - DANA
                                        - GoPay
                                        - BCA
                                    --}}

                                </td>


                                {{-- ========================================= --}}
                                {{-- STATUS PEMBAYARAN --}}
                                {{-- ========================================= --}}
                                <td class="px-4 py-3">

                                    {{-- 
                                        Status pembayaran dibuat menjadi badge
                                        agar lebih mudah dibaca.
                                    --}}
                                    @php
                                        $paymentStatus = strtolower($reservasi->payment_status ?? 'pending');
                                    @endphp


                                    {{-- STATUS PENDING --}}
                                    @if($paymentStatus === 'pending')

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{-- Ikon status pending --}}
                                            <i class="fas fa-clock mr-1"></i>

                                            {{-- Teks status --}}
                                            Pending
                                        </span>


                                    {{-- STATUS PAID / BERHASIL --}}
                                    @elseif(in_array($paymentStatus, ['paid', 'success', 'settlement']))

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{-- Ikon pembayaran berhasil --}}
                                            <i class="fas fa-check-circle mr-1"></i>

                                            {{-- Teks status --}}
                                            Lunas
                                        </span>


                                    {{-- STATUS FAILED / GAGAL --}}
                                    @elseif(in_array($paymentStatus, ['failed', 'deny', 'cancel', 'expired']))

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{-- Ikon pembayaran gagal --}}
                                            <i class="fas fa-times-circle mr-1"></i>

                                            {{-- Teks status --}}
                                            Gagal
                                        </span>


                                    {{-- STATUS LAINNYA --}}
                                    @else

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{-- Ikon status lainnya --}}
                                            <i class="fas fa-info-circle mr-1"></i>

                                            {{-- Menampilkan status asli --}}
                                            {{ ucfirst($paymentStatus) }}
                                        </span>

                                    @endif

                                </td>


                                {{-- ========================================= --}}
                                {{-- NOMOR ANTRIAN --}}
                                {{-- ========================================= --}}
                                <td class="px-4 py-3">

                                    {{-- 
                                        Menampilkan nomor antrean.
                                        Jika belum mendapatkan nomor antrean,
                                        tampilkan tanda "-".
                                    --}}
                                    {{ $reservasi->queue?->nomor_antrian ?? '-' }}

                                </td>


                                {{-- ========================================= --}}
                                {{-- STATUS RESERVASI --}}
                                {{-- ========================================= --}}
                                <td class="px-4 py-3">

                                    @php
                                        // Mengambil status reservasi.
                                        $reservationStatus = strtolower($reservasi->status ?? 'pending');
                                    @endphp


                                    {{-- STATUS PENDING --}}
                                    @if($reservationStatus === 'pending')

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>


                                    {{-- STATUS CONFIRMED --}}
                                    @elseif(in_array($reservationStatus, ['confirmed', 'confirm']))

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-check mr-1"></i>
                                            Dikonfirmasi
                                        </span>


                                    {{-- STATUS COMPLETED --}}
                                    @elseif(in_array($reservationStatus, ['completed', 'complete', 'selesai']))

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-double mr-1"></i>
                                            Selesai
                                        </span>


                                    {{-- STATUS CANCELLED --}}
                                    @elseif(in_array($reservationStatus, ['cancelled', 'canceled', 'batal']))

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>
                                            Dibatalkan
                                        </span>


                                    {{-- STATUS LAINNYA --}}
                                    @else

                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ ucfirst($reservationStatus) }}
                                        </span>

                                    @endif

                                </td>


                                {{-- ========================================= --}}
                                {{-- AKSI --}}
                                {{-- ========================================= --}}
                                <td class="px-4 py-3">

                                    <div class="flex items-center gap-2">

                                        {{-- ================================= --}}
                                        {{-- TOMBOL DETAIL --}}
                                        {{-- ================================= --}}

                                        <a
                                            href="{{ route('reservasi.show', $reservasi->id) }}"
                                            class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm transition"
                                        >
                                            {{-- Ikon mata --}}
                                            <i class="fas fa-eye mr-1"></i>

                                            {{-- Teks tombol --}}
                                            Detail
                                        </a>


                                        {{-- ================================= --}}
                                        {{-- TOMBOL BATALKAN --}}
                                        {{-- ================================= --}}

                                        {{-- 
                                            Tombol Batalkan hanya ditampilkan
                                            jika status reservasi masih pending.
                                        --}}
                                        @if($reservationStatus === 'pending')

                                            <form
                                                action="{{ route('reservasi.destroy', $reservasi->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')"
                                            >

                                                {{-- Proteksi CSRF Laravel --}}
                                                @csrf

                                                {{-- Method DELETE untuk menghapus/membatalkan --}}
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm transition"
                                                >
                                                    {{-- Ikon batal --}}
                                                    <i class="fas fa-times mr-1"></i>

                                                    {{-- Teks tombol --}}
                                                    Batalkan
                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</body>
</html>