<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Category
 *
 * Merepresentasikan tabel 'categories' di database.
 * Digunakan untuk mengelompokkan produk berdasarkan kategori.
 */
class Category extends Model
{
    /**
     * Kolom yang boleh diisi secara massal (mass assignment).
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',        // Nama kategori (unik)
        'description', // Deskripsi kategori (opsional)
    ];

    /**
     * Relasi hasMany ke model Product.
     * Satu kategori bisa memiliki banyak produk.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
