{{-- Menampilkan ringkasan dashboard sesuai peran pengguna yang sedang login. --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-700">{{ $role }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Dashboard</h2>
            </div>
            <p class="text-sm text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="relative isolate overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-petrol-900 via-petrol-800 to-petrol-700 px-6 py-8 text-white shadow-xl shadow-petrol-900/10 sm:px-9 sm:py-10">
                <div class="pointer-events-none absolute -right-20 -top-32 -z-10 h-80 w-80 rounded-full border-[46px] border-citron-200/10"></div>
                <div class="pointer-events-none absolute -bottom-36 right-28 -z-10 h-64 w-64 rounded-full bg-clay-500/20 blur-3xl"></div>
                <div class="relative max-w-3xl">
                    <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-medium text-citron-100">
                        <span class="h-1.5 w-1.5 rounded-full bg-citron-300"></span>
                        Selamat datang kembali, {{ auth()->user()->name }}
                    </p>
                    <h3 class="mt-5 text-3xl font-semibold tracking-tight sm:text-4xl">Semua urusan alat, lebih tertata.</h3>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/75 sm:text-base">
                        Kelola aktivitas sesuai hak akses akun Anda. Setiap perubahan penting tercatat untuk menjaga akuntabilitas.
                    </p>
                </div>
                <div class="relative mt-7 flex items-center gap-2 text-xs font-medium text-white/65 sm:mt-8">
                    <span class="h-px w-8 bg-citron-300"></span>
                    Sistem peminjaman alat
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($stats as $stat)
                    <article class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg sm:p-6">
                        <span class="absolute right-0 top-0 h-20 w-20 -translate-y-8 translate-x-8 rounded-full bg-citron-100 transition-transform duration-300 group-hover:scale-125"></span>
                        <div class="relative flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ $stat['label'] }}</p>
                                <p class="mt-4 text-3xl font-bold tracking-tight text-gray-900">{{ number_format($stat['value']) }}</p>
                            </div>
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-clay-400 ring-4 ring-clay-50"></span>
                        </div>
                    </article>
                @endforeach
            </section>

            @php
                $shortcuts = match (auth()->user()->role) {
                    'admin' => [
                        ['Pengguna', 'Kelola akun dan hak akses', 'admin.users.index'],
                        ['Kategori & alat', 'Perbarui data inventaris', 'admin.alat.index'],
                        ['Transaksi', 'Pantau seluruh peminjaman', 'admin.transaksi.index'],
                        ['Log aktivitas', 'Tinjau jejak aktivitas sistem', 'admin.log-aktivitas.index'],
                    ],
                    'petugas' => [
                        ['Pengajuan', 'Tinjau dan putuskan permohonan', 'petugas.pengajuan.index'],
                        ['Pengembalian', 'Proses alat yang dikembalikan', 'petugas.pengembalian.index'],
                        ['Laporan', 'Cetak laporan transaksi', 'petugas.laporan.index'],
                    ],
                    default => [
                        ['Katalog alat', 'Cari alat yang tersedia', 'peminjam.katalog'],
                        ['Peminjaman saya', 'Pantau status pengajuan', 'peminjam.peminjaman.index'],
                        ['Ajukan peminjaman', 'Buat permohonan baru', 'peminjam.peminjaman.create'],
                    ],
                };
            @endphp

            <section>
                <div class="mb-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Mulai dari sini</p>
                        <h3 class="mt-1 text-xl font-bold tracking-tight text-gray-900">Akses cepat</h3>
                    </div>
                    <span class="hidden text-sm text-gray-500 sm:block">Pintasan sesuai peran Anda</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($shortcuts as [$title, $description, $route])
                        <a href="{{ route($route) }}" class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg focus-visible:outline-indigo-600">
                            <span class="mb-5 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-800 transition-colors group-hover:bg-indigo-800 group-hover:text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.75A1.75 1.75 0 0 1 5.75 4h4.5A1.75 1.75 0 0 1 12 5.75v4.5A1.75 1.75 0 0 1 10.25 12h-4.5A1.75 1.75 0 0 1 4 10.25v-4.5ZM14 5.75A1.75 1.75 0 0 1 15.75 4h2.5A1.75 1.75 0 0 1 20 5.75v2.5A1.75 1.75 0 0 1 18.25 10h-2.5A1.75 1.75 0 0 1 14 8.25v-2.5ZM14 15.75A1.75 1.75 0 0 1 15.75 14h2.5A1.75 1.75 0 0 1 20 15.75v2.5A1.75 1.75 0 0 1 18.25 20h-2.5A1.75 1.75 0 0 1 14 18.25v-2.5ZM4 15.75A1.75 1.75 0 0 1 5.75 14h4.5A1.75 1.75 0 0 1 12 15.75v2.5A1.75 1.75 0 0 1 10.25 20h-4.5A1.75 1.75 0 0 1 4 18.25v-2.5Z" />
                                </svg>
                            </span>
                            <span class="flex items-center justify-between gap-3 font-semibold text-gray-900 group-hover:text-indigo-800">
                                {{ $title }}
                                <svg class="h-4 w-4 shrink-0 -translate-x-1 opacity-0 transition-all group-hover:translate-x-0 group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.72 5.53a.75.75 0 1 1 1.06-1.06l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 1 1-1.06-1.06l3.72-3.72H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
                            </span>
                            <p class="mt-2 text-sm leading-6 text-gray-500">{{ $description }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
