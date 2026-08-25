<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Antrian - Ma'bung Barbershop</title>
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
                <a href="/admin/reservasi" class="text-white hover:text-yellow-400">Reservasi</a>
                <a href="/admin/antrian" class="text-yellow-400 font-bold">Antrian</a>
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
            <i class="fas fa-users"></i> Kelola Antrian Hari Ini
        </h2>

        {{-- Pesan Sukses --}}
        @if(session('success'))
            <div class="bg-green-500 text-white p-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabel Antrian --}}
        <div class="bg-gray-800 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Antrian</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Layanan</th>
                        <th class="px-4 py-3 text-left">Jam</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop semua data antrian --}}
                    @forelse($queues as $queue)
                    <tr class="border-t border-gray-700">
                        {{-- Nomor antrian dengan tampilan besar --}}
                        <td class="px-4 py-3">
                            <span class="bg-yellow-400 text-gray-900 px-3 py-1 rounded-full font-bold text-lg">
                                #{{ $queue->nomor_antrian }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $queue->reservation->user->name }}</td>
                        <td class="px-4 py-3">{{ $queue->reservation->service->nama_layanan }}</td>
                        <td class="px-4 py-3">{{ $queue->reservation->jam }}</td>
                        <td class="px-4 py-3">
                            {{-- Form update status antrian --}}
                            <form method="POST" action="/admin/antrian/{{ $queue->id }}">
                                @csrf
                                @method('PUT')
                                <select name="status_antrian" onchange="this.form.submit()"
                                    class="bg-gray-700 text-white px-2 py-1 rounded text-sm">
                                    <option value="menunggu" {{ $queue->status_antrian == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                    <option value="diproses" {{ $queue->status_antrian == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                    <option value="selesai" {{ $queue->status_antrian == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3">
                            {{-- Tombol Hapus --}}
                            <form method="POST" action="/admin/antrian/{{ $queue->id }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Yakin ingin menghapus antrian ini?')"
                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    {{-- Tampilkan pesan jika belum ada antrian --}}
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                            Belum ada antrian hari ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>