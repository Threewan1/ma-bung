{{-- Tab status + filter pembayaran + isi tab, dipisah dari index.blade.php biar bisa dirender ulang lewat AJAX (tabContent()) - terima $reservations. --}}
@php
    $reservasiPending = $reservations->where('status', 'pending')->values();
    $reservasiConfirmed = $reservations->where('status', 'confirmed')->values();
    $reservasiSedangDilayani = $reservations->where('status', 'sedang_dilayani')->values();
    $reservasiDone = $reservations->where('status', 'done')->values();
    $reservasiCancelled = $reservations->where('status', 'cancelled')->values();

    // Lintas status reservasi (bisa pending atau confirmed), jadi dipisah dari 4 tab status di atas.
    $reservasiPerluVerifikasi = $reservations->filter(fn ($r) => $r->payment_method === 'online'
        && in_array($r->payment_status, ['unpaid', 'waiting_verification']))->values();

    // Cuma relevan untuk render awal, render ulang AJAX pakai terapkanStateAktif() di JS index.blade.php.
    $activeTab = (request('tab') === 'verifikasi' && $reservasiPerluVerifikasi->isNotEmpty())
        ? 'verifikasi'
        : 'pending';
@endphp

<div class="d-flex align-items-center flex-wrap gap-2 mb-3 admin-reservasi-tabs-bar">
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
        {{-- 4 tab di bawah disembunyikan total (bukan cuma CSS) kalau datanya kosong. --}}
        @if($reservasiConfirmed->isNotEmpty())
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-confirmed-btn" data-bs-toggle="tab" data-bs-target="#tab-confirmed" type="button" role="tab" aria-controls="tab-confirmed" aria-selected="false">
                    Dikonfirmasi
                    <span class="badge rounded-pill text-bg-info">{{ $reservasiConfirmed->count() }}</span>
                </button>
            </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-sedang-dilayani-btn" data-bs-toggle="tab" data-bs-target="#tab-sedang-dilayani" type="button" role="tab" aria-controls="tab-sedang-dilayani" aria-selected="false">
                Sedang Dilayani
                @if($reservasiSedangDilayani->isNotEmpty())
                    <span class="badge rounded-pill text-bg-primary">{{ $reservasiSedangDilayani->count() }}</span>
                @endif
            </button>
        </li>
        @if($reservasiDone->isNotEmpty())
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-done-btn" data-bs-toggle="tab" data-bs-target="#tab-done" type="button" role="tab" aria-controls="tab-done" aria-selected="false">
                    Selesai
                    <span class="badge rounded-pill text-bg-success">{{ $reservasiDone->count() }}</span>
                </button>
            </li>
        @endif
        @if($reservasiCancelled->isNotEmpty())
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-cancelled-btn" data-bs-toggle="tab" data-bs-target="#tab-cancelled" type="button" role="tab" aria-controls="tab-cancelled" aria-selected="false">
                    Dibatalkan
                    <span class="badge rounded-pill text-bg-danger">{{ $reservasiCancelled->count() }}</span>
                </button>
            </li>
        @endif
        @if($reservasiPerluVerifikasi->isNotEmpty())
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'verifikasi' ? 'active' : '' }}" id="tab-verifikasi-btn" data-bs-toggle="tab" data-bs-target="#tab-verifikasi" type="button" role="tab" aria-controls="tab-verifikasi" aria-selected="{{ $activeTab === 'verifikasi' ? 'true' : 'false' }}">
                    <i class="fas fa-file-invoice-dollar"></i> Perlu Verifikasi Pembayaran
                    <span class="badge rounded-pill text-bg-warning">{{ $reservasiPerluVerifikasi->count() }}</span>
                </button>
            </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-semua-btn" data-bs-toggle="tab" data-bs-target="#tab-semua" type="button" role="tab" aria-controls="tab-semua" aria-selected="false">
                Semua ({{ $reservations->count() }})
            </button>
        </li>
    </ul>
</div>

{{-- Filter metode pembayaran, murni CSS berdasarkan data-payment-filter di #reservasiStatusTabContent - otomatis berlaku di semua tab. --}}
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
