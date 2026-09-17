@props(['title' => 'Barber'])

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
<body class="site-bg">

    {{-- Layout sendiri buat barber - cuma 2 halaman (kerja + riwayat), navigasinya lewat offcanvas seperti #profilOffcanvas pelanggan. --}}
    <nav class="navbar navbar-dark barber-topbar">
        <div class="container-fluid d-flex align-items-center justify-content-between px-3">
            <div class="d-flex align-items-center gap-2">
                <button
                    class="btn btn-link text-white p-0"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#barberOffcanvas"
                    aria-controls="barberOffcanvas"
                    aria-label="Buka menu"
                >
                    <i class="bi bi-list fs-3 navbar-floating-icon"></i>
                </button>

                <span class="fw-bold text-gold fs-5 d-flex align-items-center gap-2">
                    <span class="brand-logo brand-logo-md">
                        <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Ma'bung Barbershop">
                    </span>
                    Ma'bung Barbershop
                </span>
            </div>

            <span class="text-white small d-none d-sm-inline">
                <i class="fas fa-user-tie"></i> {{ auth()->user()->name }}
            </span>
        </div>
    </nav>

    {{-- Sama pola dengan #profilOffcanvas pelanggan - tetap terbuka lintas halaman karena ada di luar #main-content, bukan lewat sessionStorage. --}}
    <div
        class="offcanvas offcanvas-start profil-offcanvas"
        tabindex="-1"
        id="barberOffcanvas"
        aria-labelledby="barberOffcanvasLabel"
        data-bs-backdrop="false"
        data-bs-scroll="true"
    >
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-gold d-flex align-items-center gap-2" id="barberOffcanvasLabel">
                <span class="brand-logo brand-logo-sm">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo Ma'bung Barbershop">
                </span>
                Ma'bung Barbershop
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>

        <div class="offcanvas-body d-flex flex-column">
            <ul class="navbar-nav gap-1 mb-3">
                <li class="nav-item">
                    <a class="nav-link offcanvas-nav-link ajax-nav-link {{ request()->routeIs('barber.dashboard') ? 'active' : '' }}" href="{{ route('barber.dashboard') }}">
                        Halaman Kerja
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link offcanvas-nav-link ajax-nav-link {{ request()->routeIs('barber.riwayat') ? 'active' : '' }}" href="{{ route('barber.riwayat') }}">
                        Riwayat Saya
                    </a>
                </li>
            </ul>

            <form method="POST" action="{{ route('logout') }}" class="no-ajax-form mt-auto pt-3 border-top border-secondary-subtle">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100">
                    Keluar
                </button>
            </form>
        </div>
    </div>

    {{-- .container sengaja di dalam <main>, bukan di <main> itu sendiri, biar margin-left push offcanvas bisa menyusutkan lebarnya secara alami. --}}
    <main id="main-content" class="py-4">
        <div class="container">
            {{ $slot }}
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Efek "push": #main-content digeser saat offcanvas terbuka, soalnya backdrop-nya sengaja "false". --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var offcanvasEl = document.getElementById('barberOffcanvas');
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

    {{-- Navigasi AJAX (pjax sederhana), adaptasi dari layouts/navigation.blade.php sisi pelanggan. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

            // Topbar/offcanvas tidak ikut dirender ulang server, jadi status "active" disinkronkan manual di sini.
            function syncActiveLinks(path) {
                document.querySelectorAll('.ajax-nav-link').forEach(function (link) {
                    if (link.pathname === path) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });
            }

            // Backdrop modal ada di luar #main-content, dibersihkan manual biar tidak nyangkut menutupi halaman.
            function bersihkanSisaModal() {
                document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
                    backdrop.remove();
                });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }

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
                        window.location.href = url;
                    });
            }

            function submitFormAjax(form) {
                var formData = new FormData(form);
                var url = form.getAttribute('action') || window.location.href;

                fetch(url, {
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
                        form.submit();
                    });
            }

            document.addEventListener('click', function (e) {
                if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                    return;
                }

                var link = e.target.closest('a[href]');
                if (!link) {
                    return;
                }

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

            document.addEventListener('submit', function (e) {
                var form = e.target;

                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

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
</body>
</html>
