{{-- Form pencatatan atau pengubahan pengembalian alat. --}}
<x-app-layout>
    <x-slot name="header">
        <p class="text-sm text-indigo-600">Administrator / Transaksi / Pengembalian</p>
        <h2 class="text-2xl font-bold text-gray-900">{{ $pengembalian ? 'Ubah pengembalian' : 'Catat pengembalian' }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <x-flash />
            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ $pengembalian ? route('admin.pengembalian.update', $pengembalian) : route('admin.pengembalian.store') }}" class="space-y-5 rounded-xl border bg-white p-6 shadow-sm">
                @csrf
                @if ($pengembalian)
                    @method('PUT')
                @else
                    <input type="hidden" name="peminjaman_id" value="{{ $peminjaman->id }}">
                @endif

                <div class="rounded-lg bg-gray-50 p-4 text-sm">
                    <p class="font-semibold text-gray-900">{{ $peminjaman->user->name }} — Peminjaman #{{ $peminjaman->id }}</p>
                    <p class="mt-1 text-gray-600">Tanggal pinjam {{ $peminjaman->tanggal_pinjam->format('d/m/Y') }}; rencana kembali {{ $peminjaman->tanggal_kembali_rencana->format('d/m/Y') }}.</p>
                </div>

                <div>
                    <x-input-label for="tanggal_dikembalikan" value="Tanggal dikembalikan" />
                    <x-text-input id="tanggal_dikembalikan" name="tanggal_dikembalikan" type="date" class="mt-1 block w-full" :value="old('tanggal_dikembalikan', $pengembalian?->tanggal_dikembalikan?->toDateString() ?? today()->toDateString())" min="{{ $peminjaman->tanggal_pinjam->toDateString() }}" max="{{ today()->toDateString() }}" required />
                </div>

                @foreach ($peminjaman->detail as $detail)
                    <div>
                        <x-input-label for="detail_{{ $detail->id }}" :value="$detail->alat->nama_alat.' ('.$detail->jumlah.' unit)'" />
                        <select id="detail_{{ $detail->id }}" name="detail[{{ $detail->id }}][kondisi_kembali]" class="mt-1 block w-full rounded-md border-gray-300" required>
                            <option value="">Pilih kondisi</option>
                            @foreach (['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'] as $kondisi)
                                <option value="{{ $kondisi }}" @selected(old("detail.{$detail->id}.kondisi_kembali", $detail->kondisi_kembali) === $kondisi)>{{ $kondisi }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

                <div>
                    <x-input-label for="denda_kerusakan" value="Denda kerusakan/kehilangan (Rp)" />
                    <x-text-input id="denda_kerusakan" name="denda_kerusakan" type="number" min="0" step="500" class="mt-1 block w-full" :value="old('denda_kerusakan', $pengembalian?->denda_kerusakan ?? 0)" />
                </div>
                <div>
                    <x-input-label for="catatan" value="Catatan verifikasi" />
                    <textarea id="catatan" name="catatan" rows="3" maxlength="2000" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Jelaskan jika ada denda kerusakan atau kehilangan">{{ old('catatan', $pengembalian?->catatan) }}</textarea>
                </div>

                <div class="flex gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.pengembalian.index') }}" class="rounded-md border px-4 py-2 text-sm">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
