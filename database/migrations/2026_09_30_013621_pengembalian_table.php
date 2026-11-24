<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Membuat tabel pencatatan pengembalian dan relasi petugas serta transaksi. */
    public function up(): void
    {
        Schema::create('pengembalian', function (Blueprint $table) {
            $table->id();
            // unique() => satu peminjaman hanya bisa dikembalikan satu kali (relasi 1 : 0..1)
            $table->foreignId('peminjaman_id')->unique()->constrained('peminjaman')->restrictOnDelete();
            $table->date('tanggal_dikembalikan');
            // Diisi otomatis oleh trigger trg_pengembalian_hitung_denda
            $table->decimal('denda', 10, 2)->default(0);
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /** Menghapus tabel pengembalian. */
    public function down(): void
    {
        Schema::dropIfExists('pengembalian');
    }
};