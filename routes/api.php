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

// ====================================
// API Public Routes (tanpa autentikasi)
// ====================================
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// ====================================
// API Protected Routes (memerlukan token autentikasi Sanctum)
// Middleware sanctum akan memastikan user authenticated dengan token
// ====================================
Route::middleware('auth:sanctum')->group(function () {
    // ====================================
    // Authentication Routes
    // ====================================
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'currentUser'])->name('api.user');
    Route::post('/user/profile', [AuthController::class, 'updateProfile'])->name('api.user.profile');

    // ====================================
    // Dashboard Routes
    // ====================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard.index');
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('api.dashboard.summary');

    // ====================================
    // Product Routes (Produk & Stok)
    // GET    /api/products              → index    (daftar produk)
    // POST   /api/products              → store    (tambah produk baru)
    // GET    /api/products/{id}         → show     (detail produk)
    // PUT    /api/products/{id}         → update   (ubah produk)
    // DELETE /api/products/{id}         → destroy  (hapus produk)
    // GET    /api/products/search       → search   (cari produk)
    // ====================================
    Route::apiResource('products', ProductController::class)->names('api.products');
    Route::get('/products/search/{query}', [ProductController::class, 'search'])->name('api.products.search');
    Route::put('/products/{product}/stock', [ProductController::class, 'updateStock'])->name('api.products.stock');

    // ====================================
    // Category Routes (Kategori Produk)
    // GET    /api/categories            → index    (daftar kategori)
    // POST   /api/categories            → store    (tambah kategori)
    // GET    /api/categories/{id}       → show     (detail kategori)
    // PUT    /api/categories/{id}       → update   (ubah kategori)
    // DELETE /api/categories/{id}       → destroy  (hapus kategori)
    // ====================================
    Route::apiResource('categories', CategoryController::class)->names('api.categories');

    // ====================================
    // Sales (Pemasukan/Penjualan) Routes
    // GET    /api/sales                 → index    (daftar penjualan)
    // POST   /api/sales                 → store    (catat penjualan baru)
    // GET    /api/sales/{id}            → show     (detail penjualan)
    // GET    /api/sales/report/period   → reportByPeriod (laporan per periode)
    // ====================================
    Route::apiResource('sales', PemasukanController::class);
    Route::get('/sales/report/period', [PemasukanController::class, 'reportByPeriod'])->name('api.sales.report');
    Route::get('/sales/report/product', [PemasukanController::class, 'reportByProduct'])->name('api.sales.product');

    // ====================================
    // Expenses (Pengeluaran) Routes
    // GET    /api/expenses              → index    (daftar pengeluaran)
    // POST   /api/expenses              → store    (catat pengeluaran baru)
    // GET    /api/expenses/{id}         → show     (detail pengeluaran)
    // PUT    /api/expenses/{id}         → update   (ubah pengeluaran)
    // DELETE /api/expenses/{id}         → destroy  (hapus pengeluaran)
    // GET    /api/expenses/report/period → reportByPeriod (laporan per periode)
    // ====================================
    Route::apiResource('expenses', PengeluaranController::class);
    Route::get('/expenses/report/period', [PengeluaranController::class, 'reportByPeriod'])->name('api.expenses.report');
    Route::get('/expenses/report/category', [PengeluaranController::class, 'reportByCategory'])->name('api.expenses.category');

    // ====================================
    // POS (Point of Sale) Routes
    // POST   /api/pos/transaction       → createTransaction (buat transaksi)
    // POST   /api/pos/transaction/{id}/complete → completeTransaction (selesaikan transaksi)
    // GET    /api/pos/items             → getItems (ambil daftar item untuk POS)
    // ====================================
    Route::prefix('pos')->group(function () {
        Route::post('/transaction', [POSController::class, 'createTransaction'])->name('api.pos.transaction');
        Route::post('/transaction/{transaction}/complete', [POSController::class, 'completeTransaction'])->name('api.pos.complete');
        Route::get('/items', [POSController::class, 'getItems'])->name('api.pos.items');
        Route::post('/items/{product}/add', [POSController::class, 'addItem'])->name('api.pos.add-item');
        Route::delete('/items/{item}', [POSController::class, 'removeItem'])->name('api.pos.remove-item');
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
    Route::apiResource('employees', EmployeeController::class)->names('api.employees');
    Route::get('/employees/{employee}/sales', [EmployeeController::class, 'getSalesData'])->name('api.employees.sales');
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
