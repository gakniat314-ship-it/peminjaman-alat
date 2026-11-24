<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\QueryException;

trait HandlesDbErrors
{
    /**
     * Ubah error database menjadi pesan yang aman ditampilkan ke user.
     */
    protected function pesanDatabase(QueryException $e): string
    {
        // 1644 = kode MySQL untuk SIGNAL SQLSTATE '45000', yaitu pesan buatan kita
        // di trigger/procedure (mis. "Stok alat tidak mencukupi"): aman ditampilkan.
        if ((int) ($e->errorInfo[1] ?? 0) === 1644) {
            return $e->errorInfo[2];
        }

        // Error lain (detail SQL) jangan bocor ke user: cukup dicatat di storage/logs
        report($e);

        return 'Terjadi kesalahan pada database. Silakan coba lagi.';
    }
}
