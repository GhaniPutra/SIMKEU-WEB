<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POSController;         // Controller untuk halaman Point of Sale (POS)
use App\Http\Controllers\ProductController;   // Controller untuk manajemen produk / stok
use App\Http\Controllers\CategoryController;  // Controller untuk manajemen kategori produk
use App\Http\Controllers\PemasukanController;    // Controller untuk halaman pemasukan / penjualan
use App\Http\Controllers\PengeluaranController;  // Controller untuk halaman pengeluaran operasional

// ====================================
// Route untuk halaman utama
// ====================================
Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

// ====================================
// Route untuk Authentication (Login & Register)
// Group ini tidak memerlukan middleware auth
// ====================================
Route::middleware('guest')->group(function () {
    // Route menampilkan form login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    
    // Route proses login (POST)
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Route menampilkan form register
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    
    // Route proses register (POST)
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
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
    // Route setup toko (harus di dalam auth)
    Route::get('/store/setup', [StoreController::class, 'setup'])->name('store.setup');
    Route::post('/store/setup', [StoreController::class, 'store'])->name('store.store');

    // Route dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ====================================
        // Route untuk halaman Pemasukan (Penjualan)
        // GET  /pemasukan           → index (daftar & statistik pemasukan)
        // GET  /pemasukan/{id}      → show  (detail satu transaksi)
        // ====================================
        Route::get('/pemasukan',        [PemasukanController::class, 'index'])->name('pemasukan.index');
        Route::get('/pemasukan/{sale}',  [PemasukanController::class, 'show'])->name('pemasukan.show');

        // ====================================
        // Route untuk halaman Pengeluaran
        // GET  /pengeluaran          → index (daftar & statistik pengeluaran)
        // GET  /pengeluaran/create   → create (form input pengeluaran baru)
        // POST /pengeluaran          → store  (simpan pengeluaran baru)
        // GET  /pengeluaran/{id}     → show   (detail satu entri pengeluaran)
        // ====================================
        Route::get('/pengeluaran',           [PengeluaranController::class, 'index'])->name('pengeluaran.index');
        Route::get('/pengeluaran/create',    [PengeluaranController::class, 'create'])->name('pengeluaran.create');
        Route::post('/pengeluaran',          [PengeluaranController::class, 'store'])->name('pengeluaran.store');
        Route::get('/pengeluaran/{expense}', [PengeluaranController::class, 'show'])->name('pengeluaran.show');

        // ====================================
        // Route untuk halaman Point of Sale (POS)
        // GET  /pos        → menampilkan form POS
        // POST /pos        → menyimpan transaksi penjualan baru
        // ====================================
        Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
        Route::post('/pos', [POSController::class, 'store'])->name('pos.store');

        // Resource routes untuk manajemen Pegawai
        Route::resource('employees', App\Http\Controllers\EmployeeController::class)->except(['show']);
        // Route untuk dashboard penjualan pegawai
        Route::get('/employees/{employee}/sales-dashboard', [App\Http\Controllers\EmployeeSalesController::class, 'dashboard'])->name('employees.sales-dashboard');

        // Route untuk distribusi stok pegawai
        Route::get('/employees/{employee}/stock', [App\Http\Controllers\EmployeeStockController::class, 'index'])->name('employees.stock');
        Route::post('/employees/{employee}/stock/transfer', [App\Http\Controllers\EmployeeStockController::class, 'transfer'])->name('employees.stock.transfer');

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
        Route::resource('products', ProductController::class)->except(['show']);

        // ====================================
        // Route untuk manajemen Kategori Produk
        // resource() membuat 6 route standar CRUD:
        // GET    /categories           → index   (daftar kategori)
        // POST   /categories           → store   (simpan kategori baru)
        // PUT    /categories/{id}      → update  (simpan perubahan)
        // DELETE /categories/{id}      → destroy (hapus kategori)
        // (create & edit tidak dipakai karena menggunakan modal)
        // ====================================
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        // Route khusus untuk penyesuaian stok cepat (tambah / kurangi)
        // PATCH /products/{product}/adjust-stock → adjustStock
        Route::patch('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])
             ->name('products.adjust-stock');
});

// ====================================
// Route settings (dari settings.php)
// ====================================
require __DIR__.'/settings.php';
