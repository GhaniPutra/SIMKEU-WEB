<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_user')->insert([
            ['id' => 3, 'user_id' => 5, 'product_id' => 5, 'stock' => 4, 'created_at' => '2026-05-18 06:43:35', 'updated_at' => '2026-05-27 12:28:28'],
            ['id' => 4, 'user_id' => 5, 'product_id' => 2, 'stock' => 5, 'created_at' => '2026-05-18 06:43:36', 'updated_at' => '2026-05-22 10:16:11'],
            ['id' => 5, 'user_id' => 5, 'product_id' => 6, 'stock' => 5, 'created_at' => '2026-05-18 06:43:38', 'updated_at' => '2026-05-22 10:16:11'],
            ['id' => 6, 'user_id' => 5, 'product_id' => 4, 'stock' => 5, 'created_at' => '2026-05-18 06:43:38', 'updated_at' => '2026-05-22 10:16:11'],
            ['id' => 7, 'user_id' => 5, 'product_id' => 3, 'stock' => 5, 'created_at' => '2026-05-18 06:43:40', 'updated_at' => '2026-05-22 10:16:11'],
        ]);
    }
}
