{{-- Menampilkan daftar akun pengguna yang dapat dikelola administrator. --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><p class="text-sm text-indigo-600">Administrator</p><h2 class="text-2xl font-bold text-gray-900">Manajemen Pengguna</h2></div>
            <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Tambah pengguna</a>
        </div>
    </x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-flash />
        <form class="mb-4 flex gap-2" method="GET"><input name="q" value="{{ request('q') }}" placeholder="Cari nama, username, email" class="w-full max-w-sm rounded-lg border-gray-300"><button class="rounded-lg border px-4 py-2 text-sm">Cari</button></form>
        <div class="overflow-x-auto rounded-xl border bg-white shadow-sm"><table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pengguna</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Username</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Role</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr><td class="px-5 py-4"><p class="font-medium text-gray-900">{{ $user->name }}</p><p class="text-sm text-gray-500">{{ $user->email }}</p></td><td class="px-5 py-4 text-sm text-gray-700">{{ $user->username }}</td><td class="px-5 py-4"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold capitalize text-indigo-700">{{ $user->role }}</span></td><td class="space-x-3 px-5 py-4 text-right text-sm">
                        <a class="font-medium text-indigo-600 hover:underline" href="{{ route('admin.users.edit', $user) }}">Ubah</a>
                        <form class="inline" method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">@csrf @method('DELETE')<button class="font-medium text-red-600 hover:underline">Hapus</button></form>
                    </td></tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada data pengguna.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="mt-5">{{ $users->links() }}</div>
    </div></div>
</x-app-layout>
