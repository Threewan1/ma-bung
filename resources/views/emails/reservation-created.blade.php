<x-email-layout title="Reservasi Berhasil Dibuat" :message="$message">

    <p style="color:#dddddd; line-height:28px; font-size:16px;">
        Halo <strong>{{ $reservation->user->name }}</strong>, terima kasih telah melakukan reservasi
        di <strong>Ma'bung Barbershop</strong>. Berikut ringkasan reservasi kamu:
    </p>

    <x-email-summary-card :rows="$ringkasan" />

    <p style="margin-top:24px; color:#f1c40f; line-height:26px; font-size:15px;">
        <strong>Status: Menunggu konfirmasi admin.</strong> Kami akan mengirim email lagi begitu
        reservasi kamu dikonfirmasi.
    </p>

    <x-email-button :href="route('reservasi.index')">Lihat Reservasi Saya</x-email-button>

    <p style="margin-top:36px; color:#bbbbbb; line-height:26px; font-size:15px;">
        Mohon datang tepat waktu sesuai jadwal reservasi. Jika ingin membatalkan, silakan lakukan
        melalui halaman "Reservasi Saya" di website.
    </p>

</x-email-layout>
