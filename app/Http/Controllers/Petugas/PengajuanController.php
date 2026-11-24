<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Concerns\HandlesDbErrors;
use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Services\PeminjamanWorkflow;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengajuanController extends Controller
{
    use HandlesDbErrors;

    /** Daftar pengajuan yang menunggu keputusan petugas */
    public function index(): View
    {
        $pengajuan = Peminjaman::with(['user', 'detail.alat'])
            ->where('status', 'pending')
            ->oldest() // yang paling lama mengantre tampil paling atas
            ->paginate(10);

        return view('petugas.pengajuan.index', compact('pengajuan'));
    }

    /** Menyetujui pengajuan melalui workflow dan menangani kendala basis data. */
    public function setujui(Peminjaman $peminjaman): RedirectResponse
    {
        try {
            app(PeminjamanWorkflow::class)->setujui($peminjaman, (int) auth()->id());
        } catch (QueryException $e) {
            return back()->with('error', $this->pesanDatabase($e));
        }

        return back()->with('success', "Peminjaman #{$peminjaman->id} disetujui. Stok alat otomatis berkurang.");
    }

    /** Menolak pengajuan yang masih pending dan mencatat aktivitas petugas. */
    public function tolak(Peminjaman $peminjaman): RedirectResponse
    {
        $updated = DB::transaction(function () use ($peminjaman): int {
            $updated = Peminjaman::where('id', $peminjaman->id)
                ->where('status', 'pending')
                ->update(['status' => 'ditolak', 'petugas_id' => auth()->id()]);

            if ($updated) {
                LogAktivitas::catat("Menolak peminjaman #{$peminjaman->id}");
            }

            return $updated;
        });

        if (! $updated) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        return back()->with('success', "Peminjaman #{$peminjaman->id} ditolak.");
    }
}
