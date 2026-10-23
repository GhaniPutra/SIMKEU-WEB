<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('movement_type');
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamp('occurred_at');
            $table->date('closing_date')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'employee_user_id', 'product_id', 'movement_type', 'closing_date'], 'stock_movements_daily_return_unique');
            $table->index(['store_id', 'occurred_at']);
            $table->index(['employee_user_id', 'movement_type']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->time('stock_closing_time')->default('23:59:00');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('stock_closing_time');
        });

        Schema::dropIfExists('stock_movements');
    }
};
