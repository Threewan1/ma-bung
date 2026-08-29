@php
    // Data ringkas untuk panel profil (offcanvas) - dihitung di sini
    // (bukan dikirim dari controller) karena navigation.blade.php dipakai
    // bersama oleh beberapa halaman (dashboard, profil, layanan), jadi
    // datanya harus selalu tersedia terlepas dari controller mana yang
    // merender.
    $navUser = Auth::user();

    $navFavorit = $navUser->reservations()
        ->select('service_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
        ->groupBy('service_id')
        ->orderByDesc('total')
        ->first();
    $navLayananFavorit = $navFavorit ? \App\Models\Service::find($navFavorit->service_id) : null;
@endphp

<nav class="navbar navbar-expand-sm navbar-dark navbar-floating">
    <div class="container-fluid navbar-floating-inner">

        {{-- Trigger panel profil - hanya icon hamburger mengambang,
             tanpa kotak/background pembungkus, tanpa teks/logo. --}}
        <div class="d-flex align-items-center gap-2">
            <button
                class="btn btn-link text-white p-0"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#profilOffcanvas"
                aria-controls="profilOffcanvas"
                aria-label="Buka menu"
            >
                <i class="bi bi-list fs-3 navbar-floating-icon"></i>
            </button>
        </div>
    </div>
</nav>

{{-- Spacer supaya konten halaman (header/main) tidak tertutup navbar
     yang sekarang "position: fixed" dan transparan. --}}
<div class="navbar-floating-spacer"></div>

{{-- ========================================================= --}}
{{-- PANEL PROFIL (OFFCANVAS) - slide dari kiri, berisi menu    --}}
{{-- navigasi ke halaman-halaman terkait akun pelanggan.        --}}
{{-- ========================================================= --}}
<div
    class="offcanvas offcanvas-start profil-offcanvas"
    tabindex="-1"
    id="profilOffcanvas"
    aria-labelledby="profilOffcanvasLabel"
    data-bs-backdrop="false"
    data-bs-scroll="true"
>
    <div class="offcanvas-header">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0 overflow-hidden" style="width: 2.5rem; height: 2.5rem; font-size: 1rem;">
                @if ($navUser->avatar)
                    <img src="{{ asset('storage/' . $navUser->avatar) }}" alt="Foto profil" class="w-100 h-100 object-fit-cover">
                @else
                    {{ strtoupper(substr($navUser->name, 0, 1)) }}
                @endif
            </div>
            <h5 class="offcanvas-title text-gold mb-0" id="profilOffcanvasLabel">Halo, {{ $navUser->name }}!</h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column">

        {{-- Menu navigasi - gaya baris disamakan dengan sidebar admin
             (ikon Bootstrap Icons + highlight aktif border-kiri gold). --}}
        <ul class="navbar-nav gap-1 mb-3">
            <li class="nav-item">
                <a class="nav-link offcanvas-nav-link ajax-nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person"></i> Akun
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link offcanvas-nav-link ajax-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            {{-- Layanan Favorit (collapsible, gaya sama dengan nav-link lain) --}}
            <li class="nav-item">
                <button
                    class="nav-link offcanvas-nav-link offcanvas-collapse-toggle w-100"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseLayananFavorit"
                    aria-expanded="false"
                    aria-controls="collapseLayananFavorit"
                >
                    <i class="bi bi-heart-fill"></i>
                    <span>Layanan Favorit</span>
                    <i class="fas fa-chevron-down small offcanvas-collapse-icon"></i>
                </button>
                <div class="collapse" id="collapseLayananFavorit">
                    <div class="offcanvas-collapse-body">
                        @if ($navLayananFavorit)
                            <p class="mb-2">{{ $navLayananFavorit->nama_layanan }}</p>
                            <a href="{{ route('reservasi.create', ['service_id' => $navLayananFavorit->id]) }}" class="btn btn-outline-primary btn-sm ajax-nav-link w-100">
                                Pesan Lagi
                            </a>
                        @else
                            <p class="mb-2">Belum ada</p>
                            <a href="{{ route('reservasi.create') }}" class="btn btn-outline-primary btn-sm ajax-nav-link w-100">
                                Buat Reservasi Pertama
                            </a>
                        @endif
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link offcanvas-nav-link ajax-nav-link {{ request()->routeIs('layanan.*') ? 'active' : '' }}" href="{{ route('layanan.index') }}">
                    <i class="bi bi-scissors"></i> Semua Layanan &amp; Harga
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link offcanvas-nav-link ajax-nav-link {{ request()->routeIs('reservasi.*') ? 'active' : '' }}" href="{{ route('reservasi.index') }}">
                    <i class="bi bi-calendar-check"></i> Semua Reservasi
                </a>
            </li>

            {{-- Info Barbershop (collapsible, gaya sama dengan nav-link lain) --}}
            <li class="nav-item">
                <button
                    class="nav-link offcanvas-nav-link offcanvas-collapse-toggle w-100"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseInfoBarbershop"
                    aria-expanded="false"
                    aria-controls="collapseInfoBarbershop"
                >
                    <i class="bi bi-shop"></i>
                    <span>Info Barbershop</span>
                    <i class="fas fa-chevron-down small offcanvas-collapse-icon"></i>
                </button>
                <div class="collapse" id="collapseInfoBarbershop">
                    <div class="offcanvas-collapse-body">
                        <p class="text-body-secondary small mb-1">Senin - Sabtu, 09.00 - 20.00 WIB</p>
                        <p class="text-body-secondary small mb-2">Jl. Contoh Raya No. 123, Jakarta</p>
                        <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" class="btn btn-success btn-sm w-100">
                            Chat WhatsApp
                        </a>
                    </div>
                </div>
            </li>
        </ul>

        {{-- Keluar - satu-satunya form yang SENGAJA dikecualikan dari
             AJAX (class "no-ajax-form"), karena logout memang harus
             mengakhiri sesi dengan reload penuh. --}}
        <form method="POST" action="{{ route('logout') }}" class="no-ajax-form pt-3 border-top border-secondary-subtle">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100">
                Keluar
            </button>
        </form>

    </div>
</div>

{{-- ========================================================= --}}
{{-- Efek "push": konten utama (<main>) digeser ke kanan saat   --}}
{{-- offcanvas profil terbuka, bukan ditutupi backdrop gelap.   --}}
{{-- ========================================================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var offcanvasEl = document.getElementById('profilOffcanvas');
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
    });
</script>

{{-- ========================================================= --}}
{{-- NAVIGASI AJAX (pjax sederhana)                              --}}
{{-- SEMUA link internal DAN form (kecuali Keluar) di-intercept: --}}
{{-- fetch/submit ke tujuan, ambil #main-content dari hasilnya,  --}}
{{-- lalu ganti isi #main-content di halaman saat ini. Navbar &  --}}
{{-- offcanvas sendiri ada DI LUAR #main-content, jadi tidak     --}}
{{-- pernah ikut ter-refresh/tertutup apa pun yang dibuka.       --}}
{{-- ========================================================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        {{--
            PENTING: harus menunggu DOMContentLoaded. navigation.blade.php
            di-include SEBELUM <main id="main-content"> di layouts/app.blade.php,
            jadi kalau kode ini dijalankan langsung (IIFE tanpa menunggu event
            ini), document.getElementById('main-content') masih null saat
            script ini dieksekusi - akibatnya event listener di bawah tidak
            pernah terpasang sama sekali, dan link/form kembali melakukan
            navigasi/submit browser biasa (full reload -> flicker + offcanvas
            ikut tertutup).
        --}}
        var mainContent = document.getElementById('main-content');
        if (!mainContent) {
            return;
        }

        // Script di dalam konten baru tidak otomatis jalan kalau
        // disisipkan lewat innerHTML - jadi setiap <script> di dalam
        // #main-content yang baru harus dibuat ulang elemennya supaya
        // browser benar-benar mengeksekusinya.
        function reRunScripts(container) {
            var oldScripts = container.querySelectorAll('script');
            oldScripts.forEach(function (oldScript) {
                var newScript = document.createElement('script');
                for (var i = 0; i < oldScript.attributes.length; i++) {
                    var attr = oldScript.attributes[i];
                    newScript.setAttribute(attr.name, attr.value);
                }
                newScript.textContent = oldScript.textContent;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
        }

        // Sinkronkan status "active" pada link .ajax-nav-link sesuai
        // URL yang sedang tampil, karena navbar/offcanvas tidak ikut
        // di-render ulang oleh server saat navigasi AJAX.
        function syncActiveLinks(path) {
            document.querySelectorAll('.ajax-nav-link').forEach(function (link) {
                if (link.pathname === path) {
                    link.classList.add('active', 'fw-semibold');
                } else {
                    link.classList.remove('active', 'fw-semibold');
                }
            });
        }

        // Bootstrap menaruh backdrop modal (dan class/style "modal-open"
        // di <body>) di LUAR #main-content - jadi kalau ada modal yang
        // masih terbuka saat form di dalamnya di-submit (mis. modal
        // Rating, modal konfirmasi Hapus Akun), meng-innerHTML ulang
        // #main-content TIDAK ikut membersihkan backdrop & style body
        // itu. Backdrop yang nyangkut ini menutupi seluruh halaman dan
        // memblokir semua klik/scroll sampai halaman di-refresh manual.
        // Dipanggil sebelum setiap penggantian #main-content supaya sisa
        // itu selalu dibersihkan, apa pun modal yang sebelumnya terbuka.
        function bersihkanSisaModal() {
            document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
                backdrop.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }

        // Menerapkan HTML hasil fetch/submit ke #main-content halaman
        // yang sedang tampil, dipakai bersama oleh navigasi link (GET)
        // maupun submit form (POST/PUT/PATCH/DELETE via method-spoofing).
        function applyResponse(html, finalUrl, pushHistory) {
            var parser = new DOMParser();
            var newDoc = parser.parseFromString(html, 'text/html');
            var newContent = newDoc.getElementById('main-content');

            if (!newContent) {
                // Halaman tujuan belum pakai layout dengan #main-content
                // (mis. ke-redirect ke halaman login karena sesi habis) -
                // fallback ke navigasi normal daripada menampilkan
                // halaman kosong.
                window.location.href = finalUrl;
                return;
            }

            bersihkanSisaModal();
            mainContent.innerHTML = newContent.innerHTML;
            reRunScripts(mainContent);

            var newTitle = newDoc.querySelector('title');
            if (newTitle) {
                document.title = newTitle.textContent;
            }

            if (pushHistory) {
                history.pushState({ ajaxNav: true }, '', finalUrl);
            }

            syncActiveLinks(new URL(finalUrl, window.location.origin).pathname);

            window.scrollTo(0, 0);
        }

        function loadPage(url, pushHistory) {
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    // Accept eksplisit "text/html" - kalau tidak, Laravel
                    // menganggap request ini "expectsJson()" (karena ada
                    // header X-Requested-With) dan akan membalas error
                    // validasi sebagai JSON 422, bukan redirect-back HTML
                    // yang bisa kita proses seperti navigasi biasa.
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
            })
                .then(function (response) {
                    return response.text().then(function (html) {
                        applyResponse(html, response.url || url, pushHistory);
                    });
                })
                .catch(function () {
                    // Kalau fetch gagal (mis. sesi habis / network error),
                    // jatuhkan ke navigasi browser biasa supaya pelanggan
                    // tidak terjebak di halaman kosong.
                    window.location.href = url;
                });
        }

        function submitFormAjax(form) {
            var formData = new FormData(form);
            var url = form.getAttribute('action') || window.location.href;

            fetch(url, {
                // HTML form cuma bisa GET/POST asli - method PUT/PATCH/
                // DELETE dikirim via field tersembunyi _method (method
                // spoofing bawaan Laravel), jadi transport-nya tetap POST.
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
                body: formData,
            })
                .then(function (response) {
                    return response.text().then(function (html) {
                        applyResponse(html, response.url || url, true);
                    });
                })
                .catch(function () {
                    // Fallback: submit form asli (full reload) kalau
                    // fetch gagal total.
                    form.submit();
                });
        }

        // Menangkap SEMUA link internal di halaman (bukan cuma yang
        // ditandai .ajax-nav-link) - jadi offcanvas tidak pernah ikut
        // tertutup/reload apa pun yang dibuka pelanggan (mis. tombol
        // "Detail", "Buat Reservasi Baru", "Kembali ke Dashboard", dll
        // yang berupa link <a> biasa).
        document.addEventListener('click', function (e) {
            // Klik kanan / klik tengah / Ctrl|Cmd|Shift|Alt+klik berarti
            // pelanggan sengaja mau buka di tab baru - biarkan browser
            // yang menangani, jangan di-intercept.
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                return;
            }

            var link = e.target.closest('a[href]');
            if (!link) {
                return;
            }

            // Lewati link eksternal, tab baru, atau yang bukan navigasi
            // halaman biasa (anchor/#, mailto:, tel:, javascript:).
            if (
                link.target === '_blank' ||
                link.hasAttribute('download') ||
                link.origin !== window.location.origin ||
                link.getAttribute('href').charAt(0) === '#' ||
                /^(mailto|tel|javascript):/i.test(link.getAttribute('href'))
            ) {
                return;
            }

            e.preventDefault();
            loadPage(link.href, true);
        });

        // Menangkap SEMUA form di halaman (Buat Reservasi, Batalkan,
        // Update Profil, Update Password, Hapus Akun, Upload Bukti
        // Pembayaran, Rating) KECUALI form Keluar (class "no-ajax-form").
        document.addEventListener('submit', function (e) {
            var form = e.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            // Kalau handler lain (mis. onsubmit="return confirm(...)"
            // pada tombol Batalkan/Hapus) sudah membatalkan submit ini
            // duluan - misalnya karena pelanggan klik "Cancel" di dialog
            // konfirmasi - jangan tetap dipaksa submit lewat AJAX.
            if (e.defaultPrevented) {
                return;
            }

            if (
                form.classList.contains('no-ajax-form') ||
                form.method.toLowerCase() === 'get' ||
                (form.target && form.target !== '' && form.target !== '_self')
            ) {
                return;
            }

            e.preventDefault();
            submitFormAjax(form);
        });

        window.addEventListener('popstate', function () {
            loadPage(window.location.href, false);
        });
    });
</script>
