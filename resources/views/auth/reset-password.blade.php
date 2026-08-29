<x-guest-layout>
    <div class="min-vh-100 d-flex align-items-center justify-content-center px-3">
        <div class="w-100 bg-panel rounded-4 shadow-lg p-4 p-md-5 border border-gold" style="max-width: 28rem;">

            <h1 class="fs-4 fw-bold text-gold mb-3">Atur Ulang Kata Sandi</h1>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" value="Email" class="text-gold" />
                    <x-text-input id="email" class="mt-2 w-100" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-3">
                    <x-input-label for="password" value="Kata Sandi Baru" class="text-gold" />
                    <x-text-input id="password" class="mt-2 w-100" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-3">
                    <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" class="text-gold" />

                    <x-text-input id="password_confirmation" class="mt-2 w-100"
                                        type="password"
                                        name="password_confirmation" required autocomplete="new-password" />

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="d-flex align-items-center justify-content-end mt-4">
                    <x-primary-button>
                        Atur Ulang Kata Sandi
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
