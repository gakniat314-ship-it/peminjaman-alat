<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlatController extends Controller
{
    /** Menampilkan daftar alat dengan pencarian, pengurutan, dan paginasi. */
    public function index(Request $request): View
    {
        $alat = Alat::query()
            ->with('kategori')
            ->when($request->filled('q'), fn ($query) => $query->where('nama_alat', 'like', '%'.$request->string('q').'%'))
            ->orderBy('nama_alat')
            ->paginate(15)
            ->withQueryString();

        return view('admin.alat.index', compact('alat'));
    }

    /** Menampilkan formulir kosong untuk menambahkan alat baru. */
    public function create(): View
    {
        return view('admin.alat.form', ['alat' => new Alat, 'kategori' => Kategori::orderBy('nama_kategori')->get()]);
    }

    /** Memvalidasi lalu menyimpan alat baru dan mencatat aktivitasnya. */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $alat = Alat::create($data);
        LogAktivitas::catat("Menambahkan alat #{$alat->id} ({$alat->nama_alat})");

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil ditambahkan.');
    }

    /** Menampilkan formulir berisi data alat yang akan diperbarui. */
    public function edit(Alat $alat): View
    {
        return view('admin.alat.form', ['alat' => $alat, 'kategori' => Kategori::orderBy('nama_kategori')->get()]);
    }

    /** Memvalidasi dan menyimpan perubahan data alat. */
    public function update(Request $request, Alat $alat): RedirectResponse
    {
        $alat->update($this->validated($request));
        LogAktivitas::catat("Memperbarui alat #{$alat->id} ({$alat->nama_alat})");

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    /** Menghapus alat jika tidak digunakan dalam riwayat peminjaman. */
    public function destroy(Alat $alat): RedirectResponse
    {
        if ($alat->detailPeminjaman()->exists()) {
            return back()->with('error', 'Alat yang tercatat dalam riwayat peminjaman tidak dapat dihapus.');
        }

        LogAktivitas::catat("Menghapus alat #{$alat->id} ({$alat->nama_alat})");
        $alat->delete();

        return back()->with('success', 'Data alat berhasil dihapus.');
    }

    /** Memvalidasi data alat sebelum dibuat atau diperbarui. */
    private function validated(Request $request): array
    {
        return $request->validate([
            'kategori_id' => ['required', 'exists:kategori,id'],
            'nama_alat' => ['required', 'string', 'max:255'],
            'stok' => ['required', 'integer', 'min:0'],
            'kondisi' => ['required', 'string', Rule::in(['baik', 'rusak ringan', 'rusak berat'])],
        ]);
    }
}
