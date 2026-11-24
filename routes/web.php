<?php

use App\Http\Controllers\Admin\AlatController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LogAktivitasController;
use App\Http\Controllers\Admin\TransaksiController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Peminjam\KatalogController;
use App\Http\Controllers\Peminjam\PeminjamanController;
use App\Http\Controllers\Petugas\LaporanController;
use App\Http\Controllers\Petugas\PengajuanController;
use App\Http\Controllers\Petugas\PengembalianController;
use App\Http\Controllers\ProfileController;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware('auth')->group(function () {

    // Setelah login, arahkan ke dashboard sesuai role
    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->role.'.dashboard'))
        ->name('dashboard');

    // ---------------- ADMIN ----------------
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', fn () => view('dashboard', [
            'role' => 'Administrator',
            'stats' => [
                ['label' => 'Total pengguna', 'value' => User::count()],
                ['label' => 'Data alat', 'value' => Alat::count()],
                ['label' => 'Menunggu persetujuan', 'value' => Peminjaman::where('status', 'pending')->count()],
            ],
        ]))->name('dashboard');

        Route::resource('users', UserController::class)->except('show');
        Route::resource('kategori', KategoriController::class)->except('show');
        Route::resource('alat', AlatController::class)->except('show');
        Route::resource('transaksi', TransaksiController::class)->except('show');
        Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas.index');
    });

    // ---------------- PETUGAS ----------------
    Route::middleware('role:petugas')->prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/dashboard', fn () => view('dashboard', [
            'role' => 'Petugas',
            'stats' => [
                ['label' => 'Pengajuan menunggu', 'value' => Peminjaman::where('status', 'pending')->count()],
                ['label' => 'Menunggu pengembalian', 'value' => Peminjaman::where('status', 'menunggu_kembali')->count()],
                ['label' => 'Sedang dipinjam', 'value' => Peminjaman::whereIn('status', ['disetujui', 'menunggu_kembali'])->count()],
            ],
        ]))->name('dashboard');

        // Persetujuan pengajuan
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::post('/pengajuan/{peminjaman}/setujui', [PengajuanController::class, 'setujui'])->name('pengajuan.setujui');
        Route::post('/pengajuan/{peminjaman}/tolak', [PengajuanController::class, 'tolak'])->name('pengajuan.tolak');

        // Pemantauan & pemrosesan pengembalian
        Route::get('/pengembalian', [PengembalianController::class, 'index'])->name('pengembalian.index');
        Route::post('/pengembalian/{peminjaman}/proses', [PengembalianController::class, 'proses'])->name('pengembalian.proses');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    });

    // ---------------- PEMINJAM ----------------
    Route::middleware('role:peminjam')->prefix('peminjam')->name('peminjam.')->group(function () {
        Route::get('/dashboard', fn () => view('dashboard', [
            'role' => 'Peminjam',
            'stats' => [
                ['label' => 'Total pengajuan', 'value' => Peminjaman::where('user_id', auth()->id())->count()],
                ['label' => 'Sedang dipinjam', 'value' => Peminjaman::where('user_id', auth()->id())->whereIn('status', ['disetujui', 'menunggu_kembali'])->count()],
                ['label' => 'Sudah dikembalikan', 'value' => Peminjaman::where('user_id', auth()->id())->where('status', 'dikembalikan')->count()],
            ],
        ]))->name('dashboard');

        Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog');

        Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
        Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
        Route::post('/peminjaman/{peminjaman}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjaman.kembali');
    });

    // Profil bawaan Breeze (boleh diakses semua role)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
