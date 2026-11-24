{{-- Menampilkan tautan navigasi yang menyesuaikan tampilan perangkat seluler. --}}
@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-xl bg-indigo-50 px-4 py-3 text-start text-sm font-semibold text-indigo-800 ring-1 ring-inset ring-indigo-100'
            : 'block w-full rounded-xl px-4 py-3 text-start text-sm font-medium text-gray-600 hover:bg-white hover:text-gray-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
