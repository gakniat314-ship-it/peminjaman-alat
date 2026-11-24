<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    /** Menampilkan katalog alat dengan pencarian dan paginasi. */
    public function index(Request $request): View
    {
        $alat = Alat::with('kategori')
            // when(): filter hanya dipasang jika kolom pencarian diisi
            ->when($request->filled('q'), fn ($query) => $query->where('nama_alat', 'like', '%' . $request->q . '%'))
            ->orderBy('nama_alat')
            ->paginate(9)
            ->withQueryString(); // agar kata kunci tidak hilang saat pindah halaman

        return view('peminjam.katalog', compact('alat'));
    }
}
