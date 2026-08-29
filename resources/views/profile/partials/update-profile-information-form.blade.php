<section>
    <header>
        <h2 class="fs-5 fw-medium mb-1">
            Informasi Profil
        </h2>

        <p class="text-body-secondary small mb-0">
            Perbarui informasi profil dan alamat email akun Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-2 d-flex flex-column gap-2">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nama" class="mb-1" />
            <x-text-input id="name" name="name" type="text" class="mt-0 w-100 form-control-sm" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" class="mb-1" />
            <x-text-input id="email" name="email" type="email" class="mt-0 w-100 form-control-sm" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-1" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="small mt-1 mb-0">
                        Alamat email Anda belum diverifikasi.

                        <button form="send-verification" class="btn btn-link p-0 small align-baseline">
                            Klik di sini untuk mengirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1 mb-0 fw-medium small text-success">
                            Link verifikasi baru telah dikirim ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="no_hp" value="Nomor WhatsApp" class="mb-1" />
            <x-text-input id="no_hp" name="no_hp" type="text" class="mt-0 w-100 form-control-sm" :value="old('no_hp', $user->no_hp)" placeholder="08xxxxxxxxxx" autocomplete="tel" />
            <x-input-error class="mt-1" :messages="$errors->get('no_hp')" />
        </div>

        <div>
            <x-input-label for="tanggal_lahir" value="Tanggal Lahir" class="mb-1" />
            <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" class="mt-0 w-100 form-control-sm" :value="old('tanggal_lahir', $user->tanggal_lahir?->format('Y-m-d'))" />
            <x-input-error class="mt-1" :messages="$errors->get('tanggal_lahir')" />
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button class="btn-sm">Simpan</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p class="small text-body-secondary mb-0">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
