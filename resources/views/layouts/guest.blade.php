{{-- Menyediakan kerangka dokumen sederhana untuk halaman autentikasi tamu. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/alpine.ts'])
    </head>
    <body class="min-h-screen font-sans antialiased">
        <main class="grid min-h-screen bg-background md:grid-cols-[minmax(20rem,0.9fr)_1.1fr]">
            <aside class="relative isolate hidden min-h-screen flex-col overflow-hidden bg-petrol-900 px-10 py-10 text-white md:flex lg:px-16">
                <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full border-[48px] border-citron-300/15"></div>
                <div class="pointer-events-none absolute -bottom-40 -left-24 h-96 w-96 rounded-full bg-clay-500/20 blur-3xl"></div>
                <a href="{{ route('home') }}" class="relative inline-flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-citron-300 text-petrol-900 shadow-lg shadow-black/10">
                        <x-application-logo class="h-7 w-7 fill-current" />
                    </span>
                    <span class="text-sm font-semibold tracking-wide text-white">Ruang Alat</span>
                </a>
                <div class="relative mt-auto max-w-lg pb-16 pt-20">
                    <span class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-medium text-citron-100">
                        <span class="h-1.5 w-1.5 rounded-full bg-citron-300"></span>
                        Inventaris sekolah, lebih tertata
                    </span>
                    <h1 class="text-4xl font-semibold leading-tight tracking-tight lg:text-5xl">
                        Pinjam alat dengan alur yang jelas.
                    </h1>
                    <p class="mt-5 max-w-md text-sm leading-7 text-white/70">
                        Satu tempat untuk mengelola inventaris, mengajukan peminjaman, dan memantau pengembalian.
                    </p>
                    <div class="mt-10 flex items-center gap-3 text-xs text-white/50">
                        <span class="h-px w-8 bg-citron-300/70"></span>
                        Sistem peminjaman alat
                    </div>
                </div>
                <p class="relative text-xs text-white/45">Akses sesuai peran akun Anda.</p>
            </aside>

            <section class="flex min-h-screen flex-col justify-center px-5 py-10 sm:px-10 lg:px-16">
                <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-3 md:hidden">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-petrol-800 text-white">
                        <x-application-logo class="h-6 w-6 fill-current" />
                    </span>
                    <span class="font-semibold text-gray-900">Ruang Alat</span>
                </a>
                <div class="mx-auto w-full max-w-md rounded-3xl border border-gray-200 bg-white p-6 shadow-[0_24px_70px_-38px_rgba(21,62,58,0.35)] sm:p-9">
                    {{ $slot }}
                </div>
                <p class="mx-auto mt-6 max-w-md text-center text-xs leading-5 text-gray-500">
                    Dengan melanjutkan, Anda menggunakan akun sesuai kebijakan peminjaman sekolah.
                </p>
            </section>
        </main>
    </body>
</html>
