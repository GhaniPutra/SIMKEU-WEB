<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
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

    public function test_custom_range_filters_income_and_expense_pages_inclusively(): void
    {
        $user = $this->actAsAdmin();
        $this->createSale($user, 10, Carbon::parse('2026-06-24 23:59:59', 'Asia/Jakarta'), 'tunai');
        $this->createSale($user, 100, Carbon::parse('2026-06-25 00:00:00', 'Asia/Jakarta'), 'tunai');
        $this->createSale($user, 200, Carbon::parse('2026-07-05 23:59:59', 'Asia/Jakarta'), 'tunai');
        $this->createSale($user, 20, Carbon::parse('2026-07-06 00:00:00', 'Asia/Jakarta'), 'tunai');
        $this->createExpense($user, 10, Carbon::parse('2026-06-24'), 'listrik', 'Di luar rentang');
        $this->createExpense($user, 25, Carbon::parse('2026-06-25'), 'listrik', 'Awal rentang');
        $this->createExpense($user, 45, Carbon::parse('2026-07-05'), 'air', 'Akhir rentang');
        $this->createExpense($user, 20, Carbon::parse('2026-07-06'), 'air', 'Di luar rentang');

        $range = [
            'period' => 'custom',
            'start_date' => '2026-06-25',
            'end_date' => '2026-07-05',
        ];

        $this->get(route('pemasukan.index', $range))
            ->assertOk()
            ->assertViewHas('penjualanList', fn ($sales) => $sales->total() === 2)
            ->assertViewHas('totalBersih', 300)
            ->assertViewHas('jumlahTransaksi', 2)
            ->assertViewHas('periodLabel', '25 Jun 2026 – 5 Jul 2026');

        $expenseResponse = $this->get(route('pengeluaran.index', $range))
            ->assertOk();
        $expenseResponse
            ->assertViewHas('totalBulanIni', 70)
            ->assertViewHas('jumlahEntri', 2)
            ->assertViewHas('pengeluaranList', fn ($expenses) => $expenses->total() === 2)
            ->assertViewHas('periodLabel', '25 Jun 2026 – 5 Jul 2026');

        $this->assertSame(11, count($expenseResponse->viewData('chartLabels')));
        $this->assertSame('25/06', $expenseResponse->viewData('chartLabels')[0]);
        $this->assertSame('05/07', $expenseResponse->viewData('chartLabels')[10]);
        $this->assertSame(70.0, array_sum($expenseResponse->viewData('chartData')));
        $this->assertNull($expenseResponse->viewData('periodLaluStart'));
    }

    public function test_long_custom_range_uses_filtered_monthly_expense_and_employee_charts(): void
    {
        $admin = $this->actAsAdmin();
        $this->createExpense($admin, 5, Carbon::parse('2026-01-09'), 'listrik', 'Sebelum rentang');
        $this->createExpense($admin, 100, Carbon::parse('2026-01-10'), 'listrik', 'Awal rentang');
        $this->createExpense($admin, 200, Carbon::parse('2026-05-10'), 'air', 'Akhir rentang');
        $this->createExpense($admin, 7, Carbon::parse('2026-05-11'), 'air', 'Sesudah rentang');

        $range = [
            'period' => 'custom',
            'start_date' => '2026-01-10',
            'end_date' => '2026-05-10',
        ];
        $expenseResponse = $this->get(route('pengeluaran.index', $range))->assertOk();

        $expenseResponse
            ->assertViewHas('totalBulanIni', 300)
            ->assertViewHas('chartLabels', ['01/2026', '02/2026', '03/2026', '04/2026', '05/2026'])
            ->assertViewHas('chartData', [100.0, 0.0, 0.0, 0.0, 200.0]);

        $employee = User::factory()->create(['role' => 'pegawai', 'store_id' => $admin->store_id]);
        $this->createSale($employee, 5, Carbon::parse('2026-01-09 23:59:59', 'Asia/Jakarta'), 'tunai');
        $this->createSale($employee, 100, Carbon::parse('2026-01-10 00:00:00', 'Asia/Jakarta'), 'tunai');
        $this->createSale($employee, 200, Carbon::parse('2026-05-10 23:59:59', 'Asia/Jakarta'), 'tunai');
        $this->createSale($employee, 7, Carbon::parse('2026-05-11 00:00:00', 'Asia/Jakarta'), 'tunai');

        $this->get(route('employees.manage', ['employee' => $employee] + $range))
            ->assertOk()
            ->assertViewHas('periodTotals', fn ($totals) => (float) $totals->revenue === 300.0)
            ->assertViewHas('trendLabels', ['01/2026', '02/2026', '03/2026', '04/2026', '05/2026'])
            ->assertViewHas('trendRevenue', [100.0, 0.0, 0.0, 0.0, 200.0]);
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
