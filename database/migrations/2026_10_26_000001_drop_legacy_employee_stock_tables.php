<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'employee_stock_transactions',
        'employee_stock_return_batches',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                throw new RuntimeException("Refusing to drop non-empty table {$table}.");
            }
        }

        if (DB::getDriverName() === 'mysql') {
            $externalReferences = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->whereIn('REFERENCED_TABLE_NAME', self::TABLES)
                ->whereNotIn('TABLE_NAME', self::TABLES)
                ->exists();

            if ($externalReferences) {
                throw new RuntimeException('Refusing to drop legacy employee stock tables with external foreign keys.');
            }
        }

        Schema::dropIfExists('employee_stock_transactions');
        Schema::dropIfExists('employee_stock_return_batches');
    }

    public function down(): void
    {
        Schema::create('employee_stock_return_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->date('closing_date');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['store_id', 'closing_date']);
        });

        Schema::create('employee_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('return_batch_id')->nullable()->constrained('employee_stock_return_batches')->nullOnDelete();
            $table->string('type', 40)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->unsignedInteger('quantity');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['employee_user_id', 'created_at']);
        });
    }
};