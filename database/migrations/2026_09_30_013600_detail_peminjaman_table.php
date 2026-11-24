<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Membuat tabel rincian alat dan jumlah untuk setiap transaksi peminjaman. */
    public function up(): void
    {
        Schema::create('detail_peminjaman', function (Blueprint $table) {
            $table->id();
            // Peminjaman dihapus -> detailnya ikut terhapus
            $table->foreignId('peminjaman_id')->constrained('peminjaman')->cascadeOnDelete();
            $table->foreignId('alat_id')->constrained('alat')->restrictOnDelete();
            $table->unsignedInteger('jumlah');
            $table->timestamps();

            // Satu alat hanya boleh muncul sekali per transaksi
            $table->unique(['peminjaman_id', 'alat_id']);
        });
    }

    /** Menghapus tabel rincian peminjaman. */
    public function down(): void
    {
        Schema::dropIfExists('detail_peminjaman');
    }
};