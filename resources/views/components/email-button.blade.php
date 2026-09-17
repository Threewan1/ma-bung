{{--
    Tombol CTA gold di email - <x-email-button :href="...">Teks</x-email-button>
--}}
@props(['href'])

<div style="margin-top:36px; text-align:center;">
    <a href="{{ $href }}" style="background:#d4af37; color:#111111; text-decoration:none; padding:15px 35px; border-radius:10px; font-weight:bold; display:inline-block; font-size:16px;">
        {{ $slot }}
    </a>
</div>
