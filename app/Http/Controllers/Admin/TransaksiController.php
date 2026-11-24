<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    /** Menampilkan daftar transaksi dengan filter status. */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'disetujui', 'menunggu_kembali', 'ditolak', 'dikembalikan'])],
        ]);

        $peminjaman = Peminjaman::query()
            ->with(['user', 'petugas', 'detail.alat', 'pengembalian'])
            ->when($filter['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.transaksi.index', compact('peminjaman'));
    }

    /** Menampilkan formulir transaksi baru. */
    public function create(): View
    {
        return $this->form(new Peminjaman);
    }

    /** Membuat pengajuan peminjaman beserta rincian alat secara atomik. */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->ensureStockAvailable($data['items']);

        $peminjaman = DB::transaction(function () use ($data): Peminjaman {
            $peminjaman = Peminjaman::create([
                'user_id' => $data['user_id'],
                'tanggal_pinjam' => $data['tanggal_pinjam'],
                'tanggal_kembali_rencana' => $data['tanggal_kembali_rencana'],
                'status' => 'pending',
            ]);
            $peminjaman->detail()->createMany($this->details($data['items']));
            LogAktivitas::catat("Admin membuat pengajuan peminjaman #{$peminjaman->id}");

            return $peminjaman;
        });

        return redirect()->route('admin.transaksi.index')
            ->with('success', "Pengajuan #{$peminjaman->id} dibuat dengan status pending.");
    }

    /** Menampilkan transaksi pending untuk diedit atau kembali ke daftar. */
    public function edit(Peminjaman $transaksi): View|RedirectResponse
    {
        if ($transaksi->status !== 'pending') {
            return redirect()->route('admin.transaksi.index')
                ->with('error', 'Hanya pengajuan berstatus pending yang dapat diubah.');
        }

        $transaksi->load('detail');

        return $this->form($transaksi);
    }

    /** Memperbarui data dan rincian transaksi yang masih pending. */
    public function update(Request $request, Peminjaman $transaksi): RedirectResponse
    {
        if ($transaksi->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan berstatus pending yang dapat diubah.');
        }

        $data = $this->validated($request);
        $this->ensureStockAvailable($data['items']);

        DB::transaction(function () use ($data, $transaksi): void {
            $peminjaman = Peminjaman::query()->lockForUpdate()->findOrFail($transaksi->id);

            if ($peminjaman->status !== 'pending') {
                throw ValidationException::withMessages(['transaksi' => 'Pengajuan sudah diproses dan tidak dapat diubah.']);
            }

            $peminjaman->update([
                'user_id' => $data['user_id'],
                'tanggal_pinjam' => $data['tanggal_pinjam'],
                'tanggal_kembali_rencana' => $data['tanggal_kembali_rencana'],
            ]);
            $peminjaman->detail()->delete();
            $peminjaman->detail()->createMany($this->details($data['items']));
            LogAktivitas::catat("Admin memperbarui pengajuan peminjaman #{$peminjaman->id}");
        });

        return redirect()->route('admin.transaksi.index')->with('success', 'Pengajuan berhasil diperbarui.');
    }

    /** Menghapus transaksi serta menyesuaikan stok dan catatan terkait. */
    public function destroy(Peminjaman $transaksi): RedirectResponse
    {
        DB::transaction(function () use ($transaksi): void {
            $peminjaman = Peminjaman::query()->lockForUpdate()->findOrFail($transaksi->id);

            if (in_array($peminjaman->status, ['disetujui', 'menunggu_kembali'], true)) {
                foreach ($peminjaman->detail as $detail) {
                    Alat::query()->whereKey($detail->alat_id)->increment('stok', $detail->jumlah);
                }
            }

            LogAktivitas::catat("Admin menghapus transaksi peminjaman #{$peminjaman->id}");
            Pengembalian::query()->where('peminjaman_id', $peminjaman->id)->delete();
            $peminjaman->delete();
        });

        return redirect()->route('admin.transaksi.index')->with('success', 'Transaksi dihapus dan stok disesuaikan.');
    }

    /** Menyediakan data transaksi, peminjam, dan alat untuk formulir. */
    private function form(Peminjaman $peminjaman): View
    {
        return view('admin.transaksi.form', [
            'peminjaman' => $peminjaman,
            'peminjam' => User::query()->where('role', 'peminjam')->orderBy('name')->get(),
            'alat' => Alat::query()->with('kategori')->orderBy('nama_alat')->get(),
        ]);
    }

    /** Memvalidasi data utama dan daftar alat pada transaksi. */
    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'peminjam')],
            'tanggal_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_kembali_rencana' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.alat_id' => ['required', 'distinct', 'exists:alat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);
    }

    /** Memastikan semua alat tersedia dan stok mencukupi sebelum transaksi. */
    private function ensureStockAvailable(array $items): void
    {
        $alat = Alat::query()->whereIn('id', collect($items)->pluck('alat_id'))->get()->keyBy('id');

        foreach ($items as $index => $item) {
            $barang = $alat[$item['alat_id']];

            if (! $barang->bisaDipinjam() || $item['jumlah'] > $barang->stok) {
                throw ValidationException::withMessages([
                    "items.{$index}.jumlah" => "Stok {$barang->nama_alat} tidak mencukupi.",
                ]);
            }
        }
    }

    /** Mengubah pilihan alat menjadi bentuk data rincian peminjaman. */
    private function details(array $items): array
    {
        return collect($items)->map(fn ($item) => [
            'alat_id' => $item['alat_id'],
            'jumlah' => $item['jumlah'],
        ])->all();
    }
}
