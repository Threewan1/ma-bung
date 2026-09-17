<x-email-layout title="Reservasi Kamu Telah Dibatalkan" :message="$message">

    <p style="color:#dddddd; line-height:28px; font-size:16px;">
        Halo <strong>{{ $reservation->user->name }}</strong>, reservasi kamu di
        <strong>Ma'bung Barbershop</strong> berikut ini telah dibatalkan:
    </p>

    <x-email-summary-card :rows="$ringkasan" />

    <p style="margin-top:24px; color:#e57373; line-height:26px; font-size:15px;">
        <strong>Status: Dibatalkan.</strong>
    </p>

    <x-email-button :href="route('reservasi.create')">Buat Reservasi Baru</x-email-button>

    <p style="margin-top:36px; color:#bbbbbb; line-height:26px; font-size:15px;">
        Kalau ini bukan permintaan kamu atau kamu punya pertanyaan, silakan hubungi kami langsung
        di barbershop.
    </p>

</x-email-layout>
