<x-guest-layout>

    <!-- Background utama -->
    <div class="min-vh-100 d-flex align-items-center justify-content-center px-3 py-3">

        <!-- Card Login -->
        <div class="w-100 card-glass rounded-4 px-4 py-3" style="max-width: 400px;">

            <!-- Judul -->
            <div class="text-center mb-3">

                <!-- Logo / Icon -->
                <div class="d-flex justify-content-center mb-2">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold shadow" style="width: 3.5rem; height: 3.5rem; font-size: 1.25rem;">
                        M
                    </div>
                </div>

                <!-- Nama Barbershop -->
                <h1 class="fs-5 fw-bold text-gold mb-0">
                    MA'BUNG BARBERSHOP
                </h1>

                <p class="text-body-secondary mt-1 small mb-0">
                    Premium Haircut & Grooming
                </p>
            </div>

            <!-- Status session -->
            <x-auth-session-status class="mb-3" :status="session('status')" />

            <!-- Form Login -->
            <form method="POST" action="{{ route('login') }}">

                @csrf

                <!-- Email -->
                <div>

                    <!-- Label -->
                    <x-input-label
                        for="email"
                        value="Email"
                        class="text-gold small mb-1"
                    />

                    <!-- Input email -->
                    <x-text-input
                        id="email"
                        class="w-100 form-control-sm"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                    />

                    <!-- Error email -->
                    <x-input-error
                        :messages="$errors->get('email')"
                        class="mt-1"
                    />

                </div>

                <!-- Password -->
                <div class="mt-2">

                    <!-- Label -->
                    <x-input-label
                        for="password"
                        value="Kata Sandi"
                        class="text-gold small mb-1"
                    />

                    <!-- Input password -->
                    <x-text-input
                        id="password"
                        class="w-100 form-control-sm"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    />

                    <!-- Error password -->
                    <x-input-error
                        :messages="$errors->get('password')"
                        class="mt-1"
                    />

                </div>

                <!-- Remember Me -->
                <div class="mt-2">

                    <div class="form-check">

                        <input
                            id="remember_me"
                            type="checkbox"
                            class="form-check-input"
                            name="remember"
                        >

                        <label for="remember_me" class="form-check-label text-body-secondary small">
                            Ingat saya
                        </label>

                    </div>

                </div>

                <!-- Tombol -->
                <div class="d-flex align-items-center justify-content-between mt-3">

                    <!-- Forgot password -->
                    @if (Route::has('password.request'))

                        <a
                            class="small text-body-secondary link-underline-opacity-0"
                            href="{{ route('password.request') }}"
                        >
                            Lupa Kata Sandi?
                        </a>

                    @endif

                    <!-- Button Login -->
                    <button
                        type="submit"
                        class="btn btn-primary btn-sm px-3 py-2"
                    >
                        MASUK
                    </button>

                </div>

            </form>

            <!-- Register -->
            <div class="mt-3 text-center">

                <p class="text-body-secondary small mb-1">
                    Belum punya akun?
                </p>

                <a
                    href="{{ route('register') }}"
                    class="text-gold fw-semibold"
                >
                    Daftar Sekarang
                </a>

            </div>

        </div>

    </div>

</x-guest-layout>
