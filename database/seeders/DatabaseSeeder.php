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
        // Urutan penting: stores -> users -> related data
        $this->call([
            StoresSeeder::class,
            UsersSeeder::class,
            PersonalAccessTokensSeeder::class,
            CategoriesSeeder::class,
            ProductsSeeder::class,
            ProductUserSeeder::class,
            SalesSeeder::class,
            ExpensesSeeder::class,
        ]);
    }
}