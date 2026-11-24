{{-- Menampilkan riwayat aktivitas pengguna yang tercatat di sistem. --}}
<x-app-layout>
    <x-slot name="header"><p class="text-sm text-indigo-600">Administrator</p><h2 class="text-2xl font-bold text-gray-900">Log Aktivitas</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <form class="mb-4 flex gap-2" method="GET"><input name="q" value="{{ request('q') }}" placeholder="Cari aktivitas" class="w-full max-w-sm rounded-lg border-gray-300"><button class="rounded-lg border px-4 py-2 text-sm">Cari</button></form>
        <div class="overflow-x-auto rounded-xl border bg-white shadow-sm"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Waktu</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pengguna</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Aktivitas</th></tr></thead><tbody class="divide-y divide-gray-100">
            @forelse ($logs as $log)<tr><td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">{{ $log->created_at->format('d/m/Y H:i:s') }}</td><td class="px-5 py-4 text-sm text-gray-700">{{ $log->user?->name ?? 'Akun dihapus' }}</td><td class="px-5 py-4 text-sm text-gray-900">{{ $log->aktivitas }}</td></tr>
            @empty<tr><td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada aktivitas tercatat.</td></tr>@endforelse
        </tbody></table></div><div class="mt-5">{{ $logs->links() }}</div>
    </div></div>
</x-app-layout>
