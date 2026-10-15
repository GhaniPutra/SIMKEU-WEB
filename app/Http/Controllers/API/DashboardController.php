<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Menampilkan data dashboard
     * GET /api/dashboard
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $today = now()->startOfDay();
            $monthStart = now()->startOfMonth();

            // Revenue & Expense hari ini
            $totalRevenueToday = Sale::where('user_id', $user->id)->where('transaction_date', '>=', $today)->sum('total');
            $totalExpenseToday = Expense::where('user_id', $user->id)->where('expense_date', '>=', $today)->sum('amount');

            // Revenue & Expense bulan ini
            $totalRevenueMonth = Sale::where('user_id', $user->id)->where('transaction_date', '>=', $monthStart)->sum('total');
            $totalExpenseMonth = Expense::where('user_id', $user->id)->where('expense_date', '>=', $monthStart)->sum('amount');

            // Profit calculation
            $grossProfit = $totalRevenueMonth - $totalExpenseMonth;

            // Transaction count
            $transactionsToday = Sale::where('user_id', $user->id)->where('transaction_date', '>=', $today)->count();
            $transactionsMonth = Sale::where('user_id', $user->id)->where('transaction_date', '>=', $monthStart)->count();

            // Employee count
            $activeEmployees = User::where('role', 'pegawai')->count();

            // Low stock products
            $lowStockProducts = Product::where('stock', '<=', 10)->count();

            return response()->json([
                'success' => true,
                'message' => 'Data dashboard berhasil diambil',
                'data' => [
                    'user' => $user,
                    'summary' => [
                        'revenue_today' => $totalRevenueToday,
                        'revenue_month' => $totalRevenueMonth,
                        'expense_today' => $totalExpenseToday,
                        'expense_month' => $totalExpenseMonth,
                        'gross_profit' => $grossProfit,
                        'transactions_today' => $transactionsToday,
                        'transactions_month' => $transactionsMonth,
                        'active_employees' => $activeEmployees,
                        'low_stock_products' => $lowStockProducts,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan ringkasan dashboard dengan statistik detail
     * GET /api/dashboard/summary
     */
    public function summary(Request $request)
    {
        try {
            $user = auth()->user();
            $today = now()->startOfDay();
            $monthStart = now()->startOfMonth();
            $trendStart = now()->subDays(29)->startOfDay();

            // ============================================
            // SUMMARY DATA
            // ============================================
            $totalRevenueToday = Sale::where('user_id', $user->id)->where('transaction_date', '>=', $today)->sum('total');
            $totalRevenueMonth = Sale::where('user_id', $user->id)->where('transaction_date', '>=', $monthStart)->sum('total');

            $totalExpenseToday = Expense::where('user_id', $user->id)->where('expense_date', '>=', $today)->sum('amount');
            $totalExpenseMonth = Expense::where('user_id', $user->id)->where('expense_date', '>=', $monthStart)->sum('amount');

            $grossProfit = $totalRevenueMonth - $totalExpenseMonth;

            $transactionsToday = Sale::where('user_id', $user->id)->where('transaction_date', '>=', $today)->count();
            $transactionsMonth = Sale::where('user_id', $user->id)->where('transaction_date', '>=', $monthStart)->count();

            $activeEmployees = User::where('role', 'pegawai')->count();
            $lowStockProducts = Product::where('stock', '<=', 10)->count();

            // ============================================
            // TREND DATA (30 HARI)
            // ============================================
            $revenueTrendQuery = Sale::selectRaw('DATE(transaction_date) as date, SUM(total) as amount')
                ->where('user_id', $user->id)
                ->where('transaction_date', '>=', $trendStart)
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('amount', 'date');

            $expenseTrendQuery = Expense::selectRaw('expense_date as date, SUM(amount) as amount')
                ->where('user_id', $user->id)
                ->where('expense_date', '>=', $trendStart->toDateString())
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('amount', 'date');

            $trendLabels = [];
            $revenueTrendData = [];
            $expenseTrendData = [];

            for ($day = 0; $day < 30; $day++) {
                $date = $trendStart->copy()->addDays($day);
                $label = $date->format('d M');
                $key = $date->toDateString();

                $trendLabels[] = $label;
                $revenueTrendData[] = (float) ($revenueTrendQuery[$key] ?? 0);
                $expenseTrendData[] = (float) ($expenseTrendQuery[$key] ?? 0);
            }

            // ============================================
            // PERCENTAGE CHANGE
            // ============================================
            $revenueYesterday = Sale::where('user_id', $user->id)
                ->whereBetween('transaction_date', [
                now()->subDay()->startOfDay(),
                now()->subDay()->endOfDay()
            ])->sum('total');

            $expenseYesterday = Expense::where('user_id', $user->id)->where('expense_date', now()->subDay()->toDateString())->sum('amount');

            $revenueTrendPercent = $revenueYesterday > 0
                ? (($totalRevenueToday - $revenueYesterday) / $revenueYesterday) * 100
                : ($totalRevenueToday > 0 ? 100 : 0);

            $expenseTrendPercent = $expenseYesterday > 0
                ? (($totalExpenseToday - $expenseYesterday) / $expenseYesterday) * 100
                : ($totalExpenseToday > 0 ? 100 : 0);

            // ============================================
            // TOP PRODUCTS
            // ============================================
            $topProducts = Product::with('category')
                ->orderByDesc('stock')
                ->limit(5)
                ->get();

            // ============================================
            // RECENT SALES
            // ============================================
            $recentSales = Sale::where('user_id', $user->id)
                ->with(['user', 'customer', 'saleItems.product'])
                ->orderByDesc('transaction_date')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Ringkasan dashboard berhasil diambil',
                'data' => [
                    'user' => $user,
                    'summary' => [
                        'revenue_today' => $totalRevenueToday,
                        'revenue_month' => $totalRevenueMonth,
                        'expense_today' => $totalExpenseToday,
                        'expense_month' => $totalExpenseMonth,
                        'gross_profit' => $grossProfit,
                        'transactions_today' => $transactionsToday,
                        'transactions_month' => $transactionsMonth,
                        'active_employees' => $activeEmployees,
                        'low_stock_products' => $lowStockProducts,
                    ],
                    'trends' => [
                        'labels' => $trendLabels,
                        'revenue_data' => $revenueTrendData,
                        'expense_data' => $expenseTrendData,
                        'revenue_percent_change' => round($revenueTrendPercent, 2),
                        'expense_percent_change' => round($expenseTrendPercent, 2),
                    ],
                    'top_products' => $topProducts,
                    'recent_sales' => $recentSales,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
