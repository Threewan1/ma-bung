<x-email-layout title="Verifikasi Alamat Email" :message="$message">

    <p style="color:#dddddd; line-height:28px; font-size:16px;">
        Halo <strong>{{ $user->name }}</strong>, terima kasih telah mendaftar di <strong>Ma'bung Barbershop</strong>.
        Sebelum bisa membuat reservasi, mohon verifikasi dulu alamat email kamu dengan menekan tombol di bawah ini.
    </p>

    <x-email-button :href="$url">Verifikasi Email Saya</x-email-button>

    <p style="margin-top:36px; color:#bbbbbb; line-height:26px; font-size:15px;">
        Jika kamu tidak merasa membuat akun ini, abaikan saja email ini - tidak ada tindakan lebih lanjut yang diperlukan.
    </p>

    <p style="margin-top:20px; color:#777777; line-height:22px; font-size:13px; word-break:break-all;">
        Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser kamu:<br>
        <a href="{{ $url }}" style="color:#d4af37;">{{ $url }}</a>
    </p>

</x-email-layout>
