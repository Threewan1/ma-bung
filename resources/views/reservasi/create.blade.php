<x-app-layout>
    <div class="d-flex align-items-center justify-content-center" style="padding-top: 0.5rem; padding-bottom: 1rem;">
        <div class="w-100 card-glass reservasi-form-card rounded-4 px-3 py-3" style="max-width: 400px;">
            <h2 class="fs-4 fw-bold text-gold mb-3">
                Buat Reservasi
            </h2>

            {{-- Pesan Error --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- enctype diperlukan agar form bisa mengirim file/gambar -->
            <!-- data-disable-on-submit: cegah double-submit, lihat script di bawah -->
            <form method="POST" action="/reservasi" enctype="multipart/form-data" data-disable-on-submit>
                @csrf

                {{-- Pilih Layanan --}}
                <div class="mb-2">
                    <label class="form-label">Pilih Layanan</label>
                    <select name="service_id" class="form-select form-select-sm">
                        <option value="">-- Pilih Layanan --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id', request('service_id')) == $service->id ? 'selected' : '' }}>
                                {{ $service->nama_layanan }} - Rp {{ number_format($service->harga, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilih Tanggal --}}
                <div class="mb-2">
                    <label class="form-label">Pilih Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" min="{{ date('Y-m-d') }}" class="form-control form-control-sm" value="{{ old('tanggal') }}">
                </div>

                {{-- Diisi dinamis lewat AJAX setelah tanggal dipilih. --}}
                <div class="mb-2">
                    <label class="form-label">Pilih Jam</label>
                    <select name="jam" id="jam" class="form-select form-select-sm" disabled>
                        <option value="">-- Pilih tanggal terlebih dahulu --</option>
                    </select>
                    <small class="text-body-secondary">Jam yang semua barber-nya sudah terisi tidak bisa dipilih.</small>
                </div>

                {{-- Dimuat via AJAX setelah tanggal & jam dipilih. --}}
                <div class="mb-2">
                    <label class="form-label">Pilih Barber</label>
                    <div class="row g-2" id="barber-list">
                        <div class="col-12">
                            <p class="text-body-secondary small mb-0">-- Pilih tanggal dan jam terlebih dahulu --</p>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('barber_id')" class="mt-2" />
                </div>

                {{-- Catatan --}}
                <div class="mb-3">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="catatan" rows="2" class="form-control form-control-sm" placeholder="Contoh: ingin potongan pendek..."></textarea>
                </div>

                {{-- METODE PEMBAYARAN --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Metode Pembayaran
                    </label>

                    <!-- COD -->
                    <div class="form-check mb-1">

                        <input
                            type="radio"
                            name="payment_method"
                            id="cod"
                            value="cod"
                            checked
                            class="form-check-input">

                        <label class="form-check-label" for="cod">

                            <p class="fw-semibold mb-0">
                                Bayar di Tempat (COD)
                            </p>

                            <small class="text-body-secondary">
                                Pembayaran dilakukan saat datang ke barbershop.
                            </small>

                        </label>

                    </div>

                    <!-- ONLINE -->
                    <div class="form-check">

                        <input
                            type="radio"
                            name="payment_method"
                            id="online"
                            value="online"
                            class="form-check-input">

                        <label class="form-check-label" for="online">

                            <p class="fw-semibold mb-0">
                                Pembayaran Online
                            </p>

                            <small class="text-body-secondary">
                                QRIS, Transfer Bank.
                            </small>

                        </label>

                    </div>

                </div>

                <!-- PEMBAYARAN ONLINE, bagian ini hanya muncul jika user memilih "Pembayaran Online". -->
            <div id="online-payment-section" class="d-none">

                <!-- Judul -->
                <label class="form-label fw-semibold">
                    Pilih Metode Pembayaran Online
                </label>

                {{-- Grid 2 kolom di HP, 3 kolom di layar >=768px, card dibuat ringkas biar section ini tidak memanjangkan halaman. --}}
                <div class="row g-2 mb-2">

                    <!-- QRIS -->
                    <div class="col-6 col-md-4">
                        <label class="payment-channel-card bg-surface" for="channel-qris">
                            <input
                                type="radio"
                                name="payment_channel"
                                id="channel-qris"
                                value="qris"
                                class="form-check-input flex-shrink-0">
                            <span class="payment-channel-name">QRIS</span>
                        </label>
                    </div>

                    <!-- Transfer Bank (BRI) -->
                    <div class="col-6 col-md-4">
                        <label class="payment-channel-card bg-surface" for="channel-bri">
                            <input
                                type="radio"
                                name="payment_channel"
                                id="channel-bri"
                                value="bri"
                                class="form-check-input flex-shrink-0">
                            <span class="payment-channel-name">Transfer Bank</span>
                        </label>
                    </div>

                </div>

                <!-- Informasi pembayaran, akan berubah sesuai metode yang dipilih. -->
                <div id="payment-information" class="bg-surface rounded-3 p-3 mb-2">

                    <p class="text-body-secondary mb-0">

                        Pilih salah satu metode pembayaran di atas.

                    </p>

                </div>

                <!-- Upload Bukti Pembayaran -->
                <div>

                    <label class="form-label">

                        Upload Bukti Pembayaran

                    </label>

                    <input
                        type="file"
                        name="payment_proof"
                        id="payment_proof"
                        accept=".jpg,.jpeg,.png"
                        class="form-control form-control-sm">

                    <small class="text-body-secondary">

                        Upload bukti pembayaran setelah melakukan transfer.

                    </small>

                </div>

            </div>
                {{-- Tombol Submit --}}
                <button type="submit" class="btn btn-primary w-100 py-2 mt-3 fs-5">
                    <i class="fas fa-check"></i> Buat Reservasi
                </button>

                <a href="{{ route('dashboard') }}" class="d-block text-center text-body-secondary mt-2">
                    Kembali ke Dashboard
                </a>
            </form>
        </div>
    </div>

<script>
// Dibungkus IIFE biar aman di-reload lewat navigasi AJAX - tanpa ini, const/let bisa bentrok "already declared".
(function () {

// Ketersediaan jam (AJAX), diisi ulang tiap kali tanggal berubah.
const tanggalInput = document.getElementById('tanggal');
const jamSelect = document.getElementById('jam');
const jamTersediaUrl = '{{ route('reservasi.jam-tersedia') }}';
const jamTerpilihSebelumnya = '{{ old('jam') }}';

function muatJamTersedia(tanggal) {
    if (!tanggal) {
        jamSelect.innerHTML = '<option value="">-- Pilih tanggal terlebih dahulu --</option>';
        jamSelect.disabled = true;
        return;
    }

    jamSelect.disabled = true;
    jamSelect.innerHTML = '<option value="">Memuat jam tersedia...</option>';

    fetch(jamTersediaUrl + '?tanggal=' + encodeURIComponent(tanggal), {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Gagal memuat jam tersedia');
            }
            return response.json();
        })
        .then(function (slots) {
            jamSelect.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = '-- Pilih Jam --';
            jamSelect.appendChild(placeholder);

            slots.forEach(function (slot) {
                const option = document.createElement('option');
                option.value = slot.jam;

                if (slot.sudah_lewat) {
                    option.textContent = slot.jam + ' - Sudah lewat';
                    option.disabled = true;
                } else if (slot.penuh) {
                    option.textContent = slot.jam + ' - Penuh';
                    option.disabled = true;
                } else {
                    option.textContent = slot.jam + ' (' + slot.sisa + ' slot tersisa)';
                }

                jamSelect.appendChild(option);
            });

            // Kembalikan pilihan jam sebelumnya kalau form dikirim ulang gara-gara validasi gagal.
            if (jamTerpilihSebelumnya) {
                jamSelect.value = jamTerpilihSebelumnya;
            }

            jamSelect.disabled = false;
        })
        .catch(function () {
            jamSelect.innerHTML = '<option value="">Gagal memuat jam, coba lagi</option>';
            jamSelect.disabled = true;
        });
}

// Ketersediaan barber (AJAX), muncul setelah tanggal & jam terisi.
const barberListEl = document.getElementById('barber-list');
const barberTersediaUrl = '{{ route('reservasi.barberTersedia') }}';
const barberTerpilihSebelumnya = '{{ old('barber_id') }}';

function pesanBarberList(html) {
    barberListEl.innerHTML = '<div class="col-12">' + html + '</div>';
}

function muatBarberTersedia(tanggal, jam) {
    if (!tanggal || !jam) {
        pesanBarberList('<p class="text-body-secondary small mb-0">-- Pilih tanggal dan jam terlebih dahulu --</p>');
        return;
    }

    pesanBarberList('<p class="text-body-secondary small mb-0">Memuat barber tersedia...</p>');

    fetch(barberTersediaUrl + '?tanggal=' + encodeURIComponent(tanggal) + '&jam=' + encodeURIComponent(jam), {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Gagal memuat barber tersedia');
            }
            return response.json();
        })
        .then(function (barbers) {
            if (!barbers.length) {
                pesanBarberList('<p class="text-danger small mb-0">Tidak ada barber aktif saat ini.</p>');
                return;
            }

            barberListEl.innerHTML = '';

            barbers.forEach(function (barber) {
                const col = document.createElement('div');
                col.className = 'col-4';

                const cardClass = barber.tersedia ? 'barber-choice-card' : 'barber-choice-card barber-choice-card-disabled';
                const checked = (barber.tersedia && String(barber.id) === barberTerpilihSebelumnya) ? 'checked' : '';
                const disabled = barber.tersedia ? '' : 'disabled';
                const statusLabel = barber.tersedia ? '' : '<span class="barber-choice-status">Sedang Bertugas</span>';
                const avatar = barber.foto
                    ? '<img src="' + barber.foto + '" alt="' + barber.nama + '" class="barber-choice-avatar">'
                    : '<span class="barber-choice-icon"><i class="fas fa-user-tie"></i></span>';

                col.innerHTML =
                    '<label class="' + cardClass + '" for="barber-' + barber.id + '">' +
                        '<input type="radio" name="barber_id" id="barber-' + barber.id + '" value="' + barber.id + '" class="form-check-input" ' + disabled + ' ' + checked + '>' +
                        avatar +
                        '<span class="barber-choice-name">' + barber.nama + '</span>' +
                        statusLabel +
                    '</label>';

                barberListEl.appendChild(col);
            });
        })
        .catch(function () {
            pesanBarberList('<p class="text-danger small mb-0">Gagal memuat barber, coba lagi.</p>');
        });
}

tanggalInput.addEventListener('change', function () {
    muatJamTersedia(this.value);
    // Daftar barber ikut direset karena tergantung tanggal+jam.
    muatBarberTersedia('', '');
});

jamSelect.addEventListener('change', function () {
    muatBarberTersedia(tanggalInput.value, this.value);
});

// Muat ulang otomatis kalau tanggal/jam sudah terisi (form dikirim ulang setelah validasi gagal).
if (tanggalInput.value) {
    muatJamTersedia(tanggalInput.value);

    if (jamTerpilihSebelumnya) {
        muatBarberTersedia(tanggalInput.value, jamTerpilihSebelumnya);
    }
}

// Radio metode pembayaran
const onlineRadio = document.getElementById('online');
const codRadio = document.getElementById('cod');

// Bagian pembayaran online
const paymentSection = document.getElementById('online-payment-section');

// Input upload bukti pembayaran
const paymentProofInput = document.getElementById('payment_proof');

// Kotak informasi pembayaran
const paymentInformation = document.getElementById('payment-information');

// Semua pilihan metode pembayaran online
const paymentChannels = document.querySelectorAll('input[name="payment_channel"]');


// Menampilkan / menyembunyikan pembayaran online.
function togglePaymentSection() {

    if (onlineRadio.checked) {

        paymentSection.classList.remove('d-none');
        paymentProofInput.required = true;

    } else {

        paymentSection.classList.add('d-none');
        paymentProofInput.required = false;

    }

}


// Mengubah informasi sesuai metode pembayaran.
function updatePaymentInformation(channel) {

    switch(channel){

        case 'qris':

            paymentInformation.innerHTML = `
                <h3 class="fs-6 fw-bold text-gold mb-2">
                    Pembayaran QRIS
                </h3>

                <img
                    src="{{ asset('images/Qris.jpeg') }}"
                    class="mx-auto d-block rounded-3 mb-2"
                    style="width: 9rem; max-width: 100%;"
                    alt="QRIS">

                <p class="text-center text-body-secondary small mb-0">
                    Scan QRIS menggunakan aplikasi pembayaran apa saja.
                </p>
            `;

        break;


        case 'bri':

            paymentInformation.innerHTML = `
                <h3 class="fs-6 fw-bold text-gold mb-2">
                    Transfer Bank BRI
                </h3>

                <p class="mb-0 small"><strong>No Rekening</strong></p>

                <p class="fs-5 fw-bold mb-2">
                    7913 0101 6186 537
                </p>

                <p class="mb-0 small"><strong>Atas Nama</strong></p>

                <p class="mb-0 small">
                    Muh Rival Hidayat
                </p>
            `;

        break;

    }

}


// Event metode pembayaran utama.
onlineRadio.addEventListener('change', togglePaymentSection);
codRadio.addEventListener('change', togglePaymentSection);


// Event pilihan metode online.
paymentChannels.forEach(channel => {

    channel.addEventListener('change', function(){

        updatePaymentInformation(this.value);

    });

});


// Jalankan saat halaman dibuka.
togglePaymentSection();

// Cegah double-submit: nonaktifkan tombol submit dan ganti teksnya jadi "Memproses...", supaya pelanggan tidak klik dua kali selagi email ReservationCreated dikirim (QUEUE_CONNECTION masih "sync" jadi butuh beberapa detik).
function nonaktifkanTombolSubmit(form, teks) {
    var btn = form.querySelector('button[type="submit"]');
    if (!btn || btn.disabled) {
        return;
    }
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + (teks || 'Memproses...');
}

document.addEventListener('submit', function (e) {
    if (e.target.matches('[data-disable-on-submit]')) {
        nonaktifkanTombolSubmit(e.target);
    }
});

})();
</script>
</x-app-layout>
