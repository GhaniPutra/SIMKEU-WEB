<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        // ensure key components appear
        $response->assertSee('Pendapatan');
        $response->assertSee('Pengeluaran');
        $response->assertSee('trendChart');
    }

    public function test_dashboard_does_not_show_daily_transaction_banner(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $sale = $this->createSale($user, 100, now());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('1 transaksi dicatat hari ini')
            ->assertSee($sale->invoice_number);
    }

    public function test_dashboard_employee_kpi_counts_only_employee_accounts(): void
    {
        $employee = User::factory()->create(['role' => 'pegawai']);
        User::factory()->create(['role' => 'admin', 'store_id' => $employee->store_id]);
        User::factory()->create(['role' => 'pegawai', 'store_id' => $employee->store_id]);
        $this->actingAs($employee);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('activeEmployees', 2);
    }

    public function test_global_period_filters_dashboard_totals_lists_and_all_time_chart(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $historicalSale = $this->createSale($user, 500, now()->subMonths(7));
        $recentSale = $this->createSale($user, 100, now()->subDay());
        Expense::create([
            'user_id' => $user->id,
            'expense_date' => now()->subMonths(7)->toDateString(),
            'amount' => 300,
            'category' => 'listrik',
            'description' => 'Pengeluaran lama',
        ]);
        Expense::create([
            'user_id' => $user->id,
            'expense_date' => now()->subDay()->toDateString(),
            'amount' => 50,
            'category' => 'air',
            'description' => 'Pengeluaran bulan ini',
        ]);

        $this->get(route('dashboard', ['period' => 'month']))
            ->assertOk()
            ->assertViewHas('totalRevenueMonth', 100)
            ->assertViewHas('totalExpenseMonth', 50)
            ->assertViewHas('transactionsPeriod', 1)
            ->assertViewHas('expenseCategoryLabels', ['Air'])
            ->assertViewHas('expenseCategoryData', [50.0])
            ->assertViewHas('revenueTrendData', fn ($data) => array_sum($data) === 100.0)
            ->assertViewHas('expenseTrendData', fn ($data) => array_sum($data) === 50.0)
            ->assertViewHas('recentTransactions', fn ($transactions) => $transactions->count() === 2
                && $transactions->contains(fn ($item) => str_contains($item['url'], (string) $recentSale->id))
                && $transactions->contains(fn ($item) => $item['type'] === 'expense'));

        $allPeriodResponse = $this->get(route('dashboard', ['period' => 'all']));
        $allPeriodResponse
            ->assertOk()
            ->assertViewHas('periodLabel', 'Semua Periode')
            ->assertViewHas('totalRevenueMonth', 600)
            ->assertViewHas('totalExpenseMonth', 350)
            ->assertViewHas('transactionsPeriod', 2)
            ->assertViewHas('trendLabels', fn ($labels) => count($labels) >= 8)
            ->assertViewHas('revenueTrendData', fn ($data) => array_sum($data) === 600.0)
            ->assertViewHas('expenseCategoryLabels', ['Listrik', 'Air'])
            ->assertViewHas('expenseCategoryData', [300.0, 50.0])
            ->assertViewHas('expenseTrendData', fn ($data) => array_sum($data) === 350.0)
            ->assertViewHas('recentTransactions', fn ($transactions) => $transactions->contains(
                fn ($item) => str_contains($item['url'], (string) $historicalSale->id)
            ));
        $this->assertSame(
            count($allPeriodResponse->viewData('trendLabels')),
            $allPeriodResponse->viewData('allPeriodMonthCount')
        );

        $this->withCookie('dashboardPeriod', 'all')
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('period', 'all')
            ->assertViewHas('totalRevenueMonth', 600);
    }

    public function test_today_chart_groups_income_and_expense_into_one_day_without_changing_kpis(): void
    {
        $this->freezeDashboardDate();
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->createSale($user, 300, Carbon::parse('2026-10-14 09:00:00', 'Asia/Jakarta'));
        $this->createExpense($user, 100, '2026-10-14');
        $this->createSale($user, 900, Carbon::parse('2026-10-13 09:00:00', 'Asia/Jakarta'));

        $response = $this->get(route('dashboard', ['period' => 'today']))->assertOk();

        $this->assertSame(['Hari Ini'], $response->viewData('trendLabels'));
        $this->assertSame([300.0], $response->viewData('revenueTrendData'));
        $this->assertSame([100.0], $response->viewData('expenseTrendData'));
        $this->assertSame(300, $response->viewData('totalRevenueToday'));
        $this->assertSame(100, $response->viewData('totalExpenseToday'));
        $this->assertSame(300, $response->viewData('totalRevenueMonth'));
        $this->assertSame(100, $response->viewData('totalExpenseMonth'));
    }

    public function test_week_chart_shows_monday_through_today_and_keeps_empty_days_at_zero(): void
    {
        $this->freezeDashboardDate();
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->createSale($user, 100, Carbon::parse('2026-10-12 09:00:00', 'Asia/Jakarta'));
        $this->createSale($user, 300, Carbon::parse('2026-10-14 09:00:00', 'Asia/Jakarta'));
        $this->createExpense($user, 50, '2026-10-12');
        $this->createExpense($user, 20, '2026-10-14');

        $response = $this->get(route('dashboard', ['period' => 'week']))->assertOk();

        $this->assertSame(['Mon', 'Tue', 'Wed'], $response->viewData('trendLabels'));
        $this->assertSame([100.0, 0.0, 300.0], $response->viewData('revenueTrendData'));
        $this->assertSame([50.0, 0.0, 20.0], $response->viewData('expenseTrendData'));
        $this->assertSame(400, $response->viewData('totalRevenueMonth'));
        $this->assertSame(70, $response->viewData('totalExpenseMonth'));
        $this->assertSame(2, $response->viewData('transactionsPeriod'));
    }

    public function test_month_chart_shows_only_dates_from_first_to_today_and_keeps_kpis_unchanged(): void
    {
        $this->freezeDashboardDate();
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->createSale($user, 100, Carbon::parse('2026-10-01 09:00:00', 'Asia/Jakarta'));
        $this->createSale($user, 300, Carbon::parse('2026-10-14 09:00:00', 'Asia/Jakarta'));
        $this->createExpense($user, 20, '2026-10-01');
        $this->createExpense($user, 30, '2026-10-14');

        $response = $this->get(route('dashboard', ['period' => 'month']))->assertOk();
        $expectedLabels = array_map(
            fn (int $day): string => sprintf('%02d/10', $day),
            range(1, 14)
        );

        $this->assertSame($expectedLabels, $response->viewData('trendLabels'));
        $this->assertSame([100.0, ...array_fill(0, 12, 0.0), 300.0], $response->viewData('revenueTrendData'));
        $this->assertSame([20.0, ...array_fill(0, 12, 0.0), 30.0], $response->viewData('expenseTrendData'));
        $this->assertSame(400, $response->viewData('totalRevenueMonth'));
        $this->assertSame(50, $response->viewData('totalExpenseMonth'));
        $this->assertSame(2, $response->viewData('transactionsPeriod'));
    }

    public function test_all_period_chart_keeps_contiguous_historical_months_and_store_isolation(): void
    {
        $this->freezeDashboardDate();
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->createSale($user, 100, Carbon::parse('2026-01-10 09:00:00', 'Asia/Jakarta'));
        $this->createSale($user, 300, Carbon::parse('2026-03-10 09:00:00', 'Asia/Jakarta'));
        $this->createExpense($user, 20, '2026-01-10');
        $this->createExpense($user, 30, '2026-03-10');

        $otherStore = Store::create(['name' => 'Toko lain']);
        $this->createSale(
            $user,
            5000,
            Carbon::parse('2026-04-10 09:00:00', 'Asia/Jakarta'),
            $otherStore->id
        );
        $this->createExpense($user, 6000, '2026-04-10', $otherStore->id);

        $response = $this->get(route('dashboard', ['period' => 'all']))->assertOk();

        $this->assertSame(['01/2026', '02/2026', '03/2026'], $response->viewData('trendLabels'));
        $this->assertSame([100.0, 0.0, 300.0], $response->viewData('revenueTrendData'));
        $this->assertSame([20.0, 0.0, 30.0], $response->viewData('expenseTrendData'));
        $this->assertSame(3, $response->viewData('allPeriodMonthCount'));
        $this->assertSame(400, $response->viewData('totalRevenueMonth'));
        $this->assertSame(50, $response->viewData('totalExpenseMonth'));
        $this->assertSame(2, $response->viewData('transactionsPeriod'));
    }

    private function freezeDashboardDate(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-14 12:30:00', 'Asia/Jakarta'));
    }

    private function createSale(User $user, float $total, $date, ?int $storeId = null): Sale
    {
        $sale = Sale::create([
            'user_id' => $user->id,
            'invoice_number' => 'INV-' . uniqid(),
            'transaction_date' => $date,
            'subtotal' => $total,
            'tax' => 0,
            'discount' => 0,
            'total' => $total,
            'payment_method' => 'tunai',
        ]);

        if ($storeId !== null && $storeId !== $user->store_id) {
            $sale->store_id = $storeId;
            $sale->save();
        }

        return $sale;
    }

    private function createExpense(User $user, float $amount, string $date, ?int $storeId = null): void
    {
        DB::table('expenses')->insert([
            'user_id' => $user->id,
            'store_id' => $storeId ?? $user->store_id,
            'expense_date' => $date,
            'amount' => $amount,
            'category' => 'listrik',
            'description' => 'Data uji grafik',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
