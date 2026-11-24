<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Peminjaman extends Model
{
    /** Menentukan tabel basis data untuk transaksi peminjaman. */
    protected $table = 'peminjaman';

    /** Menentukan atribut transaksi yang boleh diisi melalui mass assignment. */
    protected $fillable = [
        'user_id', 'petugas_id', 'tanggal_pinjam', 'tanggal_kembali_rencana', 'status',
    ];

    /** Mengubah tanggal transaksi menjadi objek tanggal saat diakses. */
    protected $casts = [
        'tanggal_pinjam'          => 'date',
        'tanggal_kembali_rencana' => 'date',
    ];

    /** Peminjam yang mengajukan */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Petugas yang menyetujui (null selama masih pending) */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /** Mengambil seluruh rincian alat yang tercatat pada transaksi ini. */
    public function detail(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'peminjaman_id');
    }

    /** Mengambil catatan pengembalian jika transaksi ini sudah dikembalikan. */
    public function pengembalian(): HasOne
    {
        return $this->hasOne(Pengembalian::class, 'peminjaman_id');
    }

    /**
     * Jumlah hari terlambat sampai tanggal tertentu (default: hari ini).
     * 0 jika belum lewat jadwal. Hanya untuk tampilan: denda resmi dihitung database.
     */
    public function hariTerlambat(?CarbonInterface $sampai = null): int
    {
        $sampai ??= today();

        return $this->tanggal_kembali_rencana->lt($sampai)
            ? (int) $this->tanggal_kembali_rencana->diffInDays($sampai)
            : 0;
    }
}
