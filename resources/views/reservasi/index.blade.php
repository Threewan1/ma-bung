<x-app-layout>

    {{-- Judul halaman mengambang di pojok kanan atas, transparan, dan
         tetap diam di tempat (fixed) walau halaman di-scroll. --}}
    <div class="page-title-floating">
        <h2 class="fs-4 fw-bold text-white mb-0">
            Reservasi Saya
        </h2>
    </div>

    <div class="container pt-5 pb-4">
        {{-- Pesan Sukses --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Indikator filter status aktif --}}
        @if(request('status'))
            <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span>
                    Menampilkan reservasi berstatus:
                    <strong>{{ request('status') === 'done' ? 'Selesai' : ucfirst(request('status')) }}</strong>
                </span>
                <a href="{{ route('reservasi.index') }}" class="btn btn-sm btn-outline-light">Tampilkan Semua</a>
            </div>
        @endif

        {{-- Tabel Reservasi --}}
        @if($reservations->isEmpty())
            <div class="bg-panel p-5 rounded-3 text-center text-body-secondary">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <p class="mb-0">Belum ada reservasi. Buat reservasi sekarang!</p>
            </div>
        @else
            {{-- ================================================= --}}
            {{-- VERSI TABEL (desktop, >=768px) --}}
            {{-- ================================================= --}}
            <div class="bg-panel rounded-3 overflow-hidden d-none d-md-block">
                <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="bg-surface">

                        <tr>

                            {{-- Nomor --}}
                            <th>No</th>

                            {{-- Nama layanan --}}
                            <th>Layanan</th>

                            {{-- Tanggal reservasi --}}
                            <th>Tanggal</th>

                            {{-- Jam reservasi --}}
                            <th>Jam</th>

                            {{-- Metode pembayaran --}}
                            <th>Pembayaran</th>

                            {{-- Status pembayaran --}}
                            <th>Status Bayar</th>

                            {{-- Nomor antrean --}}
                            <th>No. Antrian</th>

                            {{-- Status reservasi --}}
                            <th>Status Reservasi</th>

                            {{-- Tombol aksi --}}
                            <th>Aksi</th>

                        </tr>

                    </thead>
                    <tbody>
                        {{-- Melakukan perulangan terhadap seluruh data reservasi.
                            Nama $reservations harus sama dengan variabel
                            yang dikirim oleh ReservasiController. --}}
                        @foreach($reservations as $reservasi)

                            {{-- Baris untuk satu reservasi --}}
                            <tr data-reservasi-id="{{ $reservasi->id }}">

                                {{-- ========================================= --}}
                                {{-- NOMOR --}}
                                {{-- ========================================= --}}
                                <td>
                                    {{-- Menampilkan nomor urut --}}
                                    {{ $loop->iteration }}
                                </td>


                                {{-- ========================================= --}}
                                {{-- LAYANAN --}}
                                {{-- ========================================= --}}
                                <td>
                                    {{-- Menampilkan nama layanan --}}
                                    {{ $reservasi->service->nama_layanan ?? '-' }}
                                </td>


                                {{-- ========================================= --}}
                                {{-- TANGGAL --}}
                                {{-- ========================================= --}}
                                <td>
                                    {{-- Format tanggal reservasi --}}
                                    {{ \Carbon\Carbon::parse($reservasi->tanggal)->format('d/m/Y') }}
                                </td>


                                {{-- ========================================= --}}
                                {{-- JAM --}}
                                {{-- ========================================= --}}
                                <td>
                                    {{-- Menampilkan jam reservasi --}}
                                    {{ $reservasi->jam ?? '-' }}
                                </td>


                                {{-- ========================================= --}}
                                {{-- PEMBAYARAN --}}
                                {{-- ========================================= --}}
                                <td>

                                    {{--
                                        Menampilkan metode pembayaran.
                                        Contoh:
                                        - cash
                                        - transfer
                                        - qris
                                    --}}
                                    <div class="fw-medium">
                                        {{ ucfirst($reservasi->payment_method ?? '-') }}
                                    </div>

                                </td>


                                {{-- ========================================= --}}
                                {{-- STATUS PEMBAYARAN --}}
                                {{-- ========================================= --}}
                                <td data-payment-badge>

                                    {{--
                                        Status pembayaran dibuat menjadi badge
                                        agar lebih mudah dibaca.
                                    --}}
                                    @php
                                        $paymentStatus = strtolower($reservasi->payment_status ?? 'unpaid');
                                    @endphp


                                    {{-- STATUS BELUM BAYAR --}}
                                    @if($paymentStatus === 'unpaid')

                                        <span class="badge rounded-pill text-bg-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            Belum Bayar
                                        </span>


                                    {{-- STATUS MENUNGGU VERIFIKASI --}}
                                    @elseif($paymentStatus === 'waiting_verification')

                                        <span class="badge rounded-pill text-bg-warning">
                                            {{-- Ikon status menunggu --}}
                                            <i class="fas fa-clock me-1"></i>

                                            {{-- Teks status --}}
                                            Menunggu Verifikasi
                                        </span>


                                    {{-- STATUS PAID / BERHASIL --}}
                                    @elseif(in_array($paymentStatus, ['paid', 'success', 'settlement']))

                                        <span class="badge rounded-pill text-bg-success">
                                            {{-- Ikon pembayaran berhasil --}}
                                            <i class="fas fa-check-circle me-1"></i>

                                            {{-- Teks status --}}
                                            Lunas
                                        </span>


                                    {{-- STATUS REJECTED / GAGAL --}}
                                    @elseif(in_array($paymentStatus, ['rejected', 'failed', 'deny', 'cancel', 'expired']))

                                        <span class="badge rounded-pill text-bg-danger">
                                            {{-- Ikon pembayaran gagal --}}
                                            <i class="fas fa-times-circle me-1"></i>

                                            {{-- Teks status --}}
                                            Ditolak
                                        </span>


                                    {{-- STATUS LAINNYA --}}
                                    @else

                                        <span class="badge rounded-pill text-bg-secondary">
                                            {{-- Ikon status lainnya --}}
                                            <i class="fas fa-info-circle me-1"></i>

                                            {{-- Menampilkan status asli --}}
                                            {{ ucfirst($paymentStatus) }}
                                        </span>

                                    @endif

                                </td>


                                {{-- ========================================= --}}
                                {{-- NOMOR ANTRIAN --}}
                                {{-- ========================================= --}}
                                <td data-queue-value>

                                    {{--
                                        Menampilkan nomor antrean.
                                        Jika belum mendapatkan nomor antrean,
                                        tampilkan tanda "-".
                                    --}}
                                    {{ $reservasi->queue?->nomor_antrian ?? '-' }}

                                </td>


                                {{-- ========================================= --}}
                                {{-- STATUS RESERVASI --}}
                                {{-- ========================================= --}}
                                <td data-status-badge>

                                    @php
                                        // Mengambil status reservasi.
                                        $reservationStatus = strtolower($reservasi->status ?? 'pending');
                                    @endphp


                                    {{-- STATUS PENDING --}}
                                    @if($reservationStatus === 'pending')

                                        <span class="badge rounded-pill text-bg-warning">
                                            <i class="fas fa-clock me-1"></i>
                                            Pending
                                        </span>


                                    {{-- STATUS CONFIRMED --}}
                                    @elseif(in_array($reservationStatus, ['confirmed', 'confirm']))

                                        <span class="badge rounded-pill text-bg-info">
                                            <i class="fas fa-check me-1"></i>
                                            Dikonfirmasi
                                        </span>


                                    {{-- STATUS SEDANG DILAYANI --}}
                                    @elseif($reservationStatus === 'sedang_dilayani')

                                        <span class="badge rounded-pill text-bg-primary">
                                            <i class="fas fa-scissors me-1"></i>
                                            Sedang Dilayani
                                        </span>


                                    {{-- STATUS COMPLETED --}}
                                    @elseif(in_array($reservationStatus, ['completed', 'complete', 'selesai', 'done']))

                                        <span class="badge rounded-pill text-bg-success">
                                            <i class="fas fa-check-double me-1"></i>
                                            Selesai
                                        </span>


                                    {{-- STATUS CANCELLED --}}
                                    @elseif(in_array($reservationStatus, ['cancelled', 'canceled', 'batal']))

                                        <span class="badge rounded-pill text-bg-danger">
                                            <i class="fas fa-times me-1"></i>
                                            Dibatalkan
                                        </span>


                                    {{-- STATUS LAINNYA --}}
                                    @else

                                        <span class="badge rounded-pill text-bg-secondary">
                                            <i class="fas fa-info-circle me-1"></i>
                                            {{ ucfirst($reservationStatus) }}
                                        </span>

                                    @endif

                                </td>


                                {{-- ========================================= --}}
                                {{-- AKSI --}}
                                {{-- ========================================= --}}
                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        {{-- ================================= --}}
                                        {{-- TOMBOL DETAIL --}}
                                        {{-- ================================= --}}

                                        <a
                                            href="{{ route('reservasi.show', $reservasi->id) }}"
                                            class="btn btn-info btn-sm text-white"
                                        >
                                            {{-- Ikon mata --}}
                                            <i class="fas fa-eye me-1"></i>

                                            {{-- Teks tombol --}}
                                            Detail
                                        </a>


                                        {{-- ================================= --}}
                                        {{-- TOMBOL BATALKAN --}}
                                        {{-- ================================= --}}

                                        {{--
                                            Tombol Batalkan hanya ditampilkan
                                            jika status reservasi masih pending.
                                        --}}
                                        <form
                                            action="{{ route('reservasi.destroy', $reservasi->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')"
                                            class="mb-0 {{ $reservationStatus === 'pending' ? '' : 'd-none' }}"
                                            data-batalkan-form
                                        >

                                            {{-- Proteksi CSRF Laravel --}}
                                            @csrf

                                            {{-- Method DELETE untuk menghapus/membatalkan --}}
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-danger btn-sm"
                                            >
                                                {{-- Ikon batal --}}
                                                <i class="fas fa-times me-1"></i>

                                                {{-- Teks tombol --}}
                                                Batalkan
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            {{-- ================================================= --}}
            {{-- VERSI CARD (mobile/tablet, <768px) --}}
            {{-- 2 kolom berdampingan mulai >=600px lewat --}}
            {{-- .table-card-grid, tetap 1 kolom di bawah itu --}}
            {{-- ================================================= --}}
            <div class="table-card-list table-card-grid d-block d-md-none">
                @foreach($reservations as $reservasi)
                    @php
                        $paymentStatus = strtolower($reservasi->payment_status ?? 'unpaid');
                        $paymentBadge = match(true) {
                            $paymentStatus === 'unpaid' => ['danger', 'fa-exclamation-circle', 'Belum Bayar'],
                            $paymentStatus === 'waiting_verification' => ['warning', 'fa-clock', 'Menunggu Verifikasi'],
                            in_array($paymentStatus, ['paid', 'success', 'settlement']) => ['success', 'fa-check-circle', 'Lunas'],
                            in_array($paymentStatus, ['rejected', 'failed', 'deny', 'cancel', 'expired']) => ['danger', 'fa-times-circle', 'Ditolak'],
                            default => ['secondary', 'fa-info-circle', ucfirst($paymentStatus)],
                        };

                        $reservationStatus = strtolower($reservasi->status ?? 'pending');
                        $reservationBadge = match(true) {
                            $reservationStatus === 'pending' => ['warning', 'fa-clock', 'Pending'],
                            in_array($reservationStatus, ['confirmed', 'confirm']) => ['info', 'fa-check', 'Dikonfirmasi'],
                            $reservationStatus === 'sedang_dilayani' => ['primary', 'fa-scissors', 'Sedang Dilayani'],
                            in_array($reservationStatus, ['completed', 'complete', 'selesai', 'done']) => ['success', 'fa-check-double', 'Selesai'],
                            in_array($reservationStatus, ['cancelled', 'canceled', 'batal']) => ['danger', 'fa-times', 'Dibatalkan'],
                            default => ['secondary', 'fa-info-circle', ucfirst($reservationStatus)],
                        };
                    @endphp

                    <div class="table-card-item" data-reservasi-id="{{ $reservasi->id }}">

                        {{-- Nomor + nama layanan sebagai judul card --}}
                        <div class="table-card-item-title">
                            #{{ $loop->iteration }} &mdash; {{ $reservasi->service->nama_layanan ?? '-' }}
                        </div>

                        {{-- Tanggal dan Jam berdampingan --}}
                        <div class="table-card-item-cols">
                            <div>
                                <div class="table-card-item-label">Tanggal</div>
                                <div class="table-card-item-value">{{ \Carbon\Carbon::parse($reservasi->tanggal)->format('d/m/Y') }}</div>
                            </div>
                            <div class="text-end">
                                <div class="table-card-item-label">Jam</div>
                                <div class="table-card-item-value">{{ $reservasi->jam ?? '-' }}</div>
                            </div>
                        </div>

                        {{-- Pembayaran --}}
                        <div class="table-card-item-row">
                            <span class="table-card-item-label">Pembayaran</span>
                            <span class="table-card-item-value">{{ ucfirst($reservasi->payment_method ?? '-') }}</span>
                        </div>

                        {{-- Status Bayar --}}
                        <div class="table-card-item-row">
                            <span class="table-card-item-label">Status Bayar</span>
                            <span data-payment-badge>
                                <span class="badge rounded-pill text-bg-{{ $paymentBadge[0] }}">
                                    <i class="fas {{ $paymentBadge[1] }} me-1"></i>{{ $paymentBadge[2] }}
                                </span>
                            </span>
                        </div>

                        {{-- Nomor Antrian --}}
                        <div class="table-card-item-row">
                            <span class="table-card-item-label">No. Antrian</span>
                            <span class="table-card-item-value" data-queue-value>{{ $reservasi->queue?->nomor_antrian ?? '-' }}</span>
                        </div>

                        {{-- Status Reservasi --}}
                        <div class="table-card-item-row">
                            <span class="table-card-item-label">Status Reservasi</span>
                            <span data-status-badge>
                                <span class="badge rounded-pill text-bg-{{ $reservationBadge[0] }}">
                                    <i class="fas {{ $reservationBadge[1] }} me-1"></i>{{ $reservationBadge[2] }}
                                </span>
                            </span>
                        </div>

                        {{-- Aksi --}}
                        <div class="table-card-item-footer">
                            <a
                                href="{{ route('reservasi.show', $reservasi->id) }}"
                                class="btn btn-info btn-sm text-white w-100"
                            >
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>

                            <form
                                action="{{ route('reservasi.destroy', $reservasi->id) }}"
                                method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')"
                                class="mb-0 {{ $reservationStatus === 'pending' ? '' : 'd-none' }}"
                                data-batalkan-form
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-times me-1"></i> Batalkan
                                </button>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{--
        Polling status reservasi: baris tabel & card (desktop maupun
        mobile) di-refresh otomatis tiap 20 detik lewat endpoint JSON
        reservasi.statusUpdates, supaya begitu admin mengonfirmasi/
        mengubah status reservasi, badge status bayar, status reservasi,
        nomor antrian, dan tombol "Batalkan" ikut berubah di sini tanpa
        pelanggan perlu reload halaman. Interval disimpan di window
        supaya tidak dobel kalau script ini disisipkan ulang lewat
        navigasi AJAX (lihat layouts/navigation.blade.php) - nama
        variabelnya sama dengan yang dipakai di dashboard.blade.php,
        supaya cuma satu polling yang aktif sesuai halaman mana yang
        sedang tampil.
    --}}
    <script>
        (function () {
            var statusUrl = '{{ route('reservasi.statusUpdates') }}';

            var paymentBadgeMap = {
                unpaid: ['danger', 'fa-exclamation-circle', 'Belum Bayar'],
                waiting_verification: ['warning', 'fa-clock', 'Menunggu Verifikasi'],
                paid: ['success', 'fa-check-circle', 'Lunas'],
                rejected: ['danger', 'fa-times-circle', 'Ditolak'],
            };

            var statusBadgeMap = {
                pending: ['warning', 'fa-clock', 'Pending'],
                confirmed: ['info', 'fa-check', 'Dikonfirmasi'],
                sedang_dilayani: ['primary', 'fa-scissors', 'Sedang Dilayani'],
                done: ['success', 'fa-check-double', 'Selesai'],
                cancelled: ['danger', 'fa-times', 'Dibatalkan'],
            };

            function badgeHtml(map, key) {
                var label = key ? key.charAt(0).toUpperCase() + key.slice(1) : '-';
                var info = map[key] || ['secondary', 'fa-info-circle', label];

                return '<span class="badge rounded-pill text-bg-' + info[0] + '">' +
                    '<i class="fas ' + info[1] + ' me-1"></i>' + info[2] +
                    '</span>';
            }

            function refreshReservasiSaya() {
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
                        payload.reservations.forEach(function (item) {
                            document.querySelectorAll('[data-reservasi-id="' + item.id + '"]').forEach(function (row) {
                                var paymentEl = row.querySelector('[data-payment-badge]');
                                if (paymentEl) {
                                    paymentEl.innerHTML = badgeHtml(paymentBadgeMap, item.payment_status);
                                }

                                var statusEl = row.querySelector('[data-status-badge]');
                                if (statusEl) {
                                    statusEl.innerHTML = badgeHtml(statusBadgeMap, item.status);
                                }

                                var queueEl = row.querySelector('[data-queue-value]');
                                if (queueEl) {
                                    queueEl.textContent = item.nomor_antrian || '-';
                                }

                                var batalkanForm = row.querySelector('[data-batalkan-form]');
                                if (batalkanForm) {
                                    batalkanForm.classList.toggle('d-none', item.status !== 'pending');
                                }
                            });
                        });
                    })
                    .catch(function () {
                        // Diamkan saja - coba lagi di polling berikutnya.
                    });
            }

            if (window.__reservasiPollInterval) {
                clearInterval(window.__reservasiPollInterval);
            }
            window.__reservasiPollInterval = setInterval(refreshReservasiSaya, 20000);
        })();
    </script>
</x-app-layout>
