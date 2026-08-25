<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Layanan - Ma'bung Barbershop</title>
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
                <a href="/admin/layanan" class="text-yellow-400 font-bold">Layanan</a>
                <a href="/admin/reservasi" class="text-white hover:text-yellow-400">Reservasi</a>
                <a href="/admin/antrian" class="text-white hover:text-yellow-400">Antrian</a>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="text-white hover:text-yellow-400">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Form Edit Layanan --}}
    <div class="max-w-2xl mx-auto pt-24 pb-10 px-4">
        <h2 class="text-2xl font-bold text-yellow-400 mb-6">
            <i class="fas fa-edit"></i> Edit Layanan
        </h2>

        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">

            {{-- Pesan Error --}}
            @if($errors->any())
                <div class="bg-red-500 text-white p-3 rounded-lg mb-4">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form menggunakan method PUT untuk update --}}
            <form method="POST" action="/admin/layanan/{{ $layanan->id }}">
                @csrf
                @method('PUT') {{-- Memberitahu Laravel bahwa ini adalah request UPDATE --}}

                {{-- Input Nama Layanan dengan nilai yang sudah ada --}}
                <div class="mb-4">
                    <label class="block text-gray-300 mb-2">Nama Layanan</label>
                    <input type="text" name="nama_layanan"
                        value="{{ old('nama_layanan', $layanan->nama_layanan) }}"
                        class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>

                {{-- Input Harga dengan nilai yang sudah ada --}}
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Harga (Rp)</label>
                    <input type="number" name="harga"
                        value="{{ old('harga', $layanan->harga) }}"
                        class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>

                {{-- Tombol Update --}}
                <button type="submit"
                    class="w-full bg-yellow-400 text-gray-900 py-3 rounded-lg font-bold text-lg hover:bg-yellow-500">
                    <i class="fas fa-save"></i> Update Layanan
                </button>

                {{-- Tombol Kembali --}}
                <a href="/admin/layanan"
                    class="block text-center text-gray-400 mt-4 hover:text-white">
                    Kembali ke Daftar Layanan
                </a>
            </form>
        </div>
    </div>

</body>
</html>