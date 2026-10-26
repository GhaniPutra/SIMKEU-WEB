<?php

namespace App\Console\Commands;

use App\Events\StoreDataChanged;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CloseEmployeeStock extends Command
{
    protected $signature = 'stock:close-employee-balances';

    protected $description = 'Kembalikan saldo stok aktif pegawai ke gudang sesuai jadwal penutupan usaha';

    public function handle(): int
    {
        $timezone = 'Asia/Jakarta';
        $now = Carbon::now($timezone);

        Store::query()->orderBy('id')->each(function (Store $store) use ($now): void {
            $closingTime = $store->stock_closing_time ?: '23:59:00';
            if ($now->format('H:i:s') < $closingTime) {
                return;
            }

            $balances = DB::table('product_user as employee_product')
                ->join('users as employees', 'employees.id', '=', 'employee_product.user_id')
                ->join('products', 'products.id', '=', 'employee_product.product_id')
                ->where('employees.store_id', $store->id)
                ->where('employees.role', 'pegawai')
                ->where('products.store_id', $store->id)
                ->where('employee_product.stock', '>', 0)
                ->select('employee_product.user_id', 'employee_product.product_id')
                ->orderBy('employee_product.user_id')
                ->orderBy('employee_product.product_id')
                ->get();

            $storeChanged = false;
            foreach ($balances as $balance) {
                $changed = DB::transaction(function () use ($store, $balance, $now): bool {
                    $employee = User::query()->where('store_id', $store->id)->find($balance->user_id);
                    $product = DB::table('products')->where('store_id', $store->id)->where('id', $balance->product_id)->lockForUpdate()->first();
                    $pivot = DB::table('product_user')->where('user_id', $balance->user_id)->where('product_id', $balance->product_id)->lockForUpdate()->first();

                    if (! $employee || ! $product || ! $pivot || (int) $pivot->stock <= 0) {
                        return false;
                    }

                    $closingDate = $now->toDateString();
                    $alreadyReturned = StockMovement::query()
                        ->where('store_id', $store->id)
                        ->where('employee_user_id', $employee->id)
                        ->where('product_id', $product->id)
                        ->where('movement_type', StockMovement::TYPE_AUTO_RETURN)
                        ->whereDate('closing_date', $closingDate)
                        ->exists();
                    if ($alreadyReturned) {
                        return false;
                    }

                    $quantity = (int) $pivot->stock;
                    DB::table('product_user')->where('id', $pivot->id)->update(['stock' => 0, 'updated_at' => $now]);
                    DB::table('products')->where('id', $product->id)->increment('stock', $quantity, ['updated_at' => $now]);

                    StockMovement::create([
                        'store_id' => $store->id,
                        'employee_user_id' => $employee->id,
                        'product_id' => $product->id,
                        'movement_type' => StockMovement::TYPE_AUTO_RETURN,
                        'actor_user_id' => null,
                        'occurred_at' => $now,
                        'closing_date' => $closingDate,
                        'quantity' => $quantity,
                    ]);
                    return true;
                }, 3);

                $storeChanged = $storeChanged || $changed;
            }

            if ($storeChanged) {
                StoreDataChanged::dispatch($store->id, ['inventory']);
            }
        });

        return self::SUCCESS;
    }
}
