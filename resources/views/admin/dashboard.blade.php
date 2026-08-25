<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ma'bung Barbershop</title>
    <!-- Tailwind CSS via CDN untuk styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome untuk icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white">

    {{-- Navbar Admin --}}
    <nav class="bg-gray-800 shadow-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-yellow-400">
                <i class="fas fa-cut"></i> Ma'bung Barbershop - Admin
            </div>
            <div class="space-x-4">
                {{-- Menu navigasi admin --}}
                <a href="/admin" class="text-yellow-400 font-bold">Dashboard</a>
                <a href="/admin/layanan" class="text-white hover:text-yellow-400">Layanan</a>
                <a href="/admin/reservasi" class="text-white hover:text-yellow-400">Reservasi</a>
                <a href="/admin/antrian" class="text-white hover:text-yellow-400">Antrian</a>
                {{-- Tombol logout --}}
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="text-white hover:text-yellow-400">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Konten Dashboard --}}
    <div class="max-w-7xl mx-auto pt-24 pb-10 px-4">
        <h2 class="text-2xl font-bold text-yellow-400 mb-8">
            <i class="fas fa-tachometer-alt"></i> Dashboard Admin
        </h2>

        {{-- Kartu Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            {{-- Total Reservasi --}}
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg text-center">
                <i class="fas fa-calendar-check text-4xl text-yellow-400 mb-3"></i>
                <h3 class="text-lg font-bold mb-1">Total Reservasi</h3>
                <p class="text-4xl font-bold text-yellow-400">{{ $totalReservasi }}</p>
            </div>

            {{-- Total Layanan --}}
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg text-center">
                <i class="fas fa-cut text-4xl text-yellow-400 mb-3"></i>
                <h3 class="text-lg font-bold mb-1">Total Layanan</h3>
                <p class="text-4xl font-bold text-yellow-400">{{ $totalLayanan }}</p>
            </div>

            {{-- Antrian Hari Ini --}}
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg text-center">
                <i class="fas fa-users text-4xl text-yellow-400 mb-3"></i>
                <h3 class="text-lg font-bold mb-1">Antrian Hari Ini</h3>
                <p class="text-4xl font-bold text-yellow-400">{{ $antrianHariIni }}</p>
            </div>
        </div>

        {{-- Reservasi Terbaru --}}
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-yellow-400 mb-4">
                <i class="fas fa-clock"></i> Reservasi Terbaru
            </h3>
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Layanan</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Jam</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop data reservasi terbaru --}}
                    @forelse($reservasiTerbaru as $reservasi)
                    <tr class="border-t border-gray-700">
                        <td class="px-4 py-3">{{ $reservasi->user->name }}</td>
                        <td class="px-4 py-3">{{ $reservasi->service->nama_layanan }}</td>
                        <td class="px-4 py-3">{{ $reservasi->tanggal }}</td>
                        <td class="px-4 py-3">{{ $reservasi->jam }}</td>
                        <td class="px-4 py-3">
                            @if($reservasi->status == 'pending')
                                <span class="bg-blue-500 text-white px-2 py-1 rounded text-sm">Pending</span>
                            @elseif($reservasi->status == 'confirmed')
                                <span class="bg-green-500 text-white px-2 py-1 rounded text-sm">Confirmed</span>
                            @elseif($reservasi->status == 'cancelled')
                                <span class="bg-red-500 text-white px-2 py-1 rounded text-sm">Cancelled</span>
                            @elseif($reservasi->status == 'done')
                                <span class="bg-gray-500 text-white px-2 py-1 rounded text-sm">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    {{-- Tampilkan pesan jika belum ada reservasi --}}
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                            Belum ada reservasi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>