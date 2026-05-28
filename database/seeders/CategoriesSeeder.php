<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'id' => 2,
                'name' => 'Espresso Based Coffee',
                'description' => 'Kategori kopi yang berbasis shot espresso.',
                'created_at' => '2026-05-18 06:35:27',
                'updated_at' => '2026-05-18 06:35:27',
            ],
            [
                'id' => 3,
                'name' => 'Manual Brew Coffee',
                'description' => 'Kategori kopi yang dibuat dengan metode seduh manual.',
                'created_at' => '2026-05-18 06:35:48',
                'updated_at' => '2026-05-18 06:35:48',
            ],
            [
                'id' => 4,
                'name' => 'Cold Coffee',
                'description' => 'Kategori kopi dingin atau berbasis es.',
                'created_at' => '2026-05-18 06:36:09',
                'updated_at' => '2026-05-18 06:36:09',
            ],
            [
                'id' => 5,
                'name' => 'Traditional Coffee',
                'description' => 'Kategori kopi tradisional dari berbagai negara.',
                'created_at' => '2026-05-18 06:36:24',
                'updated_at' => '2026-05-18 06:36:37',
            ],
            [
                'id' => 6,
                'name' => 'Flavored & Specialty Coffee',
                'description' => 'Kategori kopi dengan rasa tambahan atau signature style.',
                'created_at' => '2026-05-18 06:37:15',
                'updated_at' => '2026-05-18 06:37:15',
            ],
        ]);
    }
}
