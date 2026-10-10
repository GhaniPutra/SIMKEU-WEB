<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
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

    public function manage(User $employee)
    {
        if ($employee->role !== 'pegawai') {
            return redirect()->route('employees.index')->with('error', 'Hanya dapat mengelola data pegawai.');
        }

        $products = Product::with(['category', 'users' => function ($query) use ($employee) {
            $query->where('user_id', $employee->id);
        }])->orderBy('name')->paginate(15);

        $pageMasterStock = 0;
        $pageEmployeeStock = 0;
        $lowStockCount = 0;

        foreach ($products as $product) {
            $pageMasterStock += $product->stock;
            $employeeProduct = $product->users->first();
            $pageEmployeeStock += $employeeProduct ? $employeeProduct->pivot->stock : 0;

            if ($product->stock > 0 && $product->stock <= 10) {
                $lowStockCount++;
            }
        }

        $sales = Sale::where('user_id', $employee->id)
            ->with(['saleItems.product', 'customer'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalSales = Sale::where('user_id', $employee->id)->count();
        $totalRevenue = (float) Sale::where('user_id', $employee->id)->sum('total');
        $totalItemsSold = Sale::where('user_id', $employee->id)
            ->with('saleItems')
            ->get()
            ->sum(function ($sale) {
                return $sale->saleItems->sum('quantity');
            });
        $currentMonthSales = (float) Sale::where('user_id', $employee->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        return view('employees.manage', compact(
            'employee',
            'products',
            'pageMasterStock',
            'pageEmployeeStock',
            'lowStockCount',
            'sales',
            'totalSales',
            'totalRevenue',
            'totalItemsSold',
            'currentMonthSales'
        ));
    }
}
