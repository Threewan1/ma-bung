<x-admin-layout title="Kelola Reservasi">

    {{-- Judul dibungkus bg-panel biar foto background admin tidak tembus di belakang teks. --}}
    <div class="d-none d-md-flex justify-content-end mb-4">
        <h2 class="admin-page-title bg-panel">
            Kelola Reservasi
        </h2>
    </div>

    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Dipisah per status jadi tab biar admin tidak perlu menyisir manual; dibungkus wrapper ini supaya bisa dirender ulang via polling (lihat script bawah). --}}
    <div id="reservasi-tab-content-wrapper">
        @include('admin.reservasi._tab-content')
    </div>

    {{-- Modal bukti pembayaran, sengaja di luar tabel karena modal di dalam <td> bikin perilakunya tidak stabil. --}}
    @foreach($reservations as $reservasi)
        @continue(! ($reservasi->payment_method === 'online' && $reservasi->payment_proof))
        @php
            $paymentStatus = $reservasi->payment_status ?? 'unpaid';
        @endphp

        <div class="modal fade" id="buktiModal{{ $reservasi->id }}" tabindex="-1" aria-labelledby="buktiModal{{ $reservasi->id }}Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content bg-panel">
                    <div class="modal-header border-secondary-subtle">
                        <h5 class="modal-title text-gold" id="buktiModal{{ $reservasi->id }}Label">
                            Bukti Pembayaran - {{ $reservasi->user->name }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body text-center modal-bukti-body">
                        <img
                            src="{{ asset('storage/' . $reservasi->payment_proof) }}"
                            alt="Bukti pembayaran {{ $reservasi->user->name }}"
                            class="img-fluid rounded-3 mb-3"
                        >

                        <p class="text-body-secondary small mb-0">
                            Channel: {{ strtoupper($reservasi->payment_channel ?? '-') }}
                        </p>
                    </div>

                    <div class="modal-footer border-secondary-subtle flex-column align-items-stretch gap-2">
                        {{-- Satu-satunya jalan konfirmasi reservasi online, sengaja di sini biar admin wajib lihat bukti dulu. --}}
                        @if($reservasi->status === 'pending')
                            <form method="POST" action="{{ route('admin.reservasi.update', $reservasi->id) }}" class="mb-0">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-calendar-check"></i> Konfirmasi Reservasi Ini
                                </button>
                            </form>
                        @endif

                        {{-- Status pembayaran, terpisah total dari konfirmasi reservasi di atas biar tidak rancu. --}}
                        <div class="d-flex gap-2 w-100">
                            @if($paymentStatus !== 'paid')
                                <form method="POST" action="{{ route('admin.reservasi.updatePaymentStatus', $reservasi->id) }}" class="mb-0 flex-fill" data-disable-on-submit>
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="payment_status" value="paid">
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        <i class="fas fa-money-bill-wave"></i> Tandai Lunas
                                    </button>
                                </form>
                            @endif

                            @if($paymentStatus !== 'rejected')
                                <form
                                    method="POST"
                                    action="{{ route('admin.reservasi.updatePaymentStatus', $reservasi->id) }}"
                                    class="mb-0 flex-fill"
                                    onsubmit="return konfirmasiDanNonaktifkan(this, 'Yakin ingin menolak bukti pembayaran ini?')"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="payment_status" value="rejected">
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Upload foto before/after sudah dipindah ke halaman kerja Barber. --}}

    {{-- Live-sync: poll signature ringan tiap 15 detik, kalau berubah ambil ulang HTML tab & terapkan lagi state tab/filter yang sedang aktif. --}}
    <script>
        (function () {
            var statusUrl = '{{ route('admin.reservasi.statusUpdates') }}';
            var tabContentUrl = '{{ route('admin.reservasi.tabContent') }}';
            var wrapper = document.getElementById('reservasi-tab-content-wrapper');
            var lastSignature = null;

            if (!wrapper) {
                return;
            }

            function pasangListenerFilter() {
                var filterSelect = document.getElementById('payment-filter-select');
                var container = document.getElementById('reservasiStatusTabContent');

                if (filterSelect && container) {
                    filterSelect.addEventListener('change', function () {
                        container.setAttribute('data-payment-filter', this.value);
                    });
                }
            }

            function ambilStateAktif() {
                var activeTabPane = wrapper.querySelector('.tab-pane.active');
                var activeTabBtn = wrapper.querySelector('.admin-reservasi-tabs .nav-link.active');
                var paymentFilter = document.getElementById('payment-filter-select');

                return {
                    tabPaneId: activeTabPane ? activeTabPane.id : null,
                    tabBtnId: activeTabBtn ? activeTabBtn.id : null,
                    paymentFilterValue: paymentFilter ? paymentFilter.value : 'all',
                };
            }

            function terapkanStateAktif(state) {
                wrapper.querySelectorAll('.admin-reservasi-tabs .nav-link').forEach(function (btn) {
                    btn.classList.remove('active');
                    btn.setAttribute('aria-selected', 'false');
                });
                wrapper.querySelectorAll('.tab-pane').forEach(function (pane) {
                    pane.classList.remove('show', 'active');
                });

                var btn = state.tabBtnId ? document.getElementById(state.tabBtnId) : null;

                if (btn) {
                    btn.classList.add('active');
                    btn.setAttribute('aria-selected', 'true');

                    if (state.tabPaneId) {
                        var pane = document.getElementById(state.tabPaneId);
                        if (pane) {
                            pane.classList.add('show', 'active');
                        }
                    }
                } else {
                    // Tab yang sebelumnya aktif sudah hilang dari DOM (badge-nya jadi 0), jatuhkan ke "Menunggu Konfirmasi".
                    var defaultBtn = document.getElementById('tab-pending-btn');
                    var defaultPane = document.getElementById('tab-pending');
                    if (defaultBtn) {
                        defaultBtn.classList.add('active');
                        defaultBtn.setAttribute('aria-selected', 'true');
                    }
                    if (defaultPane) {
                        defaultPane.classList.add('show', 'active');
                    }
                }

                var filterSelect = document.getElementById('payment-filter-select');
                var container = document.getElementById('reservasiStatusTabContent');

                if (filterSelect) {
                    filterSelect.value = state.paymentFilterValue;
                }
                if (container) {
                    container.setAttribute('data-payment-filter', state.paymentFilterValue);
                }

                pasangListenerFilter();
            }

            function muatUlangTabContent() {
                var state = ambilStateAktif();

                fetch(tabContentUrl, {
                    headers: {
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Gagal memuat data terbaru');
                        }
                        return response.text();
                    })
                    .then(function (html) {
                        wrapper.innerHTML = html;
                        terapkanStateAktif(state);
                    })
                    .catch(function () {
                        // Diamkan saja - coba lagi di polling berikutnya.
                    });
            }

            function cekPerubahan() {
                fetch(statusUrl, {
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
                        if (lastSignature === null) {
                            lastSignature = data.signature;
                            return;
                        }

                        if (data.signature !== lastSignature) {
                            lastSignature = data.signature;
                            muatUlangTabContent();
                        }
                    })
                    .catch(function () {
                        // Diamkan saja - coba lagi di polling berikutnya.
                    });
            }

            pasangListenerFilter();
            setInterval(cekPerubahan, 15000);
        })();
    </script>

    {{-- Cegah double-submit tombol yang memicu email (Tandai Lunas/Tolak/dropdown status) - dipisah dari script polling di atas karena itu early-return kalau wrapper tidak ada. --}}
    <script>
        (function () {
            function nonaktifkanTombolSubmit(form, teks) {
                var btn = form.querySelector('button[type="submit"]');
                if (!btn || btn.disabled) {
                    return;
                }
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (teks || 'Memproses...');
            }

            // Dipanggil dari onsubmit="" form "Tolak" - confirm() dulu, baru nonaktifkan tombol kalau admin klik OK.
            window.konfirmasiDanNonaktifkan = function (form, pesan) {
                if (!confirm(pesan)) {
                    return false;
                }
                nonaktifkanTombolSubmit(form);
                return true;
            };

            // PENTING: submit() dulu, baru disable - select yang sudah disabled tidak ikut terkirim (ini bug yang pernah kejadian).
            window.nonaktifkanSelectDanKirim = function (select) {
                select.form.submit();
                select.disabled = true;
            };

            // Form tanpa dialog konfirmasi cukup ditandai data-disable-on-submit, satu listener ini menangani semuanya.
            document.addEventListener('submit', function (e) {
                if (e.target.matches('[data-disable-on-submit]')) {
                    nonaktifkanTombolSubmit(e.target);
                }
            });
        })();
    </script>

</x-admin-layout>
