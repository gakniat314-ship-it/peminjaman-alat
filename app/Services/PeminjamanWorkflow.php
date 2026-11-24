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
}
