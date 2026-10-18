<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

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

    private function createSale(User $user, float $total, $date): Sale
    {
        return Sale::create([
            'user_id' => $user->id,
            'invoice_number' => 'INV-' . uniqid(),
            'transaction_date' => $date,
            'subtotal' => $total,
            'tax' => 0,
            'discount' => 0,
            'total' => $total,
            'payment_method' => 'tunai',
        ]);
    }
}
