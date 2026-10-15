<?php

namespace Database\Seeders;

// ====================================
// Import Model untuk seeding
// ====================================
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Data contoh memiliki seeder tersendiri dan tidak dimuat otomatis,
        // agar KPI selalu menghitung data yang benar-benar dimasukkan pengguna.
    }
}