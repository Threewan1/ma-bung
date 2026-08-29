<x-app-layout>
    <div class="container profile-page-container pt-3 pb-3">

        {{-- Avatar & identitas - dipadatkan (avatar diperkecil, padding
             vertikal sekitar header dikurangi) supaya tidak memakan
             banyak ruang vertikal di bagian atas halaman. --}}
        <div class="text-center mb-2">
            <form id="avatarUploadForm" method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="d-inline-block">
                @csrf
                <label for="avatarInput" class="profile-avatar-upload d-inline-block position-relative mb-1" title="Ubah foto profil">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center fw-bold shadow overflow-hidden" style="width: 3.25rem; height: 3.25rem; font-size: 1.1rem;">
                        <img
                            src="{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}"
                            alt="Foto profil"
                            id="avatarPreviewImg"
                            class="w-100 h-100 object-fit-cover {{ $user->avatar ? '' : 'd-none' }}"
                        >
                        <span id="avatarInitial" class="{{ $user->avatar ? 'd-none' : '' }}">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <span class="profile-avatar-edit-badge profile-avatar-edit-badge-sm">
                        <i class="bi bi-camera-fill"></i>
                    </span>
                    <input type="file" name="avatar" id="avatarInput" accept="image/png,image/jpeg" class="d-none">
                </label>
                <x-input-error class="mt-1" :messages="$errors->get('avatar')" />
            </form>
            <h2 class="fs-6 fw-bold mb-0">{{ $user->name }}</h2>
            <p class="text-body-secondary small mb-0">{{ $user->email }}</p>
            <p class="text-body-secondary small mb-0">
                Bergabung sejak {{ $user->created_at->translatedFormat('F Y') }}
            </p>
        </div>

        {{--
            3 form (Informasi Profil, Ubah Kata Sandi, Hapus Akun)
            sebelumnya ditumpuk sebagai 3 card vertikal terpisah -
            sekarang jadi TAB Bootstrap native (data-bs-toggle="tab"),
            supaya cuma 1 form yang terlihat sekaligus dan total tinggi
            halaman jauh berkurang. "Informasi Profil" aktif secara
            default, kecuali ada validation error / status sukses yang
            relevan ke tab lain (mis. gagal ganti password) - supaya
            pesan tersebut tetap langsung terlihat tanpa pindah tab
            manual, termasuk setelah redirect via AJAX nav.
        --}}
        @php
            $profileActiveTab = 'info';
            if ($errors->updatePassword->isNotEmpty() || session('status') === 'password-updated') {
                $profileActiveTab = 'password';
            } elseif ($errors->userDeletion->isNotEmpty()) {
                $profileActiveTab = 'delete';
            }
        @endphp

        <div class="profile-forms-stack mx-auto">
            <ul class="nav nav-tabs profile-nav-tabs" id="profileTab" role="tablist">
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button
                        class="nav-link w-100 {{ $profileActiveTab === 'info' ? 'active' : '' }}"
                        id="profile-info-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#profile-info-pane"
                        type="button"
                        role="tab"
                        aria-controls="profile-info-pane"
                        aria-selected="{{ $profileActiveTab === 'info' ? 'true' : 'false' }}"
                    >
                        Informasi Profil
                    </button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button
                        class="nav-link w-100 {{ $profileActiveTab === 'password' ? 'active' : '' }}"
                        id="profile-password-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#profile-password-pane"
                        type="button"
                        role="tab"
                        aria-controls="profile-password-pane"
                        aria-selected="{{ $profileActiveTab === 'password' ? 'true' : 'false' }}"
                    >
                        Ubah Kata Sandi
                    </button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button
                        class="nav-link w-100 text-danger {{ $profileActiveTab === 'delete' ? 'active' : '' }}"
                        id="profile-delete-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#profile-delete-pane"
                        type="button"
                        role="tab"
                        aria-controls="profile-delete-pane"
                        aria-selected="{{ $profileActiveTab === 'delete' ? 'true' : 'false' }}"
                    >
                        Hapus Akun
                    </button>
                </li>
            </ul>

            <div class="tab-content bg-panel card-bordered-gold rounded-bottom-3 shadow-sm p-4 profile-tab-content" id="profileTabContent">
                <div class="tab-pane fade {{ $profileActiveTab === 'info' ? 'show active' : '' }}" id="profile-info-pane" role="tabpanel" aria-labelledby="profile-info-tab" tabindex="0">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="tab-pane fade {{ $profileActiveTab === 'password' ? 'show active' : '' }}" id="profile-password-pane" role="tabpanel" aria-labelledby="profile-password-tab" tabindex="0">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="tab-pane fade {{ $profileActiveTab === 'delete' ? 'show active' : '' }}" id="profile-delete-pane" role="tabpanel" aria-labelledby="profile-delete-tab" tabindex="0">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    {{--
        Dibungkus IIFE dan dijalankan langsung (bukan menunggu
        DOMContentLoaded) supaya tetap jalan saat halaman ini
        disisipkan ulang lewat navigasi AJAX di
        layouts/navigation.blade.php (event DOMContentLoaded cuma
        terjadi sekali per page-load asli).
    --}}
    <script>
        (function () {
            var avatarInput = document.getElementById('avatarInput');
            var avatarForm = document.getElementById('avatarUploadForm');
            var previewImg = document.getElementById('avatarPreviewImg');
            var initialSpan = document.getElementById('avatarInitial');

            if (!avatarInput || !avatarForm || !previewImg) {
                return;
            }

            avatarInput.addEventListener('change', function () {
                var file = avatarInput.files && avatarInput.files[0];
                if (!file) {
                    return;
                }

                // Preview instan sebelum form selesai terkirim, supaya
                // pelanggan langsung lihat hasilnya tanpa menunggu
                // round-trip ke server.
                var reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('d-none');
                    if (initialSpan) {
                        initialSpan.classList.add('d-none');
                    }
                };
                reader.readAsDataURL(file);

                // Form ini ditangkap oleh listener submit AJAX global
                // di layouts/navigation.blade.php seperti form lain.
                if (avatarForm.requestSubmit) {
                    avatarForm.requestSubmit();
                } else {
                    avatarForm.submit();
                }
            });
        })();
    </script>
</x-app-layout>
