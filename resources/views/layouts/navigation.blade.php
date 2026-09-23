@php
    // Dihitung di sini (bukan di controller) karena partial ini dipakai bersama banyak halaman.
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

        {{-- Trigger panel profil (icon hamburger mengambang), transparan/
             mengambang tanpa kotak pembungkus. --}}
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

{{-- Panel profil (offcanvas), slide dari kiri berisi menu akun pelanggan. --}}
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
            <div class="offcanvas-avatar rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0 overflow-hidden">
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

        {{-- Gaya baris disamakan dengan sidebar admin. --}}
        <ul class="navbar-nav gap-1 mb-3">
            <li class="nav-item">
                <a class="nav-link offcanvas-nav-link ajax-nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    Akun
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link offcanvas-nav-link ajax-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    Dashboard
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
                    Semua Layanan &amp; Harga
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link offcanvas-nav-link ajax-nav-link {{ request()->routeIs('reservasi.*') ? 'active' : '' }}" href="{{ route('reservasi.index') }}">
                    Semua Reservasi
                </a>
            </li>
        </ul>

        <a href="{{ route('reservasi.create') }}" class="btn btn-primary mt-auto">
            <i class="fas fa-calendar-check"></i> Reservasi Sekarang
        </a>

        {{-- Satu-satunya form yang dikecualikan dari AJAX, logout harus reload penuh. --}}
        <form method="POST" action="{{ route('logout') }}" class="no-ajax-form pt-3 border-top border-secondary-subtle">
            @csrf
            <button type="submit" class="btn btn-outline-danger offcanvas-logout-btn w-100">
                Keluar
            </button>
        </form>

    </div>
</div>

{{-- Efek "push": <main> digeser ke kanan saat offcanvas profil terbuka, bukan ditutupi backdrop gelap. --}}
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

{{-- Navigasi AJAX (pjax sederhana): semua link & form (kecuali Keluar) di-intercept, #main-content diganti tanpa reload, navbar/offcanvas di luar itu jadi tidak pernah ikut ter-refresh. --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        {{-- Harus nunggu DOMContentLoaded, soalnya partial ini di-include sebelum <main id="main-content"> ada di DOM. --}}
        var mainContent = document.getElementById('main-content');
        if (!mainContent) {
            return;
        }

        // Script yang disisipkan lewat innerHTML tidak otomatis jalan, jadi elemennya harus dibuat ulang.
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

        // Navbar/offcanvas tidak ikut dirender ulang server, jadi status "active" disinkronkan manual di sini.
        function syncActiveLinks(path) {
            document.querySelectorAll('.ajax-nav-link').forEach(function (link) {
                if (link.pathname === path) {
                    link.classList.add('active', 'fw-semibold');
                } else {
                    link.classList.remove('active', 'fw-semibold');
                }
            });
        }

        // Backdrop modal Bootstrap ada di luar #main-content, jadi tidak ikut kebersihkan saat innerHTML diganti - dipanggil sebelum tiap penggantian biar tidak nyangkut nge-block klik/scroll.
        function bersihkanSisaModal() {
            document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
                backdrop.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }

        // Dipakai bersama navigasi link (GET) dan submit form (POST/PUT/PATCH/DELETE).
        function applyResponse(html, finalUrl, pushHistory) {
            var parser = new DOMParser();
            var newDoc = parser.parseFromString(html, 'text/html');
            var newContent = newDoc.getElementById('main-content');

            if (!newContent) {
                // Halaman tujuan tidak punya #main-content (mis. redirect ke login), fallback ke navigasi biasa.
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
                    // Wajib "text/html" eksplisit, kalau tidak Laravel balas error validasi sebagai JSON 422.
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
                    // Fetch gagal (sesi habis/network error) -> fallback navigasi biasa.
                    window.location.href = url;
                });
        }

        function submitFormAjax(form) {
            var formData = new FormData(form);
            var url = form.getAttribute('action') || window.location.href;

            fetch(url, {
                // Transport tetap POST, PUT/PATCH/DELETE dikirim via field _method (method spoofing Laravel).
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
                    // Fetch gagal total -> submit form asli (full reload).
                    form.submit();
                });
        }

        // Menangkap SEMUA link internal (bukan cuma .ajax-nav-link), biar offcanvas tidak ikut tertutup/reload.
        document.addEventListener('click', function (e) {
            // Klik kanan/tengah/Ctrl/Cmd/Shift/Alt+klik = sengaja buka tab baru, biarkan browser yang urus.
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                return;
            }

            var link = e.target.closest('a[href]');
            if (!link) {
                return;
            }

            // Lewati link eksternal, tab baru, anchor/#, mailto:, tel:, javascript:.
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

        // Menangkap semua form di halaman kecuali yang class "no-ajax-form" (Keluar).
        document.addEventListener('submit', function (e) {
            var form = e.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            // Kalau handler lain (mis. confirm() di tombol Batalkan) sudah membatalkan submit ini, jangan dipaksa lewat AJAX.
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
