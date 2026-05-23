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

### 4. Jalankan migrasi & seeder *(opsional)*

```bash
php artisan migrate --seed
```

### 5. Instal dependensi frontend

```bash
composer install
npm install
```

### 6. Build asset frontend

```bash
npm run build
```

> ⚠️ Jika build gagal dengan error `[@laravel/vite-plugin-wayfinder] Error generating types`,
> lihat bagian **Troubleshooting** di bawah.

### 7. Jalankan server lokal

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Akses aplikasi di: `http://localhost:8000`

## Lisensi

[GhaniPutra License]