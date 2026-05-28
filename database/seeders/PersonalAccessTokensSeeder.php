<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonalAccessTokensSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('personal_access_tokens')->insert([
            [
                'id' => 1,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 3,
                'name' => 'api-token',
                'token' => 'd1a57816f8ca58e89ed49d1adbbe7add616c44ce1a663c4d4f0c1ae990e142a6',
                'abilities' => '["*"]',
                'last_used_at' => '2026-05-02 22:49:26',
                'expires_at' => null,
                'created_at' => '2026-04-30 04:58:08',
                'updated_at' => '2026-05-02 22:49:26',
            ],
            [
                'id' => 2,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 5,
                'name' => 'api-token',
                'token' => '9cc697b71564101c37809ffae70910f7074d7d8af21920858c9886d1a87de4f3',
                'abilities' => '["*"]',
                'last_used_at' => '2026-05-11 05:57:48',
                'expires_at' => null,
                'created_at' => '2026-05-07 20:17:45',
                'updated_at' => '2026-05-11 05:57:48',
            ],
            [
                'id' => 3,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 5,
                'name' => 'api-token',
                'token' => '4578ffd5b43ea4cdecec91c9c380d8a36b0b72c0d71ae2828c4572f48dc74b1d',
                'abilities' => '["*"]',
                'last_used_at' => '2026-05-27 12:28:41',
                'expires_at' => null,
                'created_at' => '2026-05-18 07:02:18',
                'updated_at' => '2026-05-27 12:28:41',
            ],
        ]);
    }
}
