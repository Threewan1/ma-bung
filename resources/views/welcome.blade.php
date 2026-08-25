<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma'bung Barbershop</title>
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
                @auth
                    <a href="/dashboard" class="text-white hover:text-yellow-400">Dashboard</a>
                @else
                    <a href="/login" class="text-white hover:text-yellow-400">Login</a>
                    <a href="/register" class="bg-yellow-400 text-gray-900 px-4 py-2 rounded-lg font-bold hover:bg-yellow-500">Daftar</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="min-h-screen flex items-center justify-center bg-gray-900 pt-16">
        <div class="text-center px-4">
            <h1 class="text-5xl font-bold text-yellow-400 mb-4">Ma'bung Barbershop</h1>
            <p class="text-xl text-gray-300 mb-8">Tampil Keren, Percaya Diri! Reservasi Online Sekarang.</p>
            @auth
                <a href="/reservasi/create" class="bg-yellow-400 text-gray-900 px-8 py-3 rounded-lg text-xl font-bold hover:bg-yellow-500">
                    <i class="fas fa-calendar-check"></i> Reservasi Sekarang
                </a>
            @else
                <a href="/register" class="bg-yellow-400 text-gray-900 px-8 py-3 rounded-lg text-xl font-bold hover:bg-yellow-500">
                    <i class="fas fa-calendar-check"></i> Reservasi Sekarang
                </a>
            @endauth
        </div>
    </section>

    {{-- Layanan Section --}}
    <section class="py-16 bg-gray-800">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-yellow-400 mb-12">Layanan Kami</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gray-900 p-6 rounded-lg text-center shadow-lg">
                    <i class="fas fa-cut text-5xl text-yellow-400 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Potong Rambut</h3>
                    <p class="text-gray-400">Potong rambut profesional sesuai keinginan kamu.</p>
                </div>
                <div class="bg-gray-900 p-6 rounded-lg text-center shadow-lg">
                    <i class="fas fa-beard text-5xl text-yellow-400 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Cukur Jenggot</h3>
                    <p class="text-gray-400">Rapikan jenggot kamu dengan tangan profesional.</p>
                </div>
                <div class="bg-gray-900 p-6 rounded-lg text-center shadow-lg">
                    <i class="fas fa-spray-can text-5xl text-yellow-400 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Creambath</h3>
                    <p class="text-gray-400">Perawatan rambut agar tetap sehat dan bersih.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-center py-6 text-gray-400">
        <p>&copy; 2026 Ma'bung Barbershop. All rights reserved.</p>
    </footer>

</body>
</html>