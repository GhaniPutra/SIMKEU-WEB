<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockAllocation;
use App\Support\PeriodFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** Menyiapkan ringkasan, grafik, peringatan, dan aktivitas untuk halaman dashboard. */
    public function index(Request $request)
    {
        $now = Carbon::now();

        // Tentukan rentang tanggal dan label periode yang dipilih pengguna.
        ['period' => $period, 'periodLabel' => $periodLabel, 'periodStart' => $periodStart, 'periodEnd' => $periodEnd]
            = PeriodFilter::resolveFromRequest($request, $now);

        // Hitung total keuangan dan jumlah transaksi untuk kartu ringkasan.
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
        $netProfit = $totalRevenueMonth - $totalExpenseMonth;

        $transactionsPeriod = PeriodFilter::apply(Sale::query(), 'transaction_date', $periodStart, $periodEnd)->count();

        // Siapkan periode pembanding untuk menghitung perubahan indikator.
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

        // Hitung persentase dan label arah perubahan setiap indikator.
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
        $trendLabels = [];
        $revenueTrendData = [];
        $expenseTrendData = [];

        // Rentang custom yang panjang diringkas per bulan agar grafik tetap ringan.
        $customChartUsesMonths = $period === 'custom'
            && $periodStart->diffInDays($periodEnd->copy()->startOfDay()) >= 90;

        // Bentuk data grafik bulanan, harian, atau ringkasan hari ini.
        if ($period === 'all' || $customChartUsesMonths) {
            $driver = DB::connection()->getDriverName();
            $saleMonth = match ($driver) {
                'mysql', 'mariadb' => "DATE_FORMAT(transaction_date, '%Y-%m')",
                'pgsql' => "TO_CHAR(transaction_date, 'YYYY-MM')",
                default => "strftime('%Y-%m', transaction_date)",
            };
            $revenueQuery = Sale::query();
            $expenseQuery = Expense::query();
            if ($customChartUsesMonths) {
                PeriodFilter::apply($revenueQuery, 'transaction_date', $periodStart, $periodEnd);
                PeriodFilter::apply($expenseQuery, 'expense_date', $periodStart->toDateString(), $periodEnd->toDateString());
            }

            $revenueByMonth = $revenueQuery
                ->selectRaw("$saleMonth as month_key, SUM(total) as amount")
                ->groupByRaw($saleMonth)
                ->orderBy('month_key')
                ->pluck('amount', 'month_key');
            $expenseMonth = match ($driver) {
                'mysql', 'mariadb' => "DATE_FORMAT(expense_date, '%Y-%m')",
                'pgsql' => "TO_CHAR(expense_date, 'YYYY-MM')",
                default => "strftime('%Y-%m', expense_date)",
            };
            $expenseByMonth = $expenseQuery
                ->selectRaw("$expenseMonth as month_key, SUM(amount) as amount")
                ->groupByRaw($expenseMonth)
                ->orderBy('month_key')
                ->pluck('amount', 'month_key');

            if ($customChartUsesMonths) {
                $firstMonth = $periodStart->copy()->startOfMonth();
                $lastMonth = $periodEnd->copy()->startOfMonth();
            } else {
                $months = $revenueByMonth->keys()->merge($expenseByMonth->keys())->unique()->sort()->values();
                $firstMonth = $months->isNotEmpty() ? Carbon::createFromFormat('!Y-m', $months->first()) : null;
                $lastMonth = $months->isNotEmpty() ? Carbon::createFromFormat('!Y-m', $months->last()) : null;
            }

            if ($firstMonth && $lastMonth) {
                for ($month = $firstMonth->copy(); $month->lte($lastMonth); $month->addMonth()) {
                    $key = $month->format('Y-m');
                    $trendLabels[] = $month->format('m/Y');
                    $revenueTrendData[] = (float) ($revenueByMonth[$key] ?? 0);
                    $expenseTrendData[] = (float) ($expenseByMonth[$key] ?? 0);
                }
            }
        } elseif ($period === 'today') {
            // Gunakan hasil ringkasan hari ini agar grafik tidak mengulang dua query agregasi yang sama.
            $trendLabels[] = $periodLabel;
            $revenueTrendData[] = (float) $totalRevenueToday;
            $expenseTrendData[] = (float) $totalExpenseToday;
        } else {
            // Rentang grafik harian berakhir pada tanggal hari ini, bukan akhir
            // minggu/bulan kalender, agar tanggal mendatang tidak ikut ditampilkan.
            $chartStart = $periodStart->copy()->startOfDay();
            $chartEnd = $period === 'custom'
                ? $periodEnd->copy()->startOfDay()
                : $periodEnd->copy()->min($now)->startOfDay();
            $revenueByDay = PeriodFilter::apply(
                Sale::query(),
                'transaction_date',
                $chartStart,
                $chartEnd->copy()->endOfDay()
            )
                ->selectRaw('DATE(transaction_date) as day_key, SUM(total) as amount')
                ->groupByRaw('DATE(transaction_date)')
                ->pluck('amount', 'day_key');
            $expenseByDay = Expense::query()
                ->whereDate('expense_date', '>=', $chartStart->toDateString())
                ->whereDate('expense_date', '<=', $chartEnd->toDateString())
                ->selectRaw('DATE(expense_date) as day_key, SUM(amount) as amount')
                ->groupByRaw('DATE(expense_date)')
                ->pluck('amount', 'day_key');
            for ($date = $chartStart->copy(); $date->lte($chartEnd); $date->addDay()) {
                $key = $date->toDateString();
                $trendLabels[] = $period === 'week' ? $date->translatedFormat('D') : $date->format('d/m');
                $revenueTrendData[] = (float) ($revenueByDay[$key] ?? 0);
                $expenseTrendData[] = (float) ($expenseByDay[$key] ?? 0);
            }
        }

        if ($period === 'all') {
            $allPeriodMonthCount = count($trendLabels);
        } else {
            $salesDateRange = Sale::query()
                ->selectRaw('MIN(transaction_date) as first_date, MAX(transaction_date) as last_date')
                ->first();
            $expensesDateRange = Expense::query()
                ->selectRaw('MIN(expense_date) as first_date, MAX(expense_date) as last_date')
                ->first();
            $recordDates = array_values(array_filter([
                $salesDateRange?->first_date,
                $salesDateRange?->last_date,
                $expensesDateRange?->first_date,
                $expensesDateRange?->last_date,
            ]));

            if ($recordDates === []) {
                $allPeriodMonthCount = 0;
            } else {
                sort($recordDates, SORT_STRING);
                $firstMonth = Carbon::parse($recordDates[0]);
                $lastMonth = Carbon::parse($recordDates[array_key_last($recordDates)]);
                $allPeriodMonthCount = (($lastMonth->year - $firstMonth->year) * 12)
                    + ($lastMonth->month - $firstMonth->month)
                    + 1;
            }
        }

        $expenseCategories = [
            'beli_stok' => ['label' => 'Pembelian Stok', 'color' => '#cf0621'],
            'gaji' => ['label' => 'Gaji', 'color' => '#d71e37'],
            'transport' => ['label' => 'Transportasi', 'color' => '#df374e'],
            'sewa' => ['label' => 'Sewa', 'color' => '#e75064'],
            'listrik' => ['label' => 'Listrik', 'color' => '#ef687a'],
            'air' => ['label' => 'Air', 'color' => '#f78091'],
            'lainnya' => ['label' => 'Lainnya', 'color' => '#ff99a7'],
        ];
        $expensesByCategory = PeriodFilter::apply(
            Expense::query(),
            'expense_date',
            $periodStart?->toDateString(),
            $periodEnd?->toDateString()
        )
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');
        $expenseCategoryLabels = [];
        $expenseCategoryData = [];
        $expenseCategoryColors = [];
        foreach ($expenseCategories as $category => $info) {
            $total = (float) ($expensesByCategory[$category] ?? 0);
            if ($total <= 0) {
                continue;
            }

            $expenseCategoryLabels[] = $info['label'];
            $expenseCategoryData[] = $total;
            $expenseCategoryColors[] = $info['color'];
        }

        $currentUser = $request->user();
        $canViewSales = $currentUser->hasPermission('sales.view');
        $canViewExpenses = $currentUser->hasPermission('expenses.view');
        $canViewProducts = $currentUser->hasPermission('products.view');
        $canViewStockAllocations = $currentUser->hasPermission('stock_allocations.view');
        $canViewActivityHistory = $currentUser->hasPermission('activities.view');
        $canShowActivitySummary = $canViewActivityHistory || $canViewSales || $canViewExpenses || $canViewProducts || $canViewStockAllocations;
        $canViewEmployeeDetails = $currentUser->hasPermission('employees.sales.view');
        $isAllocationManager = $currentUser->isOwner() || $currentUser->hasPermission('stock_allocations.review');

        // Ringkasan operasional memakai snapshot stok dan penjualan pada periode aktif.
        $lowStockCount = $canViewProducts
            ? Product::where('stock', '<=', Product::LOW_STOCK_THRESHOLD)->count()
            : 0;
        $lowStockThreshold = Product::LOW_STOCK_THRESHOLD;
        $lowStockProducts = $canViewProducts
            ? Product::where('stock', '<=', Product::LOW_STOCK_THRESHOLD)
                ->orderBy('stock')
                ->orderBy('name')
                ->take(4)
                ->get()
            : collect();

        $recentSales = $canViewSales
            ? PeriodFilter::apply(
                Sale::with('user')->withSum('saleItems', 'quantity')->withCount('saleItems'),
                'transaction_date',
                $periodStart,
                $periodEnd
            )
                ->orderByDesc('transaction_date')
                ->take(5)
                ->get()
            : collect();

        // Alokasi milik pengelola toko menampilkan tugas tertunda dan yang perlu ditinjau.
        // Pegawai hanya melihat alokasi yang menunggu konfirmasi dirinya sendiri.
        $allocationBaseQuery = StockAllocation::query()
            ->whereHas('employee', fn ($query) => $query->where('store_id', $currentUser->store_id))
            ->whereHas('product', fn ($query) => $query->where('store_id', $currentUser->store_id));
        if ($isAllocationManager) {
            $allocationBaseQuery->where('owner_user_id', $currentUser->id);
        } else {
            $allocationBaseQuery->where('employee_user_id', $currentUser->id);
        }

        $pendingAllocationCount = $canViewStockAllocations
            ? (clone $allocationBaseQuery)->where('status', StockAllocation::STATUS_PENDING)->count()
            : 0;
        $allocationFollowUpCount = $canViewStockAllocations && $isAllocationManager
            ? (clone $allocationBaseQuery)->whereIn('status', [
                StockAllocation::STATUS_NEEDS_REVIEW,
                StockAllocation::STATUS_DECLINED,
            ])->count()
            : 0;
        $allocationAttention = collect();
        if ($canViewStockAllocations) {
            $actionableStatuses = $isAllocationManager
                ? [StockAllocation::STATUS_PENDING, StockAllocation::STATUS_NEEDS_REVIEW, StockAllocation::STATUS_DECLINED]
                : [StockAllocation::STATUS_PENDING];
            $allocationAttention = (clone $allocationBaseQuery)
                ->with(['employee', 'product'])
                ->whereIn('status', $actionableStatuses)
                ->orderByRaw("CASE status WHEN 'needs_review' THEN 0 WHEN 'declined' THEN 1 ELSE 2 END")
                ->orderByDesc('updated_at')
                ->take(4)
                ->get()
                ->map(fn (StockAllocation $allocation) => [
                    'product' => $allocation->product?->name ?? 'Produk dihapus',
                    'unit' => $allocation->product?->unit ?? 'unit',
                    'quantity' => (int) $allocation->requested_quantity,
                    'actualQuantity' => $allocation->actual_received_quantity === null ? null : (int) $allocation->actual_received_quantity,
                    'reason' => $allocation->decline_reason,
                    'employee' => $allocation->employee?->name ?? 'Pegawai tidak tersedia',
                    'status' => $allocation->status,
                    'statusLabel' => match ($allocation->status) {
                        StockAllocation::STATUS_NEEDS_REVIEW, StockAllocation::STATUS_DECLINED => 'Perlu ditindaklanjuti',
                        default => 'Menunggu konfirmasi',
                    },
                    'employeeUrl' => $canViewEmployeeDetails && $allocation->employee
                        ? route('employees.manage', ['employee' => $allocation->employee, 'period' => $period, 'tab' => 'stock'])
                        : null,
                ]);
        }

        // Kondisi pengeluaran yang melebihi pemasukan ditampilkan sebagai status di KPI pengeluaran.
        $alerts = [];
        if ($totalExpenseMonth > $totalRevenueMonth) {
            $alerts[] = ['type' => 'danger', 'message' => "Pengeluaran {$periodLabel} melebihi pendapatan"];
        }

        // Gabungkan aktivitas keuangan, perubahan produk, dan status alokasi terbaru.
        $salesActivities = $recentSales->toBase()->map(function ($sale) use ($period) {
            $wasUpdated = $sale->updated_at && $sale->created_at && $sale->updated_at->gt($sale->created_at);
            $amount = ' sebesar Rp ' . number_format($sale->total, 0, ',', '.');
            $message = $wasUpdated
                ? "Data penjualan {$sale->invoice_number} diperbarui{$amount}"
                : "Penjualan {$sale->invoice_number}{$amount} dicatat oleh " . ($sale->user?->name ?? 'pengguna');

            return [
                'type' => 'sale',
                'message' => $message,
                'date' => $sale->updated_at ?? $sale->transaction_date,
                'url' => route('pemasukan.show', ['sale' => $sale->id, 'period' => $period]),
            ];
        });
        $expenseActivities = $canViewExpenses
            ? PeriodFilter::apply(
                Expense::with('user'),
                'expense_date',
                $periodStart?->toDateString(),
                $periodEnd?->toDateString()
            )
                ->orderByDesc('updated_at')
                ->take(5)
                ->get()
                ->toBase()
                ->map(function ($expense) use ($period) {
                    $wasUpdated = $expense->updated_at && $expense->created_at && $expense->updated_at->gt($expense->created_at);
                    $amount = ' sebesar Rp ' . number_format($expense->amount, 0, ',', '.');
                    $message = $wasUpdated
                        ? "Data pengeluaran {$expense->category} diperbarui{$amount}"
                        : "Pengeluaran {$expense->category}{$amount} dicatat oleh " . ($expense->user?->name ?? 'pengguna');

                    return [
                        'type' => 'expense',
                        'message' => $message,
                        'amount' => (float) $expense->amount,
                        'transactionDate' => Carbon::parse($expense->expense_date),
                        'date' => $expense->updated_at ?? Carbon::parse($expense->expense_date),
                        'url' => route('pengeluaran.show', ['expense' => $expense->id, 'period' => $period]),
                    ];
                })
            : collect();
        $productActivities = $canViewProducts
            ? PeriodFilter::apply(Product::query(), 'updated_at', $periodStart, $periodEnd)
                ->where('updated_at', '<=', $now)
                ->orderByDesc('updated_at')
                ->take(5)
                ->get()
                ->toBase()
                ->map(fn ($product) => [
                    'type' => 'product',
                    'message' => $product->created_at?->equalTo($product->updated_at)
                        ? "Produk {$product->name} ditambahkan"
                        : "Data atau stok produk {$product->name} diperbarui",
                    'date' => $product->updated_at,
                    'url' => route('products.index', ['period' => $period]),
                ])
            : collect();
        $allocationActivities = $canViewStockAllocations
            ? PeriodFilter::apply((clone $allocationBaseQuery)->with(['employee', 'product']), 'updated_at', $periodStart, $periodEnd)
                ->orderByDesc('updated_at')
                ->take(5)
                ->get()
                ->toBase()
                ->map(function (StockAllocation $allocation) use ($isAllocationManager, $canViewEmployeeDetails, $period) {
                    $employeeName = $allocation->employee?->name ?? 'pegawai';
                    $productName = $allocation->product?->name ?? 'produk';
                    $message = match ($allocation->status) {
                        StockAllocation::STATUS_PENDING => $isAllocationManager
                            ? "Alokasi {$productName} untuk {$employeeName} menunggu konfirmasi"
                            : "Alokasi {$productName} menunggu konfirmasi Anda",
                        StockAllocation::STATUS_ACCEPTED => "Alokasi {$productName} dikonfirmasi oleh {$employeeName}",
                        StockAllocation::STATUS_DECLINED => "Alokasi {$productName} ditolak oleh {$employeeName}",
                        StockAllocation::STATUS_NEEDS_REVIEW => "Alokasi {$productName} untuk {$employeeName} perlu ditinjau",
                        StockAllocation::STATUS_CANCELLED => "Alokasi {$productName} untuk {$employeeName} dibatalkan",
                        default => "Status alokasi {$productName} diperbarui",
                    };

                    return [
                        'type' => 'allocation',
                        'message' => $message,
                        'date' => $allocation->updated_at,
                        'url' => $canViewEmployeeDetails && $allocation->employee
                            ? route('employees.manage', ['employee' => $allocation->employee, 'period' => $period])
                            : null,
                    ];
                })
            : collect();
        $latestActivities = $salesActivities
            ->merge($expenseActivities)
            ->merge($productActivities)
            ->merge($allocationActivities)
            ->sortByDesc('date')
            ->take(7)
            ->values();

        // Pertahankan data transaksi gabungan untuk konsumen view lama tanpa query penjualan ulang.
        $recentTransactions = $recentSales->toBase()
            ->map(fn ($sale) => [
                'sort_date' => $sale->transaction_date,
                'date' => $sale->transaction_date->format('d M Y'),
                'type' => 'revenue',
                'label' => 'Pemasukan',
                'amount' => $sale->total,
                'url' => route('pemasukan.show', ['sale' => $sale->id, 'period' => $period]),
            ])
            ->merge($expenseActivities->map(fn ($expenseActivity) => [
                'sort_date' => $expenseActivity['transactionDate'],
                'date' => $expenseActivity['transactionDate']->format('d M Y'),
                'type' => 'expense',
                'label' => 'Pengeluaran',
                'amount' => $expenseActivity['amount'],
                'url' => $expenseActivity['url'],
            ]))
            ->sortByDesc('sort_date')
            ->take(5)
            ->values();

        // Kirim semua data yang dibutuhkan Blade tanpa menjalankan kueri di view.
        return view('dashboard', compact(
            'period',
            'periodLabel',
            'hasComparisonPeriod',
            'comparisonLabel',
            'totalRevenueToday',
            'totalRevenueMonth',
            'totalExpenseToday',
            'totalExpenseMonth',
            'netProfit',
            'transactionsPeriod',
            'transactionsTrendLabel',
            'transactionsTrendText',
            'netProfitTrendLabel',
            'netProfitTrendText',
            'trendLabels',
            'allPeriodMonthCount',
            'revenueTrendData',
            'expenseTrendData',
            'expenseCategoryLabels',
            'expenseCategoryData',
            'expenseCategoryColors',
            'revenueTrendLabel',
            'revenueTrendText',
            'expenseTrendLabel',
            'expenseTrendText',
            'alerts',
            'currentUser',
            'canViewSales',
            'canViewExpenses',
            'recentSales',
            'canViewProducts',
            'lowStockCount',
            'lowStockThreshold',
            'lowStockProducts',
            'canViewStockAllocations',
            'isAllocationManager',
            'pendingAllocationCount',
            'allocationFollowUpCount',
            'allocationAttention',
            'canViewEmployeeDetails',
            'canViewActivityHistory',
            'canShowActivitySummary',
            'latestActivities',
            'recentTransactions'
        ));
    }
}
