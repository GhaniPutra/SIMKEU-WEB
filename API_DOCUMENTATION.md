# API Documentation - SIMKEU REST API

## Pendahuluan

Aplikasi SIMKEU sekarang dilengkapi dengan REST API yang menggunakan **Laravel Sanctum** untuk autentikasi berbasis token. API ini memungkinkan klien eksternal (mobile app, web app, dll) untuk berinteraksi dengan backend SIMKEU.

---

## Base URL

```
http://127.0.0.1:8000/api
```

---

## Autentikasi

Semua endpoint API (kecuali register dan login) memerlukan authentication token.

### Header yang Diperlukan

Tambahkan header berikut ke setiap request (kecuali login/register):

```
Authorization: Bearer {TOKEN}
Content-Type: application/json
```

### Mendapatkan Token

#### Register User Baru
**POST** `/api/register`

```json
{
  "name": "Nama User",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response:
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Nama User",
      "email": "user@example.com"
    },
    "token": "1|abc123...xyz",
    "token_type": "Bearer"
  }
}
```

#### Login
**POST** `/api/login`

```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

Response:
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {...},
    "token": "1|abc123...xyz",
    "token_type": "Bearer"
  }
}
```

---

## Endpoints

### Authentication

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/register` | Register user baru |
| POST | `/api/login` | Login dan dapatkan token |
| POST | `/api/logout` | Logout dan revoke token |
| GET | `/api/user` | Get user yang sedang login |
| POST | `/api/user/profile` | Update profil user |

### Dashboard

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/dashboard` | Ringkasan dashboard |
| GET | `/api/dashboard/summary` | Ringkasan detail dengan trend |

### Products (Produk)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/products` | Daftar semua produk (paginated) |
| POST | `/api/products` | Tambah produk baru |
| GET | `/api/products/{id}` | Detail satu produk |
| PUT/PATCH | `/api/products/{id}` | Update produk |
| DELETE | `/api/products/{id}` | Hapus produk |
| GET | `/api/products/search/{query}` | Cari produk |
| PUT | `/api/products/{id}/stock` | Update stok produk |

### Categories (Kategori)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/categories` | Daftar semua kategori |
| POST | `/api/categories` | Tambah kategori baru |
| GET | `/api/categories/{id}` | Detail satu kategori |
| PUT/PATCH | `/api/categories/{id}` | Update kategori |
| DELETE | `/api/categories/{id}` | Hapus kategori |

### Sales (Penjualan/Pemasukan)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/sales` | Daftar penjualan (paginated) |
| POST | `/api/sales` | Catat penjualan baru |
| GET | `/api/sales/{id}` | Detail satu penjualan |
| GET | `/api/sales/report/period` | Laporan per periode |
| GET | `/api/sales/report/product` | Laporan per produk |

### Expenses (Pengeluaran)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/expenses` | Daftar pengeluaran (paginated) |
| POST | `/api/expenses` | Catat pengeluaran baru |
| GET | `/api/expenses/{id}` | Detail satu pengeluaran |
| PUT/PATCH | `/api/expenses/{id}` | Update pengeluaran |
| DELETE | `/api/expenses/{id}` | Hapus pengeluaran |
| GET | `/api/expenses/report/period` | Laporan per periode |
| GET | `/api/expenses/report/category` | Laporan per kategori |

### POS (Point of Sale)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/pos/items` | Daftar item untuk POS |
| POST | `/api/pos/transaction` | Buat transaksi POS |
| POST | `/api/pos/transaction/{id}/complete` | Selesaikan transaksi |
| POST | `/api/pos/items/{product}/add` | Tambah item ke cart |
| DELETE | `/api/pos/items/{item}` | Hapus item dari cart |

### Employees (Karyawan)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/employees` | Daftar karyawan |
| POST | `/api/employees` | Tambah karyawan baru |
| GET | `/api/employees/{id}` | Detail karyawan |
| PUT/PATCH | `/api/employees/{id}` | Update karyawan |
| DELETE | `/api/employees/{id}` | Hapus karyawan |
| GET | `/api/employees/{id}/sales` | Data penjualan karyawan |

---

## Contoh Penggunaan (cURL)

### 1. Register dan Login

```bash
# Register
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### 2. Menggunakan Token untuk Protected Endpoint

```bash
# Ambil data user yang sedang login
curl -X GET http://127.0.0.1:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"

# Daftar produk
curl -X GET "http://127.0.0.1:8000/api/products?per_page=10" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### 3. Menambah Produk

```bash
curl -X POST http://127.0.0.1:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laptop Dell",
    "category_id": 1,
    "buy_price": 5000000,
    "sell_price": 6000000,
    "stock": 10,
    "unit": "pcs"
  }'
```

### 4. Membuat Transaksi Penjualan

```bash
curl -X POST http://127.0.0.1:8000/api/sales \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": null,
    "payment_method": "tunai",
    "items": [
      {
        "product_id": 1,
        "quantity": 2,
        "price": 50000
      }
    ],
    "notes": "Penjualan reguler"
  }'
```

---

## Response Format

Semua API responses menggunakan format JSON konsisten:

### Success Response

```json
{
  "success": true,
  "message": "Deskripsi operasi",
  "data": {
    // Data yang diminta
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Deskripsi error",
  "errors": {
    // Field validation errors (jika ada)
  }
}
```

### Pagination Response

```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {
    "data": [
      // Array of items
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15,
    "links": [
      // Pagination links
    ]
  }
}
```

---

## HTTP Status Codes

| Code | Arti |
|------|------|
| 200 | OK - Request berhasil |
| 201 | Created - Resource berhasil dibuat |
| 400 | Bad Request - Request tidak valid |
| 401 | Unauthorized - Token tidak valid/expired |
| 422 | Validation Error - Data tidak valid |
| 500 | Server Error - Kesalahan server |

---

## Query Parameters

### Pagination

```
GET /api/products?page=1&per_page=15
```

### Searching & Filtering

```
GET /api/products?search=laptop&category_id=1
GET /api/expenses?bulan=4&tahun=2026&kategori=gaji
GET /api/sales?search=INV&metode=tunai
```

### Date Range

```
GET /api/sales/report/period?start_date=2026-04-01&end_date=2026-04-30
```

---

## Migrasi dari Web Routes ke API

Jika Anda sudah memiliki code yang menggunakan web routes, berikut adalah mapping:

| Web Route | API Endpoint |
|-----------|--------------|
| POST /login | POST /api/login |
| POST /logout | POST /api/logout |
| GET /dashboard | GET /api/dashboard |
| GET /products | GET /api/products |
| POST /products | POST /api/products |
| PUT /products/{id} | PUT /api/products/{id} |
| DELETE /products/{id} | DELETE /api/products/{id} |
| GET /pemasukan | GET /api/sales |
| POST /pos | POST /api/pos/transaction |

---

## Tips & Best Practices

1. **Token Management**
   - Simpan token di secure storage (localStorage, SessionStorage, atau secure cookies)
   - Jangan expose token di URL
   - Refresh/rotate token secara berkala jika diperlukan

2. **Error Handling**
   - Selalu handle HTTP status codes
   - Check respons `success` flag sebelum mengakses `data`
   - Log errors untuk debugging

3. **Rate Limiting**
   - API saat ini tidak memiliki rate limiting, tapi dapat ditambahkan di masa depan
   - Gunakan request dengan bijak

4. **API Testing**
   - Gunakan tools seperti Postman, Insomnia, atau Thunder Client
   - Buat collection untuk semua endpoint
   - Share collection dengan tim

---

## Troubleshooting

### "Unauthorized" Error
- Periksa apakah token valid dan belum expired
- Pastikan header Authorization dengan format `Bearer {token}`

### "Validation Error" pada response
- Periksa field requirements di dokumentasi
- Lihat `errors` field di response untuk detail

### CORS Error
- Jika API diakses dari domain berbeda, configure CORS di `config/cors.php`
- Tambahkan domain client ke `allowed_origins`

---

## Support

Untuk pertanyaan atau masalah, silakan hubungi tim development atau buka issue di repository.

