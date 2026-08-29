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
                <i class="fas fa-cut fa-3x mb-3"></i>
                <p class="mb-0">Belum ada layanan yang tersedia.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach ($services as $service)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="bg-panel card-bordered-gold card-hover-gold rounded-3 shadow-sm p-4 h-100 d-flex flex-column text-center">
                            <i class="fas fa-scissors text-gold fs-2 mb-3"></i>
                            <h3 class="fs-5 fw-bold mb-2">{{ $service->nama_layanan }}</h3>
                            <p class="text-gold fs-4 fw-bold mb-3">
                                Rp {{ number_format($service->harga, 0, ',', '.') }}
                            </p>
                            <a href="{{ route('reservasi.create', ['service_id' => $service->id]) }}" class="btn btn-primary mt-auto">
                                <i class="fas fa-calendar-plus"></i> Reservasi Sekarang
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
