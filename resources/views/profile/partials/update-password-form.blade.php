<section>
    <header>
        <h2 class="fs-5 fw-medium mb-1">
            Ubah Kata Sandi
        </h2>

        <p class="text-body-secondary small mb-0">
            Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-2 d-flex flex-column gap-2">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Kata Sandi Saat Ini" class="mb-1" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-0 w-100 form-control-sm" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Kata Sandi Baru" class="mb-1" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-0 w-100 form-control-sm" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Konfirmasi Kata Sandi" class="mb-1" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-0 w-100 form-control-sm" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button class="btn-sm">Simpan</x-primary-button>

            @if (session('status') === 'password-updated')
                <p class="small text-body-secondary mb-0">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
