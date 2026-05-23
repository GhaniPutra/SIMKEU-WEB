<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    // Disable timestamps karena tabel sale_items hanya menyimpan created_at.
    public $timestamps = false;

    // ============================================================
    // FILLABLE — Kolom yang dapat diisi massal.
    // Menyelaraskan dengan nama kolom pada migrasi sale_items.
    // ============================================================
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price_at_sale',
        'subtotal',
    ];

    // ============================================================
    // CASTS — Konversi data otomatis untuk atribut model.
    // ============================================================
    protected $casts = [
        'quantity' => 'integer',
        'price_at_sale' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // ============================================================
    // Relasi ke header penjualan.
    // Setiap item penjualan dimiliki oleh satu sale.
    // ============================================================
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // ============================================================
    // Relasi ke produk.
    // Setiap item penjualan merujuk ke satu produk.
    // ============================================================
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
