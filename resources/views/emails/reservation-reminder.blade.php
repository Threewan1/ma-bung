<x-email-layout title="Reservasi Kamu Sebentar Lagi Dimulai" :message="$message">

    <p style="color:#dddddd; line-height:28px; font-size:16px;">
        Halo <strong>{{ $reservation->user->name }}</strong>, ini pengingat bahwa reservasi kamu di
        <strong>Ma'bung Barbershop</strong> akan berlangsung sekitar 1 jam lagi. Berikut ringkasannya:
    </p>

    <x-email-summary-card :rows="$ringkasan" />

    <p style="margin-top:24px; color:#f1c40f; line-height:26px; font-size:15px;">
        <strong>Mohon datang tepat waktu sesuai jadwal di atas.</strong>
    </p>

    <x-email-button :href="route('reservasi.index')">Lihat Reservasi Saya</x-email-button>

    <p style="margin-top:36px; color:#bbbbbb; line-height:26px; font-size:15px;">
        Jika kamu tidak bisa datang, silakan batalkan lewat halaman "Reservasi Saya" agar jadwal
        ini bisa dipakai pelanggan lain.
    </p>

</x-email-layout>
