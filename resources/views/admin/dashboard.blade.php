<x-admin-layout title="Dashboard Admin">

    {{-- Elemen dekoratif blur gold-amber --}}
    <div class="blob-decor" style="width: 20rem; height: 20rem; top: -4rem; right: -5rem;"></div>
    <div class="blob-decor" style="width: 16rem; height: 16rem; bottom: -4rem; left: 10rem;"></div>

    <div class="position-relative">

    {{-- Kartu Statistik --}}
    <div class="row g-4 mb-4">

        {{-- Total Reservasi --}}
        <div class="col-6 col-lg-3">
            <div class="admin-stat-card bg-panel p-4 rounded-3 shadow text-center h-100 fade-in-up fade-in-up-1">
                <i class="fas fa-calendar-check fa-2x text-gold mb-3"></i>
                <h3 class="fs-5 fw-bold mb-1">Total Reservasi</h3>
                <p class="display-6 fw-bold text-gold mb-0" data-stat="totalReservasi" data-count-target="{{ $totalReservasi }}">0</p>
            </div>
        </div>

        {{-- Total Layanan --}}
        <div class="col-6 col-lg-3">
            <div class="admin-stat-card bg-panel p-4 rounded-3 shadow text-center h-100 fade-in-up fade-in-up-2">
                <i class="fas fa-cut fa-2x text-gold mb-3"></i>
                <h3 class="fs-5 fw-bold mb-1">Total Layanan</h3>
                <p class="display-6 fw-bold text-gold mb-0" data-stat="totalLayanan" data-count-target="{{ $totalLayanan }}">0</p>
            </div>
        </div>

        {{-- Antrian Hari Ini --}}
        <div class="col-6 col-lg-3">
            <div class="admin-stat-card bg-panel p-4 rounded-3 shadow text-center h-100 fade-in-up fade-in-up-3">
                <i class="fas fa-users fa-2x text-gold mb-3"></i>
                <h3 class="fs-5 fw-bold mb-1">Antrian Hari Ini</h3>
                <p class="display-6 fw-bold text-gold mb-0" data-stat="antrianHariIni" data-count-target="{{ $antrianHariIni }}">0</p>
            </div>
        </div>

        {{-- Kapasitas Hari Ini --}}
        <div class="col-6 col-lg-3">
            <div class="admin-stat-card bg-panel p-4 rounded-3 shadow text-center h-100 fade-in-up fade-in-up-4">
                <i class="fas fa-gauge-high fa-2x text-gold mb-3"></i>
                <h3 class="fs-5 fw-bold mb-1">Kapasitas Hari Ini</h3>
                <p class="fs-3 fw-bold text-gold mb-0">
                    <span data-stat="kapasitasText">{{ $slotTerisiHariIni }}/{{ $totalSlotHariIni }}</span>
                    <span class="fs-6 fw-normal text-body-secondary d-block">slot terisi</span>
                </p>
            </div>
        </div>

        {{-- Menunggu Konfirmasi - aksen warning, perlu ditindak.
             Bisa diklik, mengarah ke tab "Menunggu Konfirmasi" (default)
             di halaman Kelola Reservasi. --}}
        <div class="col-6 col-lg-3">
            <a
                href="{{ route('admin.reservasi.index') }}"
                class="admin-stat-card admin-stat-card-warning admin-stat-card-link border-warning border-2 bg-panel p-4 rounded-3 shadow text-center text-decoration-none text-reset h-100 d-block fade-in-up fade-in-up-1"
            >
                <i class="fas fa-hourglass-half fa-2x text-warning mb-3"></i>
                <h3 class="fs-5 fw-bold mb-1">Menunggu Konfirmasi</h3>
                <p class="display-6 fw-bold text-warning mb-0" data-stat="menungguKonfirmasi" data-count-target="{{ $menungguKonfirmasi }}">0</p>
            </a>
        </div>

        {{-- Perlu Verifikasi Pembayaran - aksen warning, perlu ditindak.
             Bisa diklik, mengarah ke tab "Perlu Verifikasi Pembayaran"
             di halaman Kelola Reservasi (lewat query ?tab=verifikasi). --}}
        <div class="col-6 col-lg-3">
            <a
                href="{{ route('admin.reservasi.index', ['tab' => 'verifikasi']) }}"
                class="admin-stat-card admin-stat-card-warning admin-stat-card-link border-warning border-2 bg-panel p-4 rounded-3 shadow text-center text-decoration-none text-reset h-100 d-block fade-in-up fade-in-up-2"
            >
                <i class="fas fa-file-invoice-dollar fa-2x text-warning mb-3"></i>
                <h3 class="fs-5 fw-bold mb-1">Perlu Verifikasi Pembayaran</h3>
                <p class="display-6 fw-bold text-warning mb-0" data-stat="perluVerifikasiPembayaran" data-count-target="{{ $perluVerifikasiPembayaran }}">0</p>
            </a>
        </div>

        {{-- Total Pendapatan Bulan Ini - aksen sukses/hijau --}}
        <div class="col-12 col-lg-6">
            <div class="admin-stat-card admin-stat-card-success border-success border-2 bg-panel p-4 rounded-3 shadow text-center h-100 fade-in-up fade-in-up-3">
                <i class="fas fa-sack-dollar fa-2x text-success mb-3"></i>
                <h3 class="fs-5 fw-bold mb-1">Total Pendapatan Bulan Ini</h3>
                <p class="fs-2 fw-bold text-success mb-0" data-stat="pendapatanText">
                    Rp {{ number_format($totalPendapatanBulanIni, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- =============================================== --}}
    {{-- RESERVASI HARI INI --}}
    {{-- Semua reservasi tanggal hari ini (bukan sekadar 5   --}}
    {{-- terbaru secara umum), diurutkan berdasarkan jam.     --}}
    {{-- =============================================== --}}
    <div class="bg-panel rounded-3 shadow p-4 mb-4 fade-in-up">
        <h3 class="fs-4 fw-bold text-gold mb-3">
            <i class="fas fa-calendar-day"></i> Reservasi Hari Ini
        </h3>

        @if ($reservasiHariIni->isEmpty())
            <p class="text-body-secondary text-center py-3 mb-0">
                Tidak ada reservasi untuk hari ini.
            </p>
        @else
            <div class="table-responsive rounded-3">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead class="bg-surface">
                    <tr>
                        <th>Jam</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reservasiHariIni as $reservasi)
                    <tr>
                        <td class="fw-semibold">{{ $reservasi->jam }}</td>
                        <td>{{ $reservasi->user->name }}</td>
                        <td>{{ $reservasi->service->nama_layanan }}</td>
                        <td>
                            @if($reservasi->status == 'pending')
                                <span class="badge text-bg-warning">Pending</span>
                            @elseif($reservasi->status == 'confirmed')
                                <span class="badge text-bg-info">Confirmed</span>
                            @elseif($reservasi->status == 'cancelled')
                                <span class="badge text-bg-danger">Cancelled</span>
                            @elseif($reservasi->status == 'done')
                                <span class="badge text-bg-success">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    {{-- =============================================== --}}
    {{-- GRAFIK TREN RESERVASI 7 HARI TERAKHIR --}}
    {{-- =============================================== --}}
    <div class="bg-panel rounded-3 shadow p-4 mb-4 fade-in-up">
        <h3 class="fs-4 fw-bold text-gold mb-3">
            <i class="fas fa-chart-column"></i> Tren Reservasi 7 Hari Terakhir
        </h3>
        <div style="max-height: 280px;">
            <canvas id="trenReservasiChart" height="90"></canvas>
        </div>
    </div>

    {{-- Reservasi Terbaru --}}
    <div class="bg-panel rounded-3 shadow p-4 fade-in-up fade-in-up-4">
        <h3 class="fs-4 fw-bold text-gold mb-3">
            <i class="fas fa-clock"></i> Reservasi Terbaru
        </h3>
        <div class="table-responsive rounded-3">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead class="bg-surface">
                <tr>
                    <th>Pelanggan</th>
                    <th>Layanan</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loop data reservasi terbaru --}}
                @forelse($reservasiTerbaru as $reservasi)
                <tr data-reservasi-row="{{ $reservasi->id }}">
                    <td>{{ $reservasi->user->name }}</td>
                    <td>{{ $reservasi->service->nama_layanan }}</td>
                    <td>{{ $reservasi->tanggal }}</td>
                    <td>{{ $reservasi->jam }}</td>
                    <td data-status-cell>
                        @if($reservasi->status == 'pending')
                            <span class="badge text-bg-warning">Pending</span>
                        @elseif($reservasi->status == 'confirmed')
                            <span class="badge text-bg-info">Confirmed</span>
                        @elseif($reservasi->status == 'cancelled')
                            <span class="badge text-bg-danger">Cancelled</span>
                        @elseif($reservasi->status == 'done')
                            <span class="badge text-bg-success">Selesai</span>
                        @endif
                    </td>
                    <td data-aksi-cell>
                        @if($reservasi->status == 'pending')
                            <button
                                type="button"
                                class="btn btn-warning btn-sm text-nowrap konfirmasi-cepat-btn"
                                data-id="{{ $reservasi->id }}"
                                data-url="{{ route('admin.reservasi.update', $reservasi->id) }}"
                            >
                                <i class="fas fa-check"></i> Konfirmasi
                            </button>
                        @else
                            <a href="{{ route('admin.reservasi.index') }}" class="btn btn-outline-primary btn-sm text-nowrap">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                {{-- Tampilkan pesan jika belum ada reservasi --}}
                <tr>
                    <td colspan="6" class="text-center text-body-secondary py-4">
                        Belum ada reservasi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    </div>

    {{-- Animasi angka statistik menghitung naik dari 0 --}}
    <script>
        (function () {
            var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var counters = document.querySelectorAll('[data-count-target]');

            counters.forEach(function (el) {
                var target = parseInt(el.getAttribute('data-count-target'), 10) || 0;

                if (prefersReducedMotion || target === 0) {
                    el.textContent = target;
                    return;
                }

                var duration = 900;
                var startTime = null;

                function step(timestamp) {
                    if (!startTime) startTime = timestamp;
                    var progress = Math.min((timestamp - startTime) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.floor(eased * target);

                    if (progress < 1) {
                        requestAnimationFrame(step);
                    } else {
                        el.textContent = target;
                    }
                }

                requestAnimationFrame(step);
            });
        })();
    </script>

    {{-- Polling statistik: kartu di baris atas (Total Reservasi, dst)
         di-refresh otomatis tiap 20 detik lewat endpoint JSON
         admin.dashboard.stats, supaya angkanya ter-update sendiri kalau
         ada reservasi baru masuk selagi admin sedang membuka dashboard
         - tanpa perlu reload halaman manual. --}}
    <script>
        (function () {
            var statsUrl = '{{ route('admin.dashboard.stats') }}';
            var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var countableKeys = ['totalReservasi', 'totalLayanan', 'antrianHariIni', 'menungguKonfirmasi', 'perluVerifikasiPembayaran'];

            function animateCount(el, from, to) {
                if (prefersReducedMotion) {
                    el.textContent = to;
                    return;
                }

                var duration = 600;
                var startTime = null;

                function step(timestamp) {
                    if (!startTime) startTime = timestamp;
                    var progress = Math.min((timestamp - startTime) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.floor(from + (to - from) * eased);

                    if (progress < 1) {
                        requestAnimationFrame(step);
                    } else {
                        el.textContent = to;
                    }
                }

                requestAnimationFrame(step);
            }

            function refreshStats() {
                fetch(statsUrl, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Gagal memuat statistik');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        countableKeys.forEach(function (key) {
                            var el = document.querySelector('[data-stat="' + key + '"]');
                            if (!el) {
                                return;
                            }

                            var current = parseInt(el.getAttribute('data-count-target'), 10) || 0;
                            var target = data[key];

                            if (current !== target) {
                                el.setAttribute('data-count-target', target);
                                animateCount(el, current, target);
                            }
                        });

                        var kapasitasEl = document.querySelector('[data-stat="kapasitasText"]');
                        if (kapasitasEl) {
                            kapasitasEl.textContent = data.slotTerisiHariIni + '/' + data.totalSlotHariIni;
                        }

                        var pendapatanEl = document.querySelector('[data-stat="pendapatanText"]');
                        if (pendapatanEl) {
                            pendapatanEl.textContent = data.totalPendapatanBulanIniFormatted;
                        }
                    })
                    .catch(function () {
                        // Diamkan saja kalau gagal (mis. koneksi putus sesaat) -
                        // coba lagi otomatis di siklus polling berikutnya.
                    });
            }

            setInterval(refreshStats, 20000);
        })();
    </script>

    {{-- Grafik tren reservasi 7 hari terakhir (Chart.js via CDN) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var canvas = document.getElementById('trenReservasiChart');
            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: @json($trenReservasi->pluck('label')),
                    datasets: [{
                        label: 'Jumlah Reservasi',
                        data: @json($trenReservasi->pluck('jumlah')),
                        backgroundColor: 'rgba(250, 204, 21, 0.7)',
                        borderColor: '#facc15',
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 48,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.parsed.y + ' reservasi';
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            ticks: { color: '#d1d5db' },
                            grid: { display: false },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#d1d5db', precision: 0 },
                            grid: { color: 'rgba(255, 255, 255, 0.08)' },
                        },
                    },
                },
            });
        })();
    </script>

    {{-- Tombol "Konfirmasi" cepat (AJAX) di tabel Reservasi Terbaru -
         mengubah status jadi "confirmed" tanpa reload halaman, lalu
         mengganti isi baris tabel (badge status + tombol aksi) secara
         langsung di DOM. --}}
    <script>
        (function () {
            var csrfToken = '{{ csrf_token() }}';
            var indexUrl = '{{ route('admin.reservasi.index') }}';

            document.querySelectorAll('.konfirmasi-cepat-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var url = btn.getAttribute('data-url');
                    var originalHtml = btn.innerHTML;

                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                    var formData = new FormData();
                    formData.append('_method', 'PUT');
                    formData.append('_token', csrfToken);
                    formData.append('status', 'confirmed');

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: formData,
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Gagal mengonfirmasi reservasi');
                            }
                            return response.json();
                        })
                        .then(function () {
                            var row = btn.closest('tr');
                            if (!row) {
                                return;
                            }

                            var statusCell = row.querySelector('[data-status-cell]');
                            if (statusCell) {
                                statusCell.innerHTML = '<span class="badge text-bg-info">Confirmed</span>';
                            }

                            var aksiCell = row.querySelector('[data-aksi-cell]');
                            if (aksiCell) {
                                aksiCell.innerHTML =
                                    '<a href="' + indexUrl + '" class="btn btn-outline-primary btn-sm text-nowrap">' +
                                    '<i class="fas fa-eye"></i> Detail</a>';
                            }
                        })
                        .catch(function () {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                            alert('Gagal mengonfirmasi reservasi. Silakan coba lagi.');
                        });
                });
            });
        })();
    </script>

</x-admin-layout>
