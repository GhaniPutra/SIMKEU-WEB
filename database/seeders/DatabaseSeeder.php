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
        $this->call(DemoStoreSeeder::class);
    }
}
