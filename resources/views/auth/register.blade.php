<x-guest-layout>

    <!-- Background utama -->
    <div class="min-vh-100 d-flex align-items-center justify-content-center px-3 py-3">

        <!-- Card Register -->
        <div class="w-100 card-glass rounded-4 px-4 py-3" style="max-width: 400px;">

            <!-- Header -->
            <div class="text-center mb-3">

                <!-- Icon -->
                <div class="d-flex justify-content-center mb-2">

                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold shadow" style="width: 3.5rem; height: 3.5rem; font-size: 1.25rem;">
                        M
                    </div>

                </div>

                <!-- Judul -->
                <h1 class="fs-5 fw-bold text-gold mb-0">
                    MA'BUNG BARBERSHOP
                </h1>

                <p class="text-body-secondary mt-1 small mb-0">
                    Buat Akun Kamu
                </p>

            </div>

            <!-- Form Register -->
            <form method="POST" action="{{ route('register') }}">

                @csrf

                <!-- Nama -->
                <div>

                    <x-input-label
                        for="name"
                        value="Nama Lengkap"
                        class="text-gold small mb-1"
                    />

                    <x-text-input
                        id="name"
                        class="w-100 form-control-sm"
                        type="text"
                        name="name"
                        :value="old('name')"
                        required
                        autofocus
                        autocomplete="name"
                    />

                    <x-input-error
                        :messages="$errors->get('name')"
                        class="mt-1"
                    />

                </div>

                <!-- Email -->
                <div class="mt-2">

                    <x-input-label
                        for="email"
                        value="Email"
                        class="text-gold small mb-1"
                    />

                    <x-text-input
                        id="email"
                        class="w-100 form-control-sm"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autocomplete="username"
                    />

                    <x-input-error
                        :messages="$errors->get('email')"
                        class="mt-1"
                    />

                </div>

                <!-- Password -->
                <div class="mt-2">

                    <x-input-label
                        for="password"
                        value="Kata Sandi"
                        class="text-gold small mb-1"
                    />

                    <x-text-input
                        id="password"
                        class="w-100 form-control-sm"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                    />

                    <x-input-error
                        :messages="$errors->get('password')"
                        class="mt-1"
                    />

                </div>

                <!-- Konfirmasi Password -->
                <div class="mt-2">

                    <x-input-label
                        for="password_confirmation"
                        value="Konfirmasi Kata Sandi"
                        class="text-gold small mb-1"
                    />

                    <x-text-input
                        id="password_confirmation"
                        class="w-100 form-control-sm"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                    />

                    <x-input-error
                        :messages="$errors->get('password_confirmation')"
                        class="mt-1"
                    />

                </div>

                <!-- Tombol -->
                <div class="d-flex align-items-center justify-content-between mt-3">

                    <!-- Login -->
                    <a
                        class="small text-body-secondary"
                        href="{{ route('login') }}"
                    >
                        Sudah punya akun?
                    </a>

                    <!-- Button Register -->
                    <button
                        type="submit"
                        class="btn btn-primary btn-sm px-3 py-2"
                    >
                        DAFTAR
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-guest-layout>
