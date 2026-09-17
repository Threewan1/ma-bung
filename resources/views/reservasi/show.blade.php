<x-app-layout>
    <div class="container" style="max-width: 550px; padding-top: 1.1rem; padding-bottom: 1.25rem;">
        <h2 class="fs-4 fw-bold text-gold mb-3">
            Detail Reservasi
        </h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="bg-panel px-4 py-2 rounded-3 shadow">

            {{-- Tampilkan nomor antrian jika ada --}}
            @if($reservasi->queue)
            <div class="text-center mb-3">
                <p class="text-body-secondary small mb-1">Nomor Antrian Kamu</p>
                {{-- Tampilkan nomor antrian --}}
                <span class="fs-1 fw-bold text-gold">
                    #{{ $reservasi->queue->nomor_antrian }}
                </span>
            </div>
            @endif

            {{-- Detail informasi reservasi --}}
            <div class="d-flex flex-column">

                {{-- Nama layanan yang dipilih --}}
                <div class="d-flex justify-content-between border-bottom border-secondary-subtle reservasi-detail-row">
                    <span class="text-body-secondary">Layanan</span>
                    <span class="fw-bold">{{ $reservasi->service->nama_layanan }}</span>
                </div>

                {{-- Barber yang dipilih --}}
                <div class="d-flex justify-content-between border-bottom border-secondary-subtle reservasi-detail-row">
                    <span class="text-body-secondary">Barber</span>
                    <span class="fw-bold">{{ $reservasi->barber->nama ?? '-' }}</span>
                </div>

                {{-- Harga layanan dengan format rupiah --}}
                <div class="d-flex justify-content-between border-bottom border-secondary-subtle reservasi-detail-row">
                    <span class="text-body-secondary">Harga</span>
                    <span class="fw-bold text-gold">
                        Rp {{ number_format($reservasi->harga_snapshot, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Tanggal reservasi --}}
                <div class="d-flex justify-content-between border-bottom border-secondary-subtle reservasi-detail-row">
                    <span class="text-body-secondary">Tanggal</span>
                    <span class="fw-bold">{{ $reservasi->tanggal }}</span>
                </div>

                {{-- Jam reservasi --}}
                <div class="d-flex justify-content-between border-bottom border-secondary-subtle reservasi-detail-row">
                    <span class="text-body-secondary">Jam</span>
                    <span class="fw-bold">{{ $reservasi->jam }}</span>
                </div>

                {{-- Catatan dari pelanggan, jika kosong tampilkan tanda - --}}
                <div class="d-flex justify-content-between border-bottom border-secondary-subtle reservasi-detail-row">
                    <span class="text-body-secondary">Catatan</span>
                    <span class="fw-bold">{{ $reservasi->catatan ?? '-' }}</span>
                </div>

                {{-- METODE PEMBAYARAN --}}
                <div class="d-flex justify-content-between border-bottom border-secondary-subtle reservasi-detail-row">
                    <span class="text-body-secondary">Metode Pembayaran</span>

                    <span class="fw-bold">
                        {{ ucfirst($reservasi->payment_method ?? '-') }}
                    </span>
                </div>

                {{-- STATUS PEMBAYARAN --}}
                <div class="d-flex justify-content-between border-bottom border-secondary-subtle reservasi-detail-row">

                    <span class="text-body-secondary">Status Pembayaran</span>

                    @php
                        $paymentBadge = $reservasi->payment_badge;
                    @endphp

                    <span class="badge text-bg-{{ $paymentBadge[0] }}">
                        {{ $paymentBadge[2] }}
                    </span>

                </div>

                {{-- Status reservasi dengan warna berbeda --}}
                <div class="d-flex justify-content-between">
                    <span class="text-body-secondary">Status</span>
                    @if($reservasi->status == 'pending')
                        {{-- Kuning = menunggu konfirmasi --}}
                        <span class="badge text-bg-warning">Pending</span>
                    @elseif($reservasi->status == 'confirmed')
                        {{-- Biru = sudah dikonfirmasi admin --}}
                        <span class="badge text-bg-info">Dikonfirmasi</span>
                    @elseif($reservasi->status == 'sedang_dilayani')
                        {{-- Gold = sedang dikerjakan barber --}}
                        <span class="badge text-bg-primary">Sedang Dilayani</span>
                    @elseif($reservasi->status == 'cancelled')
                        {{-- Merah = dibatalkan --}}
                        <span class="badge text-bg-danger">Dibatalkan</span>
                    @elseif($reservasi->status == 'done')
                        {{-- Hijau = sudah selesai --}}
                        <span class="badge text-bg-success">Selesai</span>
                    @endif
                </div>
            </div>

            {{-- BUKTI PEMBAYARAN, hanya untuk metode pembayaran Online. --}}

            @if($reservasi->payment_method == 'online')

                {{-- Jika bukti pembayaran sudah ada --}}
                @if($reservasi->payment_proof)

                    <div class="mt-3">

                        <p class="text-body-secondary small mb-1">
                            Bukti Pembayaran
                        </p>

                        <img
                            src="{{ asset('storage/' . $reservasi->payment_proof) }}"
                            alt="Bukti Pembayaran"
                            class="rounded-3 border w-100"
                            style="max-height: 20rem; object-fit: contain;"
                        >

                    </div>

                @else

                    {{-- Form upload bukti pembayaran --}}
                    <form
                        action="{{ route('reservasi.uploadBukti', $reservasi->id) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="mt-3"
                        data-disable-on-submit
                    >

                        @csrf

                        <label class="form-label text-body-secondary small mb-1">
                            Upload Bukti Pembayaran
                        </label>

                        <input
                            type="file"
                            name="payment_proof"
                            accept="image/*"
                            required
                            class="form-control"
                        >

                        <button
                            type="submit"
                            class="btn btn-success w-100 mt-2"
                        >
                            <i class="fas fa-upload"></i>
                            Upload Bukti Pembayaran
                        </button>

                    </form>

                @endif

            @endif

            {{-- Tombol kembali --}}
            <a href="{{ route('reservasi.index') }}" class="btn btn-primary w-100 mt-3">

                <i class="fas fa-arrow-left"></i>
                Kembali ke Daftar Reservasi

            </a>
        </div>
    </div>

    {{-- Cegah double-submit tombol "Upload Bukti Pembayaran". --}}
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

            document.addEventListener('submit', function (e) {
                if (e.target.matches('[data-disable-on-submit]')) {
                    nonaktifkanTombolSubmit(e.target);
                }
            });
        })();
    </script>
</x-app-layout>
