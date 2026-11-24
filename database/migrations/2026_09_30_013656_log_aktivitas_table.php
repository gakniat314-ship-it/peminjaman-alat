<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Membuat tabel untuk menyimpan catatan aktivitas pengguna. */
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            // nullOnDelete: log tetap ada walaupun user-nya dihapus
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aktivitas');
            // Hanya created_at (log tidak pernah diubah), default waktu sekarang
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /** Menghapus tabel catatan aktivitas. */
    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};