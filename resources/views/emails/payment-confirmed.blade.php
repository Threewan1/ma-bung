<x-email-layout title="Pembayaran Kamu Telah Kami Terima" :message="$message">

    <p style="color:#dddddd; line-height:28px; font-size:16px;">
        Halo <strong>{{ $reservation->user->name }}</strong>, kami konfirmasi bahwa pembayaran untuk
        reservasi kamu di <strong>Ma'bung Barbershop</strong> sudah kami terima (Lunas). Berikut
        ringkasannya:
    </p>

    <x-email-summary-card :rows="$ringkasan" />

    <p style="margin-top:24px; color:#8fd694; line-height:26px; font-size:15px;">
        <strong>Status Pembayaran: Lunas.</strong> Terima kasih!
    </p>

    <x-email-button :href="route('reservasi.index')">Lihat Reservasi Saya</x-email-button>

    <p style="margin-top:36px; color:#bbbbbb; line-height:26px; font-size:15px;">
        Kalau ada pertanyaan seputar pembayaran ini, silakan hubungi kami langsung di barbershop.
    </p>

</x-email-layout>
