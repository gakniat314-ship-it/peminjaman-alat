{{-- Menyiapkan pilihan dan menampilkan formulir transaksi peminjaman admin. --}}
@php
    $itemsAwal = old('items', $peminjaman->exists
        ? $peminjaman->detail->map(fn ($detail) => ['alat_id' => $detail->alat_id, 'jumlah' => $detail->jumlah])->all()
        : [['alat_id' => '', 'jumlah' => 1]]);
@endphp
<x-app-layout>
    <x-slot name="header">
        <p class="text-sm text-indigo-600">Administrator / Transaksi</p>
        <h2 class="text-2xl font-bold text-gray-900">{{ $peminjaman->exists ? 'Ubah pengajuan pending' : 'Buat pengajuan peminjaman' }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-flash />
            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ $peminjaman->exists ? route('admin.transaksi.update', $peminjaman) : route('admin.transaksi.store') }}"
                  class="space-y-5 rounded-xl border bg-white p-6 shadow-sm">
                @csrf
                @if ($peminjaman->exists) @method('PUT') @endif

                <div>
                    <x-input-label for="user_id" value="Peminjam" />
                    <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                        <option value="">Pilih peminjam</option>
                        @foreach ($peminjam as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id', $peminjaman->user_id) == $user->id)>{{ $user->name }} ({{ $user->username }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div><x-input-label for="tanggal_pinjam" value="Tanggal pinjam" /><x-text-input id="tanggal_pinjam" name="tanggal_pinjam" type="date" class="mt-1 block w-full" :value="old('tanggal_pinjam', $peminjaman->tanggal_pinjam?->toDateString() ?? today()->toDateString())" min="{{ today()->toDateString() }}" required /><x-input-error :messages="$errors->get('tanggal_pinjam')" class="mt-2" /></div>
                    <div><x-input-label for="tanggal_kembali_rencana" value="Rencana tanggal kembali" /><x-text-input id="tanggal_kembali_rencana" name="tanggal_kembali_rencana" type="date" class="mt-1 block w-full" :value="old('tanggal_kembali_rencana', $peminjaman->tanggal_kembali_rencana?->toDateString())" min="{{ today()->toDateString() }}" required /><x-input-error :messages="$errors->get('tanggal_kembali_rencana')" class="mt-2" /></div>
                </div>

                <div x-data="{ rows: {{ Js::from($itemsAwal) }} }" class="space-y-3">
                    <x-input-label value="Daftar alat" />
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="flex items-center gap-3">
                            <select :name="`items[${i}][alat_id]`" x-model="row.alat_id" required class="flex-1 rounded-md border-gray-300">
                                <option value="">Pilih alat</option>
                                @foreach ($alat as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_alat }} — {{ $item->kategori->nama_kategori }} (stok {{ $item->stok }})</option>
                                @endforeach
                            </select>
                            <input type="number" min="1" :name="`items[${i}][jumlah]`" x-model.number="row.jumlah" required class="w-24 rounded-md border-gray-300">
                            <button type="button" @click="rows.splice(i, 1)" x-show="rows.length > 1" class="text-sm text-red-600 hover:underline">Hapus</button>
                        </div>
                    </template>
                    <button type="button" @click="rows.push({ alat_id: '', jumlah: 1 })" class="text-sm font-medium text-indigo-600 hover:underline">+ Tambah alat</button>
                </div>

                <p class="rounded-lg bg-indigo-50 p-3 text-sm text-indigo-800">Pengajuan dibuat berstatus pending. Stok baru berkurang setelah petugas menyetujui.</p>
                <div class="flex gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.transaksi.index') }}" class="rounded-md border px-4 py-2 text-sm">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
