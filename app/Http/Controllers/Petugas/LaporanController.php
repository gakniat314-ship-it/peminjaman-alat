<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    /** Menampilkan laporan transaksi berdasarkan rentang tanggal pilihan. */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $peminjaman = Peminjaman::query()
            ->with(['user', 'petugas', 'detail.alat', 'pengembalian'])
            ->when($filter['dari'] ?? null, fn ($query, $tanggal) => $query->whereDate('tanggal_pinjam', '>=', $tanggal))
            ->when($filter['sampai'] ?? null, fn ($query, $tanggal) => $query->whereDate('tanggal_pinjam', '<=', $tanggal))
            ->latest('tanggal_pinjam')
            ->paginate(25)
            ->withQueryString();

        return view('petugas.laporan.index', compact('peminjaman', 'filter'));
    }
}
