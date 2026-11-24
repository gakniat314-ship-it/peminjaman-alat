<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengembalian extends Model
{
    /** Menentukan tabel basis data untuk catatan pengembalian. */
    protected $table = 'pengembalian';

    /** Menentukan atribut pengembalian yang boleh diisi melalui mass assignment. */
    protected $fillable = [
        'peminjaman_id',
        'tanggal_dikembalikan',
        'denda',
        'denda_kerusakan',
        'catatan',
        'petugas_id',
    ];

    /** Mengubah tanggal dan nilai denda menjadi tipe data yang sesuai saat diakses. */
    protected $casts = [
        'tanggal_dikembalikan' => 'date',
        'denda' => 'decimal:2',
        'denda_kerusakan' => 'decimal:2',
    ];

    /** Mengambil transaksi peminjaman yang sedang dikembalikan. */
    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    /** Mengambil petugas yang memproses pengembalian ini. */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
