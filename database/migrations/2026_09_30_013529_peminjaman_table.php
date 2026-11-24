<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Membuat tabel transaksi utama beserta peminjam, petugas, tanggal, dan status. */
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            // Peminjam yang mengajukan
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            // Petugas yang menyetujui: NULL selama status masih pending
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali_rencana');
            $table->enum('status', ['pending', 'disetujui', 'menunggu_kembali', 'ditolak', 'dikembalikan'])
                ->default('pending');
            $table->timestamps();
        });
    }

    /** Menghapus tabel transaksi peminjaman. */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
