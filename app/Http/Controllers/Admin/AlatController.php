<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

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
        unset($data['gambar'], $data['hapus_gambar']);
        $imagePath = $this->storeImage($request);

        try {
            $alat = DB::transaction(fn () => Alat::create([...$data, 'image_path' => $imagePath]));
        } catch (Throwable $e) {
            $this->deleteImage($imagePath);
            throw $e;
        }

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
        $data = $this->validated($request);
        unset($data['gambar'], $data['hapus_gambar']);

        $oldImagePath = $alat->image_path;
        $newImagePath = $this->storeImage($request);
        $hapusGambar = $request->boolean('hapus_gambar') && $newImagePath === null;

        if ($newImagePath !== null) {
            $data['image_path'] = $newImagePath;
        } elseif ($hapusGambar) {
            $data['image_path'] = null;
        }

        try {
            DB::transaction(fn () => $alat->update($data));
        } catch (Throwable $e) {
            $this->deleteImage($newImagePath);
            throw $e;
        }

        if (($newImagePath !== null || $hapusGambar) && $oldImagePath !== null) {
            $this->deleteImage($oldImagePath);
        }

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
        $imagePath = $alat->image_path;
        $alat->delete();
        $this->deleteImage($imagePath);

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
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'hapus_gambar' => ['nullable', 'boolean'],
        ]);
    }

    /** Menyimpan gambar unggahan dan mengembalikan path disk publiknya. */
    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('gambar')) {
            return null;
        }

        $path = $request->file('gambar')->store('alat', 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Gambar alat gagal disimpan.');
        }

        return $path;
    }

    /** Menghapus gambar dan melaporkan kegagalan filesystem secara eksplisit. */
    private function deleteImage(?string $path): void
    {
        if ($path !== null && ! Storage::disk('public')->delete($path)) {
            throw new RuntimeException('Gambar alat gagal dihapus dari penyimpanan.');
        }
    }
}
