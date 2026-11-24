{{-- Menyiapkan pilihan alat dan formulir pengajuan peminjaman baru. --}}
@php
    // Baris awal: pakai input lama jika validasi gagal, atau alat yang dipilih dari katalog
    $rows = array_values(old('items', [['alat_id' => $alatDipilih ?? '', 'jumlah' => 1]]));
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ajukan Peminjaman</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-100 p-3 text-sm text-red-800">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('peminjam.peminjaman.store') }}"
                  class="bg-white shadow sm:rounded-lg p-6 space-y-5">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="tanggal_pinjam" value="Tanggal Pinjam" />
                        <x-text-input id="tanggal_pinjam" name="tanggal_pinjam" type="date" class="mt-1 block w-full"
                                      :value="old('tanggal_pinjam', now()->toDateString())" :min="now()->toDateString()" required />
                    </div>
                    <div>
                        <x-input-label for="tanggal_kembali_rencana" value="Rencana Tanggal Kembali" />
                        <x-text-input id="tanggal_kembali_rencana" name="tanggal_kembali_rencana" type="date" class="mt-1 block w-full"
                                      :value="old('tanggal_kembali_rencana')" :min="now()->toDateString()" required />
                    </div>
                </div>

                {{-- Alpine.js (bawaan Breeze): tambah/hapus baris alat tanpa reload halaman --}}
                <div x-data="{ rows: {{ Js::from($rows) }} }" class="space-y-3">
                    <x-input-label value="Alat yang Dipinjam" />

                    <template x-for="(row, i) in rows" :key="i">
                        <div class="flex items-center gap-3">
                            <select :name="`items[${i}][alat_id]`" x-model="row.alat_id" required
                                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Pilih alat --</option>
                                @foreach ($alat as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama_alat }} (stok {{ $a->stok }})</option>
                                @endforeach
                            </select>

                            <input type="number" min="1" :name="`items[${i}][jumlah]`" x-model.number="row.jumlah" required
                                   class="w-24 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                            <button type="button" @click="rows.splice(i, 1)" x-show="rows.length > 1"
                                    class="text-sm text-red-600 hover:underline">Hapus</button>
                        </div>
                    </template>

                    <button type="button" @click="rows.push({ alat_id: '', jumlah: 1 })"
                            class="text-sm text-indigo-600 hover:underline">+ Tambah alat lain</button>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>Kirim Pengajuan</x-primary-button>
                    <a href="{{ route('peminjam.katalog') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
