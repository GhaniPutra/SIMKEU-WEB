<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POSController;         // Controller untuk halaman Point of Sale (POS)
use App\Http\Controllers\ProductController;   // Controller untuk manajemen produk / stok
use App\Http\Controllers\CategoryController;  // Controller untuk manajemen kategori produk
use App\Http\Controllers\PemasukanController;    // Controller untuk halaman pemasukan / penjualan
use App\Http\Controllers\PengeluaranController;  // Controller untuk halaman pengeluaran operasional
use App\Http\Middleware\EnsureStoreAdmin;
use App\Support\NotificationUrl;

// ====================================
// Route untuk halaman utama
// ====================================
Route::redirect('/', '/dashboard')->name('home');

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');

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
        Route::middleware(EnsureStoreAdmin::class)->group(function () {
            Route::resource('employees', App\Http\Controllers\EmployeeController::class)->except(['show']);
            Route::get('/employees/{employee}/manage', [App\Http\Controllers\EmployeeController::class, 'manage'])->name('employees.manage');
            Route::get('/employees/{employee}/sales-dashboard', [App\Http\Controllers\EmployeeSalesController::class, 'dashboard'])->name('employees.sales-dashboard');
            Route::get('/stock-allocations/pending-status', [App\Http\Controllers\EmployeeStockController::class, 'pendingAllocations'])->name('employees.stock.allocations.pending-status');
            Route::post('/employees/{employee}/stock/allocations', [App\Http\Controllers\EmployeeStockController::class, 'createAllocation'])->name('employees.stock.allocations.store');
            Route::post('/stock-allocations/{allocation}/review', [App\Http\Controllers\EmployeeStockController::class, 'reviewAllocation'])->name('employees.stock.allocations.review');
            Route::post('/employees/{employee}/stock/transfer', [App\Http\Controllers\EmployeeStockController::class, 'transfer'])->name('employees.stock.transfer');
        });

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
        Route::middleware(EnsureStoreAdmin::class)->group(function () {
            Route::resource('products', ProductController::class)->except(['show']);

        // ====================================
        // CRUD kategori tetap tersedia; route index mengarah ke tab Kategori.
        // ====================================
            Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        // Route khusus untuk penyesuaian stok cepat (tambah / kurangi)
        // PATCH /products/{product}/adjust-stock → adjustStock
            Route::patch('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])
                 ->name('products.adjust-stock');
        });
});

// ====================================
// Route settings (dari settings.php)
// ====================================
require __DIR__.'/settings.php';
