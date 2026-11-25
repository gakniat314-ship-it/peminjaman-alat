<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesDbErrors;
use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Services\PeminjamanWorkflow;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengembalianController extends Controller
{
    use HandlesDbErrors;

    /** Menampilkan pengajuan yang menunggu serta riwayat pengembalian. */
    public function index(): View
    {
        $menunggu = Peminjaman::query()
            ->with(['user', 'detail.alat'])
            ->where('status', 'menunggu_kembali')
            ->orderBy('tanggal_kembali_rencana')
            ->paginate(10, ['*'], 'menunggu_page');

        $riwayat = Pengembalian::query()
            ->with(['peminjaman.user', 'peminjaman.detail.alat', 'petugas'])
            ->latest('id')
            ->paginate(10, ['*'], 'riwayat_page');

        return view('admin.pengembalian.index', compact('menunggu', 'riwayat'));
    }

    /** Menampilkan form pencatatan untuk peminjaman yang menunggu pengembalian. */
    public function create(Peminjaman $peminjaman): View|RedirectResponse
    {
        if ($peminjaman->status !== 'menunggu_kembali') {
            return redirect()->route('admin.pengembalian.index')
                ->with('error', 'Peminjaman ini tidak sedang menunggu pengembalian.');
        }

        $peminjaman->load(['user', 'detail.alat']);

        return view('admin.pengembalian.form', [
            'peminjaman' => $peminjaman,
            'pengembalian' => null,
        ]);
    }

    /** Memverifikasi pengembalian dan menyimpan catatannya melalui workflow transaksi. */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $peminjaman = Peminjaman::query()->findOrFail($data['peminjaman_id']);

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
            return back()->withInput()->with('error', $this->pesanDatabase($e));
        }

        return redirect()->route('admin.pengembalian.index')
            ->with('success', 'Pengembalian dicatat. Denda: Rp '.number_format($denda, 0, ',', '.').'.');
    }

    /** Menampilkan form pengubahan pengembalian yang telah selesai. */
    public function edit(Pengembalian $pengembalian): View|RedirectResponse
    {
        $pengembalian->load(['peminjaman.user', 'peminjaman.detail.alat']);

        if ($pengembalian->peminjaman->status !== 'dikembalikan') {
            return redirect()->route('admin.pengembalian.index')
                ->with('error', 'Hanya pengembalian selesai yang dapat diubah.');
        }

        return view('admin.pengembalian.form', [
            'peminjaman' => $pengembalian->peminjaman,
            'pengembalian' => $pengembalian,
        ]);
    }

    /** Memperbarui tanggal, kondisi, dan denda dengan rekonsiliasi stok. */
    public function update(Request $request, Pengembalian $pengembalian): RedirectResponse
    {
        $pengembalian->load('peminjaman');
        $data = $this->validated($request, $pengembalian->peminjaman);

        try {
            $denda = app(PeminjamanWorkflow::class)->perbaruiPengembalian(
                $pengembalian,
                $data['tanggal_dikembalikan'],
                $data['detail'],
                (float) ($data['denda_kerusakan'] ?? 0),
                $data['catatan'] ?? null,
            );
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->pesanDatabase($e));
        }

        return redirect()->route('admin.pengembalian.index')
            ->with('success', 'Data pengembalian diperbarui. Denda: Rp '.number_format($denda, 0, ',', '.').'.');
    }

    /** Menghapus pengembalian selesai dan mengembalikan transaksi ke antrean verifikasi. */
    public function destroy(Pengembalian $pengembalian): RedirectResponse
    {
        try {
            app(PeminjamanWorkflow::class)->hapusPengembalian($pengembalian);
        } catch (QueryException $e) {
            return back()->with('error', $this->pesanDatabase($e));
        }

        return redirect()->route('admin.pengembalian.index')
            ->with('success', 'Pengembalian dihapus dan peminjaman dikembalikan ke antrean verifikasi.');
    }

    /** Memvalidasi form pembuatan dan pengubahan catatan pengembalian. */
    private function validated(Request $request, ?Peminjaman $peminjaman = null): array
    {
        $tanggalPinjam = $peminjaman?->tanggal_pinjam;
        if ($tanggalPinjam === null) {
            $tanggalPinjam = Peminjaman::query()->whereKey($request->input('peminjaman_id'))->value('tanggal_pinjam');
            $tanggalPinjam = $tanggalPinjam === null ? today() : Carbon::parse($tanggalPinjam);
        }

        $rules = [
            'tanggal_dikembalikan' => [
                'required',
                'date',
                'after_or_equal:'.$tanggalPinjam->toDateString(),
                'before_or_equal:today',
            ],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.kondisi_kembali' => ['required', Rule::in(['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'])],
            'denda_kerusakan' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];

        if ($peminjaman === null) {
            $rules['peminjaman_id'] = [
                'required',
                Rule::exists('peminjaman', 'id')->where('status', 'menunggu_kembali'),
            ];
        }

        return $request->validate($rules);
    }
}
