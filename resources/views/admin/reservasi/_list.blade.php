{{-- Partial daftar reservasi (grid card), dipakai berkali-kali per tab status lewat _tab-content.blade.php - terima $reservations & $emptyMessage. --}}

<div class="admin-reservasi-grid">
    @forelse($reservations as $reservasi)
        @php
            $paymentStatus = $reservasi->payment_status ?? 'unpaid';
            $paymentBadge = $reservasi->payment_badge;
        @endphp

        <div class="admin-reservasi-card" data-row-payment-method="{{ $reservasi->payment_method }}">

            {{-- Nomor urut + nama layanan sebagai judul card --}}
            <div class="admin-reservasi-card-title">
                #{{ $loop->iteration }} &mdash; {{ $reservasi->service->nama_layanan }}
            </div>

            <div class="admin-reservasi-card-row">
                <span class="admin-reservasi-card-label">Pelanggan</span>
                <span class="admin-reservasi-card-value">{{ $reservasi->user->name }}</span>
            </div>

            {{-- Nomor WA pelanggan, pakai ulang pola yang sama dari barber/dashboard.blade.php. --}}
            <div class="admin-reservasi-card-row">
                <span class="admin-reservasi-card-label">No. WhatsApp</span>
                <span class="admin-reservasi-card-value">
                    @if($reservasi->user->no_hp)
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', $reservasi->user->no_hp) }}" target="_blank" rel="noopener" class="text-gold text-decoration-none">
                            <i class="fab fa-whatsapp"></i> {{ $reservasi->user->no_hp }}
                        </a>
                    @else
                        <span class="text-body-secondary">-</span>
                    @endif
                </span>
            </div>

            <div class="admin-reservasi-card-row">
                <span class="admin-reservasi-card-label">Barber</span>
                <span class="admin-reservasi-card-value">{{ $reservasi->barber->nama ?? '-' }}</span>
            </div>

            <div class="admin-reservasi-card-cols">
                <div>
                    <div class="admin-reservasi-card-label">Tanggal</div>
                    <div class="admin-reservasi-card-value text-start">{{ $reservasi->tanggal }}</div>
                </div>
                <div class="text-end">
                    <div class="admin-reservasi-card-label">Jam</div>
                    <div class="admin-reservasi-card-value">{{ $reservasi->jam }}</div>
                </div>
            </div>

            <div class="admin-reservasi-card-row d-none d-sm-flex">
                <span class="admin-reservasi-card-label">No. Antrian</span>
                <span class="admin-reservasi-card-value">
                    @if($reservasi->queue)
                        <span class="badge rounded-pill text-bg-primary">#{{ $reservasi->queue->nomor_antrian }}</span>
                    @else
                        -
                    @endif
                </span>
            </div>

            <div class="admin-reservasi-card-row d-none d-sm-flex">
                <span class="admin-reservasi-card-label">Pembayaran</span>
                <span class="admin-reservasi-card-value">{{ $reservasi->payment_method === 'online' ? 'Online' : 'COD' }}</span>
            </div>

            <div class="admin-reservasi-card-row">
                <span class="admin-reservasi-card-label">Status Bayar</span>
                <span class="badge text-bg-{{ $paymentBadge[0] }}">{{ $paymentBadge[2] }}</span>
            </div>

            {{-- Cuma relevan untuk online, tidak dirender sama sekali untuk COD (bukan cuma disembunyikan CSS). --}}
            @if($reservasi->payment_method === 'online')
                <div class="admin-reservasi-card-row">
                    <span class="admin-reservasi-card-label">Bukti Bayar</span>
                    <span class="admin-reservasi-card-value">
                        @if($reservasi->payment_proof)
                            <img
                                src="{{ asset('storage/' . $reservasi->payment_proof) }}"
                                alt="Bukti pembayaran {{ $reservasi->user->name }}"
                                class="rounded-2 border border-gold"
                                style="width: 2.25rem; height: 2.25rem; object-fit: cover; cursor: pointer;"
                                data-bs-toggle="modal"
                                data-bs-target="#buktiModal{{ $reservasi->id }}"
                            >
                        @else
                            <span class="text-body-secondary small fst-italic">Belum ada bukti</span>
                        @endif
                    </span>
                </div>
            @endif

            {{-- "Sedang Dilayani"/"Selesai" cuma boleh diubah barber, jadi ditampilkan read-only, bukan dropdown. --}}
            @php
                // Reservasi online yang masih pending wajib dikonfirmasi lewat modal bukti bayar, bukan dropdown ini.
                $konfirmasiViaModal = $reservasi->payment_method === 'online' && $reservasi->status === 'pending';
            @endphp
            <div class="admin-reservasi-card-status">
                <div class="admin-reservasi-card-label mb-1">Status Reservasi</div>
                @if(in_array($reservasi->status, ['sedang_dilayani', 'done']))
                    <span class="badge {{ $reservasi->status === 'done' ? 'text-bg-success' : 'text-bg-primary' }}">
                        {{ $reservasi->status === 'done' ? 'Selesai' : 'Sedang Dilayani' }}
                    </span>
                @else
                    <form method="POST" action="/admin/reservasi/{{ $reservasi->id }}" class="mb-0">
                        @csrf
                        @method('PUT')
                        <select name="status" onchange="nonaktifkanSelectDanKirim(this)" class="form-select form-select-sm">
                            <option value="pending" {{ $reservasi->status == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="confirmed" {{ $konfirmasiViaModal ? 'disabled' : '' }} {{ $reservasi->status == 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                            <option value="cancelled" {{ $reservasi->status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </form>
                    @if($konfirmasiViaModal)
                        <p class="text-body-secondary small fst-italic mt-1 mb-0">
                            Cek bukti pembayaran dulu untuk konfirmasi &darr;
                        </p>
                    @endif
                @endif
            </div>

            {{-- Aksi: Lihat Bukti & Konfirmasi (online, menunggu) + Tandai Lunas (COD belum lunas) + Hapus --}}
            <div class="admin-reservasi-card-footer">
                {{-- Satu-satunya jalan konfirmasi reservasi online, membuka modal bukti bayar di index.blade.php. --}}
                @if($konfirmasiViaModal && $reservasi->payment_proof)
                    <button
                        type="button"
                        class="btn btn-outline-warning btn-sm w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#buktiModal{{ $reservasi->id }}"
                        title="Lihat Bukti Pembayaran &amp; Konfirmasi"
                    >
                        <i class="fas fa-receipt"></i><span class="d-none d-sm-inline"> Lihat Bukti Pembayaran &amp; Konfirmasi</span>
                    </button>
                @endif

                {{-- Tombol cepat khusus COD, dipisah dari dropdown status pelayanan di atas. --}}
                @if($reservasi->payment_method !== 'online' && $paymentStatus !== 'paid')
                    <form method="POST" action="{{ route('admin.reservasi.updatePaymentStatus', $reservasi->id) }}" class="mb-0" data-disable-on-submit>
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="payment_status" value="paid">
                        <button type="submit" class="btn btn-outline-success btn-sm w-100" title="Tandai Lunas">
                            <i class="fas fa-money-bill-wave"></i><span class="d-none d-sm-inline"> Tandai Lunas</span>
                        </button>
                    </form>
                @endif

                <form method="POST" action="/admin/reservasi/{{ $reservasi->id }}" class="mb-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Yakin ingin menghapus reservasi ini?')"
                        class="btn btn-danger btn-sm w-100"
                        title="Hapus">
                        <i class="fas fa-trash"></i><span class="d-none d-sm-inline"> Hapus</span>
                    </button>
                </form>
            </div>

        </div>
    @empty
        <div class="admin-reservasi-card-empty text-center text-body-secondary">
            {{ $emptyMessage ?? 'Belum ada reservasi.' }}
        </div>
    @endforelse
</div>
