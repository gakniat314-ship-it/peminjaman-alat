<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Menyimpan lokasi gambar opsional untuk setiap alat. */
    public function up(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('kondisi');
        });
    }

    /** Menghapus lokasi gambar alat. */
    public function down(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
