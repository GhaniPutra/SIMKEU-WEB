<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;   // Controller untuk manajemen produk / stok
use App\Http\Controllers\CategoryController;  // Controller untuk manajemen kategori produk
use App\Http\Controllers\PemasukanController;    // Controller untuk halaman pemasukan / penjualan
use App\Http\Controllers\PengeluaranController;  // Controller untuk halaman pengeluaran operasional
use App\Http\Controllers\RoleController;
use App\Support\NotificationUrl;

// ====================================
// Route untuk halaman utama
// ====================================
Route::get('/', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login');
    }

    foreach ([
        'dashboard.view' => 'dashboard',
        'sales.view' => 'pemasukan.index',
        'expenses.view' => 'pengeluaran.index',
        'expenses.create' => 'pengeluaran.create',
        'products.view' => 'products.index',
        'products.create' => 'products.create',
        'categories.view' => 'categories.index',
        'categories.create' => 'categories.index',
        'employees.view' => 'employees.index',
        'employees.create' => 'employees.index',
        'activities.view' => 'activities.index',
    ] as $permission => $route) {
        $isWebPermission = !in_array($permission, ['expenses.view', 'expenses.create', 'products.view', 'categories.view', 'sales.view'], true)
            || $user->hasPermission('dashboard.view')
            || $user->hasPermission('dashboard.mobile.view');
        if ($isWebPermission && $user->hasPermission($permission)) {
            return redirect()->route($route);
        }
    }

    abort(403, 'Akun ini belum memiliki izin untuk membuka halaman web.');
})->name('home');

// ====================================
// Route untuk Authentication (Login & Register)
// Group ini tidak memerlukan middleware auth
// ====================================
Route::middleware('guest')->group(function () {
    // Route menampilkan form login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    
    // Route proses login (POST)
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.store');

    // Route menampilkan form register
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    
    // Route proses register (POST)
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

// ====================================
// Route untuk logout
// Route ini bisa diakses dari manapun setelah login
// ====================================
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ====================================
// Route untuk Dashboard
// Group ini memerlukan middleware auth
// (hanya user yang sudah login bisa akses)
// ====================================
use App\Http\Controllers\StoreController;

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/unread', function (\Illuminate\Http\Request $request) {
        $unread = $request->user()->unreadNotifications();
        return response()->json([
            'unread_count' => (clone $unread)->count(),
            'notifications' => $unread->latest()->limit(10)->get()->map(fn ($notification) => [
                'id' => $notification->id,
                'data' => NotificationUrl::normalize($notification->data),
                'created_at' => $notification->created_at->diffForHumans(),
            ]),
        ]);
    })->name('notifications.unread');
    Route::post('/notifications/read', function (\Illuminate\Http\Request $request) {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    })->name('notifications.read');
    // Route setup toko (harus di dalam auth)
    Route::get('/store/setup', [StoreController::class, 'setup'])->name('store.setup');
    Route::post('/store/setup', [StoreController::class, 'store'])->name('store.store');

    // Route dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view,dashboard.mobile.view')->name('dashboard');
    Route::get('/activities', [ActivityController::class, 'index'])->middleware('permission:activities.view')->name('activities.index');
    Route::resource('roles', RoleController::class)->except(['show'])->middleware('owner');

        // ====================================
        // Route untuk halaman Pemasukan (Penjualan)
        // GET  /pemasukan           → index (daftar & statistik pemasukan)
        // GET  /pemasukan/{id}      → show  (detail satu transaksi)
        // ====================================
        Route::get('/pemasukan',        [PemasukanController::class, 'index'])->middleware(['permission:dashboard.view', 'permission:sales.view'])->name('pemasukan.index');
        Route::get('/pemasukan/{sale}',  [PemasukanController::class, 'show'])->middleware(['permission:dashboard.view', 'permission:sales.view'])->name('pemasukan.show');

        // ====================================
        // Route untuk halaman Pengeluaran
        // GET  /pengeluaran          → index (daftar & statistik pengeluaran)
        // GET  /pengeluaran/create   → create (form input pengeluaran baru)
        // POST /pengeluaran          → store  (simpan pengeluaran baru)
        // GET  /pengeluaran/{id}     → show   (detail satu entri pengeluaran)
        // ====================================
        Route::get('/pengeluaran',           [PengeluaranController::class, 'index'])->middleware(['permission:dashboard.view', 'permission:expenses.view'])->name('pengeluaran.index');
        Route::get('/pengeluaran/create',    [PengeluaranController::class, 'create'])->middleware(['permission:dashboard.view', 'permission:expenses.create'])->name('pengeluaran.create');
        Route::post('/pengeluaran',          [PengeluaranController::class, 'store'])->middleware(['permission:dashboard.view', 'permission:expenses.create'])->name('pengeluaran.store');
        Route::get('/pengeluaran/{expense}', [PengeluaranController::class, 'show'])->middleware(['permission:dashboard.view', 'permission:expenses.view'])->name('pengeluaran.show');

        Route::get('/employees', [App\Http\Controllers\EmployeeController::class, 'index'])->middleware('permission:employees.view,employees.create')->name('employees.index');
        Route::post('/employees', [App\Http\Controllers\EmployeeController::class, 'store'])->middleware('permission:employees.create')->name('employees.store');
        Route::get('/employees/{employee}/edit', [App\Http\Controllers\EmployeeController::class, 'edit'])->middleware('permission:employees.update')->name('employees.edit');
        Route::put('/employees/{employee}', [App\Http\Controllers\EmployeeController::class, 'update'])->middleware('permission:employees.update')->name('employees.update');
        Route::patch('/employees/{employee}', [App\Http\Controllers\EmployeeController::class, 'update'])->middleware('permission:employees.update')->name('employees.update.patch');
        Route::delete('/employees/{employee}', [App\Http\Controllers\EmployeeController::class, 'destroy'])->middleware('permission:employees.delete')->name('employees.destroy');
        Route::get('/employees/{employee}/manage', [App\Http\Controllers\EmployeeController::class, 'manage'])->middleware('permission:employees.sales.view')->name('employees.manage');
        Route::get('/employees/{employee}/sales-dashboard', [App\Http\Controllers\EmployeeSalesController::class, 'dashboard'])->middleware('permission:employees.sales.view')->name('employees.sales-dashboard');
        Route::get('/stock-allocations/pending-status', [App\Http\Controllers\EmployeeStockController::class, 'pendingAllocations'])->middleware('permission:stock_allocations.view')->name('employees.stock.allocations.pending-status');
        Route::get('/stock-allocations/history', [App\Http\Controllers\StockAllocationHistoryController::class, 'index'])->middleware('permission:stock_allocations.view')->name('stock-allocations.history');
        Route::post('/employees/{employee}/stock/allocations', [App\Http\Controllers\EmployeeStockController::class, 'createAllocation'])->middleware('permission:stock_allocations.create')->name('employees.stock.allocations.store');
        Route::post('/employees/{employee}/stock/allocations/{allocation}/follow-up', [App\Http\Controllers\EmployeeStockController::class, 'followUpAllocation'])->middleware('permission:stock_allocations.review')->name('employees.stock.allocations.follow-up');
        Route::post('/employees/{employee}/stock/transfer', [App\Http\Controllers\EmployeeStockController::class, 'transfer'])->middleware('permission:stock_allocations.transfer')->name('employees.stock.transfer');

        // ====================================
        // Route untuk manajemen Produk / Stok
        // resource() membuat 6 route standar CRUD:
        // GET    /products           → index   (daftar produk)
        // GET    /products/create    → create  (form tambah)
        // POST   /products           → store   (simpan produk baru)
        // GET    /products/{id}/edit → edit    (form edit)
        // PUT    /products/{id}      → update  (simpan perubahan)
        // DELETE /products/{id}      → destroy (hapus produk)
        // ====================================
        Route::get('/products', [ProductController::class, 'index'])->middleware(['permission:dashboard.view', 'permission:products.view,categories.view,categories.create'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->middleware('permission:products.create')->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->middleware('permission:products.create')->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->middleware('permission:products.update')->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('permission:products.update')->name('products.update');
        Route::patch('/products/{product}', [ProductController::class, 'update'])->middleware('permission:products.update')->name('products.update.patch');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('permission:products.delete')->name('products.destroy');
        Route::patch('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->middleware('permission:products.stock.add,products.stock.update,products.stock.adjust')->name('products.adjust-stock');

        Route::get('/categories', [CategoryController::class, 'index'])->middleware(['permission:dashboard.view', 'permission:categories.view,categories.create'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:categories.create')->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.update')->name('categories.update');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.update')->name('categories.update.patch');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete')->name('categories.destroy');
});

// ====================================
// Route settings (dari settings.php)
// ====================================
require __DIR__.'/settings.php';
