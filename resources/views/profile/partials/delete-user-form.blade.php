<section class="d-flex flex-column gap-2">
    <header>
        <h2 class="fs-5 fw-bold text-danger mb-1">
            <i class="fas fa-triangle-exclamation"></i> Hapus Akun
        </h2>

        <p class="text-body-secondary small mb-0">
            Setelah akun Anda dihapus, semua data dan sumber dayanya akan dihapus secara permanen. Sebelum menghapus akun, silakan unduh data atau informasi yang ingin Anda simpan.
        </p>
    </header>

    <div>
        <x-danger-button class="btn-sm" data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">
            Hapus Akun
        </x-danger-button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <div class="modal-header">
                <h2 class="modal-title fs-5">
                    Apakah Anda yakin ingin menghapus akun ini?
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="text-body-secondary small">
                    Setelah akun Anda dihapus, semua data dan sumber dayanya akan dihapus secara permanen. Masukkan kata sandi Anda untuk konfirmasi bahwa Anda ingin menghapus akun ini secara permanen.
                </p>

                <div class="mt-3">
                    <x-input-label for="password" value="Kata Sandi" class="visually-hidden" />

                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        class="w-100"
                        placeholder="Kata Sandi"
                    />

                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>
            </div>

            <div class="modal-footer">
                <x-secondary-button data-bs-dismiss="modal">
                    Batal
                </x-secondary-button>

                <x-danger-button>
                    Hapus Akun
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
