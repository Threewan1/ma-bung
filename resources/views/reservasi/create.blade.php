<x-app-layout>
    <div class="d-flex align-items-center justify-content-center" style="padding-top: 1rem; padding-bottom: 2.5rem;">
        <div class="bg-panel p-4 p-md-5 rounded-3 shadow w-100" style="max-width: 32rem;">
            <h2 class="fs-3 fw-bold text-gold mb-4">
                <i class="fas fa-calendar-check"></i> Buat Reservasi
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
            <form method="POST" action="/reservasi" enctype="multipart/form-data">
                @csrf

                {{-- Pilih Layanan --}}
                <div class="mb-3">
                    <label class="form-label">Pilih Layanan</label>
                    <select name="service_id" class="form-select">
                        <option value="">-- Pilih Layanan --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id', request('service_id')) == $service->id ? 'selected' : '' }}>
                                {{ $service->nama_layanan }} - Rp {{ number_format($service->harga, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilih Tanggal --}}
                <div class="mb-3">
                    <label class="form-label">Pilih Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" min="{{ date('Y-m-d') }}" class="form-control" value="{{ old('tanggal') }}">
                </div>

                {{-- Pilih Jam - diisi dinamis lewat AJAX setelah tanggal
                     dipilih, supaya jam yang semua barber-nya sudah
                     terisi langsung tampil disabled. --}}
                <div class="mb-3">
                    <label class="form-label">Pilih Jam</label>
                    <select name="jam" id="jam" class="form-select" disabled>
                        <option value="">-- Pilih tanggal terlebih dahulu --</option>
                    </select>
                    <small class="text-body-secondary">Jam yang semua barber-nya sudah terisi tidak bisa dipilih.</small>
                </div>

                {{-- Pilih Barber - dimuat via AJAX setelah tanggal DAN
                     jam dipilih, barber yang sudah ada reservasi lain
                     di jam yang sama tampil disabled "Sedang Bertugas". --}}
                <div class="mb-3">
                    <label class="form-label">Pilih Barber</label>
                    <div class="row g-2" id="barber-list">
                        <div class="col-12">
                            <p class="text-body-secondary small mb-0">-- Pilih tanggal dan jam terlebih dahulu --</p>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('barber_id')" class="mt-2" />
                </div>

                {{-- Catatan --}}
                <div class="mb-4">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="catatan" rows="3" class="form-control" placeholder="Contoh: ingin potongan pendek..."></textarea>
                </div>

                {{-- ==========================================================
                    METODE PEMBAYARAN
                    ========================================================== --}}
                <div class="mb-4">

                    <label class="form-label fw-semibold">
                        Metode Pembayaran
                    </label>

                    <!-- ================= COD ================= -->
                    <div class="form-check mb-2">

                        <input
                            type="radio"
                            name="payment_method"
                            id="cod"
                            value="cod"
                            checked
                            class="form-check-input">

                        <label class="form-check-label" for="cod">

                            <p class="fw-semibold mb-0">
                                💵 Bayar di Tempat (COD)
                            </p>

                            <small class="text-body-secondary">
                                Pembayaran dilakukan saat datang ke barbershop.
                            </small>

                        </label>

                    </div>

                    <!-- ================= ONLINE ================= -->
                    <div class="form-check">

                        <input
                            type="radio"
                            name="payment_method"
                            id="online"
                            value="online"
                            class="form-check-input">

                        <label class="form-check-label" for="online">

                            <p class="fw-semibold mb-0">
                                🌐 Pembayaran Online
                            </p>

                            <small class="text-body-secondary">
                                QRIS, Transfer Bank, DANA, GoPay, ShopeePay.
                            </small>

                        </label>

                    </div>

                </div>

                <!-- ==========================================================
                PEMBAYARAN ONLINE
                Bagian ini hanya muncul jika user memilih
                "Pembayaran Online"
            =========================================================== -->
            <div id="online-payment-section" class="d-none">

                <!-- Judul -->
                <label class="form-label fw-semibold">
                    Pilih Metode Pembayaran Online
                </label>

                {{-- Grid 2 kolom di HP, 3 kolom di layar >=768px (bukan
                     ditumpuk 1 per baris) - card dibuat ringkas (padding
                     kecil, ikon diperkecil, tanpa teks deskripsi) supaya
                     section ini tidak memanjangkan halaman. --}}
                <div class="row g-2 mb-3">

                    <!-- ================= QRIS ================= -->
                    <div class="col-6 col-md-4">
                        <label class="payment-channel-card bg-surface" for="channel-qris">
                            <input
                                type="radio"
                                name="payment_channel"
                                id="channel-qris"
                                value="qris"
                                class="form-check-input flex-shrink-0">
                            <span class="payment-channel-icon">📱</span>
                            <span class="payment-channel-name">QRIS</span>
                        </label>
                    </div>

                    <!-- ================= Transfer Bank ================= -->
                    <div class="col-6 col-md-4">
                        <label class="payment-channel-card bg-surface" for="channel-bca">
                            <input
                                type="radio"
                                name="payment_channel"
                                id="channel-bca"
                                value="bca"
                                class="form-check-input flex-shrink-0">
                            <span class="payment-channel-icon">🏦</span>
                            <span class="payment-channel-name">Transfer Bank</span>
                        </label>
                    </div>

                    <!-- ================= DANA ================= -->
                    <div class="col-6 col-md-4">
                        <label class="payment-channel-card bg-surface" for="channel-dana">
                            <input
                                type="radio"
                                name="payment_channel"
                                id="channel-dana"
                                value="dana"
                                class="form-check-input flex-shrink-0">
                            <span class="payment-channel-icon">💙</span>
                            <span class="payment-channel-name">DANA</span>
                        </label>
                    </div>

                    <!-- ================= GoPay ================= -->
                    <div class="col-6 col-md-4">
                        <label class="payment-channel-card bg-surface" for="channel-gopay">
                            <input
                                type="radio"
                                name="payment_channel"
                                id="channel-gopay"
                                value="gopay"
                                class="form-check-input flex-shrink-0">
                            <span class="payment-channel-icon">🟢</span>
                            <span class="payment-channel-name">GoPay</span>
                        </label>
                    </div>

                    <!-- ================= ShopeePay ================= -->
                    <div class="col-6 col-md-4">
                        <label class="payment-channel-card bg-surface" for="channel-shopeepay">
                            <input
                                type="radio"
                                name="payment_channel"
                                id="channel-shopeepay"
                                value="shopeepay"
                                class="form-check-input flex-shrink-0">
                            <span class="payment-channel-icon">🛍</span>
                            <span class="payment-channel-name">ShopeePay</span>
                        </label>
                    </div>

                </div>

                <!-- ==========================================================
                    Informasi pembayaran
                    Nanti akan berubah sesuai metode yang dipilih
                ========================================================== -->
                <div id="payment-information" class="bg-surface rounded-3 p-4 mb-3">

                    <p class="text-body-secondary mb-0">

                        Pilih salah satu metode pembayaran di atas.

                    </p>

                </div>

                <!-- ==========================================================
                    Upload Bukti Pembayaran
                ========================================================== -->
                <div>

                    <label class="form-label">

                        Upload Bukti Pembayaran

                    </label>

                    <input
                        type="file"
                        name="payment_proof"
                        id="payment_proof"
                        accept=".jpg,.jpeg,.png"
                        class="form-control">

                    <small class="text-body-secondary">

                        Upload bukti pembayaran setelah melakukan transfer.

                    </small>

                </div>

            </div>
                {{-- Tombol Submit --}}
                <button type="submit" class="btn btn-primary w-100 py-2 mt-4 fs-5">
                    <i class="fas fa-check"></i> Buat Reservasi
                </button>

                <a href="{{ route('dashboard') }}" class="d-block text-center text-body-secondary mt-3">
                    Kembali ke Dashboard
                </a>
            </form>
        </div>
    </div>

<script>
// =======================================================
// Dibungkus IIFE (function-scope sendiri) supaya aman
// dijalankan berkali-kali - halaman ini bisa dimuat ulang
// via navigasi AJAX (lihat layouts/navigation.blade.php),
// dan tanpa IIFE, deklarasi const/let di sini akan bentrok
// ("already been declared") kalau script-nya disisipkan
// lebih dari sekali ke dalam dokumen yang sama.
// =======================================================
(function () {

// =======================================================
// Ketersediaan jam (AJAX) - diisi ulang tiap kali tanggal berubah
// =======================================================
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

                if (slot.penuh) {
                    option.textContent = slot.jam + ' - Penuh';
                    option.disabled = true;
                } else {
                    option.textContent = slot.jam + ' (' + slot.sisa + ' slot tersisa)';
                }

                jamSelect.appendChild(option);
            });

            // Kembalikan pilihan jam sebelumnya kalau form baru saja
            // dikirim ulang karena validasi gagal (mis. jam ternyata
            // sudah penuh), supaya pelanggan lihat kenapa jam itu
            // tertolak (opsinya akan tampil "Penuh").
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

// =======================================================
// Ketersediaan barber (AJAX) - diisi ulang tiap kali tanggal
// ATAU jam berubah (baru muncul setelah keduanya terisi).
// =======================================================
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

                col.innerHTML =
                    '<label class="' + cardClass + '" for="barber-' + barber.id + '">' +
                        '<input type="radio" name="barber_id" id="barber-' + barber.id + '" value="' + barber.id + '" class="form-check-input" ' + disabled + ' ' + checked + '>' +
                        '<span class="barber-choice-icon"><i class="fas fa-user-tie"></i></span>' +
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
    // Jam ikut berubah/reset saat tanggal ganti, jadi daftar barber
    // (yang tergantung tanggal+jam) juga direset sampai jam dipilih ulang.
    muatBarberTersedia('', '');
});

jamSelect.addEventListener('change', function () {
    muatBarberTersedia(tanggalInput.value, this.value);
});

// Muat ulang otomatis kalau tanggal (dan jam) sudah terisi saat halaman
// dibuka (mis. form dikirim ulang setelah validasi gagal dan
// old('tanggal')/old('jam') masih ada).
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


// =======================================================
// Menampilkan / menyembunyikan pembayaran online
// =======================================================
function togglePaymentSection() {

    if (onlineRadio.checked) {

        paymentSection.classList.remove('d-none');
        paymentProofInput.required = true;

    } else {

        paymentSection.classList.add('d-none');
        paymentProofInput.required = false;

    }

}


// =======================================================
// Mengubah informasi sesuai metode pembayaran
// =======================================================
function updatePaymentInformation(channel) {

    switch(channel){

        case 'qris':

            paymentInformation.innerHTML = `
                <h3 class="fs-5 fw-bold text-gold mb-3">
                    Pembayaran QRIS
                </h3>

                <img
                    src="{{ asset('images/qris.jpg') }}"
                    class="mx-auto d-block rounded-3 mb-3"
                    style="width: 14rem; max-width: 100%;"
                    alt="QRIS">

                <p class="text-center text-body-secondary mb-0">
                    Scan QRIS menggunakan aplikasi pembayaran apa saja.
                </p>
            `;

        break;


        case 'bca':

            paymentInformation.innerHTML = `
                <h3 class="fs-5 fw-bold text-gold mb-3">
                    Transfer Bank BCA
                </h3>

                <p class="mb-0"><strong>No Rekening</strong></p>

                <p class="fs-4 fw-bold mb-3">
                    1234567890
                </p>

                <p class="mb-0"><strong>Atas Nama</strong></p>

                <p class="mb-0">
                    Ma'Bung Barbershop
                </p>
            `;

        break;


        case 'dana':

            paymentInformation.innerHTML = `
                <h3 class="fs-5 fw-bold text-gold mb-3">
                    DANA
                </h3>

                <p class="fs-4 fw-bold mb-0">
                    081234567890
                </p>
            `;

        break;


        case 'gopay':

            paymentInformation.innerHTML = `
                <h3 class="fs-5 fw-bold text-gold mb-3">
                    GoPay
                </h3>

                <p class="fs-4 fw-bold mb-0">
                    081234567890
                </p>
            `;

        break;


        case 'shopeepay':

            paymentInformation.innerHTML = `
                <h3 class="fs-5 fw-bold text-gold mb-3">
                    ShopeePay
                </h3>

                <p class="fs-4 fw-bold mb-0">
                    081234567890
                </p>
            `;

        break;

    }

}


// =======================================================
// Event metode pembayaran utama
// =======================================================
onlineRadio.addEventListener('change', togglePaymentSection);
codRadio.addEventListener('change', togglePaymentSection);


// =======================================================
// Event pilihan metode online
// =======================================================
paymentChannels.forEach(channel => {

    channel.addEventListener('change', function(){

        updatePaymentInformation(this.value);

    });

});


// =======================================================
// Jalankan saat halaman dibuka
// =======================================================
togglePaymentSection();

})();
</script>
</x-app-layout>
