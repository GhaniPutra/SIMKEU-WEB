<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DummyExpensesJanuaryToOctober2026Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $user = DB::table('users')->where('id', 11)->where('store_id', 5)->first();
            if (! $user) {
                throw new RuntimeException('User ID 11 must belong to store ID 5.');
            }

            $expenses = [
                ['month' => 1, 'label' => 'Januari', 'amount' => 80000, 'category' => 'beli_stok'],
                ['month' => 2, 'label' => 'Februari', 'amount' => 45000, 'category' => 'transport'],
                ['month' => 3, 'label' => 'Maret', 'amount' => 60000, 'category' => 'listrik'],
                ['month' => 4, 'label' => 'April', 'amount' => 95000, 'category' => 'beli_stok'],
                ['month' => 5, 'label' => 'Mei', 'amount' => 350000, 'category' => 'gaji'],
                ['month' => 6, 'label' => 'Juni', 'amount' => 250000, 'category' => 'sewa'],
                ['month' => 7, 'label' => 'Juli', 'amount' => 75000, 'category' => 'transport'],
                ['month' => 8, 'label' => 'Agustus', 'amount' => 85000, 'category' => 'listrik'],
                ['month' => 9, 'label' => 'September', 'amount' => 140000, 'category' => 'beli_stok'],
                ['month' => 10, 'label' => 'Oktober', 'amount' => 65000, 'category' => 'lainnya'],
            ];

            foreach ($expenses as $expense) {
                $date = sprintf('2026-%02d-20', $expense['month']);
                $description = "Data dummy pengeluaran {$expense['label']} 2026";

                DB::table('expenses')->updateOrInsert(
                    [
                        'user_id' => 11,
                        'store_id' => 5,
                        'description' => $description,
                    ],
                    [
                        'expense_date' => $date,
                        'amount' => $expense['amount'],
                        'category' => $expense['category'],
                        'created_at' => $date . ' 12:00:00',
                        'updated_at' => $date . ' 12:00:00',
                    ]
                );
            }
        });
    }
}
