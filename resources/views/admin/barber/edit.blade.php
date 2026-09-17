<x-admin-layout title="Edit Barber">

    <h2 class="fs-2 fw-bold text-gold mb-4">
        Edit Barber
    </h2>

    {{-- Pesan Error --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-panel card-bordered-gold rounded-3 shadow-sm p-4" style="max-width: 32rem;">
        <form method="POST" action="{{ route('admin.barber.update', $barber->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="text-center mb-4">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold mx-auto overflow-hidden" style="width: 5rem; height: 5rem; font-size: 2rem;">
                    @if($barber->foto)
                        <img src="{{ asset('storage/' . $barber->foto) }}" alt="{{ $barber->nama }}" class="w-100 h-100 object-fit-cover">
                    @else
                        {{ strtoupper(substr($barber->nama, 0, 1)) }}
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $barber->nama) }}" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Foto (opsional)</label>
                <input type="file" name="foto" accept="image/*" class="form-control">
                <small class="text-body-secondary">Kosongkan kalau tidak ingin mengganti foto.</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('admin.barber.index') }}" class="btn btn-outline-secondary flex-fill">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-admin-layout>
