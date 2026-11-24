<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    /** Menentukan tabel basis data yang menyimpan kategori alat. */
    protected $table = 'kategori';

    /** Menentukan atribut kategori yang boleh diisi melalui mass assignment. */
    protected $fillable = ['nama_kategori'];

    /** Mengambil seluruh alat yang termasuk dalam kategori ini. */
    public function alat(): HasMany
    {
        return $this->hasMany(Alat::class, 'kategori_id');
    }
}
