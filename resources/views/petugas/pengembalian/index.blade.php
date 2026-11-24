{{-- Memantau pengajuan pengembalian, riwayat, kondisi barang, dan denda. --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pemantauan Pengembalian</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <x-flash />

            @if ($errors->any())
                <div class="rounded-md bg-red-100 p-3 text-sm text-red-800">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            {{-- ===== Sedang dipinjam ===== --}}
            <section>
                <h3 class="mb-2 text-base font-semibold text-gray-800">Menunggu Verifikasi Pengembalian</h3>
                <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alat</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rencana Kembali</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Proses Pengembalian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($dipinjam as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->user->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        @foreach ($item->detail as $d)
                                            <div>{{ $d->alat->nama_alat }} <span class="text-gray-500">&times; {{ $d->jumlah }}</span></div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ $item->tanggal_kembali_rencana->format('d/m/Y') }}
                                        @if ($item->hariTerlambat() > 0)
                                            <div class="text-xs text-red-600 font-semibold">Terlambat {{ $item->hariTerlambat() }} hari</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm"><x-status-badge :status="$item->status" /></td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <form method="POST" action="{{ route('petugas.pengembalian.proses', $item) }}"
                                              class="space-y-3 text-left"
                                              onsubmit="return confirm('Verifikasi dan catat pengembalian alat ini?')">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Dikembalikan</label>
                                                <input type="date" name="tanggal_dikembalikan" value="{{ now()->toDateString() }}"
                                                       min="{{ $item->tanggal_pinjam->toDateString() }}"
                                                       max="{{ now()->toDateString() }}" required
                                                       class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                            </div>
                                            @foreach ($item->detail as $d)
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                                        Kondisi {{ $d->alat->nama_alat }} ({{ $d->jumlah }} unit)
                                                    </label>
                                                    <select name="detail[{{ $d->id }}][kondisi_kembali]" required
                                                            class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                                        <option value="">Pilih kondisi</option>
                                                        <option value="Baik">Baik</option>
                                                        <option value="Rusak Ringan">Rusak Ringan</option>
                                                        <option value="Rusak Berat">Rusak Berat</option>
                                                        <option value="Hilang">Hilang</option>
                                                    </select>
                                                </div>
                                            @endforeach
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Denda Kerusakan/Kehilangan</label>
                                                <input type="number" name="denda_kerusakan" value="0" min="0" step="500"
                                                       class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Catatan Verifikasi</label>
                                                <textarea name="catatan" rows="2" maxlength="2000"
                                                          class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                                                          placeholder="Wajib diisi jika ada denda kerusakan atau kehilangan"></textarea>
                                            </div>
                                            <button class="w-full rounded-md bg-gray-800 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                                                Verifikasi &amp; Proses
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada pengajuan pengembalian yang menunggu verifikasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $dipinjam->links() }}</div>
            </section>

            {{-- ===== Riwayat pengembalian ===== --}}
            <section>
                <h3 class="mb-2 text-base font-semibold text-gray-800">Riwayat Pengembalian</h3>
                <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alat</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rencana</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dikembalikan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Denda</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($riwayat as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->peminjaman->user->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        @foreach ($item->peminjaman->detail as $d)
                                            <div>
                                                {{ $d->alat->nama_alat }}
                                                <span class="text-gray-500">&times; {{ $d->jumlah }}</span>
                                                @if ($d->kondisi_kembali)
                                                    <span class="{{ $d->kondisi_kembali === 'Baik' ? 'text-green-700' : 'text-red-700' }}">
                                                        - {{ $d->kondisi_kembali }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->peminjaman->tanggal_kembali_rencana->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->tanggal_dikembalikan->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="{{ $item->denda > 0 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                            Rp {{ number_format($item->denda, 0, ',', '.') }}
                                        </span>
                                        @if ($item->denda_kerusakan > 0)
                                            <div class="text-xs text-gray-500">
                                                Termasuk kerusakan Rp {{ number_format($item->denda_kerusakan, 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->petugas->name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada riwayat pengembalian.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $riwayat->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
