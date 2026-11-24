<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use App\Services\PeminjamanWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PeminjamanWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** Memastikan persetujuan dan pengembalian terlambat memperbarui stok serta denda secara atomik. */
    public function test_approval_and_late_return_update_stock_and_calculate_fine_atomically(): void
    {
        $peminjam = User::factory()->create(['role' => 'peminjam']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $this->actingAs($petugas);

        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Proyektor',
            'stok' => 3,
            'kondisi' => 'baik',
        ]);
        $peminjaman = Peminjaman::create([
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => today(),
            'tanggal_kembali_rencana' => today()->addDays(2),
            'status' => 'pending',
        ]);
        DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 2,
        ]);

        $workflow = app(PeminjamanWorkflow::class);
        $workflow->setujui($peminjaman, $petugas->id);

        $this->assertSame(1, $alat->fresh()->stok);
        $this->assertSame('disetujui', $peminjaman->fresh()->status);

        $peminjaman->update(['status' => 'menunggu_kembali']);
        $denda = $workflow->prosesPengembalian($peminjaman, $petugas->id, today()->addDays(5)->toDateString());

        $this->assertSame(15000.0, $denda);
        $this->assertSame(3, $alat->fresh()->stok);
        $this->assertSame('dikembalikan', $peminjaman->fresh()->status);
        $this->assertSame(15000.0, (float) Pengembalian::firstOrFail()->denda);
        $this->assertSame(2, LogAktivitas::count());
    }

    /** Memastikan pengembalian harus diajukan peminjam dan barang baik menambah stok. */
    public function test_return_requires_borrower_request_and_good_items_restore_stock(): void
    {
        $peminjam = User::factory()->create(['role' => 'peminjam']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Laptop',
            'stok' => 0,
            'kondisi' => 'baik',
        ]);
        $peminjaman = Peminjaman::create([
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => today()->subDays(3),
            'tanggal_kembali_rencana' => today()->addDay(),
            'status' => 'disetujui',
        ]);
        $detail = DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
        ]);

        try {
            app(PeminjamanWorkflow::class)->prosesPengembalian(
                $peminjaman,
                $petugas->id,
                today()->toDateString(),
                [$detail->id => ['kondisi_kembali' => 'Baik']],
            );
            $this->fail('Pengembalian harus ditolak sebelum peminjam mengajukannya.');
        } catch (ValidationException) {
            $this->assertSame(0, $alat->fresh()->stok);
            $this->assertSame('disetujui', $peminjaman->fresh()->status);
        }

        $peminjaman->update(['status' => 'menunggu_kembali']);
        app(PeminjamanWorkflow::class)->prosesPengembalian(
            $peminjaman,
            $petugas->id,
            today()->toDateString(),
            [$detail->id => ['kondisi_kembali' => 'Baik']],
        );

        $this->assertSame(1, $alat->fresh()->stok);
        $this->assertSame('Baik', $detail->fresh()->kondisi_kembali);
        $this->assertSame('dikembalikan', $peminjaman->fresh()->status);
    }

    /** Memastikan barang rusak atau hilang tidak dikembalikan ke stok tersedia. */
    public function test_damaged_or_lost_item_does_not_restore_stock(): void
    {
        $peminjam = User::factory()->create(['role' => 'peminjam']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $kategori = Kategori::create(['nama_kategori' => 'Alat']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera',
            'stok' => 0,
            'kondisi' => 'baik',
        ]);
        $peminjaman = Peminjaman::create([
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => today()->subDays(2),
            'tanggal_kembali_rencana' => today(),
            'status' => 'menunggu_kembali',
        ]);
        $detail = DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
        ]);

        app(PeminjamanWorkflow::class)->prosesPengembalian(
            $peminjaman,
            $petugas->id,
            today()->toDateString(),
            [$detail->id => ['kondisi_kembali' => 'Hilang']],
            100000,
            'Kamera hilang saat peminjaman.',
        );

        $this->assertSame(0, $alat->fresh()->stok);
        $this->assertSame('Hilang', $detail->fresh()->kondisi_kembali);
        $this->assertSame(100000.0, (float) Pengembalian::firstOrFail()->denda);
    }

    /** Memastikan stok dan status tidak berubah ketika stok pengajuan tidak cukup. */
    public function test_approval_with_insufficient_stock_does_not_change_stock_or_status(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $this->actingAs($petugas);

        $kategori = Kategori::create(['nama_kategori' => 'Alat Praktik']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Multimeter',
            'stok' => 1,
            'kondisi' => 'baik',
        ]);
        $peminjaman = Peminjaman::create([
            'user_id' => User::factory()->create(['role' => 'peminjam'])->id,
            'tanggal_pinjam' => today(),
            'tanggal_kembali_rencana' => today()->addDay(),
            'status' => 'pending',
        ]);
        DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 2,
        ]);

        try {
            app(PeminjamanWorkflow::class)->setujui($peminjaman, $petugas->id);
            $this->fail('Persetujuan harus gagal saat stok tidak cukup.');
        } catch (ValidationException) {
            $this->assertSame(1, $alat->fresh()->stok);
            $this->assertSame('pending', $peminjaman->fresh()->status);
        }
    }

    /** Memastikan pengelolaan transaksi admin mempertahankan stok dan alur status. */
    public function test_admin_transaction_management_preserves_inventory_and_workflow_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $peminjam = User::factory()->create(['role' => 'peminjam']);
        $kategori = Kategori::create(['nama_kategori' => 'Peralatan']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera',
            'stok' => 4,
            'kondisi' => 'baik',
        ]);

        $this->actingAs($admin)->post(route('admin.transaksi.store'), [
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => today()->toDateString(),
            'tanggal_kembali_rencana' => today()->addDays(2)->toDateString(),
            'items' => [['alat_id' => $alat->id, 'jumlah' => 2]],
        ])->assertRedirect(route('admin.transaksi.index'));

        $peminjaman = Peminjaman::firstOrFail();
        $this->assertSame('pending', $peminjaman->status);
        $this->assertSame(4, $alat->fresh()->stok);

        $this->put(route('admin.transaksi.update', $peminjaman), [
            'user_id' => $peminjam->id,
            'tanggal_pinjam' => today()->toDateString(),
            'tanggal_kembali_rencana' => today()->addDays(3)->toDateString(),
            'items' => [['alat_id' => $alat->id, 'jumlah' => 3]],
        ])->assertRedirect(route('admin.transaksi.index'));

        $this->assertSame(3, $peminjaman->fresh()->detail->firstOrFail()->jumlah);
        app(PeminjamanWorkflow::class)->setujui($peminjaman->fresh(), $petugas->id);
        $this->assertSame(1, $alat->fresh()->stok);

        $this->actingAs($admin)->delete(route('admin.transaksi.destroy', $peminjaman))
            ->assertRedirect(route('admin.transaksi.index'));

        $this->assertSame(4, $alat->fresh()->stok);
        $this->assertDatabaseMissing('peminjaman', ['id' => $peminjaman->id]);
    }
}
