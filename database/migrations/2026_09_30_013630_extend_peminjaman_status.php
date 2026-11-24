<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Menambahkan status pengajuan pengembalian pada enum transaksi MySQL. */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE peminjaman MODIFY status ENUM('pending', 'disetujui', 'menunggu_kembali', 'ditolak', 'dikembalikan') NOT NULL DEFAULT 'pending'");
        }
    }

    /** Mengembalikan daftar status ke enum transaksi sebelum perubahan ini. */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("UPDATE peminjaman SET status = 'disetujui' WHERE status = 'menunggu_kembali'");
            DB::statement("ALTER TABLE peminjaman MODIFY status ENUM('pending', 'disetujui', 'ditolak', 'dikembalikan') NOT NULL DEFAULT 'pending'");
        }
    }
};
