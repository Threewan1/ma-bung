<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reservasi Berhasil</title>
</head>

<body style="
    margin:0;
    padding:0;
    background-color:#0f0f0f;
    font-family:Arial, sans-serif;
">

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="center" style="padding:40px 15px;">

<table width="650" cellpadding="0" cellspacing="0"
style="
    background:#1a1a1a;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 0 20px rgba(0,0,0,0.4);
">

    <!-- HEADER -->
    <tr>
        <td align="center"
        style="
            background:linear-gradient(135deg,#111111,#1f1f1f);
            padding:40px 20px;
            border-bottom:3px solid #d4af37;
        ">

            <!-- LOGO -->
            <img src="http://127.0.0.1:8000/images/logobarber.jpeg"
                width="110"
                style="
                    display:block;
                    margin-bottom:20px;
                    border-radius:50%;
                    border:3px solid #d4af37;
                ">

            <h1 style="
                color:#ffffff;
                margin:0;
                font-size:34px;
                letter-spacing:2px;
            ">
                MA'BUNG BARBERSHOP
            </h1>

            <p style="
                color:#d4af37;
                margin-top:12px;
                font-size:15px;
                letter-spacing:1px;
            ">
                Premium Haircut Experience
            </p>

        </td>
    </tr>

    <!-- CONTENT -->
    <tr>
        <td style="padding:45px 40px; color:#ffffff;">

            <h2 style="
                margin-top:0;
                color:#d4af37;
                font-size:28px;
            ">
                Reservasi Berhasil ✂️
            </h2>

            <p style="
                color:#dddddd;
                line-height:28px;
                font-size:16px;
            ">
                Halo <strong>{{ Auth::user()->name }}</strong>,
                terima kasih telah melakukan reservasi di
                <strong>Ma'bung Barbershop</strong>.
            </p>

            <!-- CARD -->
            <table width="100%" cellpadding="0" cellspacing="0"
            style="
                margin-top:30px;
                background:#262626;
                border-radius:14px;
                overflow:hidden;
                border:1px solid #333333;
            ">

                <tr>
                    <td style="
                        padding:20px;
                        border-bottom:1px solid #333333;
                    ">
                        <span style="color:#888888;">Tanggal Reservasi</span>
                        <br>
                        <strong style="
                            color:#ffffff;
                            font-size:18px;
                        ">
                            {{ $reservation->tanggal }}
                        </strong>
                    </td>
                </tr>

                <tr>
                    <td style="
                        padding:20px;
                        border-bottom:1px solid #333333;
                    ">
                        <span style="color:#888888;">Jam Reservasi</span>
                        <br>
                        <strong style="
                            color:#ffffff;
                            font-size:18px;
                        ">
                            {{ $reservation->jam }}
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td style="
                        padding:20px;
                        border-bottom:1px solid #333333;
                    ">
                        <span style="color:#888888;">Layanan</span>
                        <br>
                        <strong style="
                            color:#ffffff;
                            font-size:18px;
                        ">
                            {{ $reservation->service->nama_layanan }}
                        </strong>
                    </td>
                </tr>

            </table>

            <!-- BUTTON -->
            <div style="margin-top:40px; text-align:center;">

                <a href="{{ url('/reservasi') }}"
                style="
                    background:#d4af37;
                    color:#111111;
                    text-decoration:none;
                    padding:15px 35px;
                    border-radius:10px;
                    font-weight:bold;
                    display:inline-block;
                    font-size:16px;
                ">
                    Lihat Reservasi
                </a>

            </div>

            <!-- NOTES -->
            <p style="
                margin-top:40px;
                color:#bbbbbb;
                line-height:28px;
                font-size:15px;
            ">
                Mohon datang tepat waktu sesuai jadwal reservasi.
                Jika ingin membatalkan reservasi,
                silakan lakukan melalui website.
            </p>

        </td>
    </tr>

    <!-- FOOTER -->
    <tr>
        <td align="center"
        style="
            background:#111111;
            padding:25px;
            color:#777777;
            font-size:13px;
            border-top:1px solid #2a2a2a;
        ">

            © {{ date('Y') }} Ma'bung Barbershop <br>
            Premium Haircut & Grooming Service

        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>