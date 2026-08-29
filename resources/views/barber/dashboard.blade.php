<x-barber-layout title="Dashboard Barber">

    <div class="mb-4">
        <h2 class="fs-3 fw-bold text-gold mb-1">
            <i class="fas fa-scissors"></i> Halo, {{ $barber->nama ?? auth()->user()->name }}!
        </h2>
        <p class="text-body-secondary mb-0">
            Jadwal kamu untuk 7 hari ke depan.
        </p>
    </div>

    @if(! $barber)
        {{-- Akun barber tapi belum ada baris di tabel "barbers" yang
             terhubung - seharusnya tidak terjadi kalau dibuat lewat
             BarberSeeder, tapi dijaga untuk kasus akun dibuat manual. --}}
        <div class="bg-panel p-5 rounded-3 text-center text-body-secondary">
            <i class="fas fa-triangle-exclamation fa-3x mb-3 text-warning"></i>
            <p class="mb-0">Akun kamu belum terhubung ke data barber manapun. Silakan hubungi admin.</p>
        </div>
    @elseif(! $adaJadwal)
        {{-- Ketujuh hari kosong semua - tampilkan satu pesan umum,
             bukan 7 section kosong berturut-turut. --}}
        <div class="bg-panel p-5 rounded-3 text-center text-body-secondary">
            <i class="fas fa-calendar-xmark fa-3x mb-3"></i>
            <p class="mb-0">Belum ada jadwal untuk 7 hari ke depan.</p>
        </div>
    @else
        {{-- Satu section per hari yang PUNYA reservasi saja - hari
             tanpa reservasi sepenuhnya disembunyikan (tidak dirender). --}}
        @foreach($jadwal as $hari)
            @continue($hari['reservasi']->isEmpty())

            <div class="mb-4">
                <h3 class="fs-5 fw-bold mb-3">
                    <i class="fas fa-calendar-day text-gold"></i>
                    {{ $hari['label'] }}
                </h3>

                <div class="row g-4">
                    @foreach($hari['reservasi'] as $reservasi)
                        @php
                            $statusMap = [
                                'pending' => ['warning', 'Menunggu Konfirmasi'],
                                'confirmed' => ['info', 'Dikonfirmasi'],
                                'sedang_dilayani' => ['primary', 'Sedang Dilayani'],
                                'done' => ['success', 'Selesai'],
                            ];
                            $info = $statusMap[$reservasi->status] ?? ['secondary', ucfirst($reservasi->status)];

                            $paymentStatusMap = [
                                'unpaid' => ['danger', 'Belum Bayar'],
                                'waiting_verification' => ['warning', 'Menunggu Verifikasi'],
                                'paid' => ['success', 'Lunas'],
                                'rejected' => ['danger', 'Ditolak'],
                            ];
                            $paymentInfo = $paymentStatusMap[$reservasi->payment_status] ?? ['secondary', ucfirst($reservasi->payment_status ?? 'unpaid')];
                        @endphp
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="bg-panel card-bordered-gold rounded-3 shadow-sm p-4 h-100 d-flex flex-column" data-reservasi-id="{{ $reservasi->id }}" data-current-status="{{ $reservasi->status }}">

                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="fs-4 fw-bold text-gold">{{ $reservasi->jam }}</span>
                                    <span data-status-badge>
                                        <span class="badge text-bg-{{ $info[0] }}">{{ $info[1] }}</span>
                                    </span>
                                </div>

                                <div class="mb-2">
                                    <span class="text-body-secondary small d-block">Pelanggan</span>
                                    <p class="fw-bold mb-0">{{ $reservasi->user->name }}</p>
                                    @if($reservasi->user->no_hp)
                                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', $reservasi->user->no_hp) }}" target="_blank" rel="noopener" class="small text-gold text-decoration-none">
                                            <i class="fab fa-whatsapp"></i> {{ $reservasi->user->no_hp }}
                                        </a>
                                    @endif
                                </div>

                                <div class="mb-2">
                                    <span class="text-body-secondary small d-block">Layanan</span>
                                    <p class="fw-bold mb-0">{{ $reservasi->service->nama_layanan ?? '-' }}</p>
                                </div>

                                {{--
                                    Status pembayaran murni informasi -
                                    barber TIDAK bisa mengubahnya dari
                                    halaman ini. Konfirmasi pembayaran
                                    tetap wewenang admin.
                                --}}
                                <div class="mb-2">
                                    <span class="text-body-secondary small d-block">Pembayaran</span>
                                    <p class="fw-bold mb-0">
                                        {{ $reservasi->payment_method === 'online' ? 'Online' : 'COD' }}
                                        <span data-payment-badge>
                                            <span class="badge text-bg-{{ $paymentInfo[0] }}">{{ $paymentInfo[1] }}</span>
                                        </span>
                                    </p>
                                </div>

                                @if($reservasi->catatan)
                                    <div class="mb-3">
                                        <span class="text-body-secondary small d-block">Catatan</span>
                                        <p class="mb-0 fst-italic">&ldquo;{{ $reservasi->catatan }}&rdquo;</p>
                                    </div>
                                @endif

                                <div class="mt-auto pt-2" data-aksi-area>
                                    @if($reservasi->status === 'confirmed')
                                        <button type="button" class="btn btn-primary w-100 btn-mulai-layani" data-id="{{ $reservasi->id }}">
                                            <i class="fas fa-play"></i> Mulai Layani
                                        </button>
                                    @elseif($reservasi->status === 'sedang_dilayani')
                                        <button type="button" class="btn btn-success w-100 btn-selesai" data-id="{{ $reservasi->id }}">
                                            <i class="fas fa-check"></i> Selesai
                                        </button>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    {{--
        AJAX: tombol "Mulai Layani" (confirmed -> sedang_dilayani) dan
        "Selesai" (sedang_dilayani -> done) - tanpa reload halaman. Juga
        polling ringan tiap 15 detik supaya kalau ADMIN yang mengubah
        sesuatu di sisi lain (mis. konfirmasi pembayaran, atau ubah
        status lewat panel admin), badge di sini ikut ter-update
        otomatis tanpa reload. Halaman ini full-page load biasa (bukan
        SPA/AJAX-nav seperti sisi pelanggan), jadi script cukup
        dijalankan langsung, tidak perlu IIFE-reentry-guard.
    --}}
    <script>
        (function () {
            var statusBadgeMap = {
                pending: ['warning', 'Menunggu Konfirmasi'],
                confirmed: ['info', 'Dikonfirmasi'],
                sedang_dilayani: ['primary', 'Sedang Dilayani'],
                done: ['success', 'Selesai'],
            };

            var paymentBadgeMap = {
                unpaid: ['danger', 'Belum Bayar'],
                waiting_verification: ['warning', 'Menunggu Verifikasi'],
                paid: ['success', 'Lunas'],
                rejected: ['danger', 'Ditolak'],
            };

            function updateStatusUrl(id) {
                return '{{ url('/barber/reservasi') }}/' + id + '/status';
            }

            function pasangTombolMulai(button) {
                if (!button) {
                    return;
                }
                button.addEventListener('click', function () {
                    kirimStatus(this, 'sedang_dilayani');
                });
            }

            function pasangTombolSelesai(button) {
                if (!button) {
                    return;
                }
                button.addEventListener('click', function () {
                    kirimStatus(this, 'done');
                });
            }

            function terapkanAksiUntukStatus(card, status) {
                var aksiEl = card.querySelector('[data-aksi-area]');
                if (!aksiEl) {
                    return;
                }

                var id = card.getAttribute('data-reservasi-id');

                if (status === 'confirmed') {
                    aksiEl.innerHTML = '<button type="button" class="btn btn-primary w-100 btn-mulai-layani" data-id="' + id + '"><i class="fas fa-play"></i> Mulai Layani</button>';
                    pasangTombolMulai(aksiEl.querySelector('.btn-mulai-layani'));
                } else if (status === 'sedang_dilayani') {
                    aksiEl.innerHTML = '<button type="button" class="btn btn-success w-100 btn-selesai" data-id="' + id + '"><i class="fas fa-check"></i> Selesai</button>';
                    pasangTombolSelesai(aksiEl.querySelector('.btn-selesai'));
                } else {
                    aksiEl.innerHTML = '';
                }
            }

            function kirimStatus(button, statusBaru) {
                var id = button.getAttribute('data-id');
                var card = document.querySelector('[data-reservasi-id="' + id + '"]');
                if (!card) {
                    return;
                }

                var originalHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                var formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('status', statusBaru);

                fetch(updateStatusUrl(id), {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: formData,
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok || !data.success) {
                                throw new Error(data.message || 'Gagal mengubah status.');
                            }
                            return data;
                        });
                    })
                    .then(function (data) {
                        card.setAttribute('data-current-status', data.status);

                        var info = statusBadgeMap[data.status] || ['secondary', data.status];
                        var badgeEl = card.querySelector('[data-status-badge]');
                        if (badgeEl) {
                            badgeEl.innerHTML = '<span class="badge text-bg-' + info[0] + '">' + info[1] + '</span>';
                        }

                        terapkanAksiUntukStatus(card, data.status);
                    })
                    .catch(function (err) {
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                        alert(err.message || 'Gagal mengubah status. Silakan coba lagi.');
                    });
            }

            document.querySelectorAll('.btn-mulai-layani').forEach(pasangTombolMulai);
            document.querySelectorAll('.btn-selesai').forEach(pasangTombolSelesai);

            // ===================================================
            // Polling: sinkronkan status/pembayaran dari sisi admin
            // (atau barber lain yang entah bagaimana ikut mengubah -
            // tidak mungkin sebenarnya, tapi tetap aman kalau terjadi)
            // ke halaman ini tanpa reload.
            // ===================================================
            var statusUpdatesUrl = '{{ route('barber.statusUpdates') }}';

            function refreshStatusPembayaran() {
                fetch(statusUpdatesUrl, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Gagal memuat status terbaru');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        data.forEach(function (item) {
                            var card = document.querySelector('[data-reservasi-id="' + item.id + '"]');
                            if (!card) {
                                return;
                            }

                            // Badge pembayaran - selalu disamakan dengan
                            // data terbaru (barber tidak pernah
                            // mengubahnya sendiri, jadi aman diupdate
                            // kapan saja tanpa risiko menimpa aksi lokal).
                            var payBadgeEl = card.querySelector('[data-payment-badge]');
                            if (payBadgeEl) {
                                var payInfo = paymentBadgeMap[item.payment_status] || ['secondary', item.payment_status];
                                payBadgeEl.innerHTML = '<span class="badge text-bg-' + payInfo[0] + '">' + payInfo[1] + '</span>';
                            }

                            // Badge status + tombol aksi - cuma diupdate
                            // kalau BEDA dari yang sedang tercatat di
                            // kartu, supaya tidak menimpa hasil klik
                            // barber sendiri yang baru saja terjadi.
                            if (card.getAttribute('data-current-status') !== item.status) {
                                card.setAttribute('data-current-status', item.status);

                                var statusInfo = statusBadgeMap[item.status] || ['secondary', item.status];
                                var statusBadgeEl = card.querySelector('[data-status-badge]');
                                if (statusBadgeEl) {
                                    statusBadgeEl.innerHTML = '<span class="badge text-bg-' + statusInfo[0] + '">' + statusInfo[1] + '</span>';
                                }

                                terapkanAksiUntukStatus(card, item.status);
                            }
                        });
                    })
                    .catch(function () {
                        // Diamkan saja - coba lagi di polling berikutnya.
                    });
            }

            setInterval(refreshStatusPembayaran, 15000);
        })();
    </script>

</x-barber-layout>
