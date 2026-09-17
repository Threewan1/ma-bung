<x-barber-layout title="Riwayat Saya">

    <div class="mb-4">
        <h2 class="fs-3 fw-bold text-gold mb-1">
            Riwayat Saya
        </h2>
        <p class="text-body-secondary mb-0">
            Semua reservasi yang pernah kamu tangani dengan status Selesai atau Dibatalkan, dari yang paling baru.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(! $barber)
        {{-- Jaga-jaga kalau akun barber belum terhubung ke tabel "barbers". --}}
        <div class="bg-panel p-5 rounded-3 text-center text-body-secondary">
            <p class="mb-0">Akun kamu belum terhubung ke data barber manapun. Silakan hubungi admin.</p>
        </div>
    @elseif($reservasiRiwayat->isEmpty())
        <div class="bg-panel p-5 rounded-3 text-center text-body-secondary">
            <p class="mb-0">Belum ada riwayat reservasi Selesai atau Dibatalkan.</p>
        </div>
    @else
        {{-- Card read-only, pakai style sama dengan halaman kerja utama tapi tanpa data-reservasi-id (halaman ini tidak dipoll). --}}
        <div class="barber-jadwal-grid barber-jadwal-grid-wide">
            @foreach($reservasiRiwayat as $reservasi)
                @php
                    $statusMap = [
                        'done' => ['success', 'Selesai'],
                        'cancelled' => ['danger', 'Dibatalkan'],
                    ];
                    $info = $statusMap[$reservasi->status] ?? ['secondary', ucfirst($reservasi->status)];
                @endphp
                <div class="barber-jadwal-card">

                    <div class="barber-jadwal-card-top">
                        <div>
                            <div class="barber-jadwal-card-tanggal">{{ \Carbon\Carbon::parse($reservasi->tanggal)->translatedFormat('D, d M Y') }}</div>
                            <span class="barber-jadwal-card-jam">{{ $reservasi->jam }}</span>
                        </div>
                        <span class="badge text-bg-{{ $info[0] }}">{{ $info[1] }}</span>
                    </div>

                    <div class="barber-jadwal-card-field">
                        <span class="barber-jadwal-card-label">Pelanggan</span>
                        <p class="barber-jadwal-card-value">{{ $reservasi->user->name }}</p>
                    </div>

                    <div class="barber-jadwal-card-field">
                        <span class="barber-jadwal-card-label">Layanan</span>
                        <p class="barber-jadwal-card-value">{{ $reservasi->service->nama_layanan ?? '-' }}</p>
                    </div>

                    {{-- Cuma untuk reservasi "Selesai", jalur permanen upload foto (bukan cuma reveal sesaat seperti di halaman kerja utama). --}}
                    @if($reservasi->status === 'done')
                        <div class="barber-jadwal-card-field">
                            <span class="barber-jadwal-card-label">Foto Before &amp; After</span>

                            <div class="row g-2 mt-1">
                                <div class="col-6">
                                    <label class="form-label small text-body-secondary mb-1">Before</label>
                                    @if($reservasi->foto_before)
                                        <img src="{{ asset('storage/' . $reservasi->foto_before) }}" class="img-fluid rounded-3 mb-1" alt="Foto before {{ $reservasi->user->name }}">
                                        <form method="POST" action="{{ route('barber.hapusTransformasi', [$reservasi->id, 'before']) }}" class="mb-0" onsubmit="return confirm('Hapus foto before? Tindakan ini permanen.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                <i class="fas fa-trash"></i> Hapus Foto
                                            </button>
                                        </form>
                                    @else
                                        <input type="file" name="foto_before" form="form-transformasi-{{ $reservasi->id }}" accept="image/*" class="form-control form-control-sm">
                                    @endif
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-body-secondary mb-1">After</label>
                                    @if($reservasi->foto_after)
                                        <img src="{{ asset('storage/' . $reservasi->foto_after) }}" class="img-fluid rounded-3 mb-1" alt="Foto after {{ $reservasi->user->name }}">
                                        <form method="POST" action="{{ route('barber.hapusTransformasi', [$reservasi->id, 'after']) }}" class="mb-0" onsubmit="return confirm('Hapus foto after? Tindakan ini permanen.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                <i class="fas fa-trash"></i> Hapus Foto
                                            </button>
                                        </form>
                                    @else
                                        <input type="file" name="foto_after" form="form-transformasi-{{ $reservasi->id }}" accept="image/*" class="form-control form-control-sm">
                                    @endif
                                </div>
                            </div>

                            @if(! $reservasi->foto_before || ! $reservasi->foto_after)
                                <form id="form-transformasi-{{ $reservasi->id }}" method="POST" action="{{ route('barber.uploadTransformasi', $reservasi->id) }}" enctype="multipart/form-data" class="mb-0 mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="fas fa-upload"></i> Upload Foto
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @endif

</x-barber-layout>
