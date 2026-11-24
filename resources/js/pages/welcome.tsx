import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    ArrowUpRight,
    Check,
    ClipboardCheck,
    Package,
    RotateCcw,
    ShieldCheck,
    Users,
} from 'lucide-react';
import { dashboard, login, register } from '@/routes';

// Merangkum fitur utama inventaris dan alur peminjaman untuk beranda.
const features = [
    {
        icon: Package,
        eyebrow: 'Inventaris',
        title: 'Katalog yang mudah dijelajahi',
        description:
            'Lihat alat, kategori, stok, dan kondisi sebelum mengajukan peminjaman.',
    },
    {
        icon: ClipboardCheck,
        eyebrow: 'Persetujuan',
        title: 'Pengajuan lebih teratur',
        description:
            'Petugas dapat meninjau permohonan dan peminjam bisa memantau statusnya.',
    },
    {
        icon: RotateCcw,
        eyebrow: 'Pengembalian',
        title: 'Jadwal kembali lebih jelas',
        description:
            'Pantau pengembalian dan informasi keterlambatan dalam alur yang tercatat.',
    },
];

// Menyusun urutan singkat proses peminjaman yang ditampilkan kepada pengunjung.
const steps = [
    {
        number: '01',
        title: 'Pilih alat',
        description: 'Jelajahi katalog dan tentukan alat yang Anda perlukan.',
        icon: Package,
    },
    {
        number: '02',
        title: 'Ajukan peminjaman',
        description: 'Kirim permohonan untuk ditinjau oleh petugas.',
        icon: ClipboardCheck,
    },
    {
        number: '03',
        title: 'Kembalikan sesuai jadwal',
        description: 'Pantau status, lalu ajukan pengembalian saat selesai.',
        icon: RotateCcw,
    },
];

/** Menampilkan halaman pengenalan aplikasi dan jalur masuk pengguna. */
export default function Welcome() {
    const { auth } = usePage().props;
    const isAuthenticated = Boolean(auth.user);
    // Login is Blade-rendered, so its links must perform a full-page navigation.
    const loginUrl = login.url();

    return (
        <>
            <Head title="Ruang Alat — Peminjaman lebih tertata">
                <meta
                    head-key="description"
                    name="description"
                    content="Kelola inventaris sekolah dan pantau peminjaman alat dalam satu alur yang jelas."
                />
            </Head>

            <main
                id="top"
                className="min-h-svh overflow-hidden bg-background text-foreground"
            >
                <a
                    href="#main"
                    className="sr-only z-50 rounded-lg bg-card px-4 py-2 text-sm font-semibold text-foreground focus:not-sr-only focus:fixed focus:top-4 focus:left-4"
                >
                    Lewati ke konten utama
                </a>

                <header className="sticky top-0 z-40 border-b border-border/80 bg-background/85 backdrop-blur-xl">
                    <div className="mx-auto flex h-[4.5rem] max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                        <a
                            href="#top"
                            className="group inline-flex shrink-0 items-center gap-3"
                            aria-label="Ruang Alat, beranda"
                        >
                            <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-sm shadow-petrol-900/15 transition-transform group-hover:scale-105 group-hover:-rotate-3">
                                <Package
                                    className="h-5 w-5"
                                    aria-hidden="true"
                                />
                            </span>
                            <span>
                                <span className="block text-sm font-bold tracking-tight">
                                    Ruang Alat
                                </span>
                                <span className="hidden text-[10px] font-semibold tracking-[0.16em] text-muted-foreground uppercase sm:block">
                                    Peminjaman sekolah
                                </span>
                            </span>
                        </a>

                        <nav
                            aria-label="Navigasi utama"
                            className="hidden items-center gap-8 md:flex"
                        >
                            <a
                                href="#fitur"
                                className="text-sm font-medium text-muted-foreground hover:text-foreground"
                            >
                                Fitur
                            </a>
                            <a
                                href="#alur"
                                className="text-sm font-medium text-muted-foreground hover:text-foreground"
                            >
                                Cara kerja
                            </a>
                            <a
                                href="#peran"
                                className="text-sm font-medium text-muted-foreground hover:text-foreground"
                            >
                                Untuk siapa
                            </a>
                        </nav>

                        <div className="flex shrink-0 items-center gap-2 sm:gap-3">
                            {isAuthenticated ? (
                                <Link
                                    href={dashboard()}
                                    className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-offset-4"
                                >
                                    Dashboard
                                    <ArrowUpRight
                                        className="h-4 w-4"
                                        aria-hidden="true"
                                    />
                                </Link>
                            ) : (
                                <>
                                    <a
                                        href={loginUrl}
                                        className="hidden rounded-xl px-3 py-2.5 text-sm font-semibold text-foreground hover:bg-muted sm:inline-flex"
                                    >
                                        Masuk
                                    </a>
                                    <Link
                                        href={register()}
                                        className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-offset-4"
                                    >
                                        Daftar akun
                                        <ArrowRight
                                            className="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                </header>

                <div id="main">
                    <section className="relative isolate mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16 lg:px-8 lg:py-24">
                        <div className="pointer-events-none absolute top-20 -left-56 -z-10 h-96 w-96 rounded-full bg-citron-200/30 blur-3xl" />
                        <div className="max-w-2xl">
                            <p className="inline-flex items-center gap-2 rounded-full border border-petrol-200 bg-petrol-50 px-3.5 py-1.5 text-xs font-semibold tracking-wide text-petrol-800">
                                <span className="h-1.5 w-1.5 rounded-full bg-clay-500" />
                                Sistem peminjaman alat sekolah
                            </p>
                            <h1 className="mt-6 text-4xl leading-[1.08] font-semibold tracking-tight text-foreground sm:text-5xl lg:text-6xl">
                                Semua urusan alat,
                                <span className="relative ml-2 inline-block text-primary">
                                    lebih tertata.
                                    <span className="absolute -bottom-1 left-0 -z-10 h-3 w-full rounded-full bg-citron-300/70" />
                                </span>
                            </h1>
                            <p className="mt-6 max-w-xl text-base leading-7 text-muted-foreground sm:text-lg sm:leading-8">
                                Dari mencari inventaris hingga memantau
                                pengembalian, setiap langkah punya alur yang
                                jelas untuk peminjam, petugas, dan admin.
                            </p>
                            <div className="mt-8 flex flex-wrap items-center gap-3">
                                {isAuthenticated ? (
                                    <Link
                                        href={dashboard()}
                                        className="group inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-petrol-900/15 transition hover:-translate-y-0.5 hover:shadow-xl focus-visible:outline-offset-4"
                                    >
                                        Buka dashboard
                                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                    </Link>
                                ) : (
                                    <>
                                        <Link
                                            href={register()}
                                            className="group inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-petrol-900/15 transition hover:-translate-y-0.5 hover:shadow-xl focus-visible:outline-offset-4"
                                        >
                                            Mulai sekarang
                                            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                        </Link>
                                        <a
                                            href={loginUrl}
                                            className="inline-flex items-center gap-2 rounded-xl border border-border bg-card px-5 py-3 text-sm font-semibold text-foreground shadow-sm hover:border-petrol-300 hover:bg-petrol-50"
                                        >
                                            Masuk ke akun
                                        </a>
                                    </>
                                )}
                            </div>
                            <ul className="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-xs font-medium text-muted-foreground sm:text-sm">
                                {[
                                    'Katalog terpusat',
                                    'Status mudah dipantau',
                                    'Akses sesuai peran',
                                ].map((item) => (
                                    <li
                                        key={item}
                                        className="inline-flex items-center gap-2"
                                    >
                                        <span className="flex h-4 w-4 items-center justify-center rounded-full bg-citron-200 text-petrol-900">
                                            <Check
                                                className="h-3 w-3"
                                                strokeWidth={3}
                                                aria-hidden="true"
                                            />
                                        </span>
                                        {item}
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div className="relative mx-auto w-full max-w-xl">
                            <div className="absolute -top-5 -right-4 h-28 w-28 rounded-full border-[18px] border-citron-300/60 sm:-top-8 sm:-right-8 sm:h-36 sm:w-36 sm:border-[24px]" />
                            <div className="absolute -bottom-8 -left-5 h-36 w-36 rounded-full bg-clay-400/20 blur-2xl" />
                            <div className="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-petrol-900 via-petrol-800 to-petrol-700 p-5 text-white shadow-2xl shadow-petrol-900/20 sm:p-7">
                                <div className="absolute top-20 -right-20 h-64 w-64 rounded-full border border-white/10" />
                                <div className="absolute top-28 -right-10 h-44 w-44 rounded-full border border-white/10" />
                                <div className="relative flex items-start justify-between gap-4">
                                    <div>
                                        <p className="text-xs font-semibold tracking-[0.16em] text-citron-200 uppercase">
                                            Satu alur, tiga tahap
                                        </p>
                                        <h2 className="mt-2 text-xl font-semibold tracking-tight sm:text-2xl">
                                            Peminjaman yang terpantau
                                        </h2>
                                    </div>
                                    <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-citron-200 ring-1 ring-white/15">
                                        <ShieldCheck
                                            className="h-5 w-5"
                                            aria-hidden="true"
                                        />
                                    </span>
                                </div>

                                <div className="relative mt-8 rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur sm:p-5">
                                    <div className="flex items-center justify-between border-b border-white/10 pb-4">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-citron-300 text-petrol-900">
                                                <Package
                                                    className="h-5 w-5"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                            <div>
                                                <p className="text-sm font-semibold">
                                                    Kelola dengan teratur
                                                </p>
                                                <p className="mt-0.5 text-xs text-white/65">
                                                    Dari pengajuan hingga
                                                    kembali
                                                </p>
                                            </div>
                                        </div>
                                        <ArrowUpRight
                                            className="h-4 w-4 text-citron-200"
                                            aria-hidden="true"
                                        />
                                    </div>
                                    <div className="mt-4 space-y-3">
                                        {[
                                            ['Pilih alat', 'Katalog'],
                                            ['Tinjau permohonan', 'Petugas'],
                                            ['Pantau pengembalian', 'Status'],
                                        ].map(([title, detail], index) => (
                                            <div
                                                key={title}
                                                className="flex items-center gap-3 rounded-xl bg-white/[0.07] px-3 py-3 transition-colors hover:bg-white/[0.12]"
                                            >
                                                <span
                                                    className={`flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold ${index === 1 ? 'bg-clay-400 text-petrol-950' : 'bg-citron-200 text-petrol-900'}`}
                                                >
                                                    0{index + 1}
                                                </span>
                                                <span className="min-w-0 flex-1">
                                                    <span className="block text-sm font-medium">
                                                        {title}
                                                    </span>
                                                    <span className="mt-0.5 block text-xs text-white/55">
                                                        {detail}
                                                    </span>
                                                </span>
                                                <Check
                                                    className="h-4 w-4 text-citron-200"
                                                    aria-hidden="true"
                                                />
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="relative mt-5 flex items-center justify-between gap-3 text-xs text-white/60">
                                    <span className="inline-flex items-center gap-2">
                                        <span className="h-2 w-2 rounded-full bg-citron-300" />
                                        Contoh alur peminjaman
                                    </span>
                                    <span>Ringkas & transparan</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        id="fitur"
                        className="scroll-mt-24 border-y border-border/70 bg-card/60"
                    >
                        <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                            <div className="max-w-2xl">
                                <p className="text-xs font-bold tracking-[0.18em] text-clay-700 uppercase">
                                    Dirancang untuk alur yang jelas
                                </p>
                                <h2 className="mt-3 text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
                                    Inventaris lebih mudah dikelola.
                                </h2>
                                <p className="mt-4 text-base leading-7 text-muted-foreground">
                                    Informasi dan proses peminjaman tersedia
                                    dalam satu tempat yang mudah dipahami.
                                </p>
                            </div>

                            <div className="mt-10 grid gap-4 md:grid-cols-3">
                                {features.map(
                                    ({
                                        icon: Icon,
                                        eyebrow,
                                        title,
                                        description,
                                    }) => (
                                        <article
                                            key={title}
                                            className="group rounded-3xl border border-border bg-card p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-petrol-200 hover:shadow-xl hover:shadow-petrol-900/5 sm:p-7"
                                        >
                                            <span className="flex h-12 w-12 items-center justify-center rounded-2xl bg-petrol-50 text-petrol-800 transition-colors group-hover:bg-primary group-hover:text-primary-foreground">
                                                <Icon
                                                    className="h-5 w-5"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                            <p className="mt-6 text-xs font-bold tracking-[0.15em] text-clay-700 uppercase">
                                                {eyebrow}
                                            </p>
                                            <h3 className="mt-2 text-lg font-semibold tracking-tight text-foreground">
                                                {title}
                                            </h3>
                                            <p className="mt-3 text-sm leading-6 text-muted-foreground">
                                                {description}
                                            </p>
                                            <span className="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-primary">
                                                Lihat alur
                                                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                            </span>
                                        </article>
                                    ),
                                )}
                            </div>
                        </div>
                    </section>

                    <section
                        id="alur"
                        className="mx-auto grid max-w-7xl scroll-mt-24 gap-10 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20 lg:px-8"
                    >
                        <div className="lg:sticky lg:top-32 lg:self-start">
                            <p className="text-xs font-bold tracking-[0.18em] text-clay-700 uppercase">
                                Cara kerja
                            </p>
                            <h2 className="mt-3 text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
                                Tiga langkah sederhana.
                            </h2>
                            <p className="mt-4 max-w-md text-base leading-7 text-muted-foreground">
                                Setiap tahap membantu peminjam dan petugas
                                mengetahui apa yang perlu dilakukan berikutnya.
                            </p>
                            <a
                                href="#mulai"
                                className="group mt-7 inline-flex items-center gap-2 text-sm font-semibold text-primary"
                            >
                                Siap memulai?
                                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                            </a>
                        </div>

                        <ol className="divide-y divide-border rounded-3xl border border-border bg-card px-5 shadow-sm sm:px-7">
                            {steps.map(
                                ({
                                    number,
                                    title,
                                    description,
                                    icon: Icon,
                                }) => (
                                    <li
                                        key={number}
                                        className="group flex gap-4 py-6 sm:gap-6 sm:py-7"
                                    >
                                        <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-citron-100 text-petrol-800 transition-colors group-hover:bg-citron-300">
                                            <Icon
                                                className="h-5 w-5"
                                                aria-hidden="true"
                                            />
                                        </span>
                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                                <span className="text-xs font-bold tracking-[0.14em] text-clay-700">
                                                    {number}
                                                </span>
                                                <h3 className="text-lg font-semibold tracking-tight text-foreground">
                                                    {title}
                                                </h3>
                                            </div>
                                            <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                                {description}
                                            </p>
                                        </div>
                                        <span className="hidden h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground transition-colors group-hover:bg-petrol-50 group-hover:text-primary sm:flex">
                                            <ArrowRight
                                                className="h-4 w-4"
                                                aria-hidden="true"
                                            />
                                        </span>
                                    </li>
                                ),
                            )}
                        </ol>
                    </section>

                    <section
                        id="peran"
                        className="scroll-mt-24 bg-petrol-900 text-white"
                    >
                        <div className="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 sm:py-16 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:px-8">
                            <div>
                                <p className="text-xs font-bold tracking-[0.18em] text-citron-200 uppercase">
                                    Untuk seluruh tim
                                </p>
                                <h2 className="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                    Satu sistem, peran yang tepat.
                                </h2>
                                <p className="mt-4 max-w-lg text-sm leading-7 text-white/70 sm:text-base">
                                    Setiap akun mendapatkan akses sesuai
                                    tanggung jawabnya agar proses tetap terarah
                                    dan tercatat.
                                </p>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-3">
                                {[
                                    {
                                        name: 'Admin',
                                        description:
                                            'Mengelola data dan pengguna',
                                        icon: ShieldCheck,
                                    },
                                    {
                                        name: 'Petugas',
                                        description:
                                            'Meninjau dan memproses transaksi',
                                        icon: Users,
                                    },
                                    {
                                        name: 'Peminjam',
                                        description:
                                            'Mengajukan dan memantau alat',
                                        icon: Package,
                                    },
                                ].map(({ name, description, icon: Icon }) => (
                                    <article
                                        key={name}
                                        className="rounded-2xl border border-white/15 bg-white/[0.07] p-4 transition-colors hover:bg-white/[0.12]"
                                    >
                                        <Icon
                                            className="h-5 w-5 text-citron-200"
                                            aria-hidden="true"
                                        />
                                        <h3 className="mt-4 font-semibold">
                                            {name}
                                        </h3>
                                        <p className="mt-1 text-xs leading-5 text-white/65">
                                            {description}
                                        </p>
                                    </article>
                                ))}
                            </div>
                        </div>
                    </section>

                    <section
                        id="mulai"
                        className="mx-auto max-w-7xl scroll-mt-24 px-4 py-16 sm:px-6 sm:py-20 lg:px-8"
                    >
                        <div className="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-citron-100 via-citron-50 to-white px-6 py-10 sm:px-10 sm:py-12 lg:px-14">
                            <div className="pointer-events-none absolute -top-28 -right-20 -z-10 h-72 w-72 rounded-full border-[38px] border-petrol-800/5" />
                            <div className="pointer-events-none absolute right-48 -bottom-28 -z-10 h-64 w-64 rounded-full bg-clay-400/10 blur-2xl" />
                            <div className="relative flex flex-col gap-7 md:flex-row md:items-center md:justify-between">
                                <div className="max-w-2xl">
                                    <span className="inline-flex items-center gap-2 text-xs font-bold tracking-[0.16em] text-petrol-800 uppercase">
                                        <Check className="h-4 w-4" />
                                        Siap digunakan
                                    </span>
                                    <h2 className="mt-3 text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
                                        Mulai dengan alur yang lebih jelas.
                                    </h2>
                                    <p className="mt-3 text-sm leading-6 text-muted-foreground sm:text-base">
                                        Masuk untuk melanjutkan, atau buat akun
                                        peminjam untuk mengajukan alat.
                                    </p>
                                </div>
                                <div className="flex shrink-0 flex-wrap gap-3">
                                    {isAuthenticated ? (
                                        <Link
                                            href={dashboard()}
                                            className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                                        >
                                            Ke dashboard
                                            <ArrowUpRight className="h-4 w-4" />
                                        </Link>
                                    ) : (
                                        <>
                                            <a
                                                href={loginUrl}
                                                className="inline-flex items-center rounded-xl border border-petrol-200 bg-card px-5 py-3 text-sm font-semibold text-foreground hover:bg-petrol-50"
                                            >
                                                Masuk
                                            </a>
                                            <Link
                                                href={register()}
                                                className="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                                            >
                                                Daftar akun
                                                <ArrowRight className="h-4 w-4" />
                                            </Link>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <footer className="border-t border-border bg-card/60">
                    <div className="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-6 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                        <div className="inline-flex items-center gap-2 font-semibold text-foreground">
                            <Package className="h-4 w-4 text-primary" />
                            Ruang Alat
                        </div>
                        <p>
                            Sistem peminjaman dan pengelolaan inventaris
                            sekolah.
                        </p>
                        <a
                            href="#top"
                            className="inline-flex items-center gap-1 font-semibold text-primary hover:text-petrol-700"
                        >
                            Kembali ke atas
                            <ArrowUpRight className="h-3.5 w-3.5" />
                        </a>
                    </div>
                </footer>
            </main>
        </>
    );
}
