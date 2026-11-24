<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Concerns\HandlesDbErrors;
use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Services\PeminjamanWorkflow;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengembalianController extends Controller
{
    use HandlesDbErrors;

    /** Memantau: alat yang sedang dipinjam + riwayat pengembalian */
    public function index(): View
    {
        // Nama halaman paginator dibedakan agar dua tabel bisa berpindah halaman sendiri-sendiri
        $dipinjam = Peminjaman::with(['user', 'detail.alat'])
            ->where('status', 'menunggu_kembali')
            ->orderBy('tanggal_kembali_rencana')              // yang jatuh tempo paling dulu
            ->paginate(10, ['*'], 'dipinjam_page');

        $riwayat = Pengembalian::with(['peminjaman.user', 'peminjaman.detail.alat', 'petugas'])
            ->latest('id')
            ->paginate(10, ['*'], 'riwayat_page');

        return view('petugas.pengembalian.index', compact('dipinjam', 'riwayat'));
    }

    /** Memvalidasi dan memproses pengembalian serta menampilkan jumlah denda. */
    public function proses(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $data = $request->validate([
            'tanggal_dikembalikan' => [
                'required',
                'date',
                'after_or_equal:'.$peminjaman->tanggal_pinjam->toDateString(),
                'before_or_equal:today',
            ],
            'detail' => ['required', 'array'],
            'detail.*.kondisi_kembali' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat,Hilang'],
            'denda_kerusakan' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $denda = app(PeminjamanWorkflow::class)->prosesPengembalian(
                $peminjaman,
                (int) auth()->id(),
                $data['tanggal_dikembalikan'],
                $data['detail'],
                (float) ($data['denda_kerusakan'] ?? 0),
                $data['catatan'] ?? null,
            );
        } catch (QueryException $e) {
            return back()->with('error', $this->pesanDatabase($e));
        }

        return back()->with('success', 'Pengembalian dicatat dan diverifikasi. Denda: Rp '.number_format($denda, 0, ',', '.')
            .'. Stok alat sudah bertambah.');
    }
}
