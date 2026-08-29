<x-guest-layout>
    <div class="min-vh-100 d-flex align-items-center justify-content-center px-3">
        <div class="w-100 bg-panel rounded-4 shadow-lg p-4 p-md-5 border border-gold" style="max-width: 28rem;">

            <h1 class="fs-4 fw-bold text-gold mb-3">Lupa Kata Sandi</h1>

            <div class="mb-4 small text-body-secondary">
                Lupa kata sandi Anda? Tidak masalah. Cukup masukkan alamat email Anda dan kami akan mengirimkan link untuk membuat kata sandi baru.
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" value="Email" class="text-gold" />
                    <x-text-input id="email" class="mt-2 w-100" type="email" name="email" :value="old('email')" required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="d-flex align-items-center justify-content-end mt-4">
                    <x-primary-button>
                        Kirim Link Reset Kata Sandi
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
