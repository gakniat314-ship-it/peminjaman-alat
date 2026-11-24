{{-- Menampilkan daftar kategori alat untuk dikelola administrator. --}}
<x-app-layout>
    <x-slot name="header"><div class="flex flex-wrap items-center justify-between gap-3"><div><p class="text-sm text-indigo-600">Administrator / Data Master</p><h2 class="text-2xl font-bold text-gray-900">Kategori Alat</h2></div><a href="{{ route('admin.kategori.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Tambah kategori</a></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8"><x-flash />
        <div class="overflow-x-auto rounded-xl border bg-white shadow-sm"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nama kategori</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Jumlah alat</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th></tr></thead><tbody class="divide-y divide-gray-100">
            @forelse ($kategori as $item)<tr><td class="px-5 py-4 font-medium text-gray-900">{{ $item->nama_kategori }}</td><td class="px-5 py-4 text-sm text-gray-600">{{ $item->alat_count }} alat</td><td class="space-x-3 px-5 py-4 text-right text-sm"><a class="font-medium text-indigo-600 hover:underline" href="{{ route('admin.kategori.edit', $item) }}">Ubah</a><form class="inline" method="POST" action="{{ route('admin.kategori.destroy', $item) }}" onsubmit="return confirm('Hapus kategori ini?')">@csrf @method('DELETE')<button class="font-medium text-red-600 hover:underline">Hapus</button></form></td></tr>
            @empty<tr><td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada kategori.</td></tr>@endforelse
        </tbody></table></div><div class="mt-5">{{ $kategori->links() }}</div>
    </div></div>
</x-app-layout>
