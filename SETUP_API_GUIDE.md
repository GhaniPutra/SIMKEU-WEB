# Setup Panduan - REST API untuk SIMKEU

## Ringkasan Perubahan

Backend SIMKEU telah dipisahkan menjadi REST API yang powerful dengan fitur-fitur berikut:

✅ **Laravel Sanctum** - Token-based API authentication  
✅ **Dedicated API Routes** - `routes/api.php` untuk semua endpoint API  
✅ **API Controllers** - Business logic terpisah di `app/Http/Controllers/API/`  
✅ **Database Migration** - Personal access tokens table untuk Sanctum  
✅ **Full Documentation** - Lengkap dengan contoh penggunaan  

---

## File & Folder Baru

### Routes
- **`routes/api.php`** - Definisi semua endpoint API dengan grouping yang jelas

### Controllers
```
app/Http/Controllers/API/
├── AuthController.php           # Register, Login, Logout, Profile
├── CategoryController.php        # CRUD Kategori Produk
├── DashboardController.php       # Dashboard & Summary
├── EmployeeController.php        # Manajemen Karyawan
├── PemasukanController.php       # Penjualan/Pemasukan + Reports
├── PengeluaranController.php     # Pengeluaran + Reports
└── POSController.php             # Point of Sale Transactions
```

### Migrations
- **`database/migrations/2025_02_27_200000_create_personal_access_tokens_table.php`** - Sanctum tokens table

### Documentation
- **`API_DOCUMENTATION.md`** - Complete API documentation dengan contoh cURL

---

## Konfigurasi yang Sudah Dilakukan

### 1. Laravel Sanctum Installation
```bash
composer require laravel/sanctum
```

### 2. User Model Update
Ditambahkan trait `HasApiTokens` untuk enable token generation:
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    use HasApiTokens;
    // ...
}
```

### 3. Auth Config Update
Di `config/auth.php`, ditambahkan API guard:
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
        'hash' => false,
    ],
],
```

### 4. Database
Tabel `personal_access_tokens` sudah dibuat untuk menyimpan API tokens.

---

## Cara Menggunakan

### Langkah 1: Test API dengan Postman

1. Buka Postman atau tool serupa
2. Buat request POST ke `http://localhost:8000/api/login`
3. Kirim JSON body:
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

4. Copy token dari response
5. Gunakan token di header Authorization untuk request berikutnya:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

### Langkah 2: Test Endpoint Tertentu

```bash
# Test Dashboard
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# Test Products List
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

---

## Struktur API Endpoints

### Public Routes (Tanpa Auth)
```
POST   /api/register              - Register user baru
POST   /api/login                 - Login & dapatkan token
```

### Protected Routes (Butuh Token)
```
POST   /api/logout                - Logout
GET    /api/user                  - Get current user
POST   /api/user/profile          - Update profile

GET    /api/dashboard             - Dashboard overview
GET    /api/dashboard/summary     - Dashboard dengan trend

GET    /api/products              - Daftar produk
POST   /api/products              - Tambah produk
GET    /api/products/{id}         - Detail produk
PUT    /api/products/{id}         - Update produk
DELETE /api/products/{id}         - Hapus produk
PUT    /api/products/{id}/stock   - Update stok

GET    /api/categories            - Daftar kategori
POST   /api/categories            - Tambah kategori
PUT    /api/categories/{id}       - Update kategori
DELETE /api/categories/{id}       - Hapus kategori

GET    /api/sales                 - Daftar penjualan
POST   /api/sales                 - Catat penjualan
GET    /api/sales/{id}            - Detail penjualan
GET    /api/sales/report/period   - Laporan periode
GET    /api/sales/report/product  - Laporan produk

GET    /api/expenses              - Daftar pengeluaran
POST   /api/expenses              - Catat pengeluaran
PUT    /api/expenses/{id}         - Update pengeluaran
DELETE /api/expenses/{id}         - Hapus pengeluaran
GET    /api/expenses/report/period    - Laporan periode
GET    /api/expenses/report/category  - Laporan kategori

GET    /api/pos/items             - Daftar item POS
POST   /api/pos/transaction       - Buat transaksi
POST   /api/pos/transaction/{id}/complete - Selesaikan transaksi

GET    /api/employees             - Daftar karyawan
POST   /api/employees             - Tambah karyawan
GET    /api/employees/{id}        - Detail karyawan
PUT    /api/employees/{id}        - Update karyawan
DELETE /api/employees/{id}        - Hapus karyawan
GET    /api/employees/{id}/sales  - Sales data karyawan
```

---

## Response Format Standar

### Success
```json
{
  "success": true,
  "message": "Operasi berhasil",
  "data": { /* actual data */ }
}
```

### Error
```json
{
  "success": false,
  "message": "Error description",
  "errors": { /* validation errors if any */ }
}
```

---

## Development Tips

### Menambah Endpoint Baru

1. **Buat Controller** di `app/Http/Controllers/API/`
```php
namespace App\Http\Controllers\API;

class MyController extends Controller {
    public function index() {
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }
}
```

2. **Tambah Route** di `routes/api.php`
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('my-resource', MyController::class);
});
```

### Testing
```bash
# Run tests
php artisan test

# Specific test
php artisan test --filter=AuthControllerTest
```

---

## Backward Compatibility

**Web Routes** (`routes/web.php`) masih aktif dan berfungsi. Anda bisa:
- Tetap gunakan web routes untuk server-side rendering
- Gunakan API routes untuk mobile app atau frontend framework (React, Vue)
- Implementasi secara bertahap

---

## Troubleshooting

### 1. "Unauthorized" Error
- Pastikan token valid dan tidak expired
- Header format: `Authorization: Bearer {token}`

### 2. CORS Error (jika API di domain berbeda)
Edit `config/cors.php`:
```php
'allowed_origins' => ['http://localhost:3000', 'https://yourdomain.com'],
```

### 3. Token Expired
- Generate token baru dengan login
- Atau implement token refresh mechanism (future enhancement)

---

## Next Steps

### Recommended Enhancements

1. **Token Refresh**
   - Implement refresh token untuk security lebih baik
   - Auto-refresh sebelum token expired

2. **Rate Limiting**
   - Tambahkan rate limiting di middleware
   - Cegah abuse

3. **API Versioning**
   - Organize routes dengan `/api/v1/`, `/api/v2/`, dll
   - Easier untuk maintain backward compatibility

4. **Logging & Monitoring**
   - Log semua API requests untuk debugging
   - Monitor performance dengan APM tools

5. **API Keys** (untuk third-party)
   - Separate dari user tokens
   - Better untuk public API

---

## Resources

- **Dokumentasi Lengkap**: Lihat `API_DOCUMENTATION.md`
- **Laravel Sanctum Docs**: https://laravel.com/docs/sanctum
- **REST API Best Practices**: https://restfulapi.net/

---

## Support

Jika ada pertanyaan atau issue, silakan:
1. Cek `API_DOCUMENTATION.md` untuk panduan lengkap
2. Buka issue di repository
3. Hubungi tim development

---

**Status**: ✅ API Production Ready

Semua endpoint telah dikonfigurasi dengan proper error handling, validation, dan response formatting.

