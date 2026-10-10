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
            ->assertViewHas('recentTransactions', fn ($transactions) => $transactions->count() === 2
                && $transactions->contains(fn ($item) => str_contains($item['url'], (string) $recentSale->id))
                && $transactions->contains(fn ($item) => $item['type'] === 'expense'));

        $this->get(route('dashboard', ['period' => 'all']))
            ->assertOk()
            ->assertViewHas('periodLabel', 'Semua Periode')
            ->assertViewHas('totalRevenueMonth', 600)
            ->assertViewHas('totalExpenseMonth', 350)
            ->assertViewHas('transactionsPeriod', 2)
            ->assertViewHas('trendLabels', fn ($labels) => count($labels) >= 8)
            ->assertViewHas('revenueTrendData', fn ($data) => array_sum($data) === 600.0)
            ->assertViewHas('recentTransactions', fn ($transactions) => $transactions->contains(
                fn ($item) => str_contains($item['url'], (string) $historicalSale->id)
            ));
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
