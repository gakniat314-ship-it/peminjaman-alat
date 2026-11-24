{{-- Menyediakan formulir untuk menambah atau mengubah kategori alat. --}}
<x-app-layout>
    <x-slot name="header"><p class="text-sm text-indigo-600">Administrator / Kategori</p><h2 class="text-2xl font-bold text-gray-900">{{ $kategori->exists ? 'Ubah kategori' : 'Tambah kategori' }}</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8"><x-flash />
        <form method="POST" action="{{ $kategori->exists ? route('admin.kategori.update', $kategori) : route('admin.kategori.store') }}" class="space-y-5 rounded-xl border bg-white p-6 shadow-sm">
            @csrf @if ($kategori->exists) @method('PUT') @endif
            <div><x-input-label for="nama_kategori" value="Nama kategori" /><x-text-input id="nama_kategori" name="nama_kategori" class="mt-1 block w-full" :value="old('nama_kategori', $kategori->nama_kategori)" required /><x-input-error :messages="$errors->get('nama_kategori')" class="mt-2" /></div>
            <div class="flex gap-3"><x-primary-button>Simpan</x-primary-button><a href="{{ route('admin.kategori.index') }}" class="rounded-md border px-4 py-2 text-sm">Batal</a></div>
        </form>
    </div></div>
</x-app-layout>
