<x-app-layout>

    {{-- Judul halaman mengambang di pojok kanan atas, transparan, dan
         tetap diam di tempat (fixed) walau halaman di-scroll. --}}
    <div class="page-title-floating">
        <h2 class="fs-4 fw-bold text-white mb-0">
            Semua Layanan & Harga
        </h2>
    </div>

    <div class="container pt-5 pb-4">
        @if ($services->isEmpty())
            <div class="bg-panel card-bordered-gold rounded-3 p-5 text-center text-body-secondary">
                <p class="mb-0">Belum ada layanan yang tersedia.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach ($services as $service)
                    <div class="col-6 col-lg-4">
                        <div class="card-layanan layanan-page-card h-100">
                            <div class="card-layanan-img-wrap">
                                <img
                                    src="{{ $service->foto ? asset('storage/' . $service->foto) : asset('images/layanan/potong-rambut.jpg') }}"
                                    alt="Layanan {{ $service->nama_layanan }}">
                            </div>
                            <div class="card-layanan-body text-center">
                                <h3 class="fw-bold mb-2">{{ $service->nama_layanan }}</h3>
                                <p class="text-gold fw-bold mb-3">
                                    Rp {{ number_format($service->harga, 0, ',', '.') }}
                                </p>
                                <a href="{{ route('reservasi.create', ['service_id' => $service->id]) }}" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus"></i> Reservasi Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
