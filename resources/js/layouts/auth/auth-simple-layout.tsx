import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

/** Menyediakan layout autentikasi dua kolom dengan informasi aplikasi. */
export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <main className="grid min-h-svh bg-background md:grid-cols-[minmax(20rem,0.9fr)_1.1fr]">
            <aside className="relative hidden min-h-svh flex-col overflow-hidden bg-petrol-900 px-10 py-10 text-white md:flex lg:px-16">
                <div className="pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full border-[48px] border-citron-300/15" />
                <div className="pointer-events-none absolute -bottom-40 -left-24 h-96 w-96 rounded-full bg-clay-500/20 blur-3xl" />
                <Link
                    href={home()}
                    className="relative inline-flex items-center gap-3"
                >
                    <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-citron-300 text-petrol-900 shadow-lg shadow-black/10">
                        <AppLogoIcon className="size-7 fill-current" />
                    </span>
                    <span className="text-sm font-semibold tracking-wide text-white">
                        Ruang Alat
                    </span>
                </Link>
                <div className="relative mt-auto max-w-lg pt-20 pb-16">
                    <span className="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-medium text-citron-100">
                        <span className="h-1.5 w-1.5 rounded-full bg-citron-300" />
                        Inventaris sekolah, lebih tertata
                    </span>
                    <p className="text-4xl leading-tight font-semibold tracking-tight lg:text-5xl">
                        Pinjam alat dengan alur yang jelas.
                    </p>
                    <p className="mt-5 max-w-md text-sm leading-7 text-white/70">
                        Satu tempat untuk mengelola inventaris, mengajukan
                        peminjaman, dan memantau pengembalian.
                    </p>
                    <div className="mt-10 flex items-center gap-3 text-xs text-white/50">
                        <span className="h-px w-8 bg-citron-300/70" />
                        Sistem peminjaman alat
                    </div>
                </div>
                <p className="relative text-xs text-white/45">
                    Akses sesuai peran akun Anda.
                </p>
            </aside>

            <section className="flex min-h-svh flex-col justify-center px-5 py-10 sm:px-10 lg:px-16">
                <Link
                    href={home()}
                    className="mb-8 inline-flex items-center gap-3 md:hidden"
                >
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-petrol-800 text-white">
                        <AppLogoIcon className="size-6 fill-current" />
                    </span>
                    <span className="font-semibold text-foreground">
                        Ruang Alat
                    </span>
                </Link>
                <div className="mx-auto w-full max-w-md rounded-3xl border border-border bg-card p-6 shadow-[0_24px_70px_-38px_rgba(21,62,58,0.35)] sm:p-9">
                    <div className="mb-7">
                        <p className="text-xs font-semibold tracking-[0.18em] text-primary uppercase">
                            Akses akun
                        </p>
                        <h1 className="mt-2 text-2xl font-bold tracking-tight text-foreground">
                            {title}
                        </h1>
                        <p className="mt-2 text-sm leading-6 text-muted-foreground">
                            {description}
                        </p>
                    </div>
                    {children}
                </div>
                <p className="mx-auto mt-6 max-w-md text-center text-xs leading-5 text-muted-foreground">
                    Akses sesuai peran akun Anda.
                </p>
            </section>
        </main>
    );
}
