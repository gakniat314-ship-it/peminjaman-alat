<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPeminjaman extends Model
{
    /** Menentukan tabel basis data untuk rincian setiap peminjaman. */
    protected $table = 'detail_peminjaman';

    /** Menentukan atribut detail yang boleh disimpan melalui mass assignment. */
    protected $fillable = ['peminjaman_id', 'alat_id', 'jumlah', 'kondisi_kembali'];

    /** Mengambil data transaksi induk dari detail ini. */
    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    /** Mengambil data alat yang tercantum pada detail peminjaman ini. */
    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'alat_id');
    }
}
