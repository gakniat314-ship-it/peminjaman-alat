{{-- Label berwarna untuk status peminjaman. Pakai: <x-status-badge :status="$item->status" /> --}}
@props(['status'])
@php
    $map = [
        'pending'          => ['Pending',               'bg-citron-100 text-citron-900'],
        'disetujui'        => ['Disetujui (Dipinjam)',  'bg-petrol-100 text-petrol-800'],
        'menunggu_kembali' => ['Menunggu Pengembalian', 'bg-clay-100 text-clay-800'],
        'ditolak'          => ['Ditolak',               'bg-red-100 text-red-800'],
        'dikembalikan'     => ['Dikembalikan',          'bg-green-100 text-green-800'],
    ];
    [$label, $warna] = $map[$status] ?? [$status, 'bg-gray-100 text-gray-700'];
@endphp
<span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium {{ $warna }}">{{ $label }}</span>
