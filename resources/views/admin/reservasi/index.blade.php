<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Reservasi - Ma'bung Barbershop</title>
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
                <a href="/admin" class="text-white hover:text-yellow-400">Dashboard</a>
                <a href="/admin/layanan" class="text-white hover:text-yellow-400">Layanan</a>
                <a href="/admin/reservasi" class="text-yellow-400 font-bold">Reservasi</a>
                <a href="/admin/antrian" class="text-white hover:text-yellow-400">Antrian</a>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="text-white hover:text-yellow-400">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Konten --}}
    <div class="max-w-7xl mx-auto pt-24 pb-10 px-4">
        <h2 class="text-2xl font-bold text-yellow-400 mb-6">
            <i class="fas fa-calendar-check"></i> Kelola Reservasi
        </h2>

        {{-- Pesan Sukses --}}
        @if(session('success'))
            <div class="bg-green-500 text-white p-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabel Reservasi --}}
        <div class="bg-gray-800 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Layanan</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Jam</th>
                        <th class="px-4 py-3 text-left">No. Antrian</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop semua data reservasi --}}
                    @forelse($reservations as $index => $reservasi)
                    <tr class="border-t border-gray-700">
                        <td class="px-4 py-3">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">{{ $reservasi->user->name }}</td>
                        <td class="px-4 py-3">{{ $reservasi->service->nama_layanan }}</td>
                        <td class="px-4 py-3">{{ $reservasi->tanggal }}</td>
                        <td class="px-4 py-3">{{ $reservasi->jam }}</td>
                        <td class="px-4 py-3">
                            @if($reservasi->queue)
                                <span class="bg-yellow-400 text-gray-900 px-3 py-1 rounded-full font-bold">
                                    #{{ $reservasi->queue->nomor_antrian }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            {{-- Form update status reservasi --}}
                            <form method="POST" action="/admin/reservasi/{{ $reservasi->id }}">
                                @csrf
                                @method('PUT')
                                <select name="status" onchange="this.form.submit()"
                                    class="bg-gray-700 text-white px-2 py-1 rounded text-sm">
                                    <option value="pending" {{ $reservasi->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ $reservasi->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="cancelled" {{ $reservasi->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="done" {{ $reservasi->status == 'done' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3">
                            {{-- Tombol Hapus --}}
                            <form method="POST" action="/admin/reservasi/{{ $reservasi->id }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Yakin ingin menghapus reservasi ini?')"
                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    {{-- Tampilkan pesan jika belum ada reservasi --}}
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-400">
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