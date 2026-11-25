<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_confirmation_input_is_visible_on_admin_user_forms(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'peminjam']);

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Konfirmasi password')
            ->assertSee('id="password_confirmation" name="password_confirmation" type="password"', false);

        $this->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('id="password_confirmation" name="password_confirmation" type="password"', false);
    }

    public function test_admin_can_create_update_and_delete_returns_while_reconciling_inventory(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $borrower = User::factory()->create(['role' => 'peminjam']);
        $category = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'kategori_id' => $category->id,
            'nama_alat' => 'Kamera',
            'stok' => 0,
            'kondisi' => 'baik',
        ]);
        $peminjaman = Peminjaman::create([
            'user_id' => $borrower->id,
            'petugas_id' => $admin->id,
            'tanggal_pinjam' => today()->subDays(3),
            'tanggal_kembali_rencana' => today(),
            'status' => 'menunggu_kembali',
        ]);
        $detail = DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pengembalian.create', $peminjaman))
            ->assertOk()
            ->assertSee('Catat pengembalian');

        $this->post(route('admin.pengembalian.store'), [
            'peminjaman_id' => $peminjaman->id,
            'tanggal_dikembalikan' => today()->toDateString(),
            'detail' => [$detail->id => ['kondisi_kembali' => 'Baik']],
            'denda_kerusakan' => 0,
        ])->assertRedirect(route('admin.pengembalian.index'));

        $pengembalian = Pengembalian::firstOrFail();
        $this->get(route('admin.pengembalian.edit', $pengembalian))
            ->assertOk()
            ->assertSee('Ubah pengembalian');
        $this->assertSame('dikembalikan', $peminjaman->fresh()->status);
        $this->assertSame(1, $alat->fresh()->stok);

        $this->put(route('admin.pengembalian.update', $pengembalian), [
            'tanggal_dikembalikan' => today()->toDateString(),
            'detail' => [$detail->id => ['kondisi_kembali' => 'Rusak Ringan']],
            'denda_kerusakan' => 1000,
            'catatan' => 'Lensa mengalami kerusakan ringan.',
        ])->assertRedirect(route('admin.pengembalian.index'));

        $this->assertSame(0, $alat->fresh()->stok);
        $this->assertSame('Rusak Ringan', $detail->fresh()->kondisi_kembali);
        $this->assertSame(1000.0, (float) $pengembalian->fresh()->denda);

        $this->put(route('admin.pengembalian.update', $pengembalian), [
            'tanggal_dikembalikan' => today()->toDateString(),
            'detail' => [$detail->id => ['kondisi_kembali' => 'Baik']],
            'denda_kerusakan' => 0,
        ])->assertRedirect(route('admin.pengembalian.index'));
        $this->assertSame(1, $alat->fresh()->stok);

        $this->delete(route('admin.pengembalian.destroy', $pengembalian))
            ->assertRedirect(route('admin.pengembalian.index'));

        $this->assertDatabaseMissing('pengembalian', ['id' => $pengembalian->id]);
        $this->assertSame('menunggu_kembali', $peminjaman->fresh()->status);
        $this->assertNull($detail->fresh()->kondisi_kembali);
        $this->assertSame(0, $alat->fresh()->stok);
    }

    public function test_admin_cannot_delete_a_return_if_reversing_it_would_make_stock_negative(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $borrower = User::factory()->create(['role' => 'peminjam']);
        $category = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'kategori_id' => $category->id,
            'nama_alat' => 'Laptop',
            'stok' => 0,
            'kondisi' => 'baik',
        ]);
        $peminjaman = Peminjaman::create([
            'user_id' => $borrower->id,
            'petugas_id' => $admin->id,
            'tanggal_pinjam' => today()->subDays(3),
            'tanggal_kembali_rencana' => today(),
            'status' => 'dikembalikan',
        ]);
        $detail = DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
            'kondisi_kembali' => 'Baik',
        ]);
        $pengembalian = Pengembalian::create([
            'peminjaman_id' => $peminjaman->id,
            'tanggal_dikembalikan' => today(),
            'denda' => 0,
            'denda_kerusakan' => 0,
            'petugas_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.pengembalian.destroy', $pengembalian))
            ->assertSessionHasErrors('pengembalian');

        $this->assertDatabaseHas('pengembalian', ['id' => $pengembalian->id]);
        $this->assertSame('dikembalikan', $peminjaman->fresh()->status);
        $this->assertSame('Baik', $detail->fresh()->kondisi_kembali);
        $this->assertSame(0, $alat->fresh()->stok);
    }

    public function test_admin_can_upload_replace_and_remove_equipment_images_displayed_in_views(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $borrower = User::factory()->create(['role' => 'peminjam']);
        $category = Kategori::create(['nama_kategori' => 'Peralatan']);
        $dataAlat = [
            'kategori_id' => $category->id,
            'nama_alat' => 'Proyektor',
            'stok' => 2,
            'kondisi' => 'baik',
        ];

        $this->actingAs($admin)->post(route('admin.alat.store'), [
            ...$dataAlat,
            'gambar' => UploadedFile::fake()->create('proyektor.png', 10, 'image/png'),
        ])->assertRedirect(route('admin.alat.index'));

        $alat = Alat::firstOrFail();
        $oldPath = $alat->image_path;
        $this->assertNotNull($oldPath);
        Storage::disk('public')->assertExists($oldPath);

        $this->get(route('admin.alat.index'))->assertSee(Storage::disk('public')->url($oldPath), false);
        $this->actingAs($borrower)
            ->get(route('peminjam.katalog'))
            ->assertSee(Storage::disk('public')->url($oldPath), false);

        $this->actingAs($admin)->put(route('admin.alat.update', $alat), [
            ...$dataAlat,
            'gambar' => UploadedFile::fake()->create('proyektor.webp', 10, 'image/webp'),
        ])->assertRedirect(route('admin.alat.index'));

        $newPath = $alat->fresh()->image_path;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);

        $this->put(route('admin.alat.update', $alat), [
            ...$dataAlat,
            'hapus_gambar' => 1,
        ])->assertRedirect(route('admin.alat.index'));

        $this->assertNull($alat->fresh()->image_path);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_equipment_image_upload_accepts_two_megabytes_and_rejects_larger_files(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $category = Kategori::create(['nama_kategori' => 'Peralatan']);
        $dataAlat = [
            'kategori_id' => $category->id,
            'nama_alat' => 'Alat Batas',
            'stok' => 1,
            'kondisi' => 'baik',
        ];

        $this->actingAs($admin)->post(route('admin.alat.store'), [
            ...$dataAlat,
            'gambar' => UploadedFile::fake()->create('batas.png', 2048, 'image/png'),
        ])->assertRedirect(route('admin.alat.index'))
            ->assertSessionHasNoErrors();

        $this->post(route('admin.alat.store'), [
            ...$dataAlat,
            'nama_alat' => 'Alat Terlalu Besar',
            'gambar' => UploadedFile::fake()->create('besar.png', 2049, 'image/png'),
        ])->assertSessionHasErrors('gambar');

        $this->assertDatabaseCount('alat', 1);
    }

    public function test_admin_transaction_navigation_contains_borrowing_and_return_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.pengembalian.index'))
            ->assertOk()
            ->assertSee('Peminjaman')
            ->assertSee('Pengembalian')
            ->assertSee(route('admin.transaksi.index'), false)
            ->assertSee(route('admin.pengembalian.index'), false);
    }
}
