<x-admin-layout title="Kelola Barber">

    {{-- Judul di pojok kanan atas, sama gaya dengan header halaman admin lain. --}}
    <div class="d-flex justify-content-end mb-4">
        <h2 class="admin-page-title bg-panel">
            Kelola Barber
        </h2>
    </div>

    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($barbers->isEmpty())
        <div class="bg-panel p-5 rounded-3 text-center text-body-secondary">
            <p class="mb-0">Belum ada data barber.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($barbers as $barber)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="bg-panel card-bordered-gold card-hover-gold rounded-3 shadow-sm p-4 h-100 d-flex flex-column text-center">

                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold mx-auto mb-3 overflow-hidden" style="width: 4rem; height: 4rem; font-size: 1.5rem;">
                            @if($barber->foto)
                                <img src="{{ asset('storage/' . $barber->foto) }}" alt="{{ $barber->nama }}" class="w-100 h-100 object-fit-cover">
                            @else
                                {{ strtoupper(substr($barber->nama, 0, 1)) }}
                            @endif
                        </div>

                        <h3 class="fs-5 fw-bold mb-1">{{ $barber->nama }}</h3>
                        <p class="text-body-secondary small mb-3">{{ $barber->user->email ?? '-' }}</p>

                        <div class="mb-3">
                            @if($barber->status_aktif)
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary">Non-aktif</span>
                            @endif
                        </div>

                        <div class="d-flex gap-2 mt-auto">
                            <a href="{{ route('admin.barber.edit', $barber->id) }}" class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.barber.toggleStatus', $barber->id) }}" class="flex-fill mb-0">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="btn btn-sm w-100 {{ $barber->status_aktif ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                    onclick="return confirm('{{ $barber->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }} {{ $barber->nama }}?')"
                                >
                                    {{ $barber->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-admin-layout>
