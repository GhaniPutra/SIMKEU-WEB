<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockAllocation;
use App\Models\User;
use App\Support\PeriodFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $roleFilter = $request->query('role', '');

        $employeesQuery = User::where('store_id', $request->user()->store_id)->whereIn('role', ['pegawai', 'admin']);

        if (!empty($search)) {
            $employeesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($roleFilter, ['pegawai', 'admin'], true)) {
            $employeesQuery->where('role', $roleFilter);
        }

        $employees = $employeesQuery->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $totalUsers = User::where('store_id', $request->user()->store_id)->whereIn('role', ['pegawai', 'admin'])->count();
        $totalPegawai = User::where('store_id', $request->user()->store_id)->where('role', 'pegawai')->count();
        $totalAdmin = User::where('store_id', $request->user()->store_id)->where('role', 'admin')->count();

        return view('employees.index', compact('employees', 'totalUsers', 'totalPegawai', 'totalAdmin', 'search', 'roleFilter'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:pegawai,admin',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'store_id' => $request->user()->store_id,
        ]);

        return redirect()->route('employees.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(User $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, User $employee)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:pegawai,admin',
        ]);

        $employee->name = $data['name'];
        $employee->email = $data['email'];
        $employee->role = $data['role'];
        if (!empty($data['password'])) {
            $employee->password = Hash::make($data['password']);
        }
        $employee->save();

        return redirect()->route('employees.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    public function destroy(User $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'User berhasil dihapus.');
    }

    public function manage(Request $request, User $employee)
    {
        if ($employee->role !== 'pegawai') {
            return redirect()->route('employees.index')->with('error', 'Hanya dapat mengelola data pegawai.');
        }

        $now = now();
        [
            'period' => $analyticsPeriod,
            'periodLabel' => $periodLabel,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
        ] = PeriodFilter::resolveFromRequest($request, $now);
        $employeeSales = Sale::query()->where('user_id', $employee->id);
        $periodSales = PeriodFilter::apply(clone $employeeSales, 'transaction_date', $periodStart, $periodEnd);

        $periodTotals = (clone $periodSales)
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(total), 0) as revenue')
            ->first();
        $periodItemsQuery = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.store_id', $request->user()->store_id)
            ->where('sales.user_id', $employee->id);
        PeriodFilter::apply($periodItemsQuery, 'sales.transaction_date', $periodStart, $periodEnd);
        $periodItemsSold = $periodItemsQuery->sum('sale_items.quantity');

        $dailySales = (clone $periodSales)
            ->selectRaw('DATE(transaction_date) as sale_date, COUNT(*) as transaction_count, COALESCE(SUM(total), 0) as revenue')
            ->groupByRaw('DATE(transaction_date)')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $trendLabels = [];
        $trendFullLabels = [];
        $trendRevenue = [];
        if ($analyticsPeriod === 'all') {
            $driver = DB::connection()->getDriverName();
            $monthExpression = match ($driver) {
                'mysql', 'mariadb' => "DATE_FORMAT(transaction_date, '%Y-%m')",
                'pgsql' => "TO_CHAR(transaction_date, 'YYYY-MM')",
                default => "strftime('%Y-%m', transaction_date)",
            };
            $monthlySales = (clone $employeeSales)
                ->selectRaw("$monthExpression as month_key, COUNT(*) as transaction_count, COALESCE(SUM(total), 0) as revenue")
                ->groupByRaw($monthExpression)
                ->orderBy('month_key')
                ->get()
                ->keyBy('month_key');
            $firstMonth = $monthlySales->keys()->first();
            if ($firstMonth !== null) {
                $lastMonth = $monthlySales->keys()->last();
                for ($date = \Carbon\Carbon::createFromFormat('!Y-m', $firstMonth);
                    $date->lte(\Carbon\Carbon::createFromFormat('!Y-m', $lastMonth));
                    $date->addMonth()) {
                    $key = $date->format('Y-m');
                    $sale = $monthlySales->get($key);
                    $trendLabels[] = $date->format('m/Y');
                    $trendFullLabels[] = $date->translatedFormat('F Y');
                    $trendRevenue[] = (float) ($sale->revenue ?? 0);
                }
            }
        } else {
            $chartEnd = $periodEnd;
            for ($date = $periodStart->copy()->startOfDay(); $date->lte($chartEnd); $date->addDay()) {
                $dailySale = $dailySales->get($date->toDateString());
                $trendLabels[] = $date->format('d/m');
                $trendFullLabels[] = $date->format('d/m/Y');
                $trendRevenue[] = (float) ($dailySale->revenue ?? 0);
            }
        }
        $trendHasTransactions = $dailySales->sum('transaction_count') > 0;

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.store_id', $request->user()->store_id)
            ->where('products.store_id', $request->user()->store_id)
            ->where('sales.user_id', $employee->id)
            ->select('products.id', 'products.name', 'products.unit')
            ->selectRaw('SUM(sale_items.quantity) as units_sold');
        PeriodFilter::apply($topProducts, 'sales.transaction_date', $periodStart, $periodEnd);
        $topProducts = $topProducts
            ->groupBy('products.id', 'products.name', 'products.unit')
            ->orderByDesc('units_sold')
            ->orderBy('products.name')
            ->limit(5)
            ->get();

        $stockSummary = DB::table('products')->where('products.store_id', $request->user()->store_id)
            ->leftJoin('product_user as employee_product', function ($join) use ($employee) {
                $join->on('products.id', '=', 'employee_product.product_id')
                    ->where('employee_product.user_id', '=', $employee->id);
            })
            ->selectRaw('
                COALESCE(SUM(products.stock), 0) as master_stock_total,
                COALESCE(SUM(CASE WHEN employee_product.id IS NOT NULL THEN employee_product.stock ELSE 0 END), 0) as employee_stock_total,
                COALESCE(SUM(CASE WHEN employee_product.id IS NOT NULL AND employee_product.stock <= 0 THEN 1 ELSE 0 END), 0) as empty_stock_count,
                COALESCE(SUM(CASE WHEN employee_product.stock BETWEEN 1 AND ? THEN 1 ELSE 0 END), 0) as low_stock_count,
                COALESCE(SUM(CASE WHEN employee_product.stock > ? THEN 1 ELSE 0 END), 0) as normal_stock_count,
                COALESCE(SUM(CASE WHEN employee_product.id IS NULL THEN 1 ELSE 0 END), 0) as unallocated_stock_count
            ', [Product::LOW_STOCK_THRESHOLD, Product::LOW_STOCK_THRESHOLD])
            ->first();
        $lowStockThreshold = Product::LOW_STOCK_THRESHOLD;

        $stockAttention = DB::table('products')->where('products.store_id', $request->user()->store_id)
            ->leftJoin('product_user as employee_product', function ($join) use ($employee) {
                $join->on('products.id', '=', 'employee_product.product_id')
                    ->where('employee_product.user_id', '=', $employee->id);
            })
            ->whereNotNull('employee_product.id')
            ->where('employee_product.stock', '<=', Product::LOW_STOCK_THRESHOLD)
            ->select('products.id', 'products.name', 'products.unit', 'employee_product.stock as employee_stock')
            ->orderBy('employee_product.stock')
            ->orderBy('products.name')
            ->limit(5)
            ->get();

        $requestedStockSearch = $request->query('stock_search', '');
        $stockSearch = is_string($requestedStockSearch) ? trim($requestedStockSearch) : '';
        $stockStatus = $request->query('stock_status', '');
        if (!in_array($stockStatus, ['unallocated', 'empty', 'low', 'normal'], true)) {
            $stockStatus = '';
        }

        $productsQuery = Product::query()
            ->leftJoin('product_user as employee_product', function ($join) use ($employee) {
                $join->on('products.id', '=', 'employee_product.product_id')
                    ->where('employee_product.user_id', '=', $employee->id);
            })
            ->with([
                'category',
                'users' => fn ($query) => $query->where('users.id', $employee->id),
            ])
            ->select('products.*', 'employee_product.stock as employee_stock');

        if ($stockSearch !== '') {
            $productsQuery->where(function ($query) use ($stockSearch) {
                $query->where('products.name', 'like', "%{$stockSearch}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$stockSearch}%"));
            });
        }

        if ($stockStatus === 'unallocated') {
            $productsQuery->whereNull('employee_product.id');
        } elseif ($stockStatus === 'empty') {
            $productsQuery->whereNotNull('employee_product.id')
                ->where('employee_product.stock', '<=', 0);
        } elseif ($stockStatus === 'low') {
            $productsQuery->whereBetween('employee_product.stock', [1, Product::LOW_STOCK_THRESHOLD]);
        } elseif ($stockStatus === 'normal') {
            $productsQuery->where('employee_product.stock', '>', Product::LOW_STOCK_THRESHOLD);
        }

        $products = $productsQuery
            ->orderBy('products.name')
            ->paginate(15, ['products.*', 'employee_product.stock as employee_stock'], 'products_page')
            ->withQueryString()
            ->appends(['tab' => 'stock']);

        $pendingProductIds = StockAllocation::query()
            ->where('owner_user_id', $request->user()->id)
            ->where('employee_user_id', $employee->id)
            ->where('status', StockAllocation::STATUS_PENDING)
            ->pluck('product_id')
            ->map(static fn ($productId) => (int) $productId)
            ->all();

        $sales = (clone $periodSales)
            ->with('customer')
            ->withSum('saleItems', 'quantity')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'sales_page')
            ->withQueryString()
            ->appends(['tab' => 'sales']);

        $recentSales = (clone $periodSales)
            ->with('customer')
            ->withSum('saleItems', 'quantity')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $activeTab = $request->query('tab', 'overview');
        if (!in_array($activeTab, ['overview', 'stock', 'sales'], true)) {
            $activeTab = 'overview';
        }

        return view('employees.manage', compact(
            'employee',
            'products',
            'pendingProductIds',
            'analyticsPeriod',
            'periodLabel',
            'periodStart',
            'periodEnd',
            'trendLabels',
            'trendFullLabels',
            'trendRevenue',
            'trendHasTransactions',
            'topProducts',
            'stockSummary',
            'stockAttention',
            'stockSearch',
            'stockStatus',
            'sales',
            'periodTotals',
            'periodItemsSold',
            'recentSales',
            'lowStockThreshold',
            'activeTab'
        ));
    }
}
