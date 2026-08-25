<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Reservasi - Ma'bung Barbershop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white">

    {{-- Navbar --}}
    <nav class="bg-gray-800 shadow-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-yellow-400">
                <i class="fas fa-cut"></i> Ma'bung Barbershop
            </div>
            <div class="space-x-4">
                <a href="/dashboard" class="text-white hover:text-yellow-400">Dashboard</a>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="text-white hover:text-yellow-400">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Form Reservasi --}}
    <div class="min-h-screen flex items-center justify-center pt-20 pb-10">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-lg">
            <h2 class="text-2xl font-bold text-yellow-400 mb-6">
                <i class="fas fa-calendar-check"></i> Buat Reservasi
            </h2>

            {{-- Pesan Error --}}
            @if($errors->any())
                <div class="bg-red-500 text-white p-3 rounded-lg mb-4">
                    <ul>
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
                <div class="mb-4">
                    <label class="block text-gray-300 mb-2">Pilih Layanan</label>
                    <select name="service_id" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <option value="">-- Pilih Layanan --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">
                                {{ $service->nama_layanan }} - Rp {{ number_format($service->harga, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilih Tanggal --}}
                <div class="mb-4">
                    <label class="block text-gray-300 mb-2">Pilih Tanggal</label>
                    <input type="date" name="tanggal" min="{{ date('Y-m-d') }}"
                        class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>

                {{-- Pilih Jam --}}
                <div class="mb-4">
                    <label class="block text-gray-300 mb-2">Pilih Jam</label>
                    <select name="jam" class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <option value="">-- Pilih Jam --</option>
                        <option value="08:00">08:00</option>
                        <option value="09:00">09:00</option>
                        <option value="10:00">10:00</option>
                        <option value="11:00">11:00</option>
                        <option value="13:00">13:00</option>
                        <option value="14:00">14:00</option>
                        <option value="15:00">15:00</option>
                        <option value="16:00">16:00</option>
                        <option value="17:00">17:00</option>
                    </select>
                </div>

                {{-- Catatan --}}
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Catatan (opsional)</label>
                    <textarea name="catatan" rows="3"
                        class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                        placeholder="Contoh: ingin potongan pendek..."></textarea>
                </div>

                {{-- ==========================================================
                    METODE PEMBAYARAN
                    ========================================================== --}}
                <div class="mb-6">

                    <label class="block text-gray-300 mb-3 font-semibold">
                        Metode Pembayaran
                    </label>

                    <!-- ================= COD ================= -->
                    <label class="flex items-center mb-3">

                        <input
                            type="radio"
                            name="payment_method"
                            id="cod"
                            value="cod"
                            checked
                            class="mr-3">

                        <div>

                            <p class="font-semibold">
                                💵 Bayar di Tempat (COD)
                            </p>

                            <small class="text-gray-400">
                                Pembayaran dilakukan saat datang ke barbershop.
                            </small>

                        </div>

                    </label>

                    <!-- ================= ONLINE ================= -->
                    <label class="flex items-center">

                        <input
                            type="radio"
                            name="payment_method"
                            id="online"
                            value="online"
                            class="mr-3">

                        <div>

                            <p class="font-semibold">
                                🌐 Pembayaran Online
                            </p>

                            <small class="text-gray-400">
                                QRIS, Transfer Bank, DANA, GoPay, ShopeePay.
                            </small>

                        </div>

                    </label>

                </div>

                <!-- ==========================================================
                PEMBAYARAN ONLINE
                Bagian ini hanya muncul jika user memilih
                "Pembayaran Online"
            =========================================================== -->
            <div id="online-payment-section" class="hidden">

                <!-- Judul -->
                <label class="block text-gray-300 mb-3 font-semibold">
                    Pilih Metode Pembayaran Online
                </label>

                <!-- ================= QRIS ================= -->
                <label class="block bg-gray-700 rounded-lg p-4 mb-3 cursor-pointer hover:bg-gray-600">

                    <input
                        type="radio"
                        name="payment_channel"
                        value="qris"
                        class="mr-2">

                    <strong>📱 QRIS</strong>

                    <p class="text-sm text-gray-400 mt-1">
                        Scan QR Code menggunakan aplikasi pembayaran apa saja.
                    </p>

                </label>

                <!-- ================= Transfer Bank ================= -->
                <label class="block bg-gray-700 rounded-lg p-4 mb-3 cursor-pointer hover:bg-gray-600">

                    <input
                        type="radio"
                        name="payment_channel"
                        value="bca"
                        class="mr-2">

                    <strong>🏦 Transfer Bank</strong>

                    <p class="text-sm text-gray-400 mt-1">
                        Transfer ke rekening bank Ma'Bung Barbershop.
                    </p>

                </label>

                <!-- ================= DANA ================= -->
                <label class="block bg-gray-700 rounded-lg p-4 mb-3 cursor-pointer hover:bg-gray-600">

                    <input
                        type="radio"
                        name="payment_channel"
                        value="dana"
                        class="mr-2">

                    <strong>💙 DANA</strong>

                </label>

                <!-- ================= GoPay ================= -->
                <label class="block bg-gray-700 rounded-lg p-4 mb-3 cursor-pointer hover:bg-gray-600">

                    <input
                        type="radio"
                        name="payment_channel"
                        value="gopay"
                        class="mr-2">

                    <strong>🟢 GoPay</strong>

                </label>

                <!-- ================= ShopeePay ================= -->
                <label class="block bg-gray-700 rounded-lg p-4 mb-5 cursor-pointer hover:bg-gray-600">

                    <input
                        type="radio"
                        name="payment_channel"
                        value="shopeepay"
                        class="mr-2">

                    <strong>🛍 ShopeePay</strong>

                </label>

                <!-- ==========================================================
                    Informasi pembayaran
                    Nanti akan berubah sesuai metode yang dipilih
                ========================================================== -->
                <div id="payment-information"
                    class="bg-gray-700 rounded-lg p-5 mb-5">

                    <p class="text-gray-400">

                        Pilih salah satu metode pembayaran di atas.

                    </p>

                </div>

                <!-- ==========================================================
                    Upload Bukti Pembayaran
                ========================================================== -->
                <div>

                    <label class="block text-gray-300 mb-2">

                        Upload Bukti Pembayaran

                    </label>

                    <input
                        type="file"
                        name="payment_proof"
                        accept=".jpg,.jpeg,.png"
                        class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg">

                    <small class="text-gray-400">

                        Upload bukti pembayaran setelah melakukan transfer.

                    </small>

                </div>

            </div>
                {{-- Tombol Submit --}}
                <button type="submit"
                    class="w-full bg-yellow-400 text-gray-900 py-3 rounded-lg font-bold text-lg hover:bg-yellow-500">
                    <i class="fas fa-check"></i> Buat Reservasi
                </button>

                <a href="/dashboard" class="block text-center text-gray-400 mt-4 hover:text-white">
                    Kembali ke Dashboard
                </a>
            </form>
        </div>
    </div>

<script>
// =======================================================
// Mengambil komponen dari halaman
// =======================================================

// Radio metode pembayaran
const onlineRadio = document.getElementById('online');
const codRadio = document.getElementById('cod');

// Bagian pembayaran online
const paymentSection = document.getElementById('online-payment-section');

// Kotak informasi pembayaran
const paymentInformation = document.getElementById('payment-information');

// Semua pilihan metode pembayaran online
const paymentChannels = document.querySelectorAll('input[name="payment_channel"]');


// =======================================================
// Menampilkan / menyembunyikan pembayaran online
// =======================================================
function togglePaymentSection() {

    if (onlineRadio.checked) {

        paymentSection.classList.remove('hidden');

    } else {

        paymentSection.classList.add('hidden');

    }

}


// =======================================================
// Mengubah informasi sesuai metode pembayaran
// =======================================================
function updatePaymentInformation(channel) {

    switch(channel){

        case 'qris':

            paymentInformation.innerHTML = `
                <h3 class="text-lg font-bold text-yellow-400 mb-3">
                    Pembayaran QRIS
                </h3>

                <img
                    src="{{ asset('images/qris.jpg') }}"
                    class="mx-auto w-56 rounded-lg mb-3"
                    alt="QRIS">

                <p class="text-center text-gray-300">
                    Scan QRIS menggunakan aplikasi pembayaran apa saja.
                </p>
            `;

        break;


        case 'bca':

            paymentInformation.innerHTML = `
                <h3 class="text-lg font-bold text-yellow-400 mb-3">
                    Transfer Bank BCA
                </h3>

                <p><strong>No Rekening</strong></p>

                <p class="text-xl font-bold mb-3">
                    1234567890
                </p>

                <p><strong>Atas Nama</strong></p>

                <p>
                    Ma'Bung Barbershop
                </p>
            `;

        break;


        case 'dana':

            paymentInformation.innerHTML = `
                <h3 class="text-lg font-bold text-yellow-400 mb-3">
                    DANA
                </h3>

                <p class="text-xl font-bold">
                    081234567890
                </p>
            `;

        break;


        case 'gopay':

            paymentInformation.innerHTML = `
                <h3 class="text-lg font-bold text-yellow-400 mb-3">
                    GoPay
                </h3>

                <p class="text-xl font-bold">
                    081234567890
                </p>
            `;

        break;


        case 'shopeepay':

            paymentInformation.innerHTML = `
                <h3 class="text-lg font-bold text-yellow-400 mb-3">
                    ShopeePay
                </h3>

                <p class="text-xl font-bold">
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

</script>

</body>
</html>