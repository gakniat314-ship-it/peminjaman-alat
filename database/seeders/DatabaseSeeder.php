<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /** Menjalankan pengisian akun awal dan contoh inventaris secara berurutan. */
    public function run(): void
    {
        $this->seedUsers();
        $this->seedSampleAlat();
    }

    /** Tiga akun awal sesuai LKPD (password semuanya: password) */
    private function seedUsers(): void
    {
        $accounts = [
            ['name' => 'Administrator', 'username' => 'admin',    'email' => 'admin@example.com',    'role' => 'admin'],
            ['name' => 'Petugas',       'username' => 'petugas',  'email' => 'petugas@example.com',  'role' => 'petugas'],
            ['name' => 'Peminjam',      'username' => 'peminjam', 'email' => 'peminjam@example.com', 'role' => 'peminjam'],
        ];

        foreach ($accounts as $account) {
            // updateOrCreate: seeder aman dijalankan berulang tanpa membuat akun ganda
            User::updateOrCreate(
                ['username' => $account['username']],
                $account + [
                    'password'          => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }

    /** Data contoh agar katalog tidak kosong saat pengujian (boleh dihapus) */
    private function seedSampleAlat(): void
    {
        DB::table('kategori')->insertOrIgnore([
            ['nama_kategori' => 'Elektronik',   'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Alat Praktik', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Hanya isi alat jika tabelnya masih kosong
        if (DB::table('alat')->count() > 0) {
            return;
        }

        $elektronik = DB::table('kategori')->where('nama_kategori', 'Elektronik')->value('id');
        $praktik    = DB::table('kategori')->where('nama_kategori', 'Alat Praktik')->value('id');

        DB::table('alat')->insert([
            ['kategori_id' => $elektronik, 'nama_alat' => 'Laptop',    'stok' => 5,  'kondisi' => 'baik', 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => $elektronik, 'nama_alat' => 'Proyektor', 'stok' => 3,  'kondisi' => 'baik', 'created_at' => now(), 'updated_at' => now()],
            ['kategori_id' => $praktik,    'nama_alat' => 'Multimeter','stok' => 10, 'kondisi' => 'baik', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}