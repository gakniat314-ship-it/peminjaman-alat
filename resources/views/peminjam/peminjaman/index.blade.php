{{-- Menampilkan riwayat pengajuan dan status peminjaman milik pengguna. --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Peminjaman Saya</h2>
            <a href="{{ route('peminjam.peminjaman.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                + Ajukan Peminjaman
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alat</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Pinjam</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rencana Kembali</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($peminjaman as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @foreach ($item->detail as $d)
                                        <div>{{ $d->alat->nama_alat }} <span class="text-gray-500">&times; {{ $d->jumlah }}</span></div>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->tanggal_pinjam->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->tanggal_kembali_rencana->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm"><x-status-badge :status="$item->status" /></td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if ($item->pengembalian)
                                        Dikembalikan {{ $item->pengembalian->tanggal_dikembalikan->format('d/m/Y') }}<br>
                                        Denda:
                                        <span class="{{ $item->pengembalian->denda > 0 ? 'text-red-600 font-semibold' : '' }}">
                                            Rp {{ number_format($item->pengembalian->denda, 0, ',', '.') }}
                                        </span>
                                    @elseif (in_array($item->status, ['disetujui', 'menunggu_kembali']) && $item->hariTerlambat() > 0)
                                        <span class="text-red-600 font-semibold">Terlambat {{ $item->hariTerlambat() }} hari</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right">
                                    @if ($item->status === 'disetujui')
                                        <form method="POST" action="{{ route('peminjam.peminjaman.kembali', $item) }}"
                                              onsubmit="return confirm('Ajukan pengembalian alat ini?')">
                                            @csrf
                                            <button class="text-indigo-600 hover:underline">Ajukan Pengembalian</button>
                                        </form>
                                    @elseif ($item->status === 'menunggu_kembali')
                                        <span class="text-amber-700 font-medium">Menunggu verifikasi petugas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada peminjaman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $peminjaman->links() }}</div>
        </div>
    </div>
</x-app-layout>
