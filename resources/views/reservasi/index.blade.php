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

        {{-- Selalu bentuk card (bukan tabel), lihat .table-card-grid di theme.css. --}}
        @if($reservations->isEmpty())
            <div class="bg-panel p-5 rounded-3 text-center text-body-secondary">
                <p class="mb-0">Belum ada reservasi. Buat reservasi sekarang!</p>
            </div>
        @else
            <div class="table-card-list table-card-grid">
                @foreach($reservations as $reservasi)
                    @php
                        $paymentBadge = $reservasi->payment_badge;

                        $reservationStatus = strtolower($reservasi->status ?? 'pending');
                        $reservationBadge = match(true) {
                            $reservationStatus === 'pending' => ['warning', 'fa-clock', 'Pending'],
                            in_array($reservationStatus, ['confirmed', 'confirm']) => ['info', 'fa-check', 'Dikonfirmasi'],
                            $reservationStatus === 'sedang_dilayani' => ['primary', 'fa-scissors', 'Sedang Dilayani'],
                            in_array($reservationStatus, ['completed', 'complete', 'selesai', 'done']) => ['success', 'fa-check-double', 'Selesai'],
                            in_array($reservationStatus, ['cancelled', 'canceled', 'batal']) => ['danger', 'fa-times', 'Dibatalkan'],
                            default => ['secondary', 'fa-info-circle', ucfirst($reservationStatus)],
                        };

                        // Reservasi masih bisa dibatalkan pelanggan selama
                        // belum "done" (selesai dikerjakan barber).
                        $bisaDibatalkan = in_array($reservationStatus, ['pending', 'confirmed', 'sedang_dilayani']);
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

                            @if($bisaDibatalkan)
                                <form
                                    action="{{ route('reservasi.destroy', $reservasi->id) }}"
                                    method="POST"
                                    onsubmit="return konfirmasiDanNonaktifkan(this, 'Apakah Anda yakin ingin membatalkan reservasi ini?')"
                                    class="mb-0"
                                    data-batalkan-form
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                        <i class="fas fa-times me-1"></i> Batalkan
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Polling tiap 20 detik biar badge/nomor antrian/tombol Batalkan ikut ter-update tanpa reload. --}}
    <script>
        (function () {
            // Cegah double-submit tombol "Batalkan", email butuh beberapa detik untuk terkirim.
            function nonaktifkanTombolSubmit(form, teks) {
                var btn = form.querySelector('button[type="submit"]');
                if (!btn || btn.disabled) {
                    return;
                }
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (teks || 'Memproses...');
            }

            window.konfirmasiDanNonaktifkan = function (form, pesan) {
                if (!confirm(pesan)) {
                    return false;
                }
                nonaktifkanTombolSubmit(form);
                return true;
            };

            var statusUrl = '{{ route('reservasi.statusUpdates') }}';

            var statusBadgeMap = {
                pending: ['warning', 'fa-clock', 'Pending'],
                confirmed: ['info', 'fa-check', 'Dikonfirmasi'],
                sedang_dilayani: ['primary', 'fa-scissors', 'Sedang Dilayani'],
                done: ['success', 'fa-check-double', 'Selesai'],
                cancelled: ['danger', 'fa-times', 'Dibatalkan'],
            };

            function badgeHtmlFromInfo(info) {
                return '<span class="badge rounded-pill text-bg-' + info[0] + '">' +
                    '<i class="fas ' + info[1] + ' me-1"></i>' + info[2] +
                    '</span>';
            }

            function badgeHtml(map, key) {
                var label = key ? key.charAt(0).toUpperCase() + key.slice(1) : '-';
                return badgeHtmlFromInfo(map[key] || ['secondary', 'fa-info-circle', label]);
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
                                    paymentEl.innerHTML = badgeHtmlFromInfo(item.payment_badge || ['secondary', 'fa-info-circle', item.payment_status]);
                                }

                                var statusEl = row.querySelector('[data-status-badge]');
                                if (statusEl) {
                                    statusEl.innerHTML = badgeHtml(statusBadgeMap, item.status);
                                }

                                var queueEl = row.querySelector('[data-queue-value]');
                                if (queueEl) {
                                    queueEl.textContent = item.nomor_antrian || '-';
                                }

                                // Sembunyikan lagi form Batalkan kalau live-sync mendeteksi status berubah jadi "done".
                                var batalkanForm = row.querySelector('[data-batalkan-form]');
                                if (batalkanForm) {
                                    var statusBisaBatal = ['pending', 'confirmed', 'sedang_dilayani'].indexOf(item.status) !== -1;
                                    batalkanForm.classList.toggle('d-none', !statusBisaBatal);
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
