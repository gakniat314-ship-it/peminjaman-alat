{{-- Menampilkan daftar alat yang tersedia untuk dipinjam pengguna. --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-700">Inventaris</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Katalog alat</h2>
                <p class="mt-2 text-sm text-gray-500">Temukan alat yang Anda perlukan untuk dipinjam.</p>
            </div>
            <form method="GET" class="flex w-full gap-2 sm:max-w-md">
                <label class="relative min-w-0 flex-1">
                    <span class="sr-only">Cari nama alat</span>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.46 9.78l3.63 3.62a.75.75 0 1 0 1.06-1.06l-3.62-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" /></svg>
                    <x-text-input name="q" type="text" placeholder="Cari nama alat..." :value="request('q')" class="w-full pl-10" />
                </label>
                <x-primary-button class="shrink-0">Cari</x-primary-button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-flash />

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($alat as $item)
                    <article class="group flex flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl hover:shadow-petrol-900/5">
                        <div class="relative flex h-32 items-end overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-citron-100 p-5">
                            <span class="absolute -right-7 -top-10 h-36 w-36 rounded-full border-[20px] border-white/60 transition-transform duration-500 group-hover:scale-110"></span>
                            <span class="relative inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1.5 text-xs font-semibold text-indigo-800 shadow-sm backdrop-blur">
                                <span class="h-1.5 w-1.5 rounded-full bg-clay-500"></span>
                                {{ $item->kategori->nama_kategori }}
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            <h3 class="text-lg font-bold tracking-tight text-gray-900">{{ $item->nama_alat }}</h3>
                            <dl class="mt-5 grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-gray-50 px-3.5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Stok tersedia</dt>
                                    <dd class="mt-1 text-lg font-bold {{ $item->stok === 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $item->stok }}</dd>
                                </div>
                                <div class="rounded-2xl bg-gray-50 px-3.5 py-3">
                                    <dt class="text-xs font-medium text-gray-500">Kondisi</dt>
                                    <dd class="mt-1 text-sm font-semibold capitalize text-gray-800">{{ $item->kondisi }}</dd>
                                </div>
                            </dl>
                            <div class="mt-5 border-t border-gray-200 pt-4">
                                @if ($item->bisaDipinjam())
                                    <a href="{{ route('peminjam.peminjaman.create', ['alat' => $item->id]) }}" class="inline-flex w-full items-center justify-between rounded-xl bg-petrol-800 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-petrol-700 focus-visible:outline-indigo-600">
                                        Ajukan peminjaman
                                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.72 5.53a.75.75 0 1 1 1.06-1.06l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 1 1-1.06-1.06l3.72-3.72H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                                    </a>
                                @else
                                    <span class="inline-flex w-full items-center justify-between rounded-xl bg-gray-100 px-4 py-3 text-sm font-medium text-gray-500">
                                        Tidak tersedia
                                        <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-gray-300 bg-white/70 px-6 py-16 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m16 16 4 4" /></svg>
                        </span>
                        <p class="mt-4 font-semibold text-gray-900">Alat tidak ditemukan</p>
                        <p class="mt-1 text-sm text-gray-500">Coba kata kunci lain atau periksa kembali nanti.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $alat->links() }}</div>
        </div>
    </div>
</x-app-layout>
