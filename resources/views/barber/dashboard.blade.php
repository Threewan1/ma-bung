<x-barber-layout title="Dashboard Barber">

    <div class="mb-4">
        <h2 class="fs-3 fw-bold text-gold mb-1">
            Halo, {{ $barber->nama ?? auth()->user()->name }}!
        </h2>
        <p class="text-body-secondary mb-0">
            Reservasi yang masih perlu kamu tangani, diurutkan dari jadwal paling dekat.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($barber)
        {{-- Card "Pelanggan Berikutnya" cuma dirender kalau ada reservasi "Dikonfirmasi" hari ini yang jamnya belum lewat. --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="barber-ringkasan-card h-100">
                    <div class="barber-ringkasan-label">Dilayani Bulan Ini</div>
                    <div class="barber-ringkasan-value">{{ $dilayaniBulanIni }}</div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="barber-ringkasan-card h-100">
                    <div class="barber-ringkasan-label">Progress Hari Ini</div>
                    <div class="barber-ringkasan-value">
                        {{ $selesaiHariIni }}/{{ $totalHariIni }}
                        <span class="barber-ringkasan-value-suffix">selesai</span>
                    </div>
                    <div class="progress barber-ringkasan-progress" role="progressbar" aria-label="Progress hari ini" aria-valuenow="{{ $progressHariIniPersen }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-warning" style="width: {{ $progressHariIniPersen }}%"></div>
                    </div>
                </div>
            </div>

            @if($pelangganBerikutnya)
                <div class="col-6 col-lg-3">
                    <div class="barber-ringkasan-card barber-ringkasan-card-highlight h-100">
                        <div class="barber-ringkasan-label">Pelanggan Berikutnya</div>
                        <div class="barber-ringkasan-value">{{ $pelangganBerikutnya->user->name }}</div>
                        <div class="barber-ringkasan-highlight-detail">
                            {{ $pelangganBerikutnya->service->nama_layanan ?? '-' }}
                            &middot;
                            {{ \Carbon\Carbon::parse($pelangganBerikutnya->jam)->format('H:i') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(! $barber)
        {{-- Jaga-jaga kalau akun barber dibuat manual tanpa baris "barbers" terhubung. --}}
        <div class="bg-panel p-5 rounded-3 text-center text-body-secondary">
            <p class="mb-0">Akun kamu belum terhubung ke data barber manapun. Silakan hubungi admin.</p>
        </div>
    @else
        {{-- Grid & pesan kosong selalu dirender bareng (cuma satu yang `hidden`), biar JS bisa toggle tanpa reload lewat perbaruiEmptyState(). --}}
        <div class="barber-jadwal-grid" id="barber-jadwal-grid" @if($reservasiPerluDitindak->isEmpty()) hidden @endif>
            @foreach($reservasiPerluDitindak as $reservasi)
                @php
                    $statusMap = [
                        'confirmed' => ['info', 'Dikonfirmasi'],
                        'sedang_dilayani' => ['primary', 'Sedang Dilayani'],
                    ];
                    $info = $statusMap[$reservasi->status] ?? ['secondary', ucfirst($reservasi->status)];

                    $paymentInfo = $reservasi->payment_badge;
                @endphp
                <div class="barber-jadwal-card" data-reservasi-id="{{ $reservasi->id }}" data-current-status="{{ $reservasi->status }}">

                    <div class="barber-jadwal-card-top">
                        <div>
                            <div class="barber-jadwal-card-tanggal">{{ \Carbon\Carbon::parse($reservasi->tanggal)->translatedFormat('D, d M Y') }}</div>
                            <span class="barber-jadwal-card-jam">{{ $reservasi->jam }}</span>
                        </div>
                        <span data-status-badge>
                            <span class="badge text-bg-{{ $info[0] }}">{{ $info[1] }}</span>
                        </span>
                    </div>

                    <div class="barber-jadwal-card-field">
                        <span class="barber-jadwal-card-label">Pelanggan</span>
                        <p class="barber-jadwal-card-value">{{ $reservasi->user->name }}</p>
                        @if($reservasi->user->no_hp)
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', $reservasi->user->no_hp) }}" target="_blank" rel="noopener" class="barber-jadwal-card-wa text-gold text-decoration-none">
                                <i class="fab fa-whatsapp"></i> {{ $reservasi->user->no_hp }}
                            </a>
                        @endif
                    </div>

                    <div class="barber-jadwal-card-field">
                        <span class="barber-jadwal-card-label">Layanan</span>
                        <p class="barber-jadwal-card-value">{{ $reservasi->service->nama_layanan ?? '-' }}</p>
                    </div>

                    {{-- Murni informasi, barber tidak bisa mengubah status pembayaran dari sini. --}}
                    <div class="barber-jadwal-card-field">
                        <span class="barber-jadwal-card-label">Pembayaran</span>
                        <p class="barber-jadwal-card-value">
                            {{ $reservasi->payment_method === 'online' ? 'Online' : 'COD' }}
                            <span data-payment-badge>
                                <span class="badge text-bg-{{ $paymentInfo[0] }}">{{ $paymentInfo[2] }}</span>
                            </span>
                        </p>
                    </div>

                    @if($reservasi->catatan)
                        <div class="barber-jadwal-card-field">
                            <span class="barber-jadwal-card-label">Catatan</span>
                            <p class="barber-jadwal-card-note mb-0 fst-italic">&ldquo;{{ $reservasi->catatan }}&rdquo;</p>
                        </div>
                    @endif

                    <div class="barber-jadwal-card-footer" data-aksi-area>
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
            @endforeach
        </div>

        <div class="bg-panel p-5 rounded-3 text-center text-body-secondary" id="barber-jadwal-empty" @if($reservasiPerluDitindak->isNotEmpty()) hidden @endif>
            <p class="mb-0">Tidak ada jadwal yang perlu dikerjakan saat ini.</p>
        </div>
    @endif

    {{-- AJAX "Mulai Layani"/"Selesai" + polling 15 detik biar perubahan dari admin ikut ter-update tanpa reload. --}}
    <script>
        (function () {
            // Cuma 2 status ini yang kartunya bisa muncul di halaman ini, "done"/"cancelled" langsung hilang dari grid.
            var statusBadgeMap = {
                confirmed: ['info', 'Dikonfirmasi'],
                sedang_dilayani: ['primary', 'Sedang Dilayani'],
            };

            function updateStatusUrl(id) {
                return '{{ url('/barber/reservasi') }}/' + id + '/status';
            }

            // Begitu status keluar dari Dikonfirmasi/Sedang Dilayani, card-nya harus hilang dari grid, bukan cuma badge-nya yang ganti.
            function perluDitindak(status) {
                return status === 'confirmed' || status === 'sedang_dilayani';
            }

            function perbaruiEmptyState() {
                var grid = document.getElementById('barber-jadwal-grid');
                var empty = document.getElementById('barber-jadwal-empty');
                if (!grid || !empty) {
                    return;
                }
                var kosong = grid.children.length === 0;
                grid.hidden = kosong;
                empty.hidden = !kosong;
            }

            function hapusKartuJikaTidakPerluDitindak(card, status) {
                if (perluDitindak(status)) {
                    return false;
                }
                card.remove();
                perbaruiEmptyState();
                return true;
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

            // Muncul langsung di kartu begitu barber tekan "Selesai", form biasa (bukan fetch) jadi submit-nya reload halaman.
            function tampilkanFormFoto(card, id) {
                var aksiEl = card.querySelector('[data-aksi-area]');
                if (!aksiEl) {
                    return;
                }

                var uploadUrl = '{{ url('/barber/reservasi') }}/' + id + '/transformasi';

                aksiEl.innerHTML =
                    '<form method="POST" action="' + uploadUrl + '" enctype="multipart/form-data" class="w-100">' +
                    '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                    '<label class="form-label small text-body-secondary mb-1">Foto Before</label>' +
                    '<input type="file" name="foto_before" accept="image/*" class="form-control form-control-sm mb-2">' +
                    '<label class="form-label small text-body-secondary mb-1">Foto After</label>' +
                    '<input type="file" name="foto_after" accept="image/*" class="form-control form-control-sm mb-2">' +
                    '<button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-upload"></i> Upload Foto</button>' +
                    '</form>';
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

                        // Beda dari transisi lain, kartu "Selesai" tidak dihapus - badge & area aksinya diganti form upload foto.
                        if (data.status === 'done') {
                            var badgeSelesaiEl = card.querySelector('[data-status-badge]');
                            if (badgeSelesaiEl) {
                                badgeSelesaiEl.innerHTML = '<span class="badge text-bg-success">Selesai</span>';
                            }
                            tampilkanFormFoto(card, id);
                            return;
                        }

                        if (hapusKartuJikaTidakPerluDitindak(card, data.status)) {
                            return;
                        }

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

            // Sinkronkan status/pembayaran dari sisi admin ke halaman ini tanpa reload.
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

                            // Aman diupdate kapan saja, barber tidak pernah mengubah status pembayaran sendiri.
                            var payBadgeEl = card.querySelector('[data-payment-badge]');
                            if (payBadgeEl) {
                                var payInfo = item.payment_badge || ['secondary', 'fa-info-circle', item.payment_status];
                                payBadgeEl.innerHTML = '<span class="badge text-bg-' + payInfo[0] + '">' + payInfo[2] + '</span>';
                            }

                            // Cuma update kalau beda, biar tidak menimpa hasil klik barber sendiri yang baru saja terjadi.
                            if (card.getAttribute('data-current-status') !== item.status) {
                                card.setAttribute('data-current-status', item.status);

                                if (hapusKartuJikaTidakPerluDitindak(card, item.status)) {
                                    return;
                                }

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

            if (window.__barberStatusPollInterval) {
                clearInterval(window.__barberStatusPollInterval);
            }
            window.__barberStatusPollInterval = setInterval(refreshStatusPembayaran, 15000);
        })();
    </script>

</x-barber-layout>
