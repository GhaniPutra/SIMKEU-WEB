<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\PemasukanController;
use App\Http\Controllers\API\PengeluaranController;
use App\Http\Controllers\API\POSController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\StockAllocationController;
use App\Http\Middleware\EnsureStoreIsConfigured;

// ====================================
// API Public Routes (tanpa autentikasi)
// ====================================
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// ====================================
// API Protected Routes (memerlukan token autentikasi Sanctum)
// Middleware sanctum akan memastikan user authenticated dengan token
// ====================================
Route::middleware(['auth:sanctum', EnsureStoreIsConfigured::class])->group(function () {
    // ====================================
    // Authentication Routes
    // ====================================
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'currentUser'])->name('api.user');
    Route::post('/user/profile', [AuthController::class, 'updateProfile'])->name('api.user.profile');

    // ====================================
    // Dashboard Routes
    // ====================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view,dashboard.mobile.view')->name('api.dashboard.index');
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->middleware('permission:dashboard.view,dashboard.mobile.view')->name('api.dashboard.summary');

    // ====================================
    // Product Routes (Produk & Stok)
    // GET    /api/products              → index    (daftar produk)
    // POST   /api/products              → store    (tambah produk baru)
    // GET    /api/products/{id}         → show     (detail produk)
    // PUT    /api/products/{id}         → update   (ubah produk)
    // DELETE /api/products/{id}         → destroy  (hapus produk)
    // GET    /api/products/search       → search   (cari produk)
    // ====================================
    Route::get('/products', [ProductController::class, 'index'])->middleware('permission:products.view,products.mobile.view')->name('api.products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->middleware('permission:products.view,products.mobile.view')->name('api.products.show');
    Route::post('/products', [ProductController::class, 'store'])->middleware('permission:products.create')->name('api.products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('permission:products.update')->name('api.products.update');
    Route::patch('/products/{product}', [ProductController::class, 'update'])->middleware('permission:products.update')->name('api.products.patch');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('permission:products.delete')->name('api.products.destroy');
    Route::get('/products/search/{query}', [ProductController::class, 'search'])->middleware('permission:products.view,products.mobile.view')->name('api.products.search');
    Route::put('/products/{product}/stock', [ProductController::class, 'updateStock'])->middleware('permission:products.stock.add,products.stock.update,products.stock.adjust')->name('api.products.stock');

    // ====================================
    // Category Routes (Kategori Produk)
    // GET    /api/categories            → index    (daftar kategori)
    // POST   /api/categories            → store    (tambah kategori)
    // GET    /api/categories/{id}       → show     (detail kategori)
    // PUT    /api/categories/{id}       → update   (ubah kategori)
    // DELETE /api/categories/{id}       → destroy  (hapus kategori)
    // ====================================
    Route::get('/categories', [CategoryController::class, 'index'])->middleware('permission:categories.view,categories.mobile.view')->name('api.categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->middleware('permission:categories.view,categories.mobile.view')->name('api.categories.show');
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:categories.create')->name('api.categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.update')->name('api.categories.update');
    Route::patch('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.update')->name('api.categories.patch');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete')->name('api.categories.destroy');

    // ====================================
    // Sales (Pemasukan/Penjualan) Routes
    // GET    /api/sales                 → index    (daftar penjualan)
    // POST   /api/sales                 → store    (catat penjualan baru)
    // GET    /api/sales/{id}            → show     (detail penjualan)
    // GET    /api/sales/report/period   → reportByPeriod (laporan per periode)
    // ====================================
    Route::get('/sales', [PemasukanController::class, 'index'])->middleware('permission:sales.view,sales.mobile.view')->name('api.sales.index');
    Route::get('/sales/{sale}', [PemasukanController::class, 'show'])->middleware('permission:sales.view,sales.mobile.view')->name('api.sales.show');
    Route::post('/sales', [PemasukanController::class, 'store'])->middleware('permission:sales.create')->name('api.sales.store');
    Route::get('/sales/report/period', [PemasukanController::class, 'reportByPeriod'])->middleware('permission:sales.view,sales.mobile.view')->name('api.sales.report');
    Route::get('/sales/report/product', [PemasukanController::class, 'reportByProduct'])->middleware('permission:sales.view,sales.mobile.view')->name('api.sales.product');

    // ====================================
    // Expenses (Pengeluaran) Routes
    // GET    /api/expenses              → index    (daftar pengeluaran)
    // POST   /api/expenses              → store    (catat pengeluaran baru)
    // GET    /api/expenses/{id}         → show     (detail pengeluaran)
    // PUT    /api/expenses/{id}         → update   (ubah pengeluaran)
    // DELETE /api/expenses/{id}         → destroy  (hapus pengeluaran)
    // GET    /api/expenses/report/period → reportByPeriod (laporan per periode)
    // ====================================
    Route::get('/expenses', [PengeluaranController::class, 'index'])->middleware('permission:expenses.view,expenses.mobile.view')->name('api.expenses.index');
    Route::get('/expenses/{expense}', [PengeluaranController::class, 'show'])->middleware('permission:expenses.view,expenses.mobile.view')->name('api.expenses.show');
    Route::post('/expenses', [PengeluaranController::class, 'store'])->middleware('permission:expenses.create,expenses.mobile.create')->name('api.expenses.store');
    Route::put('/expenses/{expense}', [PengeluaranController::class, 'update'])->middleware('permission:expenses.update')->name('api.expenses.update');
    Route::patch('/expenses/{expense}', [PengeluaranController::class, 'update'])->middleware('permission:expenses.update')->name('api.expenses.patch');
    Route::delete('/expenses/{expense}', [PengeluaranController::class, 'destroy'])->middleware('permission:expenses.delete')->name('api.expenses.destroy');
    Route::get('/expenses/report/period', [PengeluaranController::class, 'reportByPeriod'])->middleware('permission:expenses.view,expenses.mobile.view')->name('api.expenses.report');
    Route::get('/expenses/report/category', [PengeluaranController::class, 'reportByCategory'])->middleware('permission:expenses.view,expenses.mobile.view')->name('api.expenses.category');

    // ====================================
    // POS (Point of Sale) Routes
    // POST   /api/pos/transaction       → createTransaction (buat transaksi)
    // POST   /api/pos/transaction/{id}/complete → completeTransaction (selesaikan transaksi)
    // GET    /api/pos/items             → getItems (ambil daftar item untuk POS)
    // ====================================
    Route::prefix('pos')->group(function () {
        Route::post('/transaction', [POSController::class, 'createTransaction'])->middleware('permission:pos.mobile.use')->name('api.pos.transaction');
        Route::post('/transaction/{sale}/complete', [POSController::class, 'completeTransaction'])->middleware('permission:pos.mobile.use')->name('api.pos.complete');
        Route::get('/items', [POSController::class, 'getItems'])->middleware('permission:pos.mobile.use')->name('api.pos.items');
        Route::post('/items/{product}/add', [POSController::class, 'addItem'])->middleware('permission:pos.mobile.use')->name('api.pos.add-item');
        Route::delete('/items/{item}', [POSController::class, 'removeItem'])->middleware('permission:pos.mobile.use')->name('api.pos.remove-item');
    });

    // ====================================
    // Employee Routes (Karyawan)
    // GET    /api/employees             → index    (daftar karyawan)
    // POST   /api/employees             → store    (tambah karyawan)
    // GET    /api/employees/{id}        → show     (detail karyawan)
    // PUT    /api/employees/{id}        → update   (ubah karyawan)
    // DELETE /api/employees/{id}        → destroy  (hapus karyawan)
    // GET    /api/employees/{id}/sales  → getSalesData (data penjualan karyawan)
    // ====================================
    Route::get('/employees', [EmployeeController::class, 'index'])->middleware('permission:employees.view')->name('api.employees.index');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->middleware('permission:employees.view')->name('api.employees.show');
    Route::post('/employees', [EmployeeController::class, 'store'])->middleware('permission:employees.create')->name('api.employees.store');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.update')->name('api.employees.update');
    Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.update')->name('api.employees.patch');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:employees.delete')->name('api.employees.destroy');
    Route::get('/employees/{employee}/sales', [EmployeeController::class, 'getSalesData'])->middleware('permission:employees.sales.view')->name('api.employees.sales');

    // ====================================
    // Employee Stock Allocation Routes
    // GET    /api/stock-allocations/pending                     -> daftar pending milik user login
    // GET    /api/employees/{employee}/stock/allocations       -> daftar alokasi milik karyawan tertentu
    // POST   /api/stock-allocations/{allocation}/review        -> accept/decline alokasi tertentu
    // ====================================
    Route::get('/stock-allocations/pending', [StockAllocationController::class, 'pending'])->middleware('permission:stock_allocations.view')->name('api.stock-allocations.pending');
    Route::get('/stock-allocations/needs-review', [StockAllocationController::class, 'needsReview'])->middleware('permission:stock_allocations.review')->name('api.stock-allocations.needs-review');
    Route::get('/employees/{employee}/stock/allocations', [StockAllocationController::class, 'index'])->middleware('permission:stock_allocations.view')->name('api.employees.stock.allocations');
    Route::post('/stock-allocations/{allocation}/review', [StockAllocationController::class, 'review'])->middleware('permission:stock_allocations.confirm')->name('api.stock-allocations.review');
    Route::post('/stock-allocations/{allocation}/owner-follow-up', [StockAllocationController::class, 'ownerFollowUp'])->middleware('permission:stock_allocations.review')->name('api.stock-allocations.owner-follow-up');
});

// ====================================
// Error Routes
// ====================================
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint tidak ditemukan',
        'status' => 404
    ], 404);
});
