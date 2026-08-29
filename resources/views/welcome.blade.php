<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma'bung Barbershop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="site-bg">

    {{-- Navbar minimal: hamburger di kiri, nama brand di tengah.
         Pakai container-fluid (bukan .container) supaya hamburger-nya
         benar-benar mepet ke tepi kiri viewport di semua lebar layar -
         .container biasa akan ikut membatasi lebar & center dengan
         margin di layar lebar, sehingga tombolnya malah bergeser jauh
         dari tepi kiri asli. Tombolnya juga sengaja TIDAK position:
         absolute (beda dari sebelumnya) - dibuat flex item biasa +
         spacer kosong seukuran tombol di sisi kanan supaya brand tetap
         center, sama persis strukturnya dengan navbar-floating di
         halaman setelah login (termasuk ikon bi-list yang sama), jadi
         posisi vertikal ikonnya konsisten/sejajar di kedua tempat. --}}
    <nav class="navbar position-fixed top-0 w-100 z-3" style="background: linear-gradient(to bottom, rgba(0,0,0,.65), transparent);">
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

            <span class="navbar-brand fw-bold fs-4 text-gold mb-0 flex-grow-1 text-center">
                <i class="fas fa-cut"></i> Ma'bung Barbershop
            </span>

            {{-- Spacer kosong seukuran tombol hamburger, supaya brand di atas benar-benar center (bukan condong ke kanan) --}}
            <span class="flex-shrink-0" style="width: 2.5rem;" aria-hidden="true"></span>
        </div>
    </nav>

    {{-- Menu offcanvas (hamburger) - data-bs-backdrop="false" supaya
         TIDAK ada lapisan gelap yang menutupi halaman saat offcanvas
         terbuka; efeknya diganti "push" (halaman geser ke kanan lewat
         class .content-pushed di <main>, lihat script di bawah) sama
         seperti panel profil di halaman setelah login. --}}
    <div
        class="offcanvas offcanvas-start text-bg-dark"
        tabindex="-1"
        id="menuUtama"
        aria-labelledby="menuUtamaLabel"
        data-bs-backdrop="false"
        data-bs-scroll="true"
    >
        <div class="offcanvas-header border-bottom border-secondary-subtle">
            <h5 class="offcanvas-title text-gold" id="menuUtamaLabel">Ma'bung Barbershop</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">

            {{-- Menu utama - gaya item disamakan dengan sidebar admin
                 (.admin-nav-link) dan offcanvas "Profil Saya" pelanggan
                 (.offcanvas-nav-link): ikon + padding + highlight aktif
                 border-kiri gold. --}}
            <ul class="navbar-nav gap-1">
                <li class="nav-item">
                    <a class="nav-link offcanvas-nav-link" href="#hero">
                        <i class="bi bi-house-door-fill"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link offcanvas-nav-link" href="#tentang">
                        <i class="bi bi-info-circle-fill"></i> Tentang Kami
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link offcanvas-nav-link" href="#layanan">
                        <i class="bi bi-scissors"></i> Layanan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link offcanvas-nav-link" href="#galeri">
                        <i class="bi bi-images"></i> Galeri
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
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link offcanvas-nav-link" href="{{ route('reservasi.index') }}">
                            <i class="bi bi-calendar-check"></i> Reservasi Saya
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link offcanvas-nav-link" href="{{ route('login') }}">
                            <i class="bi bi-key-fill"></i> Masuk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link offcanvas-nav-link" href="{{ route('register') }}">
                            <i class="bi bi-person-plus-fill"></i> Daftar
                        </a>
                    </li>
                @endauth
            </ul>

            @auth
                {{-- Keluar - gaya disamakan dengan tombol Keluar di sidebar
                     admin & offcanvas "Profil Saya" pelanggan
                     (btn-outline-danger penuh, terpisah dari menu biasa). --}}
                <form method="POST" action="{{ route('logout') }}" class="pt-2 pb-3 border-bottom border-secondary-subtle mb-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100">
                        Keluar
                    </button>
                </form>
            @endauth

            {{-- Info kontak singkat (data placeholder) - ps-3 (1rem)
                 supaya ikonnya sejajar persis dengan ikon menu di atas
                 (.offcanvas-nav-link punya padding-left 1rem juga). --}}
            <div class="small text-body-secondary d-flex flex-column gap-2 mb-3 ps-3">
                <div><i class="bi bi-geo-alt-fill text-gold me-2"></i>Jl. Contoh Raya No. 123, Jakarta</div>
                <div><i class="bi bi-whatsapp text-gold me-2"></i>0812-3456-7890</div>
                <div><i class="bi bi-clock-fill text-gold me-2"></i>Setiap hari, 09.00 - 21.00 WIB</div>
                <a href="https://www.google.com/maps/search/?api=1&query=Jl.+Contoh+Raya+No.+123%2C+Jakarta" target="_blank" rel="noopener" class="btn btn-outline-warning btn-sm mt-1">
                    <i class="bi bi-map"></i> Buka di Google Maps
                </a>
            </div>

            {{-- Sosial media - center lagi di tengah offcanvas (bukan
                 rata kiri), align-items-center supaya ketiga ikon
                 sejajar sempurna, gap-3 (16px) untuk jarak yang konsisten. --}}
            <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                <a href="#" class="social-link fs-4" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" class="social-link fs-4" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                <a href="#" class="social-link fs-4" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            </div>

            {{-- ms-3 (margin-left 1rem) supaya sisi kiri tombol ini
                 sejajar persis dengan tombol "Buka di Google Maps" di
                 atasnya (yang beradanya di dalam div berpadding ps-3). --}}
            <a href="{{ auth()->check() ? route('reservasi.create') : route('login') }}" class="btn btn-primary mt-auto ms-3">
                <i class="fas fa-calendar-check"></i> Reservasi Sekarang
            </a>
        </div>
    </div>

    {{-- Konten halaman dibungkus <main id="main-content"> supaya bisa
         digeser bareng-bareng (class .content-pushed, sudah didefinisikan
         di theme.css) saat offcanvas menu dibuka - sama seperti pola
         "push" panel profil di halaman setelah login. --}}
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
                    <img src="https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1920&q=80"
                        class="d-block w-100 h-100" style="object-fit: cover;"
                        alt="Pelanggan sedang dicukur rapi di barbershop">
                </div>
                <div class="carousel-item h-100">
                    <img src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1920&q=80"
                        class="d-block w-100 h-100" style="object-fit: cover;"
                        alt="Gunting rambut profesional di barbershop">
                </div>
                <div class="carousel-item h-100">
                    <img src="https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?auto=format&fit=crop&w=1920&q=80"
                        class="d-block w-100 h-100" style="object-fit: cover;"
                        alt="Kursi dan suasana barbershop premium">
                </div>
            </div>
        </div>

        {{-- Overlay gelap semi-transparan supaya teks tetap terbaca --}}
        <div class="position-absolute top-0 start-0 w-100 h-100 z-1" style="background: rgba(0,0,0,.6);"></div>

        {{-- Konten hero, statis di atas carousel --}}
        <div class="position-absolute top-50 start-50 translate-middle z-2 text-center px-3" style="width: 100%; max-width: 42rem;">
            <h1 class="display-4 fw-bold text-white mb-3">Tampil Rapi, Percaya Diri</h1>
            <p class="fs-5 text-white-50 mb-4">
                Rasakan pengalaman potong rambut dan grooming premium ala pria sejati, hanya di Ma'Bung Barbershop.
            </p>
            <a href="{{ auth()->check() ? route('reservasi.create') : route('login') }}" class="btn btn-primary btn-lg px-4 py-3">
                <i class="fas fa-calendar-check"></i> Reservasi Sekarang
            </a>
        </div>
    </header>

    {{-- ===================================================================
         PROMO BANNER - KONTEN PLACEHOLDER.
         Ganti teks di dalam <span> sesuai promo yang sedang aktif, atau
         hapus seluruh <div class="promo-banner">...</div> ini kalau
         sedang tidak ada promo berjalan.
         =================================================================== --}}
    <div class="promo-banner text-center py-2 px-3">
        <span class="fw-semibold">🎉 Promo: Diskon 10% untuk reservasi pertama!</span>
    </div>

    {{-- Tentang Kami Section --}}
    <section id="tentang" class="py-5">
        <div class="container">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-6">
                    <img
                        src="https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=900&q=80"
                        alt="Suasana Ma'bung Barbershop"
                        class="img-fluid rounded-4 shadow"
                    >
                </div>
                <div class="col-lg-6">
                    <h2 class="fs-2 fw-bold text-gold mb-3">Tentang Kami</h2>
                    <p class="fs-6 text-body-secondary">
                        Ma'bung Barbershop telah melayani pelanggan sejak 2020 dengan standar potong rambut
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
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card-layanan h-100 fade-in-up fade-in-up-1">
                        <div class="card-layanan-img-wrap">
                            <img src="https://images.unsplash.com/photo-1622286342621-4bd786c2447c?auto=format&fit=crop&w=800&q=80"
                                alt="Barber sedang memotong rambut pelanggan">
                        </div>
                        <div class="p-4 text-center">
                            <h3 class="fs-5 fw-bold mb-2">Potong Rambut</h3>
                            <p class="text-body-secondary mb-0">Potong rambut profesional sesuai keinginan kamu.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card-layanan h-100 fade-in-up fade-in-up-2">
                        <div class="card-layanan-img-wrap">
                            <img src="https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=800&q=80"
                                alt="Peralatan cukur jenggot profesional">
                        </div>
                        <div class="p-4 text-center">
                            <h3 class="fs-5 fw-bold mb-2">Cukur Jenggot</h3>
                            <p class="text-body-secondary mb-0">Rapikan jenggot kamu dengan tangan profesional.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mx-auto mx-lg-0">
                    <div class="card-layanan h-100 fade-in-up fade-in-up-3">
                        <div class="card-layanan-img-wrap">
                            <img src="https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?auto=format&fit=crop&w=800&q=80"
                                alt="Perawatan cuci dan creambath rambut">
                        </div>
                        <div class="p-4 text-center">
                            <h3 class="fs-5 fw-bold mb-2">Creambath</h3>
                            <p class="text-body-secondary mb-0">Perawatan rambut agar tetap sehat dan bersih.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Galeri Section --}}
    <section id="galeri" class="py-5 position-relative">
        <div class="blob-decor" style="width: 18rem; height: 18rem; top: -4rem; right: -5rem;"></div>

        <div class="container position-relative">
            <h2 class="fs-2 fw-bold text-center text-gold mb-5">Galeri Kami</h2>
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=600&q=80" alt="Hasil potongan rambut rapi">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=600&q=80" alt="Barber memotong rambut pelanggan">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=600&q=80" alt="Peralatan cukur profesional">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?auto=format&fit=crop&w=600&q=80" alt="Suasana interior barbershop">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="https://images.unsplash.com/photo-1622286342621-4bd786c2447c?auto=format&fit=crop&w=600&q=80" alt="Proses styling rambut pelanggan">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="galeri-item">
                        <img src="https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?auto=format&fit=crop&w=600&q=80" alt="Perawatan cuci dan creambath rambut">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================================================================
         Testimoni Section - menampilkan ulasan ASLI dari pelanggan
         (rating 4-5 bintang + ada teks ulasan, lihat query $testimoni di
         routes/web.php). Kalau belum ada satupun ulasan yang memenuhi
         syarat, jatuh ke 3 KONTEN PLACEHOLDER di bawah (@empty) supaya
         section ini tidak kosong - otomatis tergantikan begitu ada
         ulasan asli yang masuk.
         =================================================================== --}}
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
                    {{-- KONTEN PLACEHOLDER - ganti/hapus kapan saja setelah
                         ada ulasan pelanggan asli yang masuk. --}}
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
                            Kami menerima pembayaran Online (QRIS, Transfer Bank, DANA, GoPay, ShopeePay) maupun
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
                    <h3 class="fs-5 fw-bold text-gold mb-3"><i class="fas fa-cut"></i> Ma'bung Barbershop</h3>
                    <p class="text-body-secondary small mb-0">
                        Potong rambut &amp; grooming premium ala pria modern.
                    </p>
                </div>
                <div class="col-md-4">
                    <h3 class="fs-6 fw-bold text-white mb-3">Kontak &amp; Lokasi</h3>
                    <p class="text-body-secondary small mb-1"><i class="bi bi-geo-alt-fill text-gold me-2"></i>Jl. Contoh Raya No. 123, Jakarta</p>
                    <p class="text-body-secondary small mb-1"><i class="bi bi-whatsapp text-gold me-2"></i>0812-3456-7890</p>
                    <p class="text-body-secondary small mb-3"><i class="bi bi-clock-fill text-gold me-2"></i>Setiap hari, 09.00 - 21.00 WIB</p>
                    <a href="https://www.google.com/maps/search/?api=1&query=Jl.+Contoh+Raya+No.+123%2C+Jakarta" target="_blank" rel="noopener" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-map"></i> Buka di Google Maps
                    </a>
                </div>
                <div class="col-md-4">
                    <h3 class="fs-6 fw-bold text-white mb-3">Ikuti Kami</h3>
                    <div class="d-flex gap-4 justify-content-center justify-content-md-start">
                        <a href="#" class="social-link fs-4" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link fs-4" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        <a href="#" class="social-link fs-4" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    </div>
                </div>
            </div>

            <hr class="border-secondary-subtle my-4">

            <p class="text-center text-body-secondary small mb-0">&copy; 2026 Ma'bung Barbershop. Seluruh hak cipta dilindungi.</p>
        </div>
    </footer>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- ========================================================= --}}
    {{-- Efek "push": konten utama (<main>) digeser ke kanan saat   --}}
    {{-- offcanvas menu terbuka, bukan ditutupi backdrop gelap -     --}}
    {{-- pola yang sama dengan panel profil di halaman setelah      --}}
    {{-- login (lihat layouts/navigation.blade.php).                --}}
    {{-- ========================================================= --}}
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
</body>
</html>
