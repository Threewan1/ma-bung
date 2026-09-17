{{-- Kerangka bersama semua email transaksional - pakai <table>+inline style karena klien email suka strip <style>/<link>, dan logo di-embed() (cid:) bukan link internet biar tetap tampil walau gambar luar diblokir. --}}
@props(['title', 'message'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $title }}</title>
    <style>
        {{-- Paksa lingkaran putih di sekitar logo tetap putih, sebagian klien email (terutama Gmail app) suka membalik warna near-white ke gelap saat dark mode aktif. --}}
        .email-logo-circle {
            background-color: #ffffff !important;
        }
        @media (prefers-color-scheme: dark) {
            .email-logo-circle {
                background-color: #ffffff !important;
            }
        }
    </style>
</head>

<body style="margin:0; padding:0; background-color:#0f0f0f; font-family:Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="center" style="padding:40px 15px;">

<table width="650" cellpadding="0" cellspacing="0" style="background:#1a1a1a; border-radius:18px; overflow:hidden; box-shadow:0 0 20px rgba(0,0,0,0.4);">

    {{-- HEADER --}}
    <tr>
        <td align="center" style="background:linear-gradient(135deg,#111111,#1f1f1f); padding:36px 20px; border-bottom:3px solid #d4af37;">
            {{-- Sel putih rounded di sekitar logo, biar tetap kontras di atas header gelap. --}}
            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 16px;">
                <tr>
                    <td align="center" valign="middle" bgcolor="#ffffff" class="email-logo-circle" style="width:76px; height:76px; background:#ffffff; border-radius:50%; border:3px solid #d4af37;">
                        <img src="{{ $message->embed(public_path('images/logo.jpeg')) }}" width="60" height="60" alt="Ma'bung Barbershop" style="display:block; width:60px; height:60px; border-radius:50%; object-fit:cover;">
                    </td>
                </tr>
            </table>
            <h1 style="color:#ffffff; margin:0; font-size:26px; letter-spacing:2px;">MA'BUNG BARBERSHOP</h1>
            <p style="color:#d4af37; margin-top:10px; margin-bottom:0; font-size:14px; letter-spacing:1px;">Premium Haircut Experience</p>
        </td>
    </tr>

    {{-- CONTENT --}}
    <tr>
        <td style="padding:45px 40px; color:#ffffff;">
            <h2 style="margin-top:0; margin-bottom:20px; color:#d4af37; font-size:24px;">{{ $title }}</h2>

            {{ $slot }}
        </td>
    </tr>

    {{-- FOOTER --}}
    <tr>
        <td align="center" style="background:#111111; padding:25px; color:#777777; font-size:13px; border-top:1px solid #2a2a2a;">
            &copy; {{ date('Y') }} Ma'bung Barbershop <br>
            Premium Haircut &amp; Grooming Service
        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
