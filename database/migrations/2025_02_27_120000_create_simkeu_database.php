<?php
// database/migrations/2025_03_02_120000_create_simkeu_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =====================================================
        // 1. Tabel categories (kategori produk)
        // =====================================================
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->notNullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // =====================================================
        // 2. Tabel products (produk)
        // =====================================================
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('name', 200)->notNullable();
            $table->decimal('buy_price', 15, 2)->default(0)->notNullable();
            $table->decimal('sell_price', 15, 2)->default(0)->notNullable();
            $table->integer('stock')->default(0)->notNullable();
            $table->string('unit', 20)->default('pcs')->notNullable();
            $table->string('image', 255)->nullable();
            $table->timestamps();
            
            $table->index('name');
        });

        // =====================================================
        // 3. Tabel customers (pelanggan)
        // =====================================================
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->notNullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // =====================================================
        // 4. Tabel sales (header transaksi penjualan)
        // =====================================================
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->notNullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('invoice_number', 50)->unique()->notNullable();
            $table->dateTime('transaction_date')->useCurrent()->notNullable();
            $table->decimal('subtotal', 15, 2)->default(0)->notNullable();
            $table->decimal('tax', 15, 2)->default(0)->notNullable();
            $table->decimal('discount', 15, 2)->default(0)->notNullable();
            $table->decimal('total', 15, 2)->default(0)->notNullable();
            $table->enum('payment_method', ['tunai', 'transfer', 'qris'])->default('tunai');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('transaction_date');
        });

        // =====================================================
        // 5. Tabel sale_items (detail item penjualan)
        // =====================================================
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->notNullable()->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->notNullable()->constrained('products')->onDelete('restrict');
            $table->integer('quantity')->default(1)->notNullable();
            $table->decimal('price_at_sale', 15, 2)->notNullable();
            $table->decimal('subtotal', 15, 2)->notNullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('sale_id');
            $table->index('product_id');
        });

        // =====================================================
        // 6. Tabel expenses (pengeluaran operasional)
        // =====================================================
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->notNullable()->constrained('users')->onDelete('restrict');
            $table->date('expense_date')->notNullable();
            $table->decimal('amount', 15, 2)->notNullable();
            $table->enum('category', ['beli_stok', 'gaji', 'listrik', 'air', 'sewa', 'transport', 'lainnya'])->default('lainnya');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('expense_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        
        // Note: users table tidak di-drop di sini karena dibuat di migration default Laravel
        // (0001_01_01_000000_create_users_table.php)
    }
};