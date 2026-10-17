<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'products', 'customers', 'sales', 'expenses'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
                $table->index('store_id');
            });
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['store_id', 'name']);
        });

        foreach (DB::table('sales')->join('users', 'users.id', '=', 'sales.user_id')
            ->whereNotNull('users.store_id')->select('sales.id', 'users.store_id')->get() as $record) {
            DB::table('sales')->where('id', $record->id)->update(['store_id' => $record->store_id]);
        }
        foreach (DB::table('expenses')->join('users', 'users.id', '=', 'expenses.user_id')
            ->whereNotNull('users.store_id')->select('expenses.id', 'users.store_id')->get() as $record) {
            DB::table('expenses')->where('id', $record->id)->update(['store_id' => $record->store_id]);
        }

        // A product is backfilled only when its employee stock links prove one store.
        $productStores = DB::table('product_user')
            ->join('users', 'users.id', '=', 'product_user.user_id')
            ->whereNotNull('users.store_id')
            ->select('product_user.product_id', DB::raw('MIN(users.store_id) as store_id'), DB::raw('COUNT(DISTINCT users.store_id) as store_count'))
            ->groupBy('product_user.product_id')
            ->havingRaw('COUNT(DISTINCT users.store_id) = 1')
            ->get();
        foreach ($productStores as $productStore) {
            DB::table('products')->where('id', $productStore->product_id)->update(['store_id' => $productStore->store_id]);
        }

        // Categories and customers are backfilled only when related records agree on one store.
        $categoryStores = DB::table('products')
            ->whereNotNull('store_id')->whereNotNull('category_id')
            ->select('category_id', DB::raw('MIN(store_id) as store_id'), DB::raw('COUNT(DISTINCT store_id) as store_count'))
            ->groupBy('category_id')->havingRaw('COUNT(DISTINCT store_id) = 1')->get();
        foreach ($categoryStores as $categoryStore) {
            DB::table('categories')->where('id', $categoryStore->category_id)->update(['store_id' => $categoryStore->store_id]);
        }
        $customerStores = DB::table('sales')->whereNotNull('store_id')->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('MIN(store_id) as store_id'), DB::raw('COUNT(DISTINCT store_id) as store_count'))
            ->groupBy('customer_id')->havingRaw('COUNT(DISTINCT store_id) = 1')->get();
        foreach ($customerStores as $customerStore) {
            DB::table('customers')->where('id', $customerStore->customer_id)->update(['store_id' => $customerStore->store_id]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['store_id', 'name']);
            $table->unique('name');
        });
        foreach (['expenses', 'sales', 'customers', 'products', 'categories'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['store_id']);
            });
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('store_id');
            });
        }
    }
};
