<x-admin-layout title="Kelola Antrian">

    <h2 class="fs-2 fw-bold text-gold mb-4">
        <i class="fas fa-users"></i> Kelola Antrian Hari Ini
    </h2>

    {{-- Pesan Sukses --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- ================================================= --}}
    {{-- VERSI TABEL (desktop, >=768px) --}}
    {{-- ================================================= --}}
    <div class="bg-panel rounded-3 overflow-hidden d-none d-md-block">
        <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead class="bg-surface">
                <tr>
                    <th>No. Antrian</th>
                    <th>Pelanggan</th>
                    <th>Layanan</th>
                    <th>Jam</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loop semua data antrian --}}
                @forelse($queues as $queue)
                <tr>
                    {{-- Nomor antrian dengan tampilan besar --}}
                    <td>
                        <span class="badge rounded-pill text-bg-primary fs-6">
                            #{{ $queue->nomor_antrian }}
                        </span>
                    </td>
                    <td>{{ $queue->reservation->user->name }}</td>
                    <td>{{ $queue->reservation->service->nama_layanan }}</td>
                    <td>{{ $queue->reservation->jam }}</td>
                    <td>
                        {{-- Form update status antrian --}}
                        <form method="POST" action="/admin/antrian/{{ $queue->id }}" class="mb-0">
                            @csrf
                            @method('PUT')
                            <select name="status_antrian" onchange="this.form.submit()" class="form-select form-select-sm">
                                <option value="menunggu" {{ $queue->status_antrian == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                <option value="diproses" {{ $queue->status_antrian == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                <option value="selesai" {{ $queue->status_antrian == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        {{-- Tombol Hapus --}}
                        <form method="POST" action="/admin/antrian/{{ $queue->id }}" class="mb-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                onclick="return confirm('Yakin ingin menghapus antrian ini?')"
                                class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                {{-- Tampilkan pesan jika belum ada antrian --}}
                <tr>
                    <td colspan="6" class="text-center text-body-secondary py-4">
                        Belum ada antrian hari ini
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
        @forelse($queues as $queue)
            <div class="table-card-item">

                <div class="table-card-item-title">
                    <span class="badge rounded-pill text-bg-primary fs-6 me-2">
                        #{{ $queue->nomor_antrian }}
                    </span>
                    {{ $queue->reservation->user->name }}
                </div>

                <div class="table-card-item-row">
                    <span class="table-card-item-label">Layanan</span>
                    <span class="table-card-item-value">{{ $queue->reservation->service->nama_layanan }}</span>
                </div>

                <div class="table-card-item-row">
                    <span class="table-card-item-label">Jam</span>
                    <span class="table-card-item-value">{{ $queue->reservation->jam }}</span>
                </div>

                <div class="mt-3">
                    <div class="table-card-item-label mb-1">Status</div>
                    <form method="POST" action="/admin/antrian/{{ $queue->id }}" class="mb-0">
                        @csrf
                        @method('PUT')
                        <select name="status_antrian" onchange="this.form.submit()" class="form-select form-select-sm">
                            <option value="menunggu" {{ $queue->status_antrian == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                            <option value="diproses" {{ $queue->status_antrian == 'diproses' ? 'selected' : '' }}>Diproses</option>
                            <option value="selesai" {{ $queue->status_antrian == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </form>
                </div>

                <div class="table-card-item-footer">
                    <form method="POST" action="/admin/antrian/{{ $queue->id }}" class="mb-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            onclick="return confirm('Yakin ingin menghapus antrian ini?')"
                            class="btn btn-danger btn-sm w-100">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                </div>

            </div>
        @empty
            <div class="table-card-item text-center text-body-secondary">
                Belum ada antrian hari ini
            </div>
        @endforelse
    </div>

</x-admin-layout>
