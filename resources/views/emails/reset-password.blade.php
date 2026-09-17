<x-email-layout title="Reset Password" :message="$message">

    <p style="color:#dddddd; line-height:28px; font-size:16px;">
        Halo <strong>{{ $user->name }}</strong>, kami menerima permintaan untuk mereset password akun kamu di <strong>Ma'bung Barbershop</strong>.
        Tekan tombol di bawah ini untuk membuat password baru.
    </p>

    <x-email-button :href="$url">Reset Password Saya</x-email-button>

    <p style="margin-top:36px; color:#bbbbbb; line-height:26px; font-size:15px;">
        Tautan ini akan kedaluwarsa dalam <strong>60 menit</strong>. Jika kamu tidak meminta reset password, abaikan saja email ini - password kamu tidak akan berubah.
    </p>

    <p style="margin-top:20px; color:#777777; line-height:22px; font-size:13px; word-break:break-all;">
        Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser kamu:<br>
        <a href="{{ $url }}" style="color:#d4af37;">{{ $url }}</a>
    </p>

</x-email-layout>
