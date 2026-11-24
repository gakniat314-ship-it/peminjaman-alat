<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    /** Menentukan tabel basis data untuk catatan aktivitas pengguna. */
    protected $table = 'log_aktivitas';

    /** Menonaktifkan timestamps otomatis karena waktu catatan diatur oleh basis data. */
    public $timestamps = false;

    /** Menentukan atribut catatan aktivitas yang boleh diisi secara massal. */
    protected $fillable = ['user_id', 'aktivitas'];

    /** Mengubah nilai waktu catatan menjadi objek tanggal saat dibaca. */
    protected $casts = ['created_at' => 'datetime'];

    /** Mencatat aktivitas beserta identitas pengguna yang sedang login. */
    public static function catat(string $aktivitas): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'aktivitas' => $aktivitas,
        ]);
    }

    /** Mengambil pengguna yang melakukan aktivitas ini. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
