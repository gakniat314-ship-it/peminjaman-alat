<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /** Menampilkan kategori beserta jumlah alat dan pencarian opsional. */
    public function index(Request $request): View
    {
        $kategori = Kategori::query()
            ->withCount('alat')
            ->when($request->filled('q'), fn ($query) => $query->where('nama_kategori', 'like', '%'.$request->string('q').'%'))
            ->orderBy('nama_kategori')
            ->paginate(15)
            ->withQueryString();

        return view('admin.kategori.index', compact('kategori'));
    }

    /** Menampilkan formulir kosong untuk membuat kategori. */
    public function create(): View
    {
        return view('admin.kategori.form', ['kategori' => new Kategori]);
    }

    /** Memvalidasi dan menyimpan kategori baru serta mencatat aktivitasnya. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['nama_kategori' => ['required', 'string', 'max:255', 'unique:kategori,nama_kategori']]);
        $kategori = Kategori::create($data);
        LogAktivitas::catat("Menambahkan kategori #{$kategori->id} ({$kategori->nama_kategori})");

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    /** Menampilkan formulir berisi kategori yang akan diperbarui. */
    public function edit(Kategori $kategori): View
    {
        return view('admin.kategori.form', compact('kategori'));
    }

    /** Memvalidasi dan menyimpan perubahan nama kategori. */
    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('kategori')->ignore($kategori->id)],
        ]);
        $kategori->update($data);
        LogAktivitas::catat("Memperbarui kategori #{$kategori->id} ({$kategori->nama_kategori})");

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /** Menghapus kategori hanya jika tidak ada alat yang menggunakannya. */
    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->alat()->exists()) {
            return back()->with('error', 'Kategori yang masih memiliki alat tidak dapat dihapus.');
        }

        LogAktivitas::catat("Menghapus kategori #{$kategori->id} ({$kategori->nama_kategori})");
        $kategori->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
