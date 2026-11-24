{{-- Menampilkan tautan navigasi dengan gaya berbeda untuk halaman aktif. --}}
@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-xl bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-800 ring-1 ring-inset ring-indigo-100'
            : 'inline-flex items-center rounded-xl px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
