<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /** Riwayat peminjaman milik user yang sedang login */
    public function index(): View
    {
        $peminjaman = Peminjaman::where('user_id', auth()->id())
            ->with(['detail.alat', 'pengembalian'])
            ->latest()
            ->paginate(10);

        return view('peminjam.peminjaman.index', compact('peminjaman'));
    }

    /** Menyediakan alat tersedia dan pilihan awal untuk formulir pengajuan. */
    public function create(Request $request): View
    {
        return view('peminjam.peminjaman.create', [
            'alat' => Alat::tersedia()->orderBy('nama_alat')->get(),
            'alatDipilih' => $request->query('alat'), // dari tombol "Pinjam" di katalog
        ]);
    }

    /** Memvalidasi stok lalu menyimpan pengajuan beserta detail alat. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_kembali_rencana' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.alat_id' => ['required', 'distinct', 'exists:alat,id'], // distinct: tidak boleh alat sama 2x
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);

        // Ambil semua alat yang diminta dalam satu query, diindeks berdasarkan id
        $alat = Alat::whereIn('id', collect($data['items'])->pluck('alat_id'))->get()->keyBy('id');

        // Cek awal supaya user langsung dapat pesan jelas.
        // Pemeriksaan final tetap dilakukan trigger database saat persetujuan.
        foreach ($data['items'] as $i => $item) {
            $a = $alat[$item['alat_id']];

            if (! $a->bisaDipinjam()) {
                throw ValidationException::withMessages(["items.$i.alat_id" => "{$a->nama_alat} sedang tidak tersedia."]);
            }
            if ($item['jumlah'] > $a->stok) {
                throw ValidationException::withMessages(["items.$i.jumlah" => "Jumlah {$a->nama_alat} melebihi stok ({$a->stok})."]);
            }
        }

        // Transaction: header dan detail harus tersimpan bersamaan, atau tidak sama sekali
        DB::transaction(function () use ($data) {
            $peminjaman = Peminjaman::create([
                'user_id' => auth()->id(),
                'tanggal_pinjam' => $data['tanggal_pinjam'],
                'tanggal_kembali_rencana' => $data['tanggal_kembali_rencana'],
                'status' => 'pending',
            ]);

            $peminjaman->detail()->createMany(
                collect($data['items'])->map(fn ($item) => [
                    'alat_id' => $item['alat_id'],
                    'jumlah' => $item['jumlah'],
                ])->all()
            );

            LogAktivitas::catat("Mengajukan peminjaman #{$peminjaman->id}");
        });

        return redirect()->route('peminjam.peminjaman.index')
            ->with('success', 'Pengajuan terkirim dengan status pending. Menunggu persetujuan petugas.');
    }

    /** Peminjam menandai bahwa ia siap mengembalikan alat; petugas yang memproses */
    public function kembali(Peminjaman $peminjaman): RedirectResponse
    {
        // Cegah peminjam mengutak-atik data milik orang lain lewat URL
        abort_unless($peminjaman->user_id === auth()->id(), 403);

        // Update bersyarat: hanya berhasil jika status MASIH 'disetujui' (aman dari klik ganda)
        $updated = DB::transaction(function () use ($peminjaman): int {
            $updated = Peminjaman::where('id', $peminjaman->id)
                ->where('status', 'disetujui')
                ->update(['status' => 'menunggu_kembali']);

            if ($updated) {
                LogAktivitas::catat("Mengajukan pengembalian peminjaman #{$peminjaman->id}");
            }

            return $updated;
        });

        if (! $updated) {
            return back()->with('error', 'Peminjaman ini tidak dapat diajukan pengembaliannya.');
        }

        return back()->with('success', 'Pengajuan pengembalian terkirim. Serahkan alat kepada petugas.');
    }
}
