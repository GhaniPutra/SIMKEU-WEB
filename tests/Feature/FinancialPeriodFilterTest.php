<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FinancialPeriodFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_period_and_payment_filters_work_together_and_all_includes_history(): void
    {
        $user = $this->actAsAdmin();
        $oldSale = $this->createSale($user, 500, now()->subMonths(6), 'transfer');
        $this->createSale($user, 150, now()->subDay(), 'tunai');

        $this->get(route('pemasukan.index', [
            'period' => 'all',
            'metode' => 'transfer',
            'search' => $oldSale->invoice_number,
        ]))
            ->assertOk()
            ->assertViewHas('penjualanList', fn ($sales) => $sales->total() === 1 && $sales->first()->id === $oldSale->id)
            ->assertViewHas('totalBersih', 650)
            ->assertSee('Semua Periode')
            ->assertSee(route('pemasukan.show', [
                'period' => 'all',
                'metode' => 'transfer',
                'search' => $oldSale->invoice_number,
                'sale' => $oldSale->id,
            ]))
            ->assertDontSee('name="analytics_period"', false);

        $this->get(route('pemasukan.index', ['period' => 'month']))
            ->assertOk()
            ->assertViewHas('penjualanList', fn ($sales) => $sales->total() === 1)
            ->assertViewHas('totalBersih', 150);

        $detailQuery = ['period' => 'all', 'metode' => 'transfer', 'search' => $oldSale->invoice_number];
        $backUrl = str_replace('&', '&amp;', route('pemasukan.index', $detailQuery));
        $this->get(route('pemasukan.show', array_merge($detailQuery, ['sale' => $oldSale])))
            ->assertOk()
            ->assertSee('href="' . $backUrl . '"', false);
    }

    public function test_expense_period_and_search_category_filters_work_together_with_all_history(): void
    {
        $user = $this->actAsAdmin();
        $this->createExpense($user, 350, now()->subMonths(6), 'listrik', 'Tagihan historis');
        $this->createExpense($user, 90, now()->subDay(), 'air', 'Tagihan bulan ini');
        $this->createExpense($user, 20, now()->startOfMonth(), 'air', 'Tagihan awal bulan');

        $this->get(route('pengeluaran.index', [
            'period' => 'all',
            'kategori' => 'listrik',
            'search' => 'historis',
        ]))
            ->assertOk()
            ->assertViewHas('pengeluaranList', fn ($expenses) => $expenses->total() === 1)
            ->assertViewHas('totalBulanIni', 460)
            ->assertSee('Semua Periode');

        $this->get(route('pengeluaran.index', ['period' => 'month']))
            ->assertOk()
            ->assertViewHas('pengeluaranList', fn ($expenses) => $expenses->total() === 2)
            ->assertViewHas('totalBulanIni', 110);
    }

    public function test_employee_sales_dashboard_uses_global_period_and_employee_scope(): void
    {
        $admin = $this->actAsAdmin();
        $employee = User::factory()->create(['role' => 'pegawai']);
        $otherEmployee = User::factory()->create(['role' => 'pegawai']);
        $this->createSale($employee, 125, now()->subMonths(6), 'tunai');
        $this->createSale($employee, 75, now()->subDay(), 'tunai');
        $this->createSale($otherEmployee, 900, now()->subMonths(6), 'tunai');

        $this->get(route('employees.sales-dashboard', ['employee' => $employee, 'period' => 'all']))
            ->assertOk()
            ->assertViewHas('totalSales', 2)
            ->assertViewHas('totalRevenue', 200)
            ->assertViewHas('periodLabel', 'Semua Periode')
            ->assertSee('Seluruh riwayat transaksi')
            ->assertDontSee('900');

        $this->get(route('employees.sales-dashboard', ['employee' => $employee, 'period' => 'month']))
            ->assertOk()
            ->assertViewHas('totalSales', 1)
            ->assertViewHas('totalRevenue', 75);
    }

    private function actAsAdmin(): User
    {
        $store = Store::create(['name' => 'Test store']);
        $user = User::factory()->create(['role' => 'admin', 'store_id' => $store->id]);
        $this->actingAs($user);

        return $user;
    }

    private function createSale(User $user, float $total, $date, string $paymentMethod): Sale
    {
        return Sale::create([
            'user_id' => $user->id,
            'invoice_number' => 'INV-' . Str::upper(Str::random(12)),
            'transaction_date' => $date,
            'subtotal' => $total,
            'tax' => 0,
            'discount' => 0,
            'total' => $total,
            'payment_method' => $paymentMethod,
        ]);
    }

    private function createExpense(User $user, float $amount, $date, string $category, string $description): Expense
    {
        return Expense::create([
            'user_id' => $user->id,
            'expense_date' => $date->toDateString(),
            'amount' => $amount,
            'category' => $category,
            'description' => $description,
        ]);
    }
}
