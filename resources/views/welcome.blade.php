<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma'bung Barbershop</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="site-bg">

    {{-- Navbar minimal: pakai container-fluid biar hamburger benar-benar mepet tepi kiri, spacer kosong di kanan biar brand tetap center. --}}
    <nav id="landingNavbar" class="navbar position-fixed top-0 z-3" style="background: linear-gradient(to bottom, rgba(0,0,0,.65), transparent);">
        <div class="container-fluid d-flex align-items-center px-3 py-2">
            <button
                class="btn btn-link text-white p-1 flex-shrink-0"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#menuUtama"
                aria-controls="menuUtama"
                aria-label="Buka menu"
            >
                <i class="bi bi-list fs-3 navbar-floating-icon"></i>
            </button>

            <span class="navbar-brand fw-bold fs-4 text-gold mb-0 flex-grow-1 text-center d-flex align-items-center justify-content-center gap-2">
                <span class="brand-logo brand-logo-md">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Ma'bung Barbershop">
                </span>
                <span class="navbar-brand-text">Ma'bung Barbershop</span>
            </span>

            {{-- Spacer kosong seukuran tombol hamburger, supaya brand di atas benar-benar center (bukan condong ke kanan) --}}
            <span class="flex-shrink-0" style="width: 2.5rem;" aria-hidden="true"></span>
        </div>
    </nav>

    {{-- data-bs-backdrop="false" biar tanpa lapisan gelap, efeknya diganti "push" (lihat script di bawah). --}}
    <div
        class="offcanvas offcanvas-start text-bg-dark"
        tabindex="-1"
        id="menuUtama"
        aria-labelledby="menuUtamaLabel"
        data-bs-backdrop="false"
        data-bs-scroll="true"
    >
        <div class="offcanvas-header border-bottom border-secondary-subtle">
            <h5 class="offcanvas-title text-gold d-flex align-items-center gap-2" id="menuUtamaLabel">
                <span class="brand-logo brand-logo-sm">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Ma'bung Barbershop">
                </span>
                Ma'bung Barbershop
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">

            {{-- Gaya item disamakan dengan sidebar admin & offcanvas "Profil Saya" pelanggan. --}}
            <ul class="navbar-nav gap-1">
                <li class="nav-item">
                    <a class="nav-link offcanvas-nav-link" href="#hero">
                        Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link offcanvas-nav-link" href="#tentang">
                        Tentang Kami
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link offcanvas-nav-link" href="#layanan">
                        Layanan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link offcanvas-nav-link" href="#galeri">
                        Galeri
                    </a>
                </li>
            </ul>

            {{-- Divider menu utama vs menu akun --}}
            <hr class="border-secondary-subtle my-3">

            {{-- Menu akun --}}
            <ul class="navbar-nav gap-1 mb-3">
                @auth
                    <li class="nav-item">
                        <a class="nav-link offcanvas-nav-link" href="{{ route('dashboard') }}">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link offcanvas-nav-link" href="{{ route('reservasi.index') }}">
                            Reservasi Saya
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link offcanvas-nav-link" href="{{ route('login') }}">
                            Masuk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link offcanvas-nav-link" href="{{ route('register') }}">
                            Daftar
                        </a>
                    </li>
                @endauth
            </ul>

            <a href="{{ auth()->check() ? route('reservasi.create') : route('login') }}" class="btn btn-primary mt-auto ms-3">
                <i class="fas fa-calendar-check"></i> Reservasi Sekarang
            </a>

            @auth
                {{-- Gaya disamakan dengan tombol Keluar di sidebar admin & offcanvas pelanggan. --}}
                <form method="POST" action="{{ route('logout') }}" class="pt-2 pb-3 border-bottom border-secondary-subtle mb-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger offcanvas-logout-btn w-100">
                        Keluar
                    </button>
                </form>
            @endauth
        </div>
    </div>

    {{-- Dibungkus <main id="main-content"> biar bisa ikut "push" saat offcanvas menu dibuka. --}}
    <main id="main-content">

    {{-- Hero Section dengan carousel foto latar --}}
    <header id="hero" class="position-relative vh-100 overflow-hidden">

        {{-- Carousel Bootstrap: 3 foto latar bergantian otomatis --}}
        <div id="heroCarousel" class="carousel slide carousel-fade h-100 z-0" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>

            <div class="carousel-inner h-100">
                <div class="carousel-item active h-100">
                    <img src="{{ asset('images/mabung-barber.jpeg') }}"
                        class="d-block w-100 h-100" style="object-fit: cover;"
                        alt="Signage Ma'bung Barbershop Shave &amp; Cuts">
                </div>
                <div class="carousel-item h-100">
                    <img src="{{ asset('images/amos.jpeg') }}"
                        class="d-block w-100 h-100" style="object-fit: cover;"
                        alt="Proses potong rambut di Ma'bung Barbershop">
                </div>
                <div class="carousel-item h-100">
                    <img src="{{ asset('images/ruangan.jpeg') }}"
                        class="d-block w-100 h-100" style="object-fit: cover;"
                        alt="Suasana interior Ma'bung Barbershop">
                </div>
            </div>
        </div>

        {{-- Overlay gelap semi-transparan supaya teks tetap terbaca --}}
        <div class="position-absolute top-0 start-0 w-100 h-100 z-1" style="background: rgba(0,0,0,.6);"></div>

        {{-- Konten hero, statis di atas carousel --}}
        <div class="position-absolute top-50 start-50 translate-middle z-2 text-center px-3" style="width: 100%; max-width: 42rem;">
            <h1 class="display-4 fw-bold text-white mb-3 hero-title">Tampil Rapi, Percaya Diri</h1>
            <p class="fs-5 text-white-50 mb-4">
                Rasakan pengalaman potong rambut dan grooming premium ala pria sejati, hanya di Ma'Bung Barbershop.
            </p>
            <a href="{{ auth()->check() ? route('reservasi.create') : route('login') }}" class="btn btn-primary btn-lg px-4 py-3">
                <i class="fas fa-calendar-check"></i> Reservasi Sekarang
            </a>
        </div>
    </header>

    {{-- Tentang Kami Section --}}
    <section id="tentang" class="py-5">
        <div class="container">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-6">
                    <img
                        src="{{ asset('images/ruangan.jpeg') }}"
                        alt="Suasana Ma'bung Barbershop"
                        class="img-fluid rounded-4 shadow"
                    >
                </div>
                <div class="col-lg-6">
                    <h2 class="fs-2 fw-bold text-gold mb-3">Tentang Kami</h2>
                    <p class="fs-6 text-body-secondary">
                        Ma'bung Barbershop telah melayani pelanggan sejak 2024 dengan standar potong rambut
                        premium ala pria modern. Ditangani oleh tim barber berpengalaman dan didukung
                        suasana tempat yang nyaman, kami berkomitmen memberikan hasil terbaik di setiap
                        kunjungan kamu.
                    </p>
                    <p class="fs-6 text-body-secondary mb-0">
                        Dari potong rambut, cukur jenggot, hingga perawatan rambut - kami hadir untuk
                        membantu kamu selalu tampil rapi dan percaya diri.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Layanan Section --}}
    <section id="layanan" class="layanan-section py-5">

        {{-- Elemen dekoratif blur gold-amber --}}
        <div class="blob-decor" style="width: 22rem; height: 22rem; top: -6rem; left: -6rem;"></div>
        <div class="blob-decor" style="width: 18rem; height: 18rem; bottom: -5rem; right: -4rem;"></div>

        <div class="container position-relative">
            <h2 class="fs-2 fw-bold text-center text-gold mb-5">Layanan Kami</h2>
            @if ($layanan->isEmpty())
                <div class="bg-panel card-bordered-gold rounded-3 p-5 text-center text-body-secondary">
                    <p class="mb-0">Belum ada layanan yang tersedia.</p>
                </div>
            @else
                {{-- Layanan tanpa foto sendiri dikasih foto toko placeholder biar card tidak kosong. --}}
                @php
                    $fotoLayanan = [
                        'potong rambut' => asset('images/mondo.jpeg'),
                        'cukur kumis dan brewok' => asset('images/kenan.jpeg'),
                    ];
                    $fotoFallback = [
                        asset('images/ruangan.jpeg'),
                        asset('images/amos.jpeg'),
                        asset('images/peralatanbarber.jpeg'),
                        asset('images/mabung-barber.jpeg'),
                    ];
                @endphp
                <div class="row g-4">
                    @foreach ($layanan as $item)
                        <div class="col-4 col-md-6 col-lg-4">
                            <div class="card-layanan h-100 fade-in-up fade-in-up-{{ ($loop->index % 4) + 1 }}">
                                <div class="card-layanan-img-wrap">
                                    <img src="{{ $item->foto ? asset('storage/' . $item->foto) : ($fotoLayanan[strtolower($item->nama_layanan)] ?? $fotoFallback[$loop->index % count($fotoFallback)]) }}"
                                        alt="Layanan {{ $item->nama_layanan }}">
                                </div>
                                <div class="card-layanan-body text-center">
                                    <h3 class="fw-bold mb-2">{{ $item->nama_layanan }}</h3>
                                    <p class="text-gold fw-bold mb-0">
                                        Rp {{ number_format($item->harga, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Galeri Section --}}
    <section id="galeri" class="py-5 position-relative overflow-hidden">
        <div class="blob-decor" style="width: 18rem; height: 18rem; top: -4rem; right: -5rem;"></div>

        <div class="container position-relative">
            <h2 class="fs-2 fw-bold text-center text-gold mb-5">Galeri Kami</h2>
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="{{ asset('images/aldo.jpeg') }}" alt="Hasil potongan rambut rapi">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="{{ asset('images/melayani.jpeg') }}" alt="Barber memotong rambut pelanggan">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="{{ asset('images/peralatanbarber.jpeg') }}" alt="Peralatan cukur profesional">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="{{ asset('images/amos.jpeg') }}" alt="Proses potong rambut di Ma'bung Barbershop">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="{{ asset('images/mondo.jpeg') }}" alt="Hasil styling rambut pelanggan">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="{{ asset('images/ongki.jpeg') }}" alt="Hasil potongan rambut modern">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimoni asli pelanggan, jatuh ke konten placeholder di bawah (@empty) kalau belum ada yang memenuhi syarat. --}}
    <section id="testimoni" class="py-5">
        <div class="container">
            <h2 class="fs-2 fw-bold text-center text-gold mb-5">Apa Kata Pelanggan Kami</h2>
            <div class="row g-4">
                @forelse ($testimoni as $item)
                    <div class="col-md-4">
                        <div class="bg-panel card-bordered-gold rounded-3 p-4 h-100 fade-in-up fade-in-up-{{ ($loop->index % 4) + 1 }}">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0 overflow-hidden" style="width: 3rem; height: 3rem;">
                                    @if ($item->user->avatar)
                                        <img src="{{ asset('storage/' . $item->user->avatar) }}" alt="{{ $item->user->name }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        {{ strtoupper(substr($item->user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <h3 class="fs-6 fw-bold mb-1">{{ $item->user->name }}</h3>
                                    <div class="text-gold small">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="{{ $i <= $item->rating ? 'fas' : 'far' }} fa-star"></i>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            <p class="text-body-secondary fst-italic mb-0">
                                "{{ $item->review }}"
                            </p>
                        </div>
                    </div>
                @empty
                    {{-- Konten placeholder, ganti/hapus kapan saja setelah ada ulasan asli. --}}
                    <div class="col-md-4">
                        <div class="bg-panel card-bordered-gold rounded-3 p-4 h-100 fade-in-up fade-in-up-1">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 3rem; height: 3rem;">B</div>
                                <div>
                                    <h3 class="fs-6 fw-bold mb-1">Budi Santoso</h3>
                                    <div class="text-gold small">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="text-body-secondary fst-italic mb-0">
                                "Potongan rambutnya rapi banget, barber-nya ramah dan sabar dengerin request. Pasti balik lagi ke sini."
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-panel card-bordered-gold rounded-3 p-4 h-100 fade-in-up fade-in-up-2">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 3rem; height: 3rem;">R</div>
                                <div>
                                    <h3 class="fs-6 fw-bold mb-1">Rian Pratama</h3>
                                    <div class="text-gold small">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="text-body-secondary fst-italic mb-0">
                                "Sistem reservasi online-nya memudahkan banget, jadi nggak perlu antre lama di tempat."
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-panel card-bordered-gold rounded-3 p-4 h-100 fade-in-up fade-in-up-3">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 3rem; height: 3rem;">D</div>
                                <div>
                                    <h3 class="fs-6 fw-bold mb-1">Dimas Aditya</h3>
                                    <div class="text-gold small">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="text-body-secondary fst-italic mb-0">
                                "Suasananya nyaman, tempatnya bersih, dan hasil cukur jenggotnya rapi sekali."
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section id="faq" class="py-5">
        <div class="container" style="max-width: 800px;">
            <h2 class="fs-2 fw-bold text-center text-gold mb-5">Pertanyaan Umum</h2>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item rounded-3 overflow-hidden mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Apakah harus reservasi terlebih dahulu?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-body-secondary">
                            Sangat disarankan untuk reservasi terlebih dahulu melalui website ini supaya kamu
                            mendapat nomor antrian dan tidak perlu menunggu lama di tempat.
                        </div>
                    </div>
                </div>
                <div class="accordion-item rounded-3 overflow-hidden mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Metode pembayaran apa saja yang tersedia?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-body-secondary">
                            Kami menerima pembayaran Online (QRIS, Transfer Bank) maupun
                            COD (bayar langsung di tempat).
                        </div>
                    </div>
                </div>
                <div class="accordion-item rounded-3 overflow-hidden mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Apakah bisa reservasi untuk hari yang sama?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-body-secondary">
                            Bisa, selama masih ada slot jam yang tersedia pada hari tersebut. Semakin cepat kamu
                            reservasi, semakin besar peluang mendapat jam yang diinginkan.
                        </div>
                    </div>
                </div>
                <div class="accordion-item rounded-3 overflow-hidden mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Bagaimana jika ingin membatalkan reservasi?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-body-secondary">
                            Kamu bisa membatalkan reservasi langsung dari halaman "Semua Reservasi" di akun kamu,
                            selama status reservasi belum selesai diproses.
                        </div>
                    </div>
                </div>
                <div class="accordion-item rounded-3 overflow-hidden mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            Apakah ada biaya tambahan untuk layanan tertentu?
                        </button>
                    </h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-body-secondary">
                            Tidak ada biaya tersembunyi. Harga yang tertera di halaman Layanan sudah termasuk
                            keseluruhan biaya layanan tersebut.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer: brand, kontak & lokasi (dengan tombol Google Maps), sosial media --}}
    <footer class="landing-footer py-5">
        <div class="container">
            <div class="row g-4 text-center text-md-start">
                <div class="col-md-4">
                    <h3 class="fs-5 fw-bold text-gold mb-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                        <span class="brand-logo brand-logo-sm">
                            <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Ma'bung Barbershop">
                        </span>
                        Ma'bung Barbershop
                    </h3>
                    <p class="text-body-secondary small mb-0">
                        Potong rambut &amp; grooming premium ala pria modern.
                    </p>
                </div>
                <div class="col-md-4">
                    <h3 class="fs-6 fw-bold text-white mb-3">Kontak &amp; Lokasi</h3>
                    <p class="text-body-secondary small mb-1">Jln. Martadinata / Tambayako</p>
                    <p class="text-body-secondary small mb-1">Kota Mamuju, Sulawesi Barat</p>
                    <p class="text-body-secondary small mb-3">Setiap hari, 10.00 - 22.00 WITA</p>
                    <a href="https://maps.app.goo.gl/bLdbwgx4Zuq4Fx1VA?g_st=aw" target="_blank" rel="noopener" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-map"></i> Buka di Google Maps
                    </a>
                </div>
                <div class="col-md-4">
                    <h3 class="fs-6 fw-bold text-white mb-3">Ikuti Kami</h3>
                    <div class="d-flex gap-4 justify-content-center justify-content-md-start">
                        <a href="https://www.instagram.com/ma.bungbarber/" target="_blank" rel="noopener" class="social-link fs-4" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="https://www.tiktok.com/@ig_ma.bungbarber" target="_blank" rel="noopener" class="social-link fs-4" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                        <a href="https://wa.me/6282188146322" target="_blank" rel="noopener" class="social-link fs-4" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
            </div>

            <hr class="border-secondary-subtle my-4">

            <p class="text-center text-body-secondary small mb-0">&copy; 2026 Ma'bung Barbershop. Seluruh hak cipta dilindungi.</p>
        </div>
    </footer>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Efek "push": <main> digeser saat offcanvas menu terbuka, bukan ditutupi backdrop gelap. --}}
    <script>
        (function () {
            var offcanvasEl = document.getElementById('menuUtama');
            var mainContent = document.getElementById('main-content');

            if (!offcanvasEl || !mainContent) {
                return;
            }

            offcanvasEl.addEventListener('show.bs.offcanvas', function () {
                mainContent.classList.add('content-pushed');
            });

            offcanvasEl.addEventListener('hide.bs.offcanvas', function () {
                mainContent.classList.remove('content-pushed');
            });
        })();
    </script>

    {{-- Navbar landing page disembunyikan total (opacity+visibility, .landing-navbar-hidden di theme.css) saat offcanvas menu terbuka. --}}
    <script>
        (function () {
            var offcanvasEl = document.getElementById('menuUtama');
            var landingNavbar = document.getElementById('landingNavbar');

            if (!offcanvasEl || !landingNavbar) {
                return;
            }

            offcanvasEl.addEventListener('show.bs.offcanvas', function () {
                landingNavbar.classList.add('landing-navbar-hidden');
            });

            offcanvasEl.addEventListener('hide.bs.offcanvas', function () {
                landingNavbar.classList.remove('landing-navbar-hidden');
            });
        })();
    </script>
</body>
</html>
