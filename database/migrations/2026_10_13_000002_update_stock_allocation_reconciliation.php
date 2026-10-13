<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_allocations', function (Blueprint $table) {
            $table->integer('difference_quantity')->nullable()->change();
            $table->unsignedInteger('returned_quantity')->default(0)->after('difference_quantity');
            $table->string('resolution_action')->nullable()->after('reviewed_at');
            $table->foreignId('resolved_by_user_id')->nullable()->after('resolution_action')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by_user_id');
        });
    }

    public function down(): void
    {
        DB::table('stock_allocations')
            ->where('difference_quantity', '<', 0)
            ->update(['difference_quantity' => DB::raw('ABS(difference_quantity)')]);

        Schema::table('stock_allocations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resolved_by_user_id');
            $table->dropColumn(['resolution_action', 'resolved_at']);
            $table->dropColumn('returned_quantity');
            $table->unsignedInteger('difference_quantity')->nullable()->change();
        });
    }
};
