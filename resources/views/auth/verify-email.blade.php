<x-guest-layout>
    <div class="min-vh-100 d-flex align-items-center justify-content-center px-3">
        <div class="w-100 bg-panel rounded-4 shadow-lg p-4 p-md-5 border border-gold" style="max-width: 32rem;">

            <div class="mb-4 small text-body-secondary">
                Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi alamat email Anda dengan mengklik link yang baru saja kami kirimkan. Jika Anda tidak menerima email tersebut, kami akan dengan senang hati mengirimkan yang baru.
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success py-2 small">
                    Link verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
                </div>
            @endif

            <div class="mt-4 d-flex align-items-center justify-content-between">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <x-primary-button>
                        Kirim Ulang Email Verifikasi
                    </x-primary-button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="btn btn-link text-body-secondary small p-0">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
