{{-- Menampilkan daftar pengajuan yang perlu ditinjau dan diputuskan petugas. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengajuan Peminjaman</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alat</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Pinjam</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rencana Kembali</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($pengajuan as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->user->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @foreach ($item->detail as $d)
                                        <div>
                                            {{ $d->alat->nama_alat }} <span class="text-gray-500">&times; {{ $d->jumlah }}</span>
                                            {{-- Peringatan jika stok saat ini tidak lagi cukup --}}
                                            @if ($d->jumlah > $d->alat->stok)
                                                <span class="text-xs text-red-600 font-semibold">(stok tersisa {{ $d->alat->stok }})</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->tanggal_pinjam->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->tanggal_kembali_rencana->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('petugas.pengajuan.setujui', $item) }}" class="inline">
                                        @csrf
                                        <button class="text-green-600 hover:underline">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('petugas.pengajuan.tolak', $item) }}" class="inline ml-3"
                                          onsubmit="return confirm('Tolak pengajuan ini?')">
                                        @csrf
                                        <button class="text-red-600 hover:underline">Tolak</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada pengajuan yang menunggu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $pengajuan->links() }}</div>
        </div>
    </div>
</x-app-layout>
