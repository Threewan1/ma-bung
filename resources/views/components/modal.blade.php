@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
])

@php
$maxWidth = [
    'sm' => 'modal-sm',
    'md' => '',
    'lg' => 'modal-lg',
    'xl' => 'modal-xl',
    '2xl' => 'modal-lg',
][$maxWidth];
@endphp

<div class="modal fade" id="{{ $name }}" tabindex="-1" aria-labelledby="{{ $name }}-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ $maxWidth }}">
        <div class="modal-content">
            {{ $slot }}
        </div>
    </div>
</div>

@if ($show)
    <script>
        {{--
            Dieksekusi langsung (bukan menunggu DOMContentLoaded) supaya
            tetap berfungsi saat halaman ini dimuat lewat navigasi/submit
            AJAX (lihat layouts/navigation.blade.php) - DOMContentLoaded
            cuma terpicu sekali per page-load asli dan tidak akan terpicu
            lagi saat konten disisipkan ulang lewat innerHTML.
        --}}
        (function () {
            var modalEl = document.getElementById('{{ $name }}');
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        })();
    </script>
@endif
