<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SaleFinancialConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_pos_stores_actual_product_discount_tax_and_gross_subtotal(): void
    {
        $user = $this->createOwner();
        Sanctum::actingAs($user);
        $product = $this->createProduct($user, [
            'sell_price' => 100,
            'discount_rate' => 10,
            'tax_rate' => 10,
        ]);
        $user->products()->attach($product->id, ['stock' => 8]);

        $this->postJson(route('api.pos.transaction'), [
            'payment_method' => 'tunai',
            'extra_discount' => 5,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 2,
                // The server must use the sale-time product price, not client input.
                'price' => 1,
            ]],
        ])->assertCreated()
            ->assertJsonPath('data.subtotal', '200.00')
            ->assertJsonPath('data.discount', '25.00')
            ->assertJsonPath('data.tax', '18.00')
            ->assertJsonPath('data.total', '193.00');

        $sale = Sale::query()->latest('id')->firstOrFail();
        $item = $sale->saleItems()->firstOrFail();

        $this->assertSame(200.0, (float) $sale->subtotal);
        $this->assertSame(25.0, (float) $sale->discount);
        $this->assertSame(18.0, (float) $sale->tax);
        $this->assertSame(193.0, (float) $sale->total);
        $this->assertSame(200.0, (float) $item->subtotal);
        $this->assertSame(20.0, (float) $item->discount);
        $this->assertSame(18.0, (float) $item->tax);
        $this->assertSame(6, $user->products()->where('product_id', $product->id)->first()->pivot->stock);

        // Later edits to product rates must not reprice the saved sale or its chart values.
        $product->update(['discount_rate' => 50, 'tax_rate' => 40]);
        $this->getJson(route('api.sales.product', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertJsonPath('data.0.total_revenue', fn ($value) => (float) $value === 180.0);

        $incomeResponse = $this->actingAs($user)
            ->get(route('pemasukan.index', ['period' => 'month']))
            ->assertOk()
            ->assertViewHas('totalSubtotal', 200)
            ->assertViewHas('totalDiskon', 25)
            ->assertViewHas('totalPajak', 18)
            ->assertViewHas('totalBersih', 193)
            ->assertViewHas('topProductValues', [180.0]);
        $incomeResponse
            ->assertDontSee('Pelanggan')
            ->assertDontSee('Ritel / Umum')
            ->assertSee($sale->invoice_number)
            ->assertSee('Tanggal')
            ->assertSee('Metode')
            ->assertSee('Subtotal')
            ->assertSee('Diskon')
            ->assertSee('Pajak')
            ->assertSee('Total');
    }

    public function test_income_period_and_store_filters_use_saved_legacy_amounts_without_repricing(): void
    {
        $owner = $this->createOwner();
        $this->actingAs($owner);
        $product = $this->createProduct($owner, [
            'name' => 'Produk Toko Satu',
            'sell_price' => 100,
            'discount_rate' => 25,
            'tax_rate' => 15,
        ]);

        // A legacy mobile sale has no item-level snapshots and stored zero
        // discount/tax. The live product rates must not change its history.
        $oldSale = $this->createSale($owner, $product, now()->subMonths(2), 100, 0, 0, 100);
        $currentSale = $this->createSale($owner, $product, now(), 120, 0, 0, 120);

        $otherOwner = $this->createOwner();
        $this->actingAs($otherOwner);
        $otherProduct = $this->createProduct($otherOwner, [
            'name' => 'Produk Toko Lain',
            'sell_price' => 900,
            'discount_rate' => 50,
            'tax_rate' => 20,
        ]);
        $this->createSale($otherOwner, $otherProduct, now(), 900, 0, 0, 900);

        $this->actingAs($owner);
        $monthResponse = $this->get(route('pemasukan.index', ['period' => 'month']))
            ->assertOk()
            ->assertViewHas('totalSubtotal', 120)
            ->assertViewHas('totalDiskon', 0)
            ->assertViewHas('totalPajak', 0)
            ->assertViewHas('totalBersih', 120)
            ->assertViewHas('jumlahTransaksi', 1)
            ->assertViewHas('topProductLabels', ['Produk Toko Satu'])
            ->assertDontSee('Produk Toko Lain');

        $monthResponse->assertViewHas('totalPeriodLalu', 0);

        $allResponse = $this->get(route('pemasukan.index', ['period' => 'all']))
            ->assertOk()
            ->assertViewHas('totalSubtotal', 220)
            ->assertViewHas('totalDiskon', 0)
            ->assertViewHas('totalPajak', 0)
            ->assertViewHas('totalBersih', 220)
            ->assertViewHas('jumlahTransaksi', 2)
            ->assertViewHas('topProductLabels', ['Produk Toko Satu']);

        $this->get(route('pemasukan.show', ['sale' => $oldSale->id, 'period' => 'all']))
            ->assertOk()
            ->assertSee('Rincian item terbatas')
            ->assertSee('tidak dihitung ulang dari pengaturan produk saat ini')
            ->assertSee('Rp 0');
    }

    public function test_mobile_completion_updates_transaction_totals_and_invalidates_stale_item_tax_breakdown(): void
    {
        $owner = $this->createOwner();
        Sanctum::actingAs($owner);
        $product = $this->createProduct($owner, ['sell_price' => 100]);
        $owner->products()->attach($product->id, ['stock' => 4]);

        $createResponse = $this->postJson(route('api.pos.transaction'), [
            'payment_method' => 'tunai',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => 100,
            ]],
        ])->assertCreated();
        $saleId = $createResponse->json('data.id');

        $this->postJson(route('api.pos.complete', ['sale' => $saleId]), [
            'discount' => 5,
            'tax' => 10,
        ])->assertOk()
            ->assertJsonPath('data.subtotal', '100.00')
            ->assertJsonPath('data.discount', '5.00')
            ->assertJsonPath('data.tax', '10.00')
            ->assertJsonPath('data.total', '105.00');

        $sale = Sale::query()->findOrFail($saleId);
        $this->assertSame(105.0, (float) $sale->total);
        $this->assertNull($sale->saleItems()->firstOrFail()->tax);
    }

    public function test_new_sale_detail_shows_item_snapshots_and_header_equation(): void
    {
        $owner = $this->createOwner();
        $this->actingAs($owner);
        $product = $this->createProduct($owner, [
            'sell_price' => 100,
            'discount_rate' => 10,
            'tax_rate' => 10,
        ]);
        $sale = Sale::create([
            'user_id' => $owner->id,
            'invoice_number' => 'INV-DETAIL-' . uniqid(),
            'transaction_date' => now(),
            'subtotal' => 200,
            'discount' => 25,
            'tax' => 18,
            'total' => 193,
            'payment_method' => 'tunai',
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_at_sale' => 100,
            'subtotal' => 200,
            'discount' => 20,
            'tax' => 18,
        ]);

        $this->get(route('pemasukan.show', ['sale' => $sale->id]))
            ->assertOk()
            ->assertDontSee('Pelanggan')
            ->assertDontSee('Ritel / Umum')
            ->assertSee($sale->invoice_number)
            ->assertSee('Kasir')
            ->assertSee('Metode pembayaran')
            ->assertSee('Produk dalam transaksi')
            ->assertSee('Subtotal sebelum diskon')
            ->assertSee('Diskon produk')
            ->assertSee('Pajak item')
            ->assertSee('Total dibayar')
            ->assertDontSee('Rincian item terbatas')
            ->assertSee('tambahan Rp 5');
    }

    public function test_legacy_web_sale_keeps_saved_total_when_old_components_do_not_reconcile(): void
    {
        $owner = $this->createOwner();
        $this->actingAs($owner);
        $product = $this->createProduct($owner, ['sell_price' => 100]);
        $sale = Sale::create([
            'user_id' => $owner->id,
            'invoice_number' => 'INV-LEGACY-' . uniqid(),
            'transaction_date' => now()->subYear(),
            // Old web POS stored subtotal after product discount, while discount
            // also included that product discount. Keep those historical values.
            'subtotal' => 90,
            'discount' => 10,
            'tax' => 9,
            'total' => 99,
            'payment_method' => 'tunai',
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_sale' => 100,
            'subtotal' => 90,
            'discount' => null,
            'tax' => null,
        ]);

        $this->get(route('pemasukan.show', ['sale' => $sale->id]))
            ->assertOk()
            ->assertViewHas('headerTotalsReconcile', false)
            ->assertSee('Komponen header transaksi menggunakan definisi lama')
            ->assertSee('Rp 99');
    }

    private function createOwner(): User
    {
        $store = Store::create(['name' => 'Store ' . uniqid()]);

        return User::factory()->create([
            'store_id' => $store->id,
            'role' => 'admin',
        ]);
    }

    private function createProduct(User $owner, array $overrides = []): Product
    {
        $this->actingAs($owner);

        return Product::create(array_merge([
            'name' => 'Produk ' . uniqid(),
            'buy_price' => 50,
            'sell_price' => 100,
            'stock' => 10,
            'unit' => 'pcs',
            'tax_rate' => 0,
            'discount_rate' => 0,
        ], $overrides));
    }

    private function createSale(
        User $owner,
        Product $product,
        $date,
        float $subtotal,
        float $discount,
        float $tax,
        float $total
    ): Sale {
        $this->actingAs($owner);
        $sale = Sale::create([
            'user_id' => $owner->id,
            'invoice_number' => 'INV-' . uniqid(),
            'transaction_date' => $date,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'payment_method' => 'tunai',
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_sale' => $subtotal,
            'subtotal' => $subtotal,
            'discount' => null,
            'tax' => null,
        ]);

        return $sale;
    }
}
