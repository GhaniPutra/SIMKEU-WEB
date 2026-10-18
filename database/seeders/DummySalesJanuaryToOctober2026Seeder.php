<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DummySalesJanuaryToOctober2026Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $user = DB::table('users')->where('id', 11)->where('store_id', 5)->first();
            if (! $user) {
                throw new RuntimeException('User ID 11 must belong to store ID 5.');
            }

            $products = DB::table('products')
                ->where('store_id', 5)
                ->where('sell_price', '>', 0)
                ->orderBy('id')
                ->get(['id', 'sell_price']);

            if ($products->isEmpty()) {
                throw new RuntimeException('Store ID 5 needs at least one product with a sell price.');
            }

            $months = [
                ['number' => 1, 'label' => 'Januari', 'quantity' => 2],
                ['number' => 2, 'label' => 'Februari', 'quantity' => 3],
                ['number' => 3, 'label' => 'Maret', 'quantity' => 4],
                ['number' => 4, 'label' => 'April', 'quantity' => 3],
                ['number' => 5, 'label' => 'Mei', 'quantity' => 5],
                ['number' => 6, 'label' => 'Juni', 'quantity' => 4],
                ['number' => 7, 'label' => 'Juli', 'quantity' => 6],
                ['number' => 8, 'label' => 'Agustus', 'quantity' => 5],
                ['number' => 9, 'label' => 'September', 'quantity' => 7],
                ['number' => 10, 'label' => 'Oktober', 'quantity' => 8],
            ];
            $paymentMethods = ['tunai', 'transfer', 'qris'];

            foreach ($months as $index => $month) {
                $date = sprintf('2026-%02d-15 12:00:00', $month['number']);
                $product = $products[$index % $products->count()];
                $subtotal = round((float) $product->sell_price * $month['quantity'], 2);
                $invoiceNumber = sprintf('DUMMY-SALE-U11-S5-2026-%02d', $month['number']);

                DB::table('sales')->updateOrInsert(
                    ['invoice_number' => $invoiceNumber],
                    [
                        'store_id' => 5,
                        'user_id' => 11,
                        'customer_id' => null,
                        'transaction_date' => $date,
                        'subtotal' => $subtotal,
                        'tax' => 0,
                        'discount' => 0,
                        'total' => $subtotal,
                        'payment_method' => $paymentMethods[$index % count($paymentMethods)],
                        'notes' => "Data dummy penjualan {$month['label']} 2026",
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]
                );

                $saleId = DB::table('sales')->where('invoice_number', $invoiceNumber)->value('id');
                DB::table('sale_items')->where('sale_id', $saleId)->delete();
                DB::table('sale_items')->insert([
                    'sale_id' => $saleId,
                    'product_id' => $product->id,
                    'quantity' => $month['quantity'],
                    'price_at_sale' => $product->sell_price,
                    'subtotal' => $subtotal,
                    'created_at' => $date,
                ]);
            }
        });
    }
}
