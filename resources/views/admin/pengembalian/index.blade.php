{{-- Menampilkan pengajuan dan riwayat pengembalian yang dikelola admin. --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-indigo-600">Administrator / Transaksi</p>
                <h2 class="text-2xl font-bold text-gray-900">Pengembalian</h2>
            </div>
            <a href="{{ route('admin.transaksi.index') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Lihat peminjaman</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <x-flash />
            @if ($errors->any())
                <div class="rounded-lg bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section>
                <h3 class="mb-3 text-lg font-semibold text-gray-900">Menunggu verifikasi</h3>
                <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Peminjam / ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Alat</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Jatuh tempo</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($menunggu as $item)
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
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $item->tanggal_kembali_rencana->format('d/m/Y') }}</td>
                                    <td class="px-4 py-4 text-right text-sm">
                                        <a href="{{ route('admin.pengembalian.create', $item) }}" class="font-semibold text-indigo-600 hover:underline">Catat pengembalian</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada pengembalian yang menunggu verifikasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $menunggu->links() }}</div>
            </section>

            <section>
                <h3 class="mb-3 text-lg font-semibold text-gray-900">Riwayat pengembalian</h3>
                <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Peminjam / ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Alat / kondisi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Denda</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Diproses oleh</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($riwayat as $item)
                                <tr>
                                    <td class="px-4 py-4 text-sm">
                                        <p class="font-medium text-gray-900">{{ $item->peminjaman->user->name }}</p>
                                        <p class="text-gray-500">#{{ $item->peminjaman_id }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        @foreach ($item->peminjaman->detail as $detail)
                                            <div>{{ $detail->alat->nama_alat }} &times; {{ $detail->jumlah }} — {{ $detail->kondisi_kembali ?? '—' }}</div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        {{ $item->tanggal_dikembalikan->format('d/m/Y') }}
                                        <div class="text-xs">Rencana {{ $item->peminjaman->tanggal_kembali_rencana->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        Rp {{ number_format($item->denda, 0, ',', '.') }}
                                        @if ($item->denda_kerusakan > 0)
                                            <div class="text-xs text-gray-500">Kerusakan Rp {{ number_format($item->denda_kerusakan, 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $item->petugas->name }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                                        <a href="{{ route('admin.pengembalian.edit', $item) }}" class="font-medium text-indigo-600 hover:underline">Ubah</a>
                                        <form class="ms-3 inline" method="POST" action="{{ route('admin.pengembalian.destroy', $item) }}" onsubmit="return confirm('Hapus catatan ini? Peminjaman akan kembali menunggu verifikasi dan stok akan disesuaikan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="font-medium text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada riwayat pengembalian.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $riwayat->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
