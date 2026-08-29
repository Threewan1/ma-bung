{{--
    Tab status + filter metode pembayaran + isi tab (tabel/card per
    status), dipisah dari index.blade.php supaya bisa dirender ulang
    lewat AJAX (lihat Admin\ReservationController::tabContent() dan
    route admin.reservasi.tabContent) - dipakai untuk polling live-sync:
    begitu barber mengubah status pelayanan (Sedang Dilayani/Selesai)
    atau admin sendiri mengonfirmasi pembayaran, halaman ini ikut
    ter-update tanpa reload penuh. Blade partial yang SAMA dipakai baik
    untuk render awal (index()) maupun render ulang AJAX (tabContent()),
    supaya keduanya selalu identik/konsisten.

    Terima 1 variabel: $reservations (Collection semua reservasi).
--}}
@php
    $reservasiPending = $reservations->where('status', 'pending')->values();
    $reservasiConfirmed = $reservations->where('status', 'confirmed')->values();
    $reservasiSedangDilayani = $reservations->where('status', 'sedang_dilayani')->values();
    $reservasiDone = $reservations->where('status', 'done')->values();
    $reservasiCancelled = $reservations->where('status', 'cancelled')->values();

    // Perlu Verifikasi Pembayaran: reservasi online yang bukti
    // pembayarannya belum diverifikasi (termasuk yang belum sempat
    // upload sama sekali) - berdasarkan payment_status, LINTAS
    // status reservasi (bisa saja masih pending atau sudah
    // confirmed), jadi dipisah dari 4 tab status di atas.
    $reservasiPerluVerifikasi = $reservations->filter(fn ($r) => $r->payment_method === 'online'
        && in_array($r->payment_status, ['unpaid', 'waiting_verification']))->values();

    // Tab aktif default hanya relevan untuk render awal (full page
    // load) - untuk render ulang AJAX, JS di index.blade.php yang akan
    // menerapkan ulang tab/filter yang sedang aktif SEBELUM refresh
    // terjadi (lihat terapkanStateAktif()), jadi nilai default di sini
    // tidak berpengaruh untuk kasus itu.
    $activeTab = request('tab') === 'verifikasi' ? 'verifikasi' : 'pending';
@endphp

<div class="d-flex align-items-center flex-wrap gap-2 mb-3">
    <span class="text-body-secondary small fw-semibold">Status:</span>
    <ul class="nav nav-tabs admin-reservasi-tabs flex-grow-1 mb-0" id="reservasiStatusTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" id="tab-pending-btn" data-bs-toggle="tab" data-bs-target="#tab-pending" type="button" role="tab" aria-controls="tab-pending" aria-selected="{{ $activeTab === 'pending' ? 'true' : 'false' }}">
                Menunggu Konfirmasi
                @if($reservasiPending->isNotEmpty())
                    <span class="badge rounded-pill text-bg-warning">{{ $reservasiPending->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-confirmed-btn" data-bs-toggle="tab" data-bs-target="#tab-confirmed" type="button" role="tab" aria-controls="tab-confirmed" aria-selected="false">
                Dikonfirmasi
                @if($reservasiConfirmed->isNotEmpty())
                    <span class="badge rounded-pill text-bg-info">{{ $reservasiConfirmed->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-sedang-dilayani-btn" data-bs-toggle="tab" data-bs-target="#tab-sedang-dilayani" type="button" role="tab" aria-controls="tab-sedang-dilayani" aria-selected="false">
                Sedang Dilayani
                @if($reservasiSedangDilayani->isNotEmpty())
                    <span class="badge rounded-pill text-bg-primary">{{ $reservasiSedangDilayani->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-done-btn" data-bs-toggle="tab" data-bs-target="#tab-done" type="button" role="tab" aria-controls="tab-done" aria-selected="false">
                Selesai
                @if($reservasiDone->isNotEmpty())
                    <span class="badge rounded-pill text-bg-success">{{ $reservasiDone->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-cancelled-btn" data-bs-toggle="tab" data-bs-target="#tab-cancelled" type="button" role="tab" aria-controls="tab-cancelled" aria-selected="false">
                Dibatalkan
                @if($reservasiCancelled->isNotEmpty())
                    <span class="badge rounded-pill text-bg-danger">{{ $reservasiCancelled->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'verifikasi' ? 'active' : '' }}" id="tab-verifikasi-btn" data-bs-toggle="tab" data-bs-target="#tab-verifikasi" type="button" role="tab" aria-controls="tab-verifikasi" aria-selected="{{ $activeTab === 'verifikasi' ? 'true' : 'false' }}">
                <i class="fas fa-file-invoice-dollar"></i> Perlu Verifikasi Pembayaran
                @if($reservasiPerluVerifikasi->isNotEmpty())
                    <span class="badge rounded-pill text-bg-warning">{{ $reservasiPerluVerifikasi->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-semua-btn" data-bs-toggle="tab" data-bs-target="#tab-semua" type="button" role="tab" aria-controls="tab-semua" aria-selected="false">
                Semua ({{ $reservations->count() }})
            </button>
        </li>
    </ul>
</div>

{{--
    Filter METODE PEMBAYARAN (COD/Online) - baris terpisah dari tab
    status di atas (garis+jarak pemisah lewat .admin-payment-filter-row
    di theme.css), murni CSS yang menyembunyikan baris & kolom
    "Bukti Bayar" berdasarkan atribut data-payment-filter di
    #reservasiStatusTabContent. Karena atributnya ditaruh di
    container yang membungkus SEMUA tab status, filter ini otomatis
    tetap berlaku dan bisa dikombinasikan walaupun admin
    pindah-pindah tab status. Satu dropdown (bukan 3 tombol
    sejajar) supaya tidak bersaing secara visual dengan tab Status
    di atasnya.
--}}
<div class="d-flex align-items-center flex-wrap gap-2 mb-3 admin-payment-filter-row">
    <label for="payment-filter-select" class="text-body-secondary small fw-semibold mb-0">Metode Pembayaran:</label>
    <select id="payment-filter-select" class="form-select form-select-sm" style="width: auto;">
        <option value="all" selected>Semua Metode</option>
        <option value="cod">COD</option>
        <option value="online">Online</option>
    </select>
</div>

<div class="tab-content" id="reservasiStatusTabContent">
    <div class="tab-pane fade {{ $activeTab === 'pending' ? 'show active' : '' }}" id="tab-pending" role="tabpanel" aria-labelledby="tab-pending-btn" tabindex="0">
        @include('admin.reservasi._list', ['reservations' => $reservasiPending, 'emptyMessage' => 'Tidak ada reservasi yang menunggu konfirmasi.'])
    </div>
    <div class="tab-pane fade" id="tab-confirmed" role="tabpanel" aria-labelledby="tab-confirmed-btn" tabindex="0">
        @include('admin.reservasi._list', ['reservations' => $reservasiConfirmed, 'emptyMessage' => 'Tidak ada reservasi yang sudah dikonfirmasi.'])
    </div>
    <div class="tab-pane fade" id="tab-sedang-dilayani" role="tabpanel" aria-labelledby="tab-sedang-dilayani-btn" tabindex="0">
        @include('admin.reservasi._list', ['reservations' => $reservasiSedangDilayani, 'emptyMessage' => 'Tidak ada reservasi yang sedang dilayani.'])
    </div>
    <div class="tab-pane fade" id="tab-done" role="tabpanel" aria-labelledby="tab-done-btn" tabindex="0">
        @include('admin.reservasi._list', ['reservations' => $reservasiDone, 'emptyMessage' => 'Belum ada reservasi yang selesai.'])
    </div>
    <div class="tab-pane fade" id="tab-cancelled" role="tabpanel" aria-labelledby="tab-cancelled-btn" tabindex="0">
        @include('admin.reservasi._list', ['reservations' => $reservasiCancelled, 'emptyMessage' => 'Tidak ada reservasi yang dibatalkan.'])
    </div>
    <div class="tab-pane fade {{ $activeTab === 'verifikasi' ? 'show active' : '' }}" id="tab-verifikasi" role="tabpanel" aria-labelledby="tab-verifikasi-btn" tabindex="0">
        @include('admin.reservasi._list', ['reservations' => $reservasiPerluVerifikasi, 'emptyMessage' => 'Tidak ada reservasi yang perlu diverifikasi pembayarannya.'])
    </div>
    <div class="tab-pane fade" id="tab-semua" role="tabpanel" aria-labelledby="tab-semua-btn" tabindex="0">
        @include('admin.reservasi._list', ['reservations' => $reservations, 'emptyMessage' => 'Belum ada reservasi.'])
    </div>
</div>
