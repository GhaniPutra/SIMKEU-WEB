<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpensesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('expenses')->insert([
            [
                'id' => 2,
                'user_id' => 5,
                'expense_date' => '2026-05-18',
                'amount' => '3000.00',
                'category' => 'lainnya',
                'description' => 'air putih',
                'created_at' => '2026-05-18 07:03:56',
                'updated_at' => '2026-05-18 07:03:56',
            ],
            [
                'id' => 3,
                'user_id' => 5,
                'expense_date' => '2026-05-22',
                'amount' => '5000.00',
                'category' => 'lainnya',
                'description' => 'Minuman',
                'created_at' => '2026-05-22 00:06:01',
                'updated_at' => '2026-05-22 00:06:01',
            ],
        ]);
    }
}
