{{-- Kartu ringkasan reservasi di email, dipakai lewat <x-email-summary-card :rows="['Label' => 'Nilai']"/>. --}}
@props(['rows'])

<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px; background:#262626; border-radius:14px; overflow:hidden; border:1px solid #333333;">
    @foreach($rows as $label => $value)
        <tr>
            <td style="padding:16px 20px; {{ $loop->last ? '' : 'border-bottom:1px solid #333333;' }}">
                <span style="color:#888888; font-size:13px;">{{ $label }}</span>
                <br>
                <strong style="color:#ffffff; font-size:17px;">{{ $value }}</strong>
            </td>
        </tr>
    @endforeach
</table>
