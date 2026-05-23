<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tambahkan kolom tax_rate dan discount_rate ke tabel products.
     * Kedua kolom menyimpan nilai persentase (0–100).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Tarif pajak dalam persen (contoh: 10 = 10%), default 0
            $table->decimal('tax_rate', 5, 2)->default(0)->after('sell_price');
            // Tarif diskon dalam persen (contoh: 5 = 5%), default 0
            $table->decimal('discount_rate', 5, 2)->default(0)->after('tax_rate');
        });
    }

    /**
     * Hapus kolom tax_rate dan discount_rate dari tabel products.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'discount_rate']);
        });
    }
};
