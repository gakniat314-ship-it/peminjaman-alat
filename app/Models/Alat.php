<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alat extends Model
{
    /** Menentukan tabel basis data yang menyimpan data alat. */
    protected $table = 'alat';

    /** Menentukan atribut alat yang boleh diisi melalui mass assignment. */
    protected $fillable = ['kategori_id', 'nama_alat', 'stok', 'kondisi', 'image_path'];

    /** Mengambil kategori yang menaungi alat ini. */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /** Mengambil seluruh detail peminjaman yang merujuk pada alat ini. */
    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'alat_id');
    }

    /** Aturan "bisa dipinjam" dibuat satu tempat: ada stok dan tidak rusak berat */
    public function bisaDipinjam(): bool
    {
        return $this->stok > 0 && $this->kondisi !== 'rusak berat';
    }

    /** Versi query dari aturan di atas: Alat::tersedia()->get() */
    public function scopeTersedia(Builder $query): Builder
    {
        return $query->where('stok', '>', 0)->where('kondisi', '!=', 'rusak berat');
    }
}
