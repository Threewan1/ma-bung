@props(['title' => 'Admin'])

@php
    // Dihitung di sini (bukan controller) karena layout ini dipakai bersama semua halaman admin.
    $adminReservasiPendingCount = \App\Models\Reservation::where('status', 'pending')->count();
@endphp

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Ma'bung Barbershop</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    {{-- Topbar mobile: hanya tampil di layar kecil, berisi tombol hamburger --}}
    <nav class="admin-mobile-topbar d-flex d-lg-none align-items-center justify-content-between px-3">
        <button
            class="btn btn-link text-white p-1 btn-hamburger-touch"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#adminSidebar"
            aria-controls="adminSidebar"
            aria-label="Buka menu"
        >
            <i class="bi bi-list fs-3"></i>
        </button>

        <span class="fw-bold text-gold d-flex align-items-center gap-2">
            <span class="brand-logo brand-logo-sm">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Ma'bung Barbershop">
            </span>
            <span class="navbar-brand-text">Ma'bung Barbershop</span>
        </span>

        <span style="width: 2rem;"></span>
    </nav>

    {{-- Sidebar: fixed di desktop (lg+), jadi offcanvas drawer di mobile --}}
    <div
        class="offcanvas-lg offcanvas-start admin-sidebar"
        tabindex="-1"
        id="adminSidebar"
        aria-labelledby="adminSidebarLabel"
    >
        <div class="offcanvas-header border-bottom border-secondary-subtle d-lg-none">
            <h5 class="offcanvas-title text-gold d-flex align-items-center gap-2" id="adminSidebarLabel">
                <span class="brand-logo brand-logo-sm">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Ma'bung Barbershop">
                </span>
                Ma'bung Barbershop
            </h5>
            <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="offcanvas"
                data-bs-target="#adminSidebar"
                aria-label="Tutup"
            ></button>
        </div>

        <div class="offcanvas-body d-flex flex-column p-0">

            {{-- Brand, hanya tampil di sidebar desktop (mobile sudah ada di offcanvas-header) --}}
            <div class="d-none d-lg-flex align-items-center gap-2 px-3 py-4 border-bottom border-secondary-subtle">
                <span class="brand-logo brand-logo-lg">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Ma'bung Barbershop">
                </span>
                <span class="fw-bold text-gold fs-5">Ma'bung Barbershop</span>
            </div>

            {{-- Menu navigasi --}}
            <ul class="nav flex-column flex-grow-1 px-2 py-3 gap-1">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.layanan.index') }}" class="admin-nav-link {{ request()->routeIs('admin.layanan.*') ? 'active' : '' }}">
                        Layanan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.reservasi.index') }}" class="admin-nav-link justify-content-between {{ request()->routeIs('admin.reservasi.*') ? 'active' : '' }}">
                        <span>Reservasi</span>
                        @if ($adminReservasiPendingCount > 0)
                            <span class="badge rounded-pill text-bg-warning">{{ $adminReservasiPendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.barber.index') }}" class="admin-nav-link {{ request()->routeIs('admin.barber.*') ? 'active' : '' }}">
                        Kelola Barber
                    </a>
                </li>
            </ul>

            {{-- Tombol keluar - gaya disamakan dengan tombol Keluar di
                 offcanvas profil pelanggan (btn-outline-danger penuh). --}}
            <form method="POST" action="{{ route('logout') }}" class="mt-auto pt-3 px-2 pb-2 border-top border-secondary-subtle">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100">
                    Keluar
                </button>
            </form>

        </div>
    </div>

    {{-- Konten utama --}}
    <main class="admin-content">
        <div class="container-fluid p-4">
            {{ $slot }}
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
