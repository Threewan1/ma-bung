<x-guest-layout>
    <div class="min-vh-100 d-flex align-items-center justify-content-center px-3">
        <div class="w-100 bg-panel rounded-4 shadow-lg p-4 p-md-5 border border-gold" style="max-width: 28rem;">

            <div class="mb-4 small text-body-secondary">
                Ini adalah area aman pada aplikasi. Mohon konfirmasi kata sandi Anda sebelum melanjutkan.
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <!-- Password -->
                <div>
                    <x-input-label for="password" value="Kata Sandi" class="text-gold" />

                    <x-text-input id="password" class="mt-2 w-100"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <x-primary-button>
                        Konfirmasi
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
