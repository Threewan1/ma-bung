<x-guest-layout>

    <!-- Background utama -->
    <div class="min-h-screen flex items-center justify-center bg-[#0f0f0f] px-4 py-10">

        <!-- Card Register -->
        <div class="w-full max-w-md bg-[#1a1a1a] rounded-2xl shadow-2xl p-8 border border-yellow-500">

            <!-- Header -->
            <div class="text-center mb-8">

                <!-- Icon -->
                <div class="flex justify-center mb-4">

                    <div class="w-20 h-20 rounded-full bg-yellow-500 flex items-center justify-center text-black text-3xl font-bold shadow-lg">
                        M
                    </div>

                </div>

                <!-- Judul -->
                <h1 class="text-3xl font-bold text-yellow-400 tracking-wide">
                    MA'BUNG BARBERSHOP
                </h1>

                <p class="text-gray-400 mt-2 text-sm">
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
                        :value="__('Nama Lengkap')"
                        class="text-yellow-400"
                    />

                    <x-text-input
                        id="name"
                        class="block mt-2 w-full bg-[#262626] border-gray-700 text-white focus:border-yellow-500 focus:ring-yellow-500 rounded-lg"
                        type="text"
                        name="name"
                        :value="old('name')"
                        required
                        autofocus
                        autocomplete="name"
                    />

                    <x-input-error
                        :messages="$errors->get('name')"
                        class="mt-2 text-red-400"
                    />

                </div>

                <!-- Email -->
                <div class="mt-5">

                    <x-input-label
                        for="email"
                        :value="__('Email')"
                        class="text-yellow-400"
                    />

                    <x-text-input
                        id="email"
                        class="block mt-2 w-full bg-[#262626] border-gray-700 text-white focus:border-yellow-500 focus:ring-yellow-500 rounded-lg"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autocomplete="username"
                    />

                    <x-input-error
                        :messages="$errors->get('email')"
                        class="mt-2 text-red-400"
                    />

                </div>

                <!-- Password -->
                <div class="mt-5">

                    <x-input-label
                        for="password"
                        :value="__('Password')"
                        class="text-yellow-400"
                    />

                    <x-text-input
                        id="password"
                        class="block mt-2 w-full bg-[#262626] border-gray-700 text-white focus:border-yellow-500 focus:ring-yellow-500 rounded-lg"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                    />

                    <x-input-error
                        :messages="$errors->get('password')"
                        class="mt-2 text-red-400"
                    />

                </div>

                <!-- Konfirmasi Password -->
                <div class="mt-5">

                    <x-input-label
                        for="password_confirmation"
                        :value="__('Konfirmasi Password')"
                        class="text-yellow-400"
                    />

                    <x-text-input
                        id="password_confirmation"
                        class="block mt-2 w-full bg-[#262626] border-gray-700 text-white focus:border-yellow-500 focus:ring-yellow-500 rounded-lg"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                    />

                    <x-input-error
                        :messages="$errors->get('password_confirmation')"
                        class="mt-2 text-red-400"
                    />

                </div>

                <!-- Tombol -->
                <div class="flex items-center justify-between mt-8">

                    <!-- Login -->
                    <a
                        class="text-sm text-gray-400 hover:text-yellow-400 transition"
                        href="{{ route('login') }}"
                    >
                        Sudah punya akun?
                    </a>

                    <!-- Button Register -->
                    <button
                        type="submit"
                        class="bg-yellow-500 hover:bg-yellow-400 text-black font-bold px-6 py-3 rounded-lg transition duration-300 shadow-lg"
                    >
                        REGISTER
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-guest-layout>