<x-email-layout title="Reservasi Kamu Telah Dikonfirmasi!" :message="$message">

    <p style="color:#dddddd; line-height:28px; font-size:16px;">
        Halo <strong>{{ $reservation->user->name }}</strong>, kabar baik! Reservasi kamu di
        <strong>Ma'bung Barbershop</strong> sudah dikonfirmasi oleh admin. Berikut ringkasannya:
    </p>

    <x-email-summary-card :rows="$ringkasan" />

    <p style="margin-top:24px; color:#8fd694; line-height:26px; font-size:15px;">
        <strong>Status: Dikonfirmasi.</strong> Sampai jumpa di jadwal kamu!
    </p>

    <x-email-button :href="route('reservasi.index')">Lihat Reservasi Saya</x-email-button>

    <p style="margin-top:36px; color:#bbbbbb; line-height:26px; font-size:15px;">
        Mohon datang tepat waktu sesuai jadwal reservasi. Jika ada perubahan rencana, silakan
        batalkan lewat halaman "Reservasi Saya" agar jadwal ini bisa dipakai pelanggan lain.
    </p>

</x-email-layout>
