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

        $response = $this->get(route('employees.manage', ['employee' => $employee, 'period' => 'month']));
        $response->assertOk()
            ->assertViewHas('periodTotals', fn ($totals) => (int) $totals->transactions === 2 && (float) $totals->revenue === 225.0)
            ->assertViewHas('periodItemsSold', 9)
            ->assertViewHas('trendHasTransactions', true)
            ->assertViewHas('topProducts', fn ($products) => $products->first()->id === $product->id && (int) $products->first()->units_sold === 7);
    }

    public function test_manage_dashboard_distinguishes_unallocated_empty_low_and_normal_employee_stock(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);
        $emptyProduct = Product::create(['name' => 'Stok Kosong', 'stock' => 30, 'unit' => 'pcs']);
        $lowProduct = Product::create(['name' => 'Stok Rendah', 'stock' => 20, 'unit' => 'pcs']);
        $normalProduct = Product::create(['name' => 'Stok Normal', 'stock' => 15, 'unit' => 'pcs']);
        $unallocatedProduct = Product::create(['name' => 'Belum Dialokasikan', 'stock' => 5, 'unit' => 'pcs']);
        $employee->products()->attach($emptyProduct->id, ['stock' => 0]);
        $employee->products()->attach($lowProduct->id, ['stock' => Product::LOW_STOCK_THRESHOLD]);
        $employee->products()->attach($normalProduct->id, ['stock' => 18]);

        $response = $this->get(route('employees.manage', ['employee' => $employee, 'tab' => 'stock']));

        $response->assertOk()
            ->assertViewHas('stockSummary', fn ($summary) => (int) $summary->employee_stock_total === 28
                && (int) $summary->empty_stock_count === 1
                && (int) $summary->low_stock_count === 1
                && (int) $summary->normal_stock_count === 1
                && (int) $summary->unallocated_stock_count === 1)
            ->assertViewHas('stockAttention', fn ($products) => $products->pluck('id')->all() === [$emptyProduct->id, $lowProduct->id])
            ->assertViewHas('products', fn ($products) => $products->total() === 4
                && $products->firstWhere('id', $unallocatedProduct->id)->employee_stock === null)
            ->assertSee('Belum dialokasikan')
            ->assertSee('Kosong')
            ->assertSee('Rendah')
            ->assertSee('Normal')
            ->assertSee('28 <small>unit</small>', false);

        $this->get(route('employees.manage', [
            'employee' => $employee,
            'tab' => 'stock',
            'stock_status' => 'low',
        ]))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 1 && $products->first()->name === 'Stok Rendah');
        $this->get(route('employees.manage', ['employee' => $employee, 'tab' => 'stock', 'stock_status' => 'empty']))
            ->assertViewHas('products', fn ($products) => $products->total() === 1 && $products->first()->name === 'Stok Kosong');
        $this->get(route('employees.manage', ['employee' => $employee, 'tab' => 'stock', 'stock_status' => 'unallocated']))
            ->assertViewHas('products', fn ($products) => $products->total() === 1 && $products->first()->name === 'Belum Dialokasikan');

        $this->get(route('employees.manage', $employee))
            ->assertViewHas('stockSummary', fn ($summary) => (int) $summary->empty_stock_count === 1
                && (int) $summary->low_stock_count === 1
                && (int) $summary->normal_stock_count === 1
                && (int) $summary->unallocated_stock_count === 1)
            ->assertViewHas('stockAttention', fn ($products) => $products->pluck('id')->all() === [$emptyProduct->id, $lowProduct->id])
            ->assertSee('1 kosong · 1 rendah');
    }

    public function test_employee_with_no_allocated_products_has_no_employee_stock_alerts(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);
        Product::create(['name' => 'Produk gudang normal', 'stock' => 30, 'unit' => 'pcs']);
        Product::create(['name' => 'Produk gudang kosong', 'stock' => 0, 'unit' => 'pcs']);

        $this->get(route('employees.manage', ['employee' => $employee, 'tab' => 'stock']))
            ->assertOk()
            ->assertViewHas('stockSummary', fn ($summary) => (int) $summary->employee_stock_total === 0
                && (int) $summary->empty_stock_count === 0
                && (int) $summary->low_stock_count === 0
                && (int) $summary->normal_stock_count === 0
                && (int) $summary->unallocated_stock_count === 2)
            ->assertViewHas('stockAttention', fn ($products) => $products->isEmpty())
            ->assertViewHas('products', fn ($products) => $products->total() === 2
                && $products->every(fn ($product) => $product->employee_stock === null))
            ->assertSee('Belum dialokasikan')
            ->assertSee('0 kosong · 0 rendah');
    }

    public function test_manage_dashboard_shows_empty_sales_states_for_an_employee_without_transactions(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);

        $this->get(route('employees.manage', $employee))
            ->assertOk()
            ->assertViewHas('periodTotals', fn ($totals) => (int) $totals->transactions === 0 && (float) $totals->revenue === 0.0)
            ->assertViewHas('periodItemsSold', 0)
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
            'period' => 'month',
            'sales_page' => 2,
        ]))
            ->assertOk()
            ->assertViewHas('sales', fn ($sales) => $sales->total() === 16 && $sales->currentPage() === 2 && $sales->getPageName() === 'sales_page')
            ->assertViewHas('products', fn ($products) => $products->getPageName() === 'products_page');
    }

    public function test_global_all_period_includes_employee_history_without_mixing_other_employees(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);
        $otherEmployee = User::factory()->create(['role' => 'pegawai']);
        $historicalSale = $this->createSale($employee, 125, now()->subMonths(8));
        $recentSale = $this->createSale($employee, 75, now()->subDay());
        $foreignSale = $this->createSale($otherEmployee, 900, now()->subMonths(8));

        $this->get(route('employees.manage', [
            'employee' => $employee,
            'period' => 'all',
            'tab' => 'sales',
        ]))
            ->assertOk()
            ->assertViewHas('periodTotals', fn ($totals) => (int) $totals->transactions === 2 && (float) $totals->revenue === 200.0)
            ->assertViewHas('sales', fn ($sales) => $sales->total() === 2
                && $sales->pluck('id')->all() === [$recentSale->id, $historicalSale->id])
            ->assertViewHas('analyticsPeriod', 'all')
            ->assertSee('Semua Periode')
            ->assertDontSee($foreignSale->invoice_number);
    }

    public function test_global_month_period_excludes_employee_sales_outside_this_month(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);
        $this->createSale($employee, 125, now()->subMonths(8));
        $this->createSale($employee, 75, now()->subDay());

        $this->get(route('employees.manage', ['employee' => $employee, 'period' => 'month']))
            ->assertOk()
            ->assertViewHas('periodTotals', fn ($totals) => (int) $totals->transactions === 1 && (float) $totals->revenue === 75.0)
            ->assertViewHas('sales', fn ($sales) => $sales->total() === 1)
            ->assertSee('Bulan Ini');
    }

    public function test_stock_transfer_endpoint_updates_real_master_and_employee_balances(): void
    {
        $this->actAsConfiguredAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);
        $product = Product::create(['name' => 'Produk Transfer', 'stock' => 8, 'unit' => 'pcs']);

        $this->postJson(route('employees.stock.transfer', $employee), [
            'product_id' => $product->id,
            'amount' => 3,
        ])->assertCreated()
            ->assertJson([
                'success' => true,
                'master_stock' => 5,
                'employee_stock' => 0,
            ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
        $this->assertDatabaseMissing('product_user', ['product_id' => $product->id, 'user_id' => $employee->id]);

        $this->postJson(route('employees.stock.transfer', $employee), [
            'product_id' => $product->id,
            'amount' => 100,
        ])->assertStatus(422);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
        $this->assertDatabaseMissing('product_user', ['product_id' => $product->id, 'user_id' => $employee->id]);
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
