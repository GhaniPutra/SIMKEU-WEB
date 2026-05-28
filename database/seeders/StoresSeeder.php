<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoresSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stores')->insert([
            [
                'id' => 2,
                'name' => 'Toko Kopi',
                'address' => 'Gaming',
                'phone' => '1234567891011',
                'logo' => null,
                'created_at' => '2026-05-18 06:22:20',
                'updated_at' => '2026-05-18 06:22:20',
            ],
        ]);
    }
}
