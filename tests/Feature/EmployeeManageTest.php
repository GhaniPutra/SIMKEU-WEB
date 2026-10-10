<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeManageTest extends TestCase
{
    use RefreshDatabase;

    public function test_manage_dashboard_aggregates_employee_sales_and_best_sellers_from_transaction_data(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);
        $otherEmployee = User::factory()->create(['role' => 'pegawai']);
        $product = Product::create(['name' => 'Produk Terlaris', 'sell_price' => 25, 'stock' => 10, 'unit' => 'pcs']);
        $otherProduct = Product::create(['name' => 'Produk Kedua', 'sell_price' => 40, 'stock' => 8, 'unit' => 'box']);

        $firstSale = $this->createSale($employee, 150, now()->subDays(2));
        SaleItem::create(['sale_id' => $firstSale->id, 'product_id' => $product->id, 'quantity' => 3, 'price_at_sale' => 25, 'subtotal' => 75]);
        SaleItem::create(['sale_id' => $firstSale->id, 'product_id' => $otherProduct->id, 'quantity' => 2, 'price_at_sale' => 40, 'subtotal' => 80]);

        $secondSale = $this->createSale($employee, 75, now()->subDay());
        SaleItem::create(['sale_id' => $secondSale->id, 'product_id' => $product->id, 'quantity' => 4, 'price_at_sale' => 25, 'subtotal' => 100]);

        $foreignSale = $this->createSale($otherEmployee, 900, now()->subDay());
        SaleItem::create(['sale_id' => $foreignSale->id, 'product_id' => $product->id, 'quantity' => 20, 'price_at_sale' => 25, 'subtotal' => 500]);

        $response = $this->get(route('employees.manage', ['employee' => $employee, 'analytics_period' => 7]));
        $response->assertOk()
            ->assertViewHas('salesTotals', fn ($totals) => (int) $totals->transactions === 2 && (float) $totals->revenue === 225.0)
            ->assertViewHas('totalItemsSold', 9)
            ->assertViewHas('periodTotals', fn ($totals) => (int) $totals->transactions === 2 && (float) $totals->revenue === 225.0)
            ->assertViewHas('periodItemsSold', 9)
            ->assertViewHas('trendHasTransactions', true)
            ->assertViewHas('topProducts', fn ($products) => $products->first()->id === $product->id && (int) $products->first()->units_sold === 7);
    }

    public function test_manage_dashboard_separates_empty_low_and_normal_employee_stock_and_filters_rows(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);
        $emptyProduct = Product::create(['name' => 'Stok Kosong', 'stock' => 30, 'unit' => 'pcs']);
        $lowProduct = Product::create(['name' => 'Stok Rendah', 'stock' => 20, 'unit' => 'pcs']);
        $normalProduct = Product::create(['name' => 'Stok Normal', 'stock' => 15, 'unit' => 'pcs']);
        Product::create(['name' => 'Belum Dialokasikan', 'stock' => 5, 'unit' => 'pcs']);
        $employee->products()->attach($emptyProduct->id, ['stock' => 0]);
        $employee->products()->attach($lowProduct->id, ['stock' => 6]);
        $employee->products()->attach($normalProduct->id, ['stock' => 18]);

        $response = $this->get(route('employees.manage', [
            'employee' => $employee,
            'tab' => 'stock',
            'stock_status' => 'low',
        ]));

        $response->assertOk()
            ->assertViewHas('stockSummary', fn ($summary) => (int) $summary->empty_stock_count === 2 && (int) $summary->low_stock_count === 1)
            ->assertViewHas('products', fn ($products) => $products->total() === 1 && $products->first()->name === 'Stok Rendah')
            ->assertSee('Stok pegawai');
    }

    public function test_manage_dashboard_shows_empty_sales_states_for_an_employee_without_transactions(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);

        $this->get(route('employees.manage', $employee))
            ->assertOk()
            ->assertViewHas('salesTotals', fn ($totals) => (int) $totals->transactions === 0 && (float) $totals->revenue === 0.0)
            ->assertViewHas('totalItemsSold', 0)
            ->assertViewHas('trendHasTransactions', false)
            ->assertViewHas('topProducts', fn ($products) => $products->isEmpty())
            ->assertSee('Belum ada transaksi penjualan');
    }

    public function test_sales_table_uses_its_own_pagination_and_period_filter(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);
        for ($saleIndex = 0; $saleIndex < 16; $saleIndex++) {
            $this->createSale($employee, 10 + $saleIndex, now()->subDays(1));
        }

        $this->get(route('employees.manage', [
            'employee' => $employee,
            'tab' => 'sales',
            'analytics_period' => 7,
            'sales_page' => 2,
        ]))
            ->assertOk()
            ->assertViewHas('sales', fn ($sales) => $sales->total() === 16 && $sales->currentPage() === 2 && $sales->getPageName() === 'sales_page')
            ->assertViewHas('products', fn ($products) => $products->getPageName() === 'products_page');
    }

    public function test_stock_transfer_endpoint_updates_real_master_and_employee_balances(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);
        $product = Product::create(['name' => 'Produk Transfer', 'stock' => 8, 'unit' => 'pcs']);

        $this->postJson(route('employees.stock.transfer', $employee), [
            'product_id' => $product->id,
            'amount' => 3,
        ])->assertOk()
            ->assertJson([
                'success' => true,
                'master_stock' => 5,
                'employee_stock' => 3,
            ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
        $this->assertDatabaseHas('product_user', ['product_id' => $product->id, 'user_id' => $employee->id, 'stock' => 3]);

        $this->postJson(route('employees.stock.transfer', $employee), [
            'product_id' => $product->id,
            'amount' => 100,
        ])->assertBadRequest()
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
        $this->assertDatabaseHas('product_user', ['product_id' => $product->id, 'user_id' => $employee->id, 'stock' => 3]);
    }

    private function createSale(User $employee, float $total, $date): Sale
    {
        return Sale::create([
            'user_id' => $employee->id,
            'invoice_number' => 'INV-' . Str::upper(Str::random(12)),
            'transaction_date' => $date,
            'subtotal' => $total,
            'tax' => 0,
            'discount' => 0,
            'total' => $total,
            'payment_method' => 'tunai',
        ]);
    }

    private function actAsConfiguredAdmin(): void
    {
        $store = Store::create(['name' => 'Test store']);
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'store_id' => $store->id,
        ]));
    }
}
