{{--
    Partial daftar reservasi (versi tabel desktop + versi card mobile),
    dipakai berkali-kali oleh admin/reservasi/index.blade.php - satu kali
    per tab status (Menunggu Konfirmasi/Dikonfirmasi/Selesai/Dibatalkan/
    Semua), supaya markup tabel & card-nya tidak perlu ditulis ulang 5
    kali. Terima 2 variabel:
    - $reservations : Collection reservasi yang mau ditampilkan di tab ini
    - $emptyMessage  : pesan saat $reservations kosong (khusus per tab)
--}}

{{-- ================================================= --}}
{{-- VERSI TABEL (desktop, >=768px) --}}
{{-- ================================================= --}}
<div class="bg-panel rounded-3 overflow-hidden d-none d-md-block">
    <div class="table-responsive">
    <table class="table table-dark table-hover align-middle mb-0">
        <thead class="bg-surface">
            <tr>
                <th>No</th>
                <th>Pelanggan</th>
                <th>Layanan</th>
                <th>Barber</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>No. Antrian</th>
                <th>Pembayaran</th>
                <th data-col="bukti-bayar">Bukti Bayar</th>
                <th>Status</th>
                <th>Before/After</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            {{-- Loop reservasi untuk tab ini --}}
            @forelse($reservations as $reservasi)
            @php
                $paymentStatus = $reservasi->payment_status ?? 'unpaid';
            @endphp
            <tr data-row-payment-method="{{ $reservasi->payment_method }}">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $reservasi->user->name }}</td>
                <td>{{ $reservasi->service->nama_layanan }}</td>
                <td>{{ $reservasi->barber->nama ?? '-' }}</td>
                <td>{{ $reservasi->tanggal }}</td>
                <td>{{ $reservasi->jam }}</td>
                <td>
                    @if($reservasi->queue)
                        <span class="badge rounded-pill text-bg-primary">
                            #{{ $reservasi->queue->nomor_antrian }}
                        </span>
                    @else
                        -
                    @endif
                </td>

                {{-- ========================================= --}}
                {{-- METODE & STATUS PEMBAYARAN --}}
                {{-- ========================================= --}}
                <td>
                    <div class="fw-medium mb-1">
                        {{ $reservasi->payment_method === 'online' ? 'Online' : 'COD' }}
                    </div>

                    @if($paymentStatus === 'unpaid')
                        <span class="badge text-bg-danger">Belum Bayar</span>
                    @elseif($paymentStatus === 'waiting_verification')
                        <span class="badge text-bg-warning">Menunggu Verifikasi</span>
                    @elseif($paymentStatus === 'paid')
                        <span class="badge text-bg-success">Lunas</span>
                    @elseif($paymentStatus === 'rejected')
                        <span class="badge text-bg-danger">Ditolak</span>
                    @endif
                </td>

                {{-- ========================================= --}}
                {{-- BUKTI PEMBAYARAN (thumbnail + modal) --}}
                {{-- Kolom ini disembunyikan sepenuhnya saat filter        --}}
                {{-- metode pembayaran "COD" aktif (lihat data-col di       --}}
                {{-- <th> & CSS di theme.css) - untuk baris COD di tampilan --}}
                {{-- "Semua Metode", cukup tampilkan strip pendek karena    --}}
                {{-- memang tidak relevan (bukan kalimat panjang).          --}}
                {{-- ========================================= --}}
                <td data-col="bukti-bayar">
                    @if($reservasi->payment_method === 'online' && $reservasi->payment_proof)
                        <img
                            src="{{ asset('storage/' . $reservasi->payment_proof) }}"
                            alt="Bukti pembayaran {{ $reservasi->user->name }}"
                            class="rounded-2 border border-gold"
                            style="width: 3rem; height: 3rem; object-fit: cover; cursor: pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#buktiModal{{ $reservasi->id }}"
                        >
                    @elseif($reservasi->payment_method !== 'online')
                        <span class="text-body-secondary">&mdash;</span>
                    @else
                        <span class="text-body-secondary small fst-italic">Tidak ada bukti pembayaran</span>
                    @endif
                </td>

                <td>
                    {{-- Form update status reservasi --}}
                    <form method="POST" action="/admin/reservasi/{{ $reservasi->id }}" class="mb-0">
                        @csrf
                        @method('PUT')
                        <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                            <option value="pending" {{ $reservasi->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $reservasi->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="sedang_dilayani" {{ $reservasi->status == 'sedang_dilayani' ? 'selected' : '' }}>Sedang Dilayani</option>
                            <option value="cancelled" {{ $reservasi->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="done" {{ $reservasi->status == 'done' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </form>
                </td>

                {{-- ========================================= --}}
                {{-- FOTO BEFORE/AFTER (hanya reservasi selesai) --}}
                {{-- ========================================= --}}
                <td>
                    @if($reservasi->status === 'done')
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm text-nowrap"
                            data-bs-toggle="modal"
                            data-bs-target="#transformasiModal{{ $reservasi->id }}"
                        >
                            <i class="fas fa-images"></i>
                            {{ ($reservasi->foto_before && $reservasi->foto_after) ? 'Edit Foto' : 'Upload Foto' }}
                        </button>
                    @else
                        <span class="text-body-secondary">&mdash;</span>
                    @endif
                </td>

                <td>
                    {{-- Tombol Hapus --}}
                    <form method="POST" action="/admin/reservasi/{{ $reservasi->id }}" class="mb-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            onclick="return confirm('Yakin ingin menghapus reservasi ini?')"
                            class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            {{-- Tampilkan pesan jika belum ada reservasi di tab ini --}}
            <tr>
                <td colspan="12" class="text-center text-body-secondary py-4">
                    {{ $emptyMessage ?? 'Belum ada reservasi.' }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

{{-- ================================================= --}}
{{-- VERSI CARD (mobile/tablet, <768px) --}}
{{-- ================================================= --}}
<div class="table-card-list d-block d-md-none">
    @forelse($reservations as $reservasi)
        @php
            $paymentStatus = $reservasi->payment_status ?? 'unpaid';
            $paymentBadge = match($paymentStatus) {
                'unpaid' => ['danger', 'Belum Bayar'],
                'waiting_verification' => ['warning', 'Menunggu Verifikasi'],
                'paid' => ['success', 'Lunas'],
                'rejected' => ['danger', 'Ditolak'],
                default => ['secondary', ucfirst($paymentStatus)],
            };
        @endphp

        <div class="table-card-item" data-row-payment-method="{{ $reservasi->payment_method }}">

            {{-- Pelanggan + layanan sebagai judul card --}}
            <div class="table-card-item-title">
                #{{ $loop->iteration }} &mdash; {{ $reservasi->user->name }}
                <div class="fw-normal text-body-secondary" style="font-size: 0.85rem;">
                    {{ $reservasi->service->nama_layanan }}
                </div>
            </div>

            {{-- Tanggal dan Jam berdampingan --}}
            <div class="table-card-item-cols">
                <div>
                    <div class="table-card-item-label">Tanggal</div>
                    <div class="table-card-item-value">{{ $reservasi->tanggal }}</div>
                </div>
                <div class="text-end">
                    <div class="table-card-item-label">Jam</div>
                    <div class="table-card-item-value">{{ $reservasi->jam }}</div>
                </div>
            </div>

            {{-- Barber --}}
            <div class="table-card-item-row">
                <span class="table-card-item-label">Barber</span>
                <span class="table-card-item-value">{{ $reservasi->barber->nama ?? '-' }}</span>
            </div>

            {{-- Nomor Antrian --}}
            <div class="table-card-item-row">
                <span class="table-card-item-label">No. Antrian</span>
                <span class="table-card-item-value">
                    @if($reservasi->queue)
                        <span class="badge rounded-pill text-bg-primary">#{{ $reservasi->queue->nomor_antrian }}</span>
                    @else
                        -
                    @endif
                </span>
            </div>

            {{-- Pembayaran --}}
            <div class="table-card-item-row">
                <span class="table-card-item-label">Pembayaran</span>
                <span class="table-card-item-value">{{ $reservasi->payment_method === 'online' ? 'Online' : 'COD' }}</span>
            </div>

            <div class="table-card-item-row">
                <span class="table-card-item-label">Status Bayar</span>
                <span class="badge text-bg-{{ $paymentBadge[0] }}">{{ $paymentBadge[1] }}</span>
            </div>

            {{-- Bukti Pembayaran --}}
            @if($reservasi->payment_method === 'online' && $reservasi->payment_proof)
                <div class="table-card-item-row">
                    <span class="table-card-item-label">Bukti Bayar</span>
                    <img
                        src="{{ asset('storage/' . $reservasi->payment_proof) }}"
                        alt="Bukti pembayaran {{ $reservasi->user->name }}"
                        class="rounded-2 border border-gold"
                        style="width: 3rem; height: 3rem; object-fit: cover; cursor: pointer;"
                        data-bs-toggle="modal"
                        data-bs-target="#buktiModal{{ $reservasi->id }}"
                    >
                </div>
            @endif

            {{-- Status Reservasi --}}
            <div class="mt-3">
                <div class="table-card-item-label mb-1">Status Reservasi</div>
                <form method="POST" action="/admin/reservasi/{{ $reservasi->id }}" class="mb-0">
                    @csrf
                    @method('PUT')
                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                        <option value="pending" {{ $reservasi->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ $reservasi->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ $reservasi->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="done" {{ $reservasi->status == 'done' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </form>
            </div>

            {{-- Foto Before/After (hanya reservasi selesai) --}}
            @if($reservasi->status === 'done')
                <div class="mt-3">
                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#transformasiModal{{ $reservasi->id }}"
                    >
                        <i class="fas fa-images"></i>
                        {{ ($reservasi->foto_before && $reservasi->foto_after) ? 'Edit Foto Before/After' : 'Upload Foto Before/After' }}
                    </button>
                </div>
            @endif

            {{-- Aksi --}}
            <div class="table-card-item-footer">
                <form method="POST" action="/admin/reservasi/{{ $reservasi->id }}" class="mb-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Yakin ingin menghapus reservasi ini?')"
                        class="btn btn-danger btn-sm w-100">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>

        </div>
    @empty
        <div class="table-card-item text-center text-body-secondary">
            {{ $emptyMessage ?? 'Belum ada reservasi.' }}
        </div>
    @endforelse
</div>
