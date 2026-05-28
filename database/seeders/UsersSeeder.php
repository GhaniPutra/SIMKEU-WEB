<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => 4,
                'store_id' => 2,
                'name' => 'Admin SIMKEU 2',
                'email' => 'admin2@simkeu.com',
                'password' => '$2y$12$JbTKEjSAg0JW3kNYsQJmaubIgx/xv1hzoUnnPKRreoSSPS1ANxExq',
                'role' => 'admin',
                'remember_token' => null,
                'created_at' => '2026-05-05 04:32:43',
                'updated_at' => '2026-05-18 06:22:20',
            ],
            [
                'id' => 5,
                'store_id' => null,
                'name' => 'Pegawai SIMKEU',
                'email' => 'pegawai@gmail.com',
                'password' => '$2y$12$IAiWRjEVw3NDlV47ZRtWHuVD03PD8931DiJSTlE.jtDQ5gBWfsr92',
                'role' => 'pegawai',
                'remember_token' => null,
                'created_at' => '2026-05-07 19:43:44',
                'updated_at' => '2026-05-07 19:43:44',
            ],
            [
                'id' => 6,
                'store_id' => null,
                'name' => 'Pegawai SIMKEU 2',
                'email' => 'pegawai2@gmail.com',
                'password' => '$2y$12$upL/dktIg5tg5ncjZg0ltOG5RxWTRqO7/7X9VaMZ1bbbP2VWtllTK',
                'role' => 'pegawai',
                'remember_token' => null,
                'created_at' => '2026-05-18 06:38:35',
                'updated_at' => '2026-05-18 06:38:35',
            ],
        ]);
    }
}
