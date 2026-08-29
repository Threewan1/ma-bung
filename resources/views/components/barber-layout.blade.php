@props(['title' => 'Barber'])

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Ma'bung Barbershop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="site-bg">

    {{-- Layout terpisah dari layout pelanggan/admin - barber cuma
         punya 1 halaman kerja, jadi tidak perlu sidebar navigasi
         seperti admin, cukup top bar sederhana (brand + nama barber +
         tombol keluar), tetap konsisten tema dark+gold + glassmorphism
         yang sama dengan halaman lain. --}}
    <nav class="navbar navbar-dark barber-topbar">
        <div class="container-fluid d-flex align-items-center justify-content-between px-3">
            <span class="fw-bold text-gold fs-5">
                <i class="fas fa-cut"></i> Ma'bung Barbershop
            </span>

            <div class="d-flex align-items-center gap-3">
                <span class="text-white small d-none d-sm-inline">
                    <i class="fas fa-user-tie"></i> {{ auth()->user()->name }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        {{ $slot }}
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
