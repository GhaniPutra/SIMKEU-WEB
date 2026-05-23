<?php

namespace Database\Seeders;

// ====================================
// Import Model untuk seeding
// ====================================
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    // ====================================
    // Method untuk menjalankan seeding
    // ====================================
    /**
     * Seed the application's database.
     * 
     * Seeder ini membuat data default untuk testing aplikasi
     */
    public function run(): void
    {
        // StoreSeeder dihapus karena fitur Cabang dihapus

        // ====================================
        // 2. Buat beberapa admin utama (tidak terkait toko)
        // Semua user akan dianggap admin utama
        // ====================================
        User::create([
            'name' => 'Admin Super',           // Nama super admin
            'email' => 'admin@simkeu.com',     // Email default
            'password' => Hash::make('admin123'), // Password default: admin123
            'store_id' => null,                // Null = tidak terkait toko
            'role' => 'admin',                 // Role admin
        ]);

        User::create([
            'name' => 'Admin Kedua',
            'email' => 'admin2@simkeu.com',
            'password' => Hash::make('admin123'),
            'store_id' => null,
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Admin Tiga',
            'email' => 'admin3@simkeu.com',
            'password' => Hash::make('admin123'),
            'store_id' => null,
            'role' => 'admin',
        ]);

        // ====================================
        // 7. Informasi login untuk testing
        // ====================================
        // Super Admin:
        //   Email: admin@simkeu.com
        //   Password: admin123
        //
        // Admin Toko Pusat:
        //   Email: admin.pusat@simkeu.com
        //   Password: pusat123
        //
        // Kasir Toko Pusat:
        //   Email: kasir.pusat@simkeu.com
        //   Password: kasir123
        //
        // Admin Toko Surabaya:
        //   Email: admin.surabaya@simkeu.com
        //   Password: surabaya123
        //
        // Kasir Toko Surabaya:
        //   Email: kasir.surabaya@simkeu.com
        //   Password: kasir.surabaya123
    }
}
