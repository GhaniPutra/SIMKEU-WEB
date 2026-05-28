<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'id' => 2,
                'category_id' => 2,
                'name' => 'Espresso',
                'buy_price' => '10000.00',
                'sell_price' => '12000.00',
                'tax_rate' => '3.00',
                'discount_rate' => '0.00',
                'stock' => 490,
                'unit' => 'pcs',
                'image' => null,
                'created_at' => '2026-05-18 06:39:50',
                'updated_at' => '2026-05-18 06:43:36',
            ],
            [
                'id' => 3,
                'category_id' => 2,
                'name' => 'Americano',
                'buy_price' => '10000.00',
                'sell_price' => '11000.00',
                'tax_rate' => '3.00',
                'discount_rate' => '0.00',
                'stock' => 490,
                'unit' => 'pcs',
                'image' => null,
                'created_at' => '2026-05-18 06:40:30',
                'updated_at' => '2026-05-23 08:24:16',
            ],
            [
                'id' => 4,
                'category_id' => 2,
                'name' => 'Cappuccino',
                'buy_price' => '10000.00',
                'sell_price' => '14000.00',
                'tax_rate' => '3.00',
                'discount_rate' => '0.00',
                'stock' => 490,
                'unit' => 'pcs',
                'image' => null,
                'created_at' => '2026-05-18 06:41:04',
                'updated_at' => '2026-05-18 06:43:38',
            ],
            [
                'id' => 5,
                'category_id' => 3,
                'name' => 'v60',
                'buy_price' => '11000.00',
                'sell_price' => '16000.00',
                'tax_rate' => '4.00',
                'discount_rate' => '0.00',
                'stock' => 490,
                'unit' => 'pcs',
                'image' => null,
                'created_at' => '2026-05-18 06:41:44',
                'updated_at' => '2026-05-18 06:43:35',
            ],
            [
                'id' => 6,
                'category_id' => 3,
                'name' => 'Chemex',
                'buy_price' => '11000.00',
                'sell_price' => '12000.00',
                'tax_rate' => '4.00',
                'discount_rate' => '0.00',
                'stock' => 490,
                'unit' => 'pcs',
                'image' => null,
                'created_at' => '2026-05-18 06:42:16',
                'updated_at' => '2026-05-18 06:43:38',
            ],
        ]);
    }
}
