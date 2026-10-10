<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $roleFilter = $request->query('role', '');

        $employeesQuery = User::whereIn('role', ['pegawai', 'admin']);

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
        $totalUsers = User::whereIn('role', ['pegawai', 'admin'])->count();
        $totalPegawai = User::where('role', 'pegawai')->count();
        $totalAdmin = User::where('role', 'admin')->count();

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
            'store_id' => null,
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

        $requestedPeriod = $request->query('analytics_period', 7);
        $analyticsPeriod = is_numeric($requestedPeriod) ? (int) $requestedPeriod : 7;
        if (!in_array($analyticsPeriod, [7, 30, 90], true)) {
            $analyticsPeriod = 7;
        }

        $now = now();
        $periodStart = $now->copy()->startOfDay()->subDays($analyticsPeriod - 1);
        $periodEnd = $now->copy();
        $employeeSales = Sale::query()->where('user_id', $employee->id);
        $periodSales = (clone $employeeSales)->whereBetween('transaction_date', [$periodStart, $periodEnd]);

        $salesTotals = (clone $employeeSales)
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(total), 0) as revenue')
            ->first();
        $totalItemsSold = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.user_id', $employee->id)
            ->sum('sale_items.quantity');

        $periodTotals = (clone $periodSales)
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(total), 0) as revenue')
            ->first();
        $periodItemsSold = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.user_id', $employee->id)
            ->whereBetween('sales.transaction_date', [$periodStart, $periodEnd])
            ->sum('sale_items.quantity');
        $currentMonthSales = (float) (clone $employeeSales)
            ->whereBetween('transaction_date', [$now->copy()->startOfMonth(), $periodEnd])
            ->sum('total');

        $dailySales = (clone $periodSales)
            ->selectRaw('DATE(transaction_date) as sale_date, COUNT(*) as transaction_count, COALESCE(SUM(total), 0) as revenue')
            ->groupByRaw('DATE(transaction_date)')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $trendLabels = [];
        $trendFullLabels = [];
        $trendRevenue = [];
        for ($dayIndex = 0; $dayIndex < $analyticsPeriod; $dayIndex++) {
            $date = $periodStart->copy()->addDays($dayIndex);
            $dailySale = $dailySales->get($date->toDateString());
            $trendLabels[] = $date->format('d/m');
            $trendFullLabels[] = $date->format('d/m/Y');
            $trendRevenue[] = (float) ($dailySale->revenue ?? 0);
        }
        $trendHasTransactions = $dailySales->sum('transaction_count') > 0;

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.user_id', $employee->id)
            ->whereBetween('sales.transaction_date', [$periodStart, $periodEnd])
            ->select('products.id', 'products.name', 'products.unit')
            ->selectRaw('SUM(sale_items.quantity) as units_sold')
            ->groupBy('products.id', 'products.name', 'products.unit')
            ->orderByDesc('units_sold')
            ->orderBy('products.name')
            ->limit(5)
            ->get();

        $stockSummary = DB::table('products')
            ->leftJoin('product_user as employee_product', function ($join) use ($employee) {
                $join->on('products.id', '=', 'employee_product.product_id')
                    ->where('employee_product.user_id', '=', $employee->id);
            })
            ->selectRaw('
                COALESCE(SUM(products.stock), 0) as master_stock_total,
                COALESCE(SUM(COALESCE(employee_product.stock, 0)), 0) as employee_stock_total,
                COALESCE(SUM(CASE WHEN COALESCE(employee_product.stock, 0) <= 0 THEN 1 ELSE 0 END), 0) as empty_stock_count,
                COALESCE(SUM(CASE WHEN employee_product.stock BETWEEN 1 AND ? THEN 1 ELSE 0 END), 0) as low_stock_count
            ', [Product::LOW_STOCK_THRESHOLD])
            ->first();
        $lowStockThreshold = Product::LOW_STOCK_THRESHOLD;

        $stockAttention = DB::table('products')
            ->leftJoin('product_user as employee_product', function ($join) use ($employee) {
                $join->on('products.id', '=', 'employee_product.product_id')
                    ->where('employee_product.user_id', '=', $employee->id);
            })
            ->whereRaw('COALESCE(employee_product.stock, 0) <= ?', [Product::LOW_STOCK_THRESHOLD])
            ->select('products.id', 'products.name', 'products.unit', 'employee_product.stock as employee_stock')
            ->orderByRaw('COALESCE(employee_product.stock, 0) asc')
            ->orderBy('products.name')
            ->limit(5)
            ->get();

        $requestedStockSearch = $request->query('stock_search', '');
        $stockSearch = is_string($requestedStockSearch) ? trim($requestedStockSearch) : '';
        $stockStatus = $request->query('stock_status', '');
        if (!in_array($stockStatus, ['empty', 'low', 'normal'], true)) {
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

        if ($stockStatus === 'empty') {
            $productsQuery->whereRaw('COALESCE(employee_product.stock, 0) <= 0');
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

        $sales = (clone $periodSales)
            ->with('customer')
            ->withSum('saleItems', 'quantity')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'sales_page')
            ->withQueryString()
            ->appends(['tab' => 'sales']);

        $recentSales = (clone $employeeSales)
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
            'analyticsPeriod',
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
            'salesTotals',
            'totalItemsSold',
            'periodTotals',
            'periodItemsSold',
            'currentMonthSales',
            'recentSales',
            'lowStockThreshold',
            'activeTab'
        ));
    }
}
