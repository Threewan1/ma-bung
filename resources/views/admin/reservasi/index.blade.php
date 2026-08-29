<x-admin-layout title="Kelola Reservasi">

    <h2 class="fs-2 fw-bold text-gold mb-4">
        <i class="fas fa-calendar-check"></i> Kelola Reservasi
    </h2>

    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{--
        Reservasi dipisah per status jadi TAB (bukan satu tabel besar
        campur semua status) supaya admin tidak perlu menyisir satu-satu
        untuk membedakan mana yang masih perlu ditindak (Menunggu
        Konfirmasi) dan mana yang sudah beres (Selesai). "Menunggu
        Konfirmasi" aktif secara default karena itu yang paling butuh
        perhatian admin duluan - kecuali datang dari card "Perlu
        Verifikasi Pembayaran" di dashboard admin (?tab=verifikasi),
        yang langsung membuka tab itu.

        Dibungkus #reservasi-tab-content-wrapper supaya bisa dirender
        ulang lewat AJAX (polling) - begitu barber mengubah status
        pelayanan atau admin sendiri mengonfirmasi pembayaran, tab &
        tabel di sini ikut ter-update tanpa reload halaman penuh (lihat
        script di bagian bawah).
    --}}
    <div id="reservasi-tab-content-wrapper">
        @include('admin.reservasi._tab-content')
    </div>

    {{-- ========================================================= --}}
    {{-- MODAL BUKTI PEMBAYARAN                                     --}}
    {{-- Sengaja dirender di luar <table> (bukan di dalam <td>),    --}}
    {{-- karena menaruh .modal di dalam struktur tabel adalah       --}}
    {{-- anti-pattern yang bisa bikin perilaku modal tidak stabil.  --}}
    {{-- ========================================================= --}}
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

                    <div class="modal-footer border-secondary-subtle">
                        @if($paymentStatus !== 'paid')
                            <form method="POST" action="{{ route('admin.reservasi.updatePaymentStatus', $reservasi->id) }}" class="mb-0">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="payment_status" value="paid">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Konfirmasi Pembayaran
                                </button>
                            </form>
                        @endif

                        @if($paymentStatus !== 'rejected')
                            <form
                                method="POST"
                                action="{{ route('admin.reservasi.updatePaymentStatus', $reservasi->id) }}"
                                class="mb-0"
                                onsubmit="return confirm('Yakin ingin menolak bukti pembayaran ini?')"
                            >
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="payment_status" value="rejected">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- ========================================================= --}}
    {{-- MODAL UPLOAD FOTO BEFORE & AFTER (transformasi)            --}}
    {{-- Hanya dirender untuk reservasi yang sudah berstatus         --}}
    {{-- "done", di luar struktur tabel (sama seperti modal bukti    --}}
    {{-- pembayaran di atas).                                        --}}
    {{-- ========================================================= --}}
    @foreach($reservations as $reservasi)
        @continue($reservasi->status !== 'done')

        <div class="modal fade" id="transformasiModal{{ $reservasi->id }}" tabindex="-1" aria-labelledby="transformasiModal{{ $reservasi->id }}Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-panel">
                    <form method="POST" action="{{ route('admin.reservasi.uploadTransformasi', $reservasi->id) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-header border-secondary-subtle">
                            <h5 class="modal-title text-gold" id="transformasiModal{{ $reservasi->id }}Label">
                                Foto Before &amp; After - {{ $reservasi->user->name }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small text-body-secondary">Foto Before</label>
                                    @if($reservasi->foto_before)
                                        <img src="{{ asset('storage/' . $reservasi->foto_before) }}" class="img-fluid rounded-3 mb-2" alt="Foto before {{ $reservasi->user->name }}">
                                    @endif
                                    <input type="file" name="foto_before" accept="image/*" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-body-secondary">Foto After</label>
                                    @if($reservasi->foto_after)
                                        <img src="{{ asset('storage/' . $reservasi->foto_after) }}" class="img-fluid rounded-3 mb-2" alt="Foto after {{ $reservasi->user->name }}">
                                    @endif
                                    <input type="file" name="foto_after" accept="image/*" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-secondary-subtle">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{--
        Live-sync: begitu barber mengubah status pelayanan (Sedang
        Dilayani/Selesai) di halamannya sendiri, atau admin mengonfirmasi
        pembayaran, tab & tabel di halaman ini ikut ter-update otomatis
        tanpa reload - dengan cara poll endpoint JSON ringan tiap 15 detik
        (cuma id+status+payment_status semua reservasi, murah), dan HANYA
        kalau ada yang benar-benar berubah, ambil ulang HTML tab (partial
        _tab-content.blade.php yang sama dipakai render awal) lalu ganti
        isi wrapper-nya - supaya reservasi otomatis pindah ke tab status
        yang benar & badge Bukti Bayar/dst selalu akurat, bukan cuma
        tempelan update sebagian. Tab & filter metode pembayaran yang
        sedang aktif diingat dulu sebelum diganti, lalu diterapkan lagi
        setelahnya supaya admin tidak "terlempar" balik ke tab default.
    --}}
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

                if (state.tabBtnId) {
                    var btn = document.getElementById(state.tabBtnId);
                    if (btn) {
                        btn.classList.add('active');
                        btn.setAttribute('aria-selected', 'true');
                    }
                }

                if (state.tabPaneId) {
                    var pane = document.getElementById(state.tabPaneId);
                    if (pane) {
                        pane.classList.add('show', 'active');
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

</x-admin-layout>
