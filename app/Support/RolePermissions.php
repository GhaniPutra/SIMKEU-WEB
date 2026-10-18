<?php

namespace App\Support;

class RolePermissions
{
    /**
     * Ready-made access patterns for owners who do not want to configure every
     * permission individually. These are starting points; the role form still
     * submits the ordinary permission list used by the rest of the app.
     */
    public static function profiles(): array
    {
        return [
            'manager' => [
                'name' => 'Manajer',
                'description' => 'Memantau laporan dan pengeluaran, serta mengelola pegawai, produk, stok, dan pembagian barang.',
                'permissions' => [
                    'dashboard.view', 'activities.view', 'sales.view',
                    'expenses.view',
                    'products.view', 'products.create', 'products.update', 'products.stock.add', 'products.stock.update',
                    'categories.view', 'categories.create', 'categories.update',
                    'employees.view', 'employees.create', 'employees.update', 'employees.sales.view',
                    'stock_allocations.view', 'stock_allocations.create',
                    'stock_allocations.review', 'stock_allocations.transfer',
                ],
            ],
            'finance' => [
                'name' => 'Keuangan',
                'description' => 'Melihat penjualan dan mengelola pengeluaran.',
                'permissions' => [
                    'dashboard.view', 'activities.view', 'sales.view',
                    'expenses.view', 'expenses.create', 'expenses.update', 'expenses.delete',
                ],
            ],
            'warehouse' => [
                'name' => 'Gudang',
                'description' => 'Mengelola produk, stok, dan alokasi barang.',
                'permissions' => [
                    'dashboard.view', 'products.view', 'products.create', 'products.update',
                    'products.stock.add', 'products.stock.update',
                    'categories.view', 'categories.create', 'categories.update',
                    'stock_allocations.view', 'stock_allocations.create', 'stock_allocations.confirm',
                    'stock_allocations.review', 'stock_allocations.transfer',
                ],
            ],
            'viewer' => [
                'name' => 'Lihat data saja',
                'description' => 'Melihat ringkasan dan data tanpa mengubahnya.',
                'permissions' => [
                    'dashboard.view', 'activities.view', 'sales.view', 'expenses.view',
                    'products.view', 'categories.view', 'employees.view',
                    'employees.sales.view', 'stock_allocations.view',
                ],
            ],
            'mobile_cashier' => [
                'name' => 'Kasir (aplikasi HP)',
                'description' => 'Mencatat penjualan dan pengeluaran harian dari aplikasi HP.',
                'permissions' => self::starterEmployee(),
            ],
            'custom' => [
                'name' => 'Atur sendiri',
                'description' => 'Pilih akses satu per satu sesuai kebutuhan.',
                'permissions' => null,
            ],
        ];
    }

    public static function profileFor(?array $permissions): string
    {
        $normalized = self::normalize(self::canonicalize($permissions ?? []));

        foreach (self::profiles() as $key => $profile) {
            if ($key !== 'custom' && self::normalize($profile['permissions']) === $normalized) {
                return $key;
            }
        }

        return 'custom';
    }

    /** Convert older channel-specific read keys to the shared task permissions. */
    public static function canonicalize(array $permissions): array
    {
        $aliases = [
            'dashboard.mobile.view' => 'dashboard.view',
            'expenses.mobile.view' => 'expenses.view',
            'expenses.mobile.create' => 'expenses.create',
            'products.mobile.view' => 'products.view',
            'categories.mobile.view' => 'categories.view',
            'sales.mobile.view' => 'sales.view',
        ];

        return array_values(array_unique(array_map(
            fn ($permission) => $aliases[$permission] ?? $permission,
            $permissions,
        )));
    }

    private static function normalize(array $permissions): array
    {
        $permissions = array_values(array_unique($permissions));
        sort($permissions);

        return $permissions;
    }

    /**
     * Permissions available to store roles. Owner-only actions are deliberately
     * excluded from this list and remain protected by the owner middleware.
     */
    public static function groups(): array
    {
        return [
            'Dashboard & aktivitas' => [
                'dashboard.view' => 'Lihat ringkasan',
                'activities.view' => 'Lihat catatan aktivitas',
            ],
            'Penjualan & kasir HP' => [
                'sales.view' => 'Lihat daftar penjualan',
                'sales.create' => 'Catat penjualan',
                'pos.mobile.use' => 'Buka kasir di aplikasi HP',
            ],
            'Pengeluaran' => [
                'expenses.view' => 'Lihat daftar pengeluaran',
                'expenses.create' => 'Catat pengeluaran baru',
                'expenses.update' => 'Ubah catatan pengeluaran',
                'expenses.delete' => 'Hapus catatan pengeluaran',
            ],
            'Produk & stok' => [
                'products.view' => 'Lihat produk dan stok',
                'products.create' => 'Tambah produk',
                'products.update' => 'Ubah informasi produk',
                'products.stock.add' => 'Tambah stok',
                'products.stock.update' => 'Ubah jumlah stok',
                'products.delete' => 'Hapus produk dan stok',
            ],
            'Kategori' => [
                'categories.view' => 'Lihat kategori produk',
                'categories.create' => 'Tambah kategori',
                'categories.update' => 'Ubah nama kategori',
                'categories.delete' => 'Hapus kategori',
            ],
            'Pegawai' => [
                'employees.view' => 'Lihat daftar pegawai',
                'employees.create' => 'Tambah akun pegawai',
                'employees.update' => 'Ubah data pegawai',
                'employees.delete' => 'Hapus akun pegawai',
                'employees.sales.view' => 'Lihat laporan penjualan per pegawai',
            ],
            'Alokasi stok pegawai' => [
                'stock_allocations.view' => 'Lihat pembagian stok',
                'stock_allocations.create' => 'Buat pembagian stok',
                'stock_allocations.confirm' => 'Konfirmasi barang di aplikasi HP',
                'stock_allocations.review' => 'Periksa pembagian stok',
                'stock_allocations.transfer' => 'Pindahkan stok antar pegawai',
            ],
        ];
    }

    public static function all(): array
    {
        return array_keys(array_merge(...array_values(self::groups())));
    }

    /** Minimal default for newly created stores: POS and employee stock flow on mobile. */
    public static function starterEmployee(): array
    {
        return [
            'dashboard.view', 'sales.view', 'sales.create',
            'products.view', 'categories.view', 'pos.mobile.use',
            'stock_allocations.view', 'stock_allocations.confirm',
            'expenses.view', 'expenses.create', 'expenses.update',
        ];
    }

    /** Existing pegawai permissions retained during the migration. */
    public static function legacyEmployee(): array
    {
        return [
            'dashboard.view', 'dashboard.mobile.view', 'activities.view', 'sales.view', 'sales.mobile.view', 'sales.create',
            'expenses.view', 'expenses.create', 'expenses.update', 'expenses.delete',
            'products.view', 'products.mobile.view', 'categories.view', 'categories.mobile.view', 'pos.mobile.use',
            'stock_allocations.view', 'stock_allocations.confirm',
        ];
    }
}
