<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Reservasi - Ma'bung Barbershop</title>
    <!-- Tailwind CSS via CDN untuk styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome untuk icon -->
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
                {{-- Form logout menggunakan method POST --}}
                <form method="POST" action="/logout" class="inline">
                    @csrf {{-- Token keamanan Laravel --}}
                    <button type="submit" class="text-white hover:text-yellow-400">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Konten Utama --}}
    <div class="max-w-2xl mx-auto pt-24 pb-10 px-4">
        <h2 class="text-2xl font-bold text-yellow-400 mb-6">
            <i class="fas fa-info-circle"></i> Detail Reservasi
        </h2>

        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">

            {{-- Tampilkan nomor antrian jika ada --}}
            @if($reservasi->queue)
            <div class="text-center mb-6">
                <p class="text-gray-400 mb-2">Nomor Antrian Kamu</p>
                {{-- Tampilkan nomor antrian dengan ukuran besar --}}
                <span class="text-6xl font-bold text-yellow-400">
                    #{{ $reservasi->queue->nomor_antrian }}
                </span>
            </div>
            @endif

            {{-- Detail informasi reservasi --}}
            <div class="space-y-4">

                {{-- Nama layanan yang dipilih --}}
                <div class="flex justify-between border-b border-gray-700 pb-3">
                    <span class="text-gray-400">Layanan</span>
                    <span class="font-bold">{{ $reservasi->service->nama_layanan }}</span>
                </div>

                {{-- Harga layanan dengan format rupiah --}}
                <div class="flex justify-between border-b border-gray-700 pb-3">
                    <span class="text-gray-400">Harga</span>
                    <span class="font-bold text-yellow-400">
                        Rp {{ number_format($reservasi->service->harga, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Tanggal reservasi --}}
                <div class="flex justify-between border-b border-gray-700 pb-3">
                    <span class="text-gray-400">Tanggal</span>
                    <span class="font-bold">{{ $reservasi->tanggal }}</span>
                </div>

                {{-- Jam reservasi --}}
                <div class="flex justify-between border-b border-gray-700 pb-3">
                    <span class="text-gray-400">Jam</span>
                    <span class="font-bold">{{ $reservasi->jam }}</span>
                </div>

                {{-- Catatan dari pelanggan, jika kosong tampilkan tanda - --}}
                <div class="flex justify-between border-b border-gray-700 pb-3">
                    <span class="text-gray-400">Catatan</span>
                    <span class="font-bold">{{ $reservasi->catatan ?? '-' }}</span>
                </div>

                {{-- Status reservasi dengan warna berbeda --}}
                <div class="flex justify-between">
                    <span class="text-gray-400">Status</span>
                    @if($reservasi->status == 'pending')
                        {{-- Biru = menunggu konfirmasi --}}
                        <span class="bg-blue-500 text-white px-3 py-1 rounded">Pending</span>
                    @elseif($reservasi->status == 'confirmed')
                        {{-- Hijau = sudah dikonfirmasi admin --}}
                        <span class="bg-green-500 text-white px-3 py-1 rounded">Confirmed</span>
                    @elseif($reservasi->status == 'cancelled')
                        {{-- Merah = dibatalkan --}}
                        <span class="bg-red-500 text-white px-3 py-1 rounded">Cancelled</span>
                    @elseif($reservasi->status == 'done')
                        {{-- Abu-abu = sudah selesai --}}
                        <span class="bg-gray-500 text-white px-3 py-1 rounded">Selesai</span>
                    @endif
                </div>
            </div>

            {{-- Tombol kembali ke daftar reservasi --}}
            <a href="/reservasi"
                class="block text-center mt-6 bg-yellow-400 text-gray-900 py-2 rounded-lg font-bold hover:bg-yellow-500">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Reservasi
            </a>
        </div>
    </div>

</body>
</html>