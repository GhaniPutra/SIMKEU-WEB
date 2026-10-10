<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Product
 *
 * Merepresentasikan tabel 'products' di database.
 * Digunakan untuk manajemen stok utama sebelum didistribusikan ke karyawan.
 */
class Product extends Model
{
    public const LOW_STOCK_THRESHOLD = 10;

    /**
     * Kolom yang boleh diisi secara massal (mass assignment).
     * Ini penting untuk keamanan agar tidak semua kolom bisa diisi sembarang.
     *
     * @var array<string>
     */
    protected $fillable = [
        'category_id',   // ID kategori produk (relasi ke tabel categories)
        'name',          // Nama produk
        'buy_price',     // Harga beli produk
        'sell_price',    // Harga jual produk
        'tax_rate',      // Tarif pajak dalam persen (contoh: 10 = 10%)
        'discount_rate', // Tarif diskon dalam persen (contoh: 5 = 5%)
        'stock',         // Jumlah stok yang tersedia
        'unit',          // Satuan produk (pcs, kg, liter, dll)
        'image',         // Path gambar produk (opsional)
    ];

    /**
     * Casting tipe data kolom.
     * Memastikan kolom harga dan stok dikonversi ke tipe yang tepat.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'buy_price'     => 'decimal:2',  // Harga beli dalam format desimal 2 angka
        'sell_price'    => 'decimal:2',  // Harga jual dalam format desimal 2 angka
        'tax_rate'      => 'decimal:2',  // Tarif pajak dalam persen
        'discount_rate' => 'decimal:2',  // Tarif diskon dalam persen
        'stock'         => 'integer',    // Stok dalam format bilangan bulat
    ];

    /**
     * Relasi belongsTo ke model Category.
     * Setiap produk dimiliki oleh satu kategori.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi hasMany ke model SaleItem.
     * Satu produk bisa muncul di banyak item penjualan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Atribut virtual: apakah stok produk tergolong rendah.
     * Stok dianggap rendah jika kurang dari atau sama dengan 10 unit.
     *
     * @return bool
     */
    public function getLowStockAttribute(): bool
    {
        return $this->stock <= self::LOW_STOCK_THRESHOLD;
    }

    /**
     * Atribut virtual: nilai total inventori produk ini.
     * Dihitung dari harga beli × jumlah stok.
     *
     * @return float
     */
    public function getInventoryValueAttribute(): float
    {
        return (float) $this->buy_price * $this->stock;
    }

    /**
     * Relasi belongsToMany ke model User.
     * Merepresentasikan distribusi stok ke kasir.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('stock')
                    ->withTimestamps();
    }
}
