{{-- Menampilkan pesan berhasil atau gagal dari flash session. --}}
@if (session('success') || session('error'))
    <div {{ $attributes->merge(['class' => 'mb-5 rounded-lg border px-4 py-3 text-sm ' . (session('success') ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800')]) }} role="status">
        @if (session('success'))
            {{ session('success') }}
        @else
            {{ session('error') }}
        @endif
    </div>
@endif
