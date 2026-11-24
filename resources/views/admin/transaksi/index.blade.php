{{-- Menampilkan seluruh transaksi peminjaman beserta filter dan aksi admin. --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-indigo-600">Administrator</p>
                <h2 class="text-2xl font-bold text-gray-900">Manajemen Transaksi</h2>
            </div>
            <a href="{{ route('admin.transaksi.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Buat pengajuan</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-flash />
            <form class="mb-4 flex flex-wrap items-end gap-3" method="GET">
                <div>
                    <label for="status" class="mb-1 block text-sm font-medium">Status</label>
                    <select id="status" name="status" class="rounded-lg border-gray-300">
                        <option value="">Semua status</option>
                        @foreach (['pending', 'disetujui', 'menunggu_kembali', 'ditolak', 'dikembalikan'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-lg border px-4 py-2 text-sm">Filter</button>
            </form>

            <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Peminjam / ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Alat</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Jadwal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pengembalian</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($peminjaman as $item)
                            <tr>
                                <td class="px-4 py-4 text-sm">
                                    <p class="font-medium text-gray-900">{{ $item->user->name }}</p>
                                    <p class="text-gray-500">#{{ $item->id }}</p>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    @foreach ($item->detail as $detail)
                                        <div>{{ $detail->alat->nama_alat }} &times; {{ $detail->jumlah }}</div>
                                    @endforeach
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600">
                                    {{ $item->tanggal_pinjam->format('d/m/Y') }}<br>
                                    hingga {{ $item->tanggal_kembali_rencana->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <x-status-badge :status="$item->status" />
                                    @if ($item->petugas)<p class="mt-1 text-xs text-gray-500">Petugas: {{ $item->petugas->name }}</p>@endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    @if ($item->pengembalian)
                                        {{ $item->pengembalian->tanggal_dikembalikan->format('d/m/Y') }}<br>
                                        Denda Rp {{ number_format($item->pengembalian->denda, 0, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                                    @if ($item->status === 'pending')
                                        <a class="font-medium text-indigo-600 hover:underline" href="{{ route('admin.transaksi.edit', $item) }}">Ubah</a>
                                    @endif
                                    <form class="inline" method="POST" action="{{ route('admin.transaksi.destroy', $item) }}" onsubmit="return confirm('Hapus transaksi ini? Stok transaksi aktif akan dikembalikan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ms-3 font-medium text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $peminjaman->links() }}</div>
        </div>
    </div>
</x-app-layout>
