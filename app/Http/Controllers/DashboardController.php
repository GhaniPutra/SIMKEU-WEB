<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Support\PeriodFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $store = null;
        $now = Carbon::now();
        ['period' => $period, 'periodLabel' => $periodLabel, 'periodStart' => $periodStart, 'periodEnd' => $periodEnd]
            = PeriodFilter::resolveFromRequest($request, $now);

        $totalRevenueToday = Sale::whereBetween('transaction_date', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->sum('total');
        $totalExpenseToday = Expense::where('expense_date', $now->toDateString())->sum('amount');
        $totalRevenueMonth = PeriodFilter::apply(Sale::query(), 'transaction_date', $periodStart, $periodEnd)->sum('total');
        $totalExpenseMonth = PeriodFilter::apply(
            Expense::query(),
            'expense_date',
            $periodStart?->toDateString(),
            $periodEnd?->toDateString()
        )->sum('amount');
        $grossProfit = $totalRevenueMonth - $totalExpenseMonth;
        $netProfit = $grossProfit;

        $transactionsPeriod = PeriodFilter::apply(Sale::query(), 'transaction_date', $periodStart, $periodEnd)->count();

        [$comparisonStart, $comparisonEnd] = match ($period) {
            'today' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'week' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            default => [null, null],
        };
        $hasComparisonPeriod = $comparisonStart !== null;
        $comparisonLabel = match ($period) {
            'today' => 'Dibanding kemarin',
            'week' => 'Dibanding minggu lalu',
            'month' => 'Dibanding bulan lalu',
            default => null,
        };

        $previousRevenue = $hasComparisonPeriod
            ? PeriodFilter::apply(Sale::query(), 'transaction_date', $comparisonStart, $comparisonEnd)->sum('total')
            : null;
        $previousExpense = $hasComparisonPeriod
            ? PeriodFilter::apply(
                Expense::query(),
                'expense_date',
                $comparisonStart->toDateString(),
                $comparisonEnd->toDateString()
            )->sum('amount')
            : null;
        $previousTransactions = $hasComparisonPeriod
            ? PeriodFilter::apply(Sale::query(), 'transaction_date', $comparisonStart, $comparisonEnd)->count()
            : null;
        $previousNetProfit = $hasComparisonPeriod ? $previousRevenue - $previousExpense : null;

        $calculateTrendPercent = static function (float|int $current, float|int|null $previous): float {
            if ($previous === null) {
                return 0.0;
            }

            if ((float) $previous === 0.0) {
                return (float) $current === 0.0 ? 0.0 : ((float) $current > 0 ? 100.0 : -100.0);
            }

            return (($current - $previous) / abs($previous)) * 100;
        };
        $trendLabelFor = static fn (float $percent): string => $percent > 0
            ? 'trend-up'
            : ($percent < 0 ? 'trend-down' : 'trend-neutral');

        $revenueTrendPercent = $calculateTrendPercent($totalRevenueMonth, $previousRevenue);
        $expenseTrendPercent = $calculateTrendPercent($totalExpenseMonth, $previousExpense);
        $transactionsTrendPercent = $calculateTrendPercent($transactionsPeriod, $previousTransactions);
        $netProfitTrendPercent = $calculateTrendPercent($netProfit, $previousNetProfit);
        $revenueTrendLabel = $trendLabelFor($revenueTrendPercent);
        $expenseTrendLabel = $trendLabelFor($expenseTrendPercent);
        $transactionsTrendLabel = $trendLabelFor($transactionsTrendPercent);
        $netProfitTrendLabel = $trendLabelFor($netProfitTrendPercent);
        $revenueTrendText = number_format(abs($revenueTrendPercent), 1, ',', '.') . '%';
        $expenseTrendText = number_format(abs($expenseTrendPercent), 1, ',', '.') . '%';
        $transactionsTrendText = number_format(abs($transactionsTrendPercent), 1, ',', '.') . '%';
        $netProfitTrendText = number_format(abs($netProfitTrendPercent), 1, ',', '.') . '%';
        $activeEmployees = User::where('role', 'pegawai')->count();

        $trendLabels = [];
        $revenueTrendData = [];
        $expenseTrendData = [];

        if ($period === 'all') {
            $driver = DB::connection()->getDriverName();
            $saleMonth = match ($driver) {
                'mysql', 'mariadb' => "DATE_FORMAT(transaction_date, '%Y-%m')",
                'pgsql' => "TO_CHAR(transaction_date, 'YYYY-MM')",
                default => "strftime('%Y-%m', transaction_date)",
            };
            $expenseMonth = str_replace('transaction_date', 'expense_date', $saleMonth);
            $revenueByMonth = Sale::query()
                ->selectRaw("$saleMonth as month_key, SUM(total) as amount")
                ->groupByRaw($saleMonth)
                ->orderBy('month_key')
                ->pluck('amount', 'month_key');
            $expenseByMonth = Expense::query()
                ->selectRaw("$expenseMonth as month_key, SUM(amount) as amount")
                ->groupByRaw($expenseMonth)
                ->orderBy('month_key')
                ->pluck('amount', 'month_key');

            $months = $revenueByMonth->keys()->merge($expenseByMonth->keys())->sort()->values();
            if ($months->isNotEmpty()) {
                $firstMonth = Carbon::createFromFormat('!Y-m', $months->first());
                $lastMonth = Carbon::createFromFormat('!Y-m', $months->last());
                for ($month = $firstMonth; $month->lte($lastMonth); $month->addMonth()) {
                    $key = $month->format('Y-m');
                    $trendLabels[] = $month->format('m/Y');
                    $revenueTrendData[] = (float) ($revenueByMonth[$key] ?? 0);
                    $expenseTrendData[] = (float) ($expenseByMonth[$key] ?? 0);
                }
            }
        } elseif ($period === 'today') {
            $trendLabels[] = $periodLabel;
            $revenueTrendData[] = (float) $totalRevenueToday;
            $expenseTrendData[] = (float) $totalExpenseToday;
        } else {
            $chartEnd = $periodEnd;
            $revenueByDay = PeriodFilter::apply(Sale::query(), 'transaction_date', $periodStart, $periodEnd)
                ->where('transaction_date', '<=', $now)
                ->selectRaw('DATE(transaction_date) as day_key, SUM(total) as amount')
                ->groupByRaw('DATE(transaction_date)')
                ->pluck('amount', 'day_key');
            $expenseByDay = PeriodFilter::apply(
                Expense::query(),
                'expense_date',
                $periodStart?->toDateString(),
                $periodEnd?->toDateString()
            )
                ->where('expense_date', '<=', $now->toDateString())
                ->selectRaw('expense_date as day_key, SUM(amount) as amount')
                ->groupBy('expense_date')
                ->pluck('amount', 'day_key');

            for ($date = $periodStart->copy(); $date->lte($chartEnd); $date->addDay()) {
                $key = $date->toDateString();
                $trendLabels[] = $period === 'week' ? $date->translatedFormat('D') : $date->format('d/m');
                $revenueTrendData[] = (float) ($revenueByDay[$key] ?? 0);
                $expenseTrendData[] = (float) ($expenseByDay[$key] ?? 0);
            }
        }

        $alerts = [];
        foreach (Product::where('stock', '<=', 10)->orderBy('stock')->take(3)->get() as $product) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Stok produk {$product->name} tinggal {$product->stock} {$product->unit}",
            ];
        }
        if ($totalExpenseMonth > $totalRevenueMonth) {
            $alerts[] = ['type' => 'danger', 'message' => "Pengeluaran {$periodLabel} melebihi pendapatan"];
        }
        $salesActivities = PeriodFilter::apply(Sale::with('user'), 'transaction_date', $periodStart, $periodEnd)
            ->orderByDesc('transaction_date')
            ->take(5)
            ->get()
            ->map(fn ($sale) => [
                'type' => 'sale',
                'message' => "Transaksi {$sale->invoice_number} berhasil diproses oleh {$sale->user->name}",
                'date' => $sale->transaction_date,
            ])
            ->toBase();
        $expenseActivities = PeriodFilter::apply(
            Expense::with('user'),
            'expense_date',
            $periodStart?->toDateString(),
            $periodEnd?->toDateString()
        )
            ->orderByDesc('expense_date')
            ->take(5)
            ->get()
            ->map(fn ($expense) => [
                'type' => 'expense',
                'message' => "Pengeluaran {$expense->category} sebesar Rp " . number_format($expense->amount, 0, ',', '.') . " dicatat oleh {$expense->user->name}",
                'date' => Carbon::parse($expense->expense_date),
            ])
            ->toBase();
        $latestActivities = $salesActivities
            ->merge($expenseActivities)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        $recentTransactions = collect();
        foreach (PeriodFilter::apply(Sale::query(), 'transaction_date', $periodStart, $periodEnd)
            ->orderByDesc('transaction_date')->take(5)->get() as $sale) {
            $recentTransactions->push([
                'sort_date' => $sale->transaction_date,
                'date' => $sale->transaction_date->format('d M Y'),
                'type' => 'revenue',
                'label' => 'Pemasukan',
                'amount' => $sale->total,
                'url' => route('pemasukan.show', ['sale' => $sale->id, 'period' => $period]),
            ]);
        }
        foreach (PeriodFilter::apply(
            Expense::query(),
            'expense_date',
            $periodStart?->toDateString(),
            $periodEnd?->toDateString()
        )->orderByDesc('expense_date')->take(5)->get() as $expense) {
            $recentTransactions->push([
                'sort_date' => Carbon::parse($expense->expense_date),
                'date' => Carbon::parse($expense->expense_date)->format('d M Y'),
                'type' => 'expense',
                'label' => 'Pengeluaran',
                'amount' => $expense->amount,
                'url' => route('pengeluaran.show', ['expense' => $expense->id, 'period' => $period]),
            ]);
        }
        $recentTransactions = $recentTransactions->sortByDesc('sort_date')->take(5)->values();

        return view('dashboard', compact(
            'user',
            'store',
            'period',
            'periodLabel',
            'hasComparisonPeriod',
            'comparisonLabel',
            'totalRevenueToday',
            'totalRevenueMonth',
            'totalExpenseToday',
            'totalExpenseMonth',
            'grossProfit',
            'netProfit',
            'transactionsPeriod',
            'transactionsTrendLabel',
            'transactionsTrendText',
            'netProfitTrendLabel',
            'netProfitTrendText',
            'activeEmployees',
            'trendLabels',
            'revenueTrendData',
            'expenseTrendData',
            'revenueTrendLabel',
            'revenueTrendText',
            'expenseTrendLabel',
            'expenseTrendText',
            'alerts',
            'latestActivities',
            'recentTransactions'
        ));
    }
}
