{{-- Menyiapkan tautan yang ditampilkan pada navigasi desktop dan seluler. --}}
@php
    $menus = match (Auth::user()->role) {
        'admin' => [
            ['Dashboard', 'admin.dashboard'],
            ['Pengguna', 'admin.users.index'],
            ['Kategori', 'admin.kategori.index'],
            ['Data Alat', 'admin.alat.index'],
            ['Transaksi', 'admin.transaksi.index'],
            ['Log Aktivitas', 'admin.log-aktivitas.index'],
        ],
        'petugas' => [
            ['Dashboard', 'petugas.dashboard'],
            ['Pengajuan', 'petugas.pengajuan.index'],
            ['Pengembalian', 'petugas.pengembalian.index'],
            ['Laporan', 'petugas.laporan.index'],
        ],
        default => [
            ['Dashboard', 'peminjam.dashboard'],
            ['Katalog', 'peminjam.katalog'],
            ['Peminjaman Saya', 'peminjam.peminjaman.index'],
        ],
    };
@endphp
<nav x-data="{ open: false }" class="sticky top-0 z-30 border-b border-gray-200/80 bg-white/90 shadow-[0_8px_30px_-24px_rgba(21,62,58,0.5)] backdrop-blur-xl">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-[4.5rem] items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-6">
                <a href="{{ route('dashboard') }}" class="inline-flex shrink-0 items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-petrol-800 text-white shadow-sm shadow-petrol-900/20"><x-application-logo class="block h-6 w-6 fill-current" /></span>
                    <span class="hidden text-sm font-bold tracking-tight text-gray-900 sm:inline">Ruang Alat</span>
                </a>
                <div class="hidden items-center gap-1 lg:flex">
                    @foreach ($menus as [$label, $route])
                        @if ($label === 'Transaksi' && Auth::user()->role === 'admin')
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button type="button" class="{{ request()->routeIs('admin.transaksi.*', 'admin.pengembalian.*') ? 'inline-flex items-center rounded-xl bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-800 ring-1 ring-inset ring-indigo-100' : 'inline-flex items-center rounded-xl px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}" aria-label="Buka menu Transaksi">
                                        Transaksi
                                        <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06.03L10 11.4l3.72-3.9a.75.75 0 1 1 1.08 1.04l-4.25 4.45a.75.75 0 0 1-1.08 0L5.2 8.53a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('admin.transaksi.index')">Peminjaman</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.pengembalian.index')">Pengembalian</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        @else
                            <x-nav-link :href="route($route)" :active="request()->routeIs($route, str_replace('.index', '.*', $route))">{{ $label }}</x-nav-link>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="hidden items-center gap-4 sm:flex">
                <div class="text-right"><p class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</p><p class="text-[11px] font-medium uppercase tracking-[0.12em] text-gray-500">{{ Auth::user()->role }}</p></div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger"><button class="rounded-xl border border-gray-200 p-2 text-gray-600 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-800" aria-label="Buka menu akun"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06.03L10 11.4l3.72-3.9a.75.75 0 1 1 1.08 1.04l-4.25 4.45a.75.75 0 0 1-1.08 0L5.2 8.53a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg></button></x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">@csrf<x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</x-dropdown-link></form>
                    </x-slot>
                </x-dropdown>
            </div>
            <button type="button" class="rounded-xl border border-gray-200 p-2 text-gray-600 hover:bg-gray-100 lg:hidden" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-navigation" aria-label="Buka navigasi"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg></button>
        </div>
    </div>
    <div id="mobile-navigation" x-show="open" x-transition class="border-t border-gray-200 bg-gray-50/90 px-4 py-4 lg:hidden">
        <div class="space-y-1">
            @foreach ($menus as [$label, $route])
                @if ($label === 'Transaksi' && Auth::user()->role === 'admin')
                    <div x-data="{ submenuOpen: false }">
                        <button type="button" @click="submenuOpen = !submenuOpen" :aria-expanded="submenuOpen.toString()" class="{{ request()->routeIs('admin.transaksi.*', 'admin.pengembalian.*') ? 'block w-full rounded-xl bg-indigo-50 px-4 py-3 text-start text-sm font-semibold text-indigo-800 ring-1 ring-inset ring-indigo-100' : 'block w-full rounded-xl px-4 py-3 text-start text-sm font-medium text-gray-600 hover:bg-white hover:text-gray-900' }}">
                            <span class="flex items-center justify-between">
                                Transaksi
                                <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': submenuOpen }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06.03L10 11.4l3.72-3.9a.75.75 0 1 1 1.08 1.04l-4.25 4.45a.75.75 0 0 1-1.08 0L5.2 8.53a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
                            </span>
                        </button>
                        <div x-show="submenuOpen" x-transition class="mt-1 space-y-1 ps-3">
                            <x-responsive-nav-link @click="open = false" :href="route('admin.transaksi.index')" :active="request()->routeIs('admin.transaksi.*')">Peminjaman</x-responsive-nav-link>
                            <x-responsive-nav-link @click="open = false" :href="route('admin.pengembalian.index')" :active="request()->routeIs('admin.pengembalian.*')">Pengembalian</x-responsive-nav-link>
                        </div>
                    </div>
                @else
                    <x-responsive-nav-link @click="open = false" :href="route($route)" :active="request()->routeIs($route, str_replace('.index', '.*', $route))">{{ $label }}</x-responsive-nav-link>
                @endif
            @endforeach
        </div>
        <div class="mt-3 border-t border-gray-200 pt-3">
            <p class="px-3 text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</p>
            <x-responsive-nav-link @click="open = false" :href="route('profile.edit')">Profil</x-responsive-nav-link>
            <form method="POST" action="{{ route('logout') }}">@csrf<x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</x-responsive-nav-link></form>
        </div>
    </div>
</nav>
