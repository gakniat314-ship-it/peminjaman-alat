<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /** Memastikan peminjam tidak dapat membuka modul admin maupun petugas. */
    public function test_peminjam_cannot_open_admin_or_petugas_routes(): void
    {
        $peminjam = User::factory()->create(['role' => 'peminjam']);

        $this->actingAs($peminjam)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($peminjam)
            ->get(route('petugas.pengajuan.index'))
            ->assertForbidden();
    }

    /** Memastikan admin dan petugas hanya dapat mengakses modul sesuai role. */
    public function test_admin_and_petugas_can_open_only_their_own_modules(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $petugas = User::factory()->create(['role' => 'petugas']);

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.users.create'))->assertOk();
        $this->get(route('admin.kategori.index'))->assertOk();
        $this->get(route('admin.kategori.create'))->assertOk();
        $this->get(route('admin.alat.index'))->assertOk();
        $this->get(route('admin.alat.create'))->assertOk();
        $this->get(route('admin.transaksi.index'))->assertOk();
        $this->get(route('admin.transaksi.create'))->assertOk();
        $this->get(route('admin.log-aktivitas.index'))->assertOk();
        $this->actingAs($admin)->get(route('petugas.pengajuan.index'))->assertForbidden();
        $this->actingAs($petugas)->get(route('petugas.pengajuan.index'))->assertOk();
        $this->get(route('petugas.laporan.index'))->assertOk();
        $this->actingAs($petugas)->get(route('admin.users.index'))->assertForbidden();
    }

    /** Memastikan pendaftaran publik tidak dapat menetapkan role istimewa. */
    public function test_public_registration_cannot_assign_a_privileged_role(): void
    {
        $this->post('/register', [
            'name' => 'New Borrower',
            'username' => 'newborrower',
            'email' => 'borrower@example.test',
            'role' => 'admin',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'username' => 'newborrower',
            'role' => 'peminjam',
        ]);
    }

    /** Memastikan admin dapat membuat data utama dan aktivitasnya tercatat. */
    public function test_admin_can_create_master_data_and_user_records_with_activity_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('admin.kategori.store'), ['nama_kategori' => 'Elektronik'])
            ->assertRedirect(route('admin.kategori.index'));
        $kategori = Kategori::firstOrFail();

        $this->post(route('admin.alat.store'), [
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Proyektor',
            'stok' => 3,
            'kondisi' => 'baik',
        ])->assertRedirect(route('admin.alat.index'));

        $this->post(route('admin.users.store'), [
            'name' => 'Petugas Baru',
            'username' => 'petugasbaru',
            'email' => 'petugasbaru@example.test',
            'role' => 'petugas',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('alat', ['nama_alat' => 'Proyektor', 'stok' => 3]);
        $this->assertDatabaseHas('users', ['username' => 'petugasbaru', 'role' => 'petugas']);
        $this->assertSame(3, LogAktivitas::count());
    }
}
