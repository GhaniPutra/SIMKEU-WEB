<?php

namespace Database\Seeders;

use App\Support\RolePermissions;
use App\Support\SaleCalculator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoStoreSeeder extends Seeder
{
    private const STORE_NAME = 'SIMKEU Coffee Demo';
    private const ADMIN_EMAIL = 'admin@simkeu-demo.test';
    private const EMPLOYEE_EMAIL = 'pegawai@simkeu-demo.test';
    private const INITIAL_STOCK = 100;
    private const ALLOCATION_PER_PRODUCT = 20;

    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            DB::table('stores')->updateOrInsert(
                ['name' => self::STORE_NAME],
                [
                    'address' => 'Jl. Kopi Demo No. 1',
                    'phone' => '081234567890',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
            $storeId = (int) DB::table('stores')->where('name', self::STORE_NAME)->value('id');

            $adminId = $this->upsertUser(self::ADMIN_EMAIL, [
                'store_id' => $storeId,
                'name' => 'Admin SIMKEU Demo',
                'role' => 'admin',
                'role_id' => null,
                'password' => Hash::make('DemoAdmin2026!'),
                'email_verified_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ]);

            DB::table('roles')->updateOrInsert(
                ['store_id' => $storeId, 'name' => 'Pegawai'],
                [
                    'permissions' => json_encode(RolePermissions::starterEmployee(), JSON_THROW_ON_ERROR),
                    'is_system' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
            $employeeRoleId = (int) DB::table('roles')
                ->where('store_id', $storeId)
                ->where('name', 'Pegawai')
                ->value('id');

            $employeeId = $this->upsertUser(self::EMPLOYEE_EMAIL, [
                'store_id' => $storeId,
                'name' => 'Pegawai SIMKEU Demo',
                'role' => 'pegawai',
                'role_id' => $employeeRoleId,
                'password' => Hash::make('DemoPegawai2026!'),
                'email_verified_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ]);

            $categoryNames = [
                'Espresso',
                'Manual Brew',
                'Kopi Susu',
                'Cold Brew',
                'Kopi Tradisional',
                'Single Origin',
                'Coffee Blend',
                'Kopi Decaf',
                'Kopi Rempah',
                'Signature Coffee',
            ];
            $categoryIds = [];
            foreach ($categoryNames as $categoryName) {
                DB::table('categories')->updateOrInsert(
                    ['store_id' => $storeId, 'name' => $categoryName],
                    [
                        'description' => "Kategori {$categoryName} untuk toko demo.",
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
                $categoryIds[$categoryName] = (int) DB::table('categories')
                    ->where('store_id', $storeId)
                    ->where('name', $categoryName)
                    ->value('id');
            }

            $productDefinitions = [
                ['name' => 'Espresso', 'category' => 'Espresso', 'buy' => 9000, 'sell' => 18000, 'tax' => 11, 'discount' => 0],
                ['name' => 'Americano', 'category' => 'Espresso', 'buy' => 8000, 'sell' => 16000, 'tax' => 11, 'discount' => 0],
                ['name' => 'Cappuccino', 'category' => 'Espresso', 'buy' => 12000, 'sell' => 22000, 'tax' => 11, 'discount' => 0],
                ['name' => 'Caffe Latte', 'category' => 'Espresso', 'buy' => 13000, 'sell' => 24000, 'tax' => 11, 'discount' => 0],
                ['name' => 'V60', 'category' => 'Manual Brew', 'buy' => 10000, 'sell' => 20000, 'tax' => 0, 'discount' => 0],
                ['name' => 'Japanese Iced Coffee', 'category' => 'Cold Brew', 'buy' => 11000, 'sell' => 22000, 'tax' => 11, 'discount' => 0],
                ['name' => 'Cold Brew', 'category' => 'Cold Brew', 'buy' => 12000, 'sell' => 24000, 'tax' => 11, 'discount' => 0],
                ['name' => 'Kopi Susu Gula Aren', 'category' => 'Kopi Susu', 'buy' => 10000, 'sell' => 20000, 'tax' => 11, 'discount' => 5],
                ['name' => 'Mocha', 'category' => 'Signature Coffee', 'buy' => 14000, 'sell' => 25000, 'tax' => 11, 'discount' => 0],
                ['name' => 'Caramel Macchiato', 'category' => 'Signature Coffee', 'buy' => 15000, 'sell' => 27000, 'tax' => 11, 'discount' => 5],
            ];

            $products = [];
            foreach ($productDefinitions as $definition) {
                DB::table('products')->updateOrInsert(
                    ['store_id' => $storeId, 'name' => $definition['name']],
                    [
                        'category_id' => $categoryIds[$definition['category']],
                        'buy_price' => $definition['buy'],
                        'sell_price' => $definition['sell'],
                        'tax_rate' => $definition['tax'],
                        'discount_rate' => $definition['discount'],
                        'stock' => self::INITIAL_STOCK,
                        'unit' => 'cup',
                        'image' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                $products[] = (array) DB::table('products')
                    ->where('store_id', $storeId)
                    ->where('name', $definition['name'])
                    ->first();
            }

            $soldByProduct = array_fill_keys(array_column($products, 'id'), 0);
            $paymentMethods = ['tunai', 'transfer', 'qris'];
            $dailySalesTotals = [];
            $saleIndex = 0;

            // One employee sale for every valid date from June through October 2026.
            for ($month = 6; $month <= 10; $month++) {
                $daysInMonth = Carbon::create(2026, $month, 1)->daysInMonth;

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $product = $products[$saleIndex % count($products)];
                    $soldByProduct[$product['id']]++;

                    $transactionDate = Carbon::create(2026, $month, $day, 12, 0, 0);
                    $timestamp = $transactionDate->toDateTimeString();
                    $invoiceNumber = sprintf('DEMO-SALE-2026-%02d-%02d', $month, $day);
                    $calculation = SaleCalculator::calculate([[
                        'product_id' => $product['id'],
                        'quantity' => 1,
                        'price' => $product['sell_price'],
                        'discount_rate' => $product['discount_rate'],
                        'tax_rate' => $product['tax_rate'],
                    ]]);
                    $dailySalesTotals[$transactionDate->toDateString()] = $calculation['total'];

                    DB::table('sales')->updateOrInsert(
                        ['invoice_number' => $invoiceNumber],
                        [
                            'store_id' => $storeId,
                            'user_id' => $employeeId,
                            'transaction_date' => $timestamp,
                            'subtotal' => $calculation['subtotal'],
                            'tax' => $calculation['tax'],
                            'discount' => $calculation['discount'],
                            'total' => $calculation['total'],
                            'payment_method' => $paymentMethods[$saleIndex % count($paymentMethods)],
                            'notes' => 'Data penjualan demo pegawai.',
                            'updated_at' => $timestamp,
                            'created_at' => $timestamp,
                        ]
                    );
                    $saleId = (int) DB::table('sales')->where('invoice_number', $invoiceNumber)->value('id');
                    $item = $calculation['items'][0];

                    DB::table('sale_items')->updateOrInsert(
                        ['sale_id' => $saleId, 'product_id' => $product['id']],
                        [
                            'quantity' => 1,
                            'price_at_sale' => $item['price_at_sale'],
                            'subtotal' => $item['subtotal'],
                            'discount' => $item['discount'],
                            'tax' => $item['tax'],
                            'created_at' => $timestamp,
                        ]
                    );

                    $saleIndex++;
                }
            }

            foreach ($products as $product) {
                $productId = (int) $product['id'];
                $employeeStock = self::ALLOCATION_PER_PRODUCT - $soldByProduct[$productId];

                DB::table('stock_allocations')->updateOrInsert(
                    [
                        'owner_user_id' => $adminId,
                        'employee_user_id' => $employeeId,
                        'product_id' => $productId,
                        'revised_from_allocation_id' => null,
                    ],
                    [
                        'requested_quantity' => self::ALLOCATION_PER_PRODUCT,
                        'confirmed_quantity' => self::ALLOCATION_PER_PRODUCT,
                        'actual_received_quantity' => self::ALLOCATION_PER_PRODUCT,
                        'difference_quantity' => 0,
                        'returned_quantity' => 0,
                        'status' => 'accepted',
                        'decline_reason' => null,
                        'reviewed_by_user_id' => $employeeId,
                        'reviewed_at' => $now,
                        'resolution_action' => null,
                        'resolved_by_user_id' => null,
                        'resolved_at' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                // The initial 100 units are split between store stock and employee stock.
                DB::table('products')->where('id', $productId)->update([
                    'stock' => self::INITIAL_STOCK - self::ALLOCATION_PER_PRODUCT,
                    'updated_at' => $now,
                ]);
                DB::table('product_user')->updateOrInsert(
                    ['user_id' => $employeeId, 'product_id' => $productId],
                    [
                        'stock' => $employeeStock,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            // Remove only the five monthly balancing rows written by the earlier
            // version of this demo seeder; retain unrelated store expenses.
            $previousMonthlyDescriptions = [];
            for ($month = 6; $month <= 10; $month++) {
                $previousMonthlyDescriptions[] = sprintf('Data pengeluaran demo bulan %02d 2026.', $month);
            }
            DB::table('expenses')
                ->where('store_id', $storeId)
                ->where('user_id', $employeeId)
                ->whereIn('description', $previousMonthlyDescriptions)
                ->delete();

            foreach ($dailySalesTotals as $date => $salesTotal) {
                $expenseDate = Carbon::parse($date);
                $description = "Pengeluaran penyeimbang demo {$date}.";
                DB::table('expenses')->updateOrInsert(
                    ['store_id' => $storeId, 'user_id' => $employeeId, 'description' => $description],
                    [
                        'expense_date' => $expenseDate->toDateString(),
                        'amount' => $salesTotal,
                        'category' => 'lainnya',
                        'updated_at' => $expenseDate,
                        'created_at' => $expenseDate,
                    ]
                );
            }
        });
    }

    private function upsertUser(string $email, array $values): int
    {
        DB::table('users')->updateOrInsert(['email' => $email], $values);

        return (int) DB::table('users')->where('email', $email)->value('id');
    }
}
