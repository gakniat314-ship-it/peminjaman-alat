{{-- Menyediakan formulir untuk menambah atau mengubah data alat. --}}
<x-app-layout>
    <x-slot name="header"><p class="text-sm text-indigo-600">Administrator / Data Alat</p><h2 class="text-2xl font-bold text-gray-900">{{ $alat->exists ? 'Ubah alat' : 'Tambah alat' }}</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8"><x-flash />
        <form method="POST" action="{{ $alat->exists ? route('admin.alat.update', $alat) : route('admin.alat.store') }}" class="space-y-5 rounded-xl border bg-white p-6 shadow-sm">
            @csrf @if ($alat->exists) @method('PUT') @endif
            <div><x-input-label for="nama_alat" value="Nama alat" /><x-text-input id="nama_alat" name="nama_alat" class="mt-1 block w-full" :value="old('nama_alat', $alat->nama_alat)" required /><x-input-error :messages="$errors->get('nama_alat')" class="mt-2" /></div>
            <div><x-input-label for="kategori_id" value="Kategori" /><select id="kategori_id" name="kategori_id" class="mt-1 block w-full rounded-md border-gray-300" required><option value="">Pilih kategori</option>@foreach ($kategori as $item)<option value="{{ $item->id }}" @selected(old('kategori_id', $alat->kategori_id) == $item->id)>{{ $item->nama_kategori }}</option>@endforeach</select><x-input-error :messages="$errors->get('kategori_id')" class="mt-2" /></div>
            <div><x-input-label for="stok" value="Stok tersedia" /><x-text-input id="stok" name="stok" type="number" min="0" class="mt-1 block w-full" :value="old('stok', $alat->stok ?? 0)" required /><x-input-error :messages="$errors->get('stok')" class="mt-2" /></div>
            <div><x-input-label for="kondisi" value="Kondisi" /><select id="kondisi" name="kondisi" class="mt-1 block w-full rounded-md border-gray-300" required>@foreach (['baik' => 'Baik', 'rusak ringan' => 'Rusak ringan', 'rusak berat' => 'Rusak berat'] as $value => $label)<option value="{{ $value }}" @selected(old('kondisi', $alat->kondisi ?? 'baik') === $value)>{{ $label }}</option>@endforeach</select><x-input-error :messages="$errors->get('kondisi')" class="mt-2" /></div>
            <div class="flex gap-3"><x-primary-button>Simpan</x-primary-button><a href="{{ route('admin.alat.index') }}" class="rounded-md border px-4 py-2 text-sm">Batal</a></div>
        </form>
    </div></div>
</x-app-layout>
