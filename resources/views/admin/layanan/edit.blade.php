<x-admin-layout title="Edit Layanan">

    <div class="mx-auto" style="max-width: 36rem;">
        <h2 class="fs-2 fw-bold text-gold mb-4">
            Edit Layanan
        </h2>

        <div class="bg-panel p-4 rounded-3 shadow">

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

            {{-- Form menggunakan method PUT untuk update --}}
            <form method="POST" action="/admin/layanan/{{ $layanan->id }}" enctype="multipart/form-data">
                @csrf
                @method('PUT') {{-- Memberitahu Laravel bahwa ini adalah request UPDATE --}}

                {{-- Input Nama Layanan dengan nilai yang sudah ada --}}
                <div class="mb-3">
                    <label class="form-label">Nama Layanan</label>
                    <input type="text" name="nama_layanan"
                        value="{{ old('nama_layanan', $layanan->nama_layanan) }}"
                        class="form-control">
                </div>

                {{-- Input Harga dengan nilai yang sudah ada --}}
                <div class="mb-3">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="harga"
                        value="{{ old('harga', $layanan->harga) }}"
                        class="form-control">
                </div>

                {{-- Foto Layanan saat ini + input ganti foto --}}
                <div class="mb-4">
                    <label class="form-label">Foto Layanan</label>
                    @if($layanan->foto)
                        <img src="{{ asset('storage/' . $layanan->foto) }}" alt="{{ $layanan->nama_layanan }}"
                            class="rounded-3 mb-2 d-block" style="width: 100%; max-width: 12rem; height: 8rem; object-fit: cover;">
                    @endif
                    <input type="file" name="foto" accept="image/*" class="form-control">
                    <small class="text-body-secondary">Kosongkan kalau tidak ingin mengganti foto.</small>
                </div>

                {{-- Tombol Update --}}
                <button type="submit" class="btn btn-primary w-100 py-2 fs-5">
                    <i class="fas fa-save"></i> Update Layanan
                </button>

                {{-- Tombol Kembali --}}
                <a href="/admin/layanan" class="d-block text-center text-body-secondary mt-3">
                    Kembali ke Daftar Layanan
                </a>
            </form>
        </div>
    </div>

</x-admin-layout>
