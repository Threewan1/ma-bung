<x-admin-layout title="Kelola Layanan">

    <h2 class="fs-2 fw-bold text-gold mb-4">
        <i class="fas fa-cut"></i> Kelola Layanan
    </h2>

    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tombol Tambah Layanan --}}
    <a href="/admin/layanan/create" class="btn btn-primary mb-4">
        <i class="fas fa-plus"></i> Tambah Layanan
    </a>

    {{-- ================================================= --}}
    {{-- VERSI TABEL (desktop, >=768px) --}}
    {{-- ================================================= --}}
    <div class="bg-panel rounded-3 overflow-hidden d-none d-md-block">
        <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead class="bg-surface">
                <tr>
                    <th>No</th>
                    <th>Nama Layanan</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loop semua data layanan --}}
                @forelse($services as $index => $service)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $service->nama_layanan }}</td>
                    <td class="text-gold">
                        Rp {{ number_format($service->harga, 0, ',', '.') }}
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            {{-- Tombol Edit --}}
                            <a href="/admin/layanan/{{ $service->id }}/edit" class="btn btn-info btn-sm text-white">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            {{-- Tombol Hapus --}}
                            <form method="POST" action="/admin/layanan/{{ $service->id }}" class="mb-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Yakin ingin menghapus layanan ini?')"
                                    class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                {{-- Tampilkan pesan jika belum ada layanan --}}
                <tr>
                    <td colspan="4" class="text-center text-body-secondary py-4">
                        Belum ada layanan. Tambahkan layanan baru!
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
        @forelse($services as $index => $service)
            <div class="table-card-item">

                <div class="table-card-item-title">
                    #{{ $index + 1 }} &mdash; {{ $service->nama_layanan }}
                </div>

                <div class="table-card-item-row">
                    <span class="table-card-item-label">Harga</span>
                    <span class="table-card-item-value text-gold">
                        Rp {{ number_format($service->harga, 0, ',', '.') }}
                    </span>
                </div>

                <div class="table-card-item-footer">
                    <a href="/admin/layanan/{{ $service->id }}/edit" class="btn btn-info btn-sm text-white w-100">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form method="POST" action="/admin/layanan/{{ $service->id }}" class="mb-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            onclick="return confirm('Yakin ingin menghapus layanan ini?')"
                            class="btn btn-danger btn-sm w-100">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                </div>

            </div>
        @empty
            <div class="table-card-item text-center text-body-secondary">
                Belum ada layanan. Tambahkan layanan baru!
            </div>
        @endforelse
    </div>

</x-admin-layout>
