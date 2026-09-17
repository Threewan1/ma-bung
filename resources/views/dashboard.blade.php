<x-app-layout>
    <div class="position-relative overflow-hidden">

        {{-- Elemen dekoratif blur gold-amber --}}
        <div class="blob-decor" style="width: 20rem; height: 20rem; top: -5rem; right: -6rem;"></div>
        <div class="blob-decor" style="width: 16rem; height: 16rem; bottom: -4rem; left: -5rem;"></div>

    <div class="container pt-5 pb-4 position-relative">

        {{-- UCAPAN ULANG TAHUN, murni ucapan, tanpa promo/diskon apapun. --}}
        @if ($ulangTahunHariIni)
            <div class="member-card member-card-gold rounded-3 p-3 p-md-4 mb-3 text-center fade-in-up">
                <p class="fs-4 fw-bold mb-1">
                    Selamat Ulang Tahun, {{ $user->name }}!
                </p>
                <p class="mb-0">
                    Semoga harimu menyenangkan.
                </p>
            </div>
        @endif

        {{-- REMINDER: RESERVASI HARI INI / BESOK --}}
        @if ($reservasiSegera)
            <div class="bg-panel card-bordered-gold border-start border-4 border-warning rounded-3 p-3 p-md-4 mb-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 fade-in-up">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h3 class="fs-6 fw-bold mb-1">Reservasi Segera!</h3>
                        <p class="mb-0 text-body-secondary small">
                            {{ $reservasiSegera->service->nama_layanan ?? '-' }} pada
                            {{ \Carbon\Carbon::parse($reservasiSegera->tanggal)->isToday() ? 'hari ini' : 'besok' }},
                            jam {{ $reservasiSegera->jam }}.
                        </p>
                    </div>
                </div>
                <a href="{{ route('reservasi.show', $reservasiSegera->id) }}" class="btn btn-warning btn-sm text-nowrap">
                    Lihat Detail
                </a>
            </div>
        @endif

        {{-- AJAKAN RATING KUNJUNGAN TERAKHIR --}}
        @if ($reservasiPerluRating)
            <div class="bg-panel card-bordered-gold rounded-3 p-3 p-md-4 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 fade-in-up">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h3 class="fs-6 fw-bold mb-1">Beri rating untuk kunjungan terakhirmu</h3>
                        <p class="mb-0 text-body-secondary small">
                            {{ $reservasiPerluRating->service->nama_layanan ?? '-' }} -
                            {{ \Carbon\Carbon::parse($reservasiPerluRating->tanggal)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#ratingModal">
                    <i class="fas fa-star"></i> Beri Rating
                </button>
            </div>

            {{-- Modal rating bintang 1-5 + ulasan --}}
            <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-panel">
                        <form method="POST" action="{{ route('reservasi.rate', $reservasiPerluRating->id) }}">
                            @csrf

                            <div class="modal-header border-secondary-subtle">
                                <h5 class="modal-title text-gold" id="ratingModalLabel">Beri Rating</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>

                            <div class="modal-body">
                                <p class="text-body-secondary small mb-3">
                                    {{ $reservasiPerluRating->service->nama_layanan ?? '-' }} -
                                    {{ \Carbon\Carbon::parse($reservasiPerluRating->tanggal)->format('d/m/Y') }}
                                </p>

                                <div class="rating-stars mb-2">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                                        <label for="star{{ $i }}"><i class="fas fa-star"></i></label>
                                    @endfor
                                </div>
                                <x-input-error :messages="$errors->get('rating')" class="mb-3 text-center" />

                                <label for="review" class="form-label">Ulasan (opsional)</label>
                                <textarea name="review" id="review" rows="3" class="form-control" placeholder="Ceritakan pengalaman kamu...">{{ old('review') }}</textarea>
                                <x-input-error :messages="$errors->get('review')" class="mt-2" />
                            </div>

                            <div class="modal-footer border-secondary-subtle">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Kirim Rating
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if ($errors->has('rating') || $errors->has('review'))
                <script>
                    {{-- Dijalankan langsung (bukan nunggu DOMContentLoaded), soalnya event itu tidak terpicu lagi saat konten disuntik ulang lewat navigasi AJAX. --}}
                    (function () {
                        var modalEl = document.getElementById('ratingModal');
                        if (modalEl) {
                            bootstrap.Modal.getOrCreateInstance(modalEl).show();
                        }
                    })();
                </script>
            @endif
        @endif

        {{-- REMINDER: PEMBAYARAN ONLINE BELUM SELESAI --}}
        @if ($reservasiPerluBayar)
            <div class="bg-panel card-bordered-gold border-start border-4 border-danger rounded-3 p-3 p-md-4 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 fade-in-up">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h3 class="fs-6 fw-bold mb-1">Pembayaran Belum Selesai</h3>
                        <p class="mb-0 text-body-secondary small">
                            Kamu punya pembayaran yang perlu diselesaikan untuk
                            {{ $reservasiPerluBayar->service->nama_layanan ?? '-' }}
                            ({{ \Carbon\Carbon::parse($reservasiPerluBayar->tanggal)->format('d/m/Y') }}).
                        </p>
                    </div>
                </div>
                <a href="{{ route('reservasi.show', $reservasiPerluBayar->id) }}" class="btn btn-danger btn-sm text-nowrap">
                    Lihat Detail
                </a>
            </div>
        @endif

        {{-- "WAKTUNYA POTONG LAGI!" --}}
        @if ($waktunyaPotongLagi !== null)
            <div class="bg-panel card-bordered-gold border-start border-4 border-gold rounded-3 p-3 p-md-4 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 fade-in-up">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h3 class="fs-6 fw-bold mb-1">Waktunya Potong Lagi!</h3>
                        <p class="mb-0 text-body-secondary small">
                            Sudah {{ $waktunyaPotongLagi }} hari sejak kunjungan terakhirmu, waktunya potong lagi!
                        </p>
                    </div>
                </div>
                <a href="{{ route('reservasi.create') }}" class="btn btn-primary btn-sm text-nowrap">
                    <i class="fas fa-calendar-plus"></i> Buat Reservasi Baru
                </a>
            </div>
        @endif

        {{-- KARTU MEMBER DIGITAL --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-7 col-lg-5">
                <div class="member-card member-card-{{ $memberLevel }} fade-in-up">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <span class="member-card-label">
                            Ma'Bung Barbershop &mdash; Kartu Member
                        </span>
                        <span class="member-card-level-badge">
                            @if ($memberLevel === 'gold')
                                <i class="fas fa-crown"></i> Gold
                            @elseif ($memberLevel === 'silver')
                                <i class="fas fa-award"></i> Silver
                            @else
                                <i class="fas fa-medal"></i> Bronze
                            @endif
                        </span>
                    </div>

                    <p class="member-card-name mb-4">{{ $user->name }}</p>

                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <span class="member-card-label d-block mb-1">No. Member</span>
                            <span class="member-card-number">{{ $nomorMember }}</span>
                        </div>
                        <div class="text-end">
                            <span class="member-card-label d-block mb-1">Total Kunjungan</span>
                            <span class="fs-4 fw-bold">{{ $totalKunjungan }}x</span>
                        </div>
                    </div>
                </div>

                {{-- Badge/achievement yang sudah didapat pelanggan --}}
                @if (count($badges) > 0)
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @foreach ($badges as $badge)
                            <span
                                class="member-badge-icon"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="{{ $badge['label'] }} - {{ $badge['deskripsi'] }}"
                            >
                                <i class="fas {{ $badge['icon'] }}"></i>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- RESERVASI TERDEKAT + AKSI CEPAT --}}
        <div class="row g-4">

            {{-- Reservasi Terdekat --}}
            <div class="col-12 col-md-7 col-lg-8">
                <div
                    id="reservasi-terdekat-card"
                    class="bg-panel card-bordered-gold card-hover-gold rounded-3 shadow-sm p-4 h-100 d-flex flex-column fade-in-up fade-in-up-1"
                    @if ($reservasiTerdekat) data-reservasi-id="{{ $reservasiTerdekat->id }}" @endif
                >
                    <h3 class="fs-5 fw-bold mb-3">
                        Reservasi Terdekat
                    </h3>

                    @if ($reservasiTerdekat)
                        @php
                            $statusReservasi = strtolower($reservasiTerdekat->status ?? 'pending');

                            // Tahap stepper: 1=Dipesan, 2=Dikonfirmasi,
                            // 3=Sedang Dilayani, 4=Selesai
                            $stepReservasi = match (true) {
                                in_array($statusReservasi, ['completed', 'complete', 'selesai', 'done']) => 4,
                                $statusReservasi === 'sedang_dilayani' => 3,
                                in_array($statusReservasi, ['confirmed', 'confirm']) => 2,
                                default => 1,
                            };
                        @endphp

                        <div class="mb-2">
                            <span class="text-body-secondary small">Layanan</span>
                            <p class="fw-bold mb-0">{{ $reservasiTerdekat->service->nama_layanan ?? '-' }}</p>
                        </div>

                        <div class="d-flex gap-4 mb-3">
                            <div>
                                <span class="text-body-secondary small">Tanggal</span>
                                <p class="fw-bold mb-0">{{ \Carbon\Carbon::parse($reservasiTerdekat->tanggal)->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <span class="text-body-secondary small">Jam</span>
                                <p class="fw-bold mb-0">{{ $reservasiTerdekat->jam ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- Stepper progress: Dipesan -> Dikonfirmasi -> Sedang Dilayani -> Selesai --}}
                        <div class="reservasi-stepper mb-3" data-stepper>
                            <div class="reservasi-stepper-step {{ $stepReservasi >= 1 ? 'is-active' : '' }}" data-step="1">
                                <span class="reservasi-stepper-dot"></span>
                                <span class="reservasi-stepper-label">Dipesan</span>
                            </div>
                            <div class="reservasi-stepper-connector {{ $stepReservasi >= 2 ? 'is-active' : '' }}" data-connector="2"></div>
                            <div class="reservasi-stepper-step {{ $stepReservasi >= 2 ? 'is-active' : '' }}" data-step="2">
                                <span class="reservasi-stepper-dot"></span>
                                <span class="reservasi-stepper-label">Dikonfirmasi</span>
                            </div>
                            <div class="reservasi-stepper-connector {{ $stepReservasi >= 3 ? 'is-active' : '' }}" data-connector="3"></div>
                            <div class="reservasi-stepper-step {{ $stepReservasi >= 3 ? 'is-active' : '' }}" data-step="3">
                                <span class="reservasi-stepper-dot"></span>
                                <span class="reservasi-stepper-label">Dilayani</span>
                            </div>
                            <div class="reservasi-stepper-connector {{ $stepReservasi >= 4 ? 'is-active' : '' }}" data-connector="4"></div>
                            <div class="reservasi-stepper-step {{ $stepReservasi >= 4 ? 'is-active' : '' }}" data-step="4">
                                <span class="reservasi-stepper-dot"></span>
                                <span class="reservasi-stepper-label">Selesai</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <span class="text-body-secondary small d-block mb-1">Status</span>

                            <div data-status-badge>
                                @if ($statusReservasi === 'pending')
                                    <span class="badge text-bg-warning">Menunggu Konfirmasi</span>
                                @elseif ($statusReservasi === 'confirmed')
                                    <span class="badge text-bg-info">Dikonfirmasi</span>
                                @elseif ($statusReservasi === 'sedang_dilayani')
                                    <span class="badge text-bg-primary">Sedang Dilayani</span>
                                @elseif (in_array($statusReservasi, ['completed', 'complete', 'selesai', 'done']))
                                    <span class="badge text-bg-success">Selesai</span>
                                @elseif (in_array($statusReservasi, ['cancelled', 'canceled', 'batal']))
                                    <span class="badge text-bg-danger">Dibatalkan</span>
                                @else
                                    <span class="badge text-bg-secondary">{{ ucfirst($statusReservasi) }}</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('reservasi.show', $reservasiTerdekat->id) }}" class="btn btn-outline-primary mt-auto">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                    @else
                        <div class="text-center text-body-secondary my-auto py-3">
                            <p class="mb-0">Belum ada reservasi aktif saat ini.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Aksi Cepat --}}
            <div class="col-12 col-md-5 col-lg-4">
                <div class="bg-panel card-bordered-gold card-hover-gold rounded-3 shadow-sm p-4 h-100 d-flex flex-column fade-in-up fade-in-up-2">
                    <h3 class="fs-5 fw-bold mb-3">
                        Aksi Cepat
                    </h3>

                    <div class="bg-surface rounded-3 p-3 mb-4 text-center">
                        <p class="fs-3 fw-bold text-gold mb-0" data-stat="reservasiSelesaiBulanIni">{{ $reservasiSelesaiBulanIni }}</p>
                        <p class="text-body-secondary small mb-0">reservasi selesai bulan ini</p>
                    </div>

                    <div class="d-flex flex-column gap-3 mt-auto">
                        <a href="{{ route('reservasi.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-plus"></i> Buat Reservasi Baru
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- INSPIRASI GAYA + TIPS GROOMING HARIAN --}}
        <div class="row g-4 mt-1">

            {{-- Inspirasi Gaya untuk Kamu --}}
            <div class="col-12 col-lg-8">
                <div class="bg-panel card-bordered-gold rounded-3 shadow-sm p-4 h-100 fade-in-up fade-in-up-3">
                    <h3 class="fs-5 fw-bold mb-3">
                        Inspirasi Gaya untuk Kamu
                    </h3>
                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <img
                                src="{{ asset('images/andika.jpeg') }}"
                                class="img-fluid rounded-3 w-100"
                                style="aspect-ratio: 1 / 1; object-fit: cover;"
                                alt="Gaya rambut side part klimis"
                                loading="lazy">
                        </div>
                        <div class="col-6 col-md-4">
                            <img
                                src="{{ asset('images/nandar.jpeg') }}"
                                class="img-fluid rounded-3 w-100"
                                style="aspect-ratio: 1 / 1; object-fit: cover;"
                                alt="Gaya taper fade rapi"
                                loading="lazy">
                        </div>
                        <div class="col-6 col-md-4">
                            <img
                                src="{{ asset('images/ridi.jpeg') }}"
                                class="img-fluid rounded-3 w-100"
                                style="aspect-ratio: 1 / 1; object-fit: cover;"
                                alt="Gaya potongan rapi"
                                loading="lazy">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tips Grooming Harian --}}
            <div class="col-12 col-lg-4">
                <div class="bg-panel card-bordered-gold rounded-3 shadow-sm p-4 h-100 d-flex flex-column fade-in-up fade-in-up-4">
                    <h3 class="fs-5 fw-bold mb-3">
                        Tips Grooming Hari Ini
                    </h3>
                    <p class="text-body-secondary mb-0">{{ $tipHariIni }}</p>
                </div>
            </div>

        </div>

        {{-- TRANSFORMASI KAMU - disembunyikan total kalau belum ada reservasi dengan kedua foto before/after. --}}
        @if ($galeriTransformasi->isNotEmpty())
            <div class="row g-4 mt-1">
                <div class="col-12">
                    <div class="bg-panel card-bordered-gold rounded-3 shadow-sm p-4 fade-in-up">
                        <h3 class="fs-5 fw-bold mb-3">
                            Transformasi Kamu
                        </h3>

                        <div class="row g-3">
                            @foreach ($galeriTransformasi as $item)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div
                                        class="bg-surface rounded-3 p-2 h-100 transformasi-card"
                                        style="cursor: pointer;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#transformasiView{{ $item->id }}"
                                    >
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <img
                                                    src="{{ asset('storage/' . $item->foto_before) }}"
                                                    class="img-fluid rounded-2 w-100"
                                                    style="aspect-ratio: 1 / 1; object-fit: cover;"
                                                    alt="Sebelum - {{ $item->service->nama_layanan ?? '-' }}">
                                                <p class="text-center small text-body-secondary mb-0 mt-1">Before</p>
                                            </div>
                                            <div class="col-6">
                                                <img
                                                    src="{{ asset('storage/' . $item->foto_after) }}"
                                                    class="img-fluid rounded-2 w-100"
                                                    style="aspect-ratio: 1 / 1; object-fit: cover;"
                                                    alt="Sesudah - {{ $item->service->nama_layanan ?? '-' }}">
                                                <p class="text-center small text-body-secondary mb-0 mt-1">After</p>
                                            </div>
                                        </div>
                                        <p class="small fw-semibold mb-0 mt-1 text-truncate">
                                            {{ $item->service->nama_layanan ?? '-' }}
                                        </p>
                                        <p class="small text-body-secondary mb-0">
                                            {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal lihat foto ukuran penuh, satu per item, muncul saat thumbnail diklik. --}}
            @foreach ($galeriTransformasi as $item)
                <div class="modal fade" id="transformasiView{{ $item->id }}" tabindex="-1" aria-labelledby="transformasiView{{ $item->id }}Label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                        <div class="modal-content bg-panel">
                            <div class="modal-header border-secondary-subtle">
                                <h5 class="modal-title text-gold" id="transformasiView{{ $item->id }}Label">
                                    {{ $item->service->nama_layanan ?? '-' }} &mdash;
                                    {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6 text-center">
                                        <p class="fw-semibold text-body-secondary mb-2">Before</p>
                                        <img
                                            src="{{ asset('storage/' . $item->foto_before) }}"
                                            class="img-fluid rounded-3"
                                            alt="Sebelum - {{ $item->service->nama_layanan ?? '-' }}">
                                    </div>
                                    <div class="col-12 col-md-6 text-center">
                                        <p class="fw-semibold text-body-secondary mb-2">After</p>
                                        <img
                                            src="{{ asset('storage/' . $item->foto_after) }}"
                                            class="img-fluid rounded-3"
                                            alt="Sesudah - {{ $item->service->nama_layanan ?? '-' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

    </div>
    </div>

    {{-- Tooltip Bootstrap wajib diinisialisasi lewat JS, tidak otomatis lewat data-attribute saja. --}}
    <script>
        (function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                bootstrap.Tooltip.getOrCreateInstance(el);
            });
        })();
    </script>

    {{-- Polling tiap 20 detik: kartu "Reservasi Terdekat" & angka "selesai bulan ini" ikut ter-update tanpa reload. --}}
    <script>
        (function () {
            var statusUrl = '{{ route('reservasi.statusUpdates') }}';

            var statusBadgeMap = {
                pending: ['badge text-bg-warning', 'Menunggu Konfirmasi'],
                confirmed: ['badge text-bg-info', 'Dikonfirmasi'],
                sedang_dilayani: ['badge text-bg-primary', 'Sedang Dilayani'],
                done: ['badge text-bg-success', 'Selesai'],
                cancelled: ['badge text-bg-danger', 'Dibatalkan'],
            };

            var stepMap = { pending: 1, confirmed: 2, sedang_dilayani: 3, done: 4, cancelled: 1 };

            function updateReservasiTerdekat(data) {
                var card = document.getElementById('reservasi-terdekat-card');
                if (!card) {
                    return;
                }

                var id = card.getAttribute('data-reservasi-id');
                if (!id) {
                    return;
                }

                var match = data.find(function (item) {
                    return String(item.id) === String(id);
                });
                if (!match) {
                    return;
                }

                var badgeInfo = statusBadgeMap[match.status] || ['badge text-bg-secondary', match.status];
                var badgeContainer = card.querySelector('[data-status-badge]');
                if (badgeContainer) {
                    badgeContainer.innerHTML = '<span class="' + badgeInfo[0] + '">' + badgeInfo[1] + '</span>';
                }

                var step = stepMap[match.status] || 1;

                card.querySelectorAll('[data-step]').forEach(function (el) {
                    var stepNum = parseInt(el.getAttribute('data-step'), 10);
                    el.classList.toggle('is-active', step >= stepNum);
                });

                card.querySelectorAll('[data-connector]').forEach(function (el) {
                    var connectorNum = parseInt(el.getAttribute('data-connector'), 10);
                    el.classList.toggle('is-active', step >= connectorNum);
                });
            }

            function updateAksiCepat(payload) {
                var el = document.querySelector('[data-stat="reservasiSelesaiBulanIni"]');
                if (!el) {
                    return;
                }

                // Dihitung di server, bukan di JS, biar tidak meleset gara-gara zona waktu perangkat.
                var count = payload.reservasiSelesaiBulanIni;
                if (typeof count === 'number' && parseInt(el.textContent, 10) !== count) {
                    el.textContent = count;
                }
            }

            function refreshReservasiStatus() {
                fetch(statusUrl, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Gagal memuat status reservasi');
                        }
                        return response.json();
                    })
                    .then(function (payload) {
                        updateReservasiTerdekat(payload.reservations);
                        updateAksiCepat(payload);
                    })
                    .catch(function () {
                        // Diamkan saja - coba lagi di polling berikutnya.
                    });
            }

            if (window.__reservasiPollInterval) {
                clearInterval(window.__reservasiPollInterval);
            }
            window.__reservasiPollInterval = setInterval(refreshReservasiStatus, 20000);
        })();
    </script>
</x-app-layout>
