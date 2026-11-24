<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Membuat tabel inventaris alat beserta kategori, stok, dan kondisi. */
    public function up(): void
    {
        Schema::create('alat', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete: kategori yang masih punya alat tidak bisa dihapus
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->string('nama_alat');
            $table->unsignedInteger('stok')->default(0); // unsigned: stok tidak bisa minus
            $table->string('kondisi')->default('baik');
            $table->timestamps();
        });
    }

    /** Menghapus tabel inventaris alat. */
    public function down(): void
    {
        Schema::dropIfExists('alat');
    }
};