<?php

namespace App\Services;

use App\Models\Alat;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanWorkflow
{
    /** Menyetujui transaksi dan mengurangi stok alat dalam satu proses atomik. */
    public function setujui(Peminjaman $peminjaman, int $petugasId): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('CALL sp_setujui_peminjaman(?, ?)', [$peminjaman->id, $petugasId]);

            return;
        }

        DB::transaction(function () use ($peminjaman, $petugasId): void {
            $peminjaman = Peminjaman::query()->lockForUpdate()->findOrFail($peminjaman->id);

            if ($peminjaman->status !== 'pending') {
                throw ValidationException::withMessages(['peminjaman' => 'Pengajuan ini sudah diproses sebelumnya.']);
            }

            foreach ($peminjaman->detail as $detail) {
                $alat = Alat::query()->lockForUpdate()->findOrFail($detail->alat_id);

                if (! $alat->bisaDipinjam() || $alat->stok < $detail->jumlah) {
                    throw ValidationException::withMessages(['peminjaman' => "Stok {$alat->nama_alat} tidak mencukupi."]);
                }

                $alat->decrement('stok', $detail->jumlah);
            }

            $peminjaman->update(['status' => 'disetujui', 'petugas_id' => $petugasId]);
            LogAktivitas::catat("Menyetujui peminjaman #{$peminjaman->id}");
        });
    }

    /** Mencatat kondisi barang, denda, stok, dan status saat pengembalian diproses. */
    public function prosesPengembalian(
        Peminjaman $peminjaman,
        int $petugasId,
        string $tanggal,
        array $kondisiKembali = [],
        float $dendaKerusakan = 0,
        ?string $catatan = null,
    ): float {
        return DB::transaction(function () use (
            $peminjaman,
            $petugasId,
            $tanggal,
            $kondisiKembali,
            $dendaKerusakan,
            $catatan,
        ): float {
            $peminjaman = Peminjaman::query()->lockForUpdate()->findOrFail($peminjaman->id);

            if ($peminjaman->status !== 'menunggu_kembali') {
                throw ValidationException::withMessages(['peminjaman' => 'Peminjaman ini belum diajukan pengembaliannya.']);
            }

            $peminjaman->load('detail');
            if ($kondisiKembali === []) {
                $kondisiKembali = $peminjaman->detail
                    ->mapWithKeys(fn ($detail) => [$detail->id => ['kondisi_kembali' => 'Baik']])
                    ->all();
            }
            $idDetailValid = $peminjaman->detail->modelKeys();
            $idDetailDikirim = array_map('intval', array_keys($kondisiKembali));

            if (count($idDetailDikirim) !== count($idDetailValid)
                || array_diff($idDetailDikirim, $idDetailValid)) {
                throw ValidationException::withMessages([
                    'detail' => 'Data kondisi barang tidak sesuai dengan peminjaman ini.',
                ]);
            }

            $adaMasalah = false;
            foreach ($peminjaman->detail as $detail) {
                $kondisi = $kondisiKembali[$detail->id]['kondisi_kembali']
                    ?? $kondisiKembali[$detail->id]
                    ?? null;

                if (! in_array($kondisi, ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'], true)) {
                    throw ValidationException::withMessages([
                        'detail' => 'Pilih kondisi untuk setiap barang yang dikembalikan.',
                    ]);
                }

                $detail->update(['kondisi_kembali' => $kondisi]);
                $adaMasalah = $adaMasalah || $kondisi !== 'Baik';
            }

            if (! $adaMasalah && $dendaKerusakan > 0) {
                throw ValidationException::withMessages([
                    'denda_kerusakan' => 'Denda kerusakan hanya dapat dicatat jika ada barang rusak atau hilang.',
                ]);
            }
            if ($dendaKerusakan > 0 && blank($catatan)) {
                throw ValidationException::withMessages([
                    'catatan' => 'Jelaskan alasan denda kerusakan atau kehilangan.',
                ]);
            }

            $hariTerlambat = max(0, (int) Carbon::parse($peminjaman->tanggal_kembali_rencana)
                ->diffInDays(Carbon::parse($tanggal), false));
            $dendaKeterlambatan = $hariTerlambat * 5000;
            $denda = $dendaKeterlambatan + $dendaKerusakan;

            $pengembalian = Pengembalian::query()->create([
                'peminjaman_id' => $peminjaman->id,
                'tanggal_dikembalikan' => $tanggal,
                'denda' => DB::connection()->getDriverName() === 'mysql'
                    ? $dendaKerusakan
                    : $denda,
                'denda_kerusakan' => $dendaKerusakan,
                'catatan' => $catatan,
                'petugas_id' => $petugasId,
            ]);

            if (DB::connection()->getDriverName() !== 'mysql') {
                foreach ($peminjaman->detail as $detail) {
                    if ($detail->kondisi_kembali === 'Baik') {
                        Alat::query()->whereKey($detail->alat_id)->increment('stok', $detail->jumlah);
                    }
                }
            }

            $peminjaman->update(['status' => 'dikembalikan']);
            LogAktivitas::catat("Memproses pengembalian peminjaman #{$peminjaman->id}");

            return (float) $pengembalian->fresh()->denda;
        });
    }

    /** Memperbarui pengembalian selesai sambil menyesuaikan stok untuk perubahan kondisi. */
    public function perbaruiPengembalian(
        Pengembalian $pengembalian,
        string $tanggal,
        array $kondisiKembali,
        float $dendaKerusakan,
        ?string $catatan = null,
    ): float {
        return DB::transaction(function () use (
            $pengembalian,
            $tanggal,
            $kondisiKembali,
            $dendaKerusakan,
            $catatan,
        ): float {
            $pengembalian = Pengembalian::query()->lockForUpdate()->findOrFail($pengembalian->id);
            $peminjaman = Peminjaman::query()->lockForUpdate()->findOrFail($pengembalian->peminjaman_id);

            if ($peminjaman->status !== 'dikembalikan') {
                throw ValidationException::withMessages([
                    'pengembalian' => 'Catatan ini tidak terhubung dengan peminjaman berstatus dikembalikan.',
                ]);
            }

            $peminjaman->load('detail');
            $idDetailValid = $peminjaman->detail->modelKeys();
            $idDetailDikirim = array_map('intval', array_keys($kondisiKembali));

            if (count($idDetailDikirim) !== count($idDetailValid)
                || array_diff($idDetailDikirim, $idDetailValid)) {
                throw ValidationException::withMessages([
                    'detail' => 'Data kondisi barang tidak sesuai dengan peminjaman ini.',
                ]);
            }

            $adaMasalah = false;
            foreach ($peminjaman->detail as $detail) {
                $kondisi = $kondisiKembali[$detail->id]['kondisi_kembali'] ?? null;

                if (! in_array($kondisi, ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'], true)) {
                    throw ValidationException::withMessages([
                        'detail' => 'Pilih kondisi untuk setiap barang yang dikembalikan.',
                    ]);
                }

                $stokSebelumnyaTersedia = $detail->kondisi_kembali === 'Baik';
                $stokBaruTersedia = $kondisi === 'Baik';

                if ($stokSebelumnyaTersedia !== $stokBaruTersedia) {
                    $alat = Alat::query()->lockForUpdate()->findOrFail($detail->alat_id);

                    if ($stokSebelumnyaTersedia) {
                        if ($alat->stok < $detail->jumlah) {
                            throw ValidationException::withMessages([
                                'detail' => "Stok {$alat->nama_alat} tidak cukup untuk mengubah kondisi pengembalian.",
                            ]);
                        }

                        $alat->decrement('stok', $detail->jumlah);
                    } else {
                        $alat->increment('stok', $detail->jumlah);
                    }
                }

                $detail->update(['kondisi_kembali' => $kondisi]);
                $adaMasalah = $adaMasalah || ! $stokBaruTersedia;
            }

            if (! $adaMasalah && $dendaKerusakan > 0) {
                throw ValidationException::withMessages([
                    'denda_kerusakan' => 'Denda kerusakan hanya dapat dicatat jika ada barang rusak atau hilang.',
                ]);
            }
            if ($dendaKerusakan > 0 && blank($catatan)) {
                throw ValidationException::withMessages([
                    'catatan' => 'Jelaskan alasan denda kerusakan atau kehilangan.',
                ]);
            }

            $hariTerlambat = max(0, (int) Carbon::parse($peminjaman->tanggal_kembali_rencana)
                ->diffInDays(Carbon::parse($tanggal), false));
            $denda = ($hariTerlambat * 5000) + $dendaKerusakan;

            $pengembalian->update([
                'tanggal_dikembalikan' => $tanggal,
                'denda' => $denda,
                'denda_kerusakan' => $dendaKerusakan,
                'catatan' => $catatan,
            ]);
            LogAktivitas::catat("Admin memperbarui pengembalian peminjaman #{$peminjaman->id}");

            return (float) $pengembalian->fresh()->denda;
        });
    }

    /** Menghapus pengembalian selesai dan mengembalikan transaksi ke status menunggu. */
    public function hapusPengembalian(Pengembalian $pengembalian): void
    {
        DB::transaction(function () use ($pengembalian): void {
            $pengembalian = Pengembalian::query()->lockForUpdate()->findOrFail($pengembalian->id);
            $peminjaman = Peminjaman::query()->lockForUpdate()->findOrFail($pengembalian->peminjaman_id);

            if ($peminjaman->status !== 'dikembalikan') {
                throw ValidationException::withMessages([
                    'pengembalian' => 'Hanya pengembalian yang sudah selesai yang dapat dihapus.',
                ]);
            }

            $peminjaman->load('detail');
            foreach ($peminjaman->detail as $detail) {
                if ($detail->kondisi_kembali === 'Baik') {
                    $alat = Alat::query()->lockForUpdate()->findOrFail($detail->alat_id);

                    if ($alat->stok < $detail->jumlah) {
                        throw ValidationException::withMessages([
                            'pengembalian' => "Stok {$alat->nama_alat} tidak cukup untuk membatalkan pengembalian.",
                        ]);
                    }

                    $alat->decrement('stok', $detail->jumlah);
                }

                $detail->update(['kondisi_kembali' => null]);
            }

            $peminjaman->update(['status' => 'menunggu_kembali']);
            LogAktivitas::catat("Admin menghapus pengembalian peminjaman #{$peminjaman->id}");
            $pengembalian->delete();
        });
    }
}
