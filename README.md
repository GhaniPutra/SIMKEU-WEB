# SIMKEU-WEB

Sistem Informasi Manajemen Keuangan (SIMKEU) berbasis web.


## Cara Menjalankan Proyek (Setelah Clone)

### 1. Clone repositori

```bash
git clone https://github.com/username/SIMKEU-WEB.git
cd SIMKEU-WEB
```

### 2. Instal dependensi PHP

```bash
composer install
```

### 3. Buat file environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan konfigurasi berikut:

```env
DB_DATABASE=nama_database
DB_USERNAME=username_db
DB_PASSWORD=password_db
```

### 4. Jalankan migrasi

```bash
php artisan migrate
```

`php artisan db:seed` tidak otomatis memasukkan data contoh. Tambahkan toko,
produk, transaksi, dan pengguna melalui aplikasi agar KPI mencerminkan data
operasional yang sebenarnya.

### 5. Instal dependensi frontend

```bash
npm install
```

### 6. Build asset frontend

```bash
npm run build
```

### 7. Jalankan aplikasi untuk pengembangan lokal

```bash
composer run dev
```

Perintah ini menjalankan server Laravel pada `0.0.0.0:8000`, Vite, queue, log,
dan Laravel Scheduler bersamaan. Alamat ini juga dapat dipakai perangkat lain
di jaringan yang sama untuk mengakses web/API. Biarkan terminal tetap berjalan
selama aplikasi digunakan. Scheduler
memeriksa tugas setiap menit, termasuk pengembalian otomatis stok pegawai pada
jam tutup usaha (default 23.59 zona waktu `Asia/Jakarta`).

Akses aplikasi di: `http://localhost:8000`.

Jika server dijalankan sendiri dengan `php artisan serve`, jalankan scheduler di
terminal kedua:

```bash
php artisan schedule:work
```

## Lisensi

[GhaniPutra License]
