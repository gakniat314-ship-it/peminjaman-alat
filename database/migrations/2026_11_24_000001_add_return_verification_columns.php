<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Menambahkan kolom kondisi barang dan rincian denda pada pengembalian. */
    public function up(): void
    {
        Schema::table('detail_peminjaman', function (Blueprint $table) {
            $table->string('kondisi_kembali')->nullable()->after('jumlah');
        });

        Schema::table('pengembalian', function (Blueprint $table) {
            $table->decimal('denda_kerusakan', 10, 2)->default(0)->after('denda');
            $table->text('catatan')->nullable()->after('denda_kerusakan');
        });
    }

    /** Menghapus kembali kolom verifikasi yang ditambahkan pada migrasi ini. */
    public function down(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            $table->dropColumn(['denda_kerusakan', 'catatan']);
        });

        Schema::table('detail_peminjaman', function (Blueprint $table) {
            $table->dropColumn('kondisi_kembali');
        });
    }
};
