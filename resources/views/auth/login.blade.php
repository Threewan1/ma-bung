<x-guest-layout>

    <!-- Background utama -->
    <div class="min-h-screen flex items-center justify-center bg-[#0f0f0f] px-4">

        <!-- Card Login -->
        <div class="w-full max-w-md bg-[#1a1a1a] rounded-2xl shadow-2xl p-8 border border-yellow-500">

            <!-- Judul -->
            <div class="text-center mb-8">

                <!-- Logo / Icon -->
                <div class="flex justify-center mb-4">
                    <div class="w-20 h-20 rounded-full bg-yellow-500 flex items-center justify-center text-black text-3xl font-bold shadow-lg">
                        M
                    </div>
                </div>

                <!-- Nama Barbershop -->
                <h1 class="text-3xl font-bold text-yellow-400 tracking-wide">
                    MA'BUNG BARBERSHOP
                </h1>

                <p class="text-gray-400 mt-2 text-sm">
                    Premium Haircut & Grooming
                </p>
            </div>

            <!-- Status session -->
            <x-auth-session-status class="mb-4 text-green-400" :status="session('status')" />

            <!-- Form Login -->
            <form method="POST" action="{{ route('login') }}">

                @csrf

                <!-- Email -->
                <div>

                    <!-- Label -->
                    <x-input-label
                        for="email"
                        :value="__('Email')"
                        class="text-yellow-400"
                    />

                    <!-- Input email -->
                    <x-text-input
                        id="email"
                        class="block mt-2 w-full bg-[#262626] border-gray-700 text-white focus:border-yellow-500 focus:ring-yellow-500 rounded-lg"
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
                        class="mt-2 text-red-400"
                    />

                </div>

                <!-- Password -->
                <div class="mt-5">

                    <!-- Label -->
                    <x-input-label
                        for="password"
                        :value="__('Password')"
                        class="text-yellow-400"
                    />

                    <!-- Input password -->
                    <x-text-input
                        id="password"
                        class="block mt-2 w-full bg-[#262626] border-gray-700 text-white focus:border-yellow-500 focus:ring-yellow-500 rounded-lg"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    />

                    <!-- Error password -->
                    <x-input-error
                        :messages="$errors->get('password')"
                        class="mt-2 text-red-400"
                    />

                </div>

                <!-- Remember Me -->
                <div class="block mt-5">

                    <label for="remember_me" class="inline-flex items-center">

                        <input
                            id="remember_me"
                            type="checkbox"
                            class="rounded border-gray-600 bg-[#262626] text-yellow-500 shadow-sm focus:ring-yellow-500"
                            name="remember"
                        >

                        <span class="ms-2 text-sm text-gray-300">
                            {{ __('Remember me') }}
                        </span>

                    </label>

                </div>

                <!-- Tombol -->
                <div class="flex items-center justify-between mt-8">

                    <!-- Forgot password -->
                    @if (Route::has('password.request'))

                        <a
                            class="text-sm text-gray-400 hover:text-yellow-400 transition"
                            href="{{ route('password.request') }}"
                        >
                            Forgot Password?
                        </a>

                    @endif

                    <!-- Button Login -->
                    <button
                        type="submit"
                        class="bg-yellow-500 hover:bg-yellow-400 text-black font-bold px-6 py-3 rounded-lg transition duration-300 shadow-lg"
                    >
                        LOGIN
                    </button>

                </div>

            </form>

            <!-- Register -->
            <div class="mt-8 text-center">

                <p class="text-gray-400 text-sm">
                    Belum punya akun?
                </p>

                <a
                    href="{{ route('register') }}"
                    class="text-yellow-400 hover:text-yellow-300 font-semibold transition"
                >
                    Daftar Sekarang
                </a>

            </div>

        </div>

    </div>

</x-guest-layout>