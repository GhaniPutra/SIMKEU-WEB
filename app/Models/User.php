<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    // ====================================
    // Atribut yang dapat diisi secara massal
    // ====================================
    protected $fillable = [
        'store_id',     // ID toko/cabang
        'name',         // Nama pengguna
        'email',        // Email unik untuk login
        'password',     // Password terenkripsi
        'role',         // Role: admin atau pegawai
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // ====================================
    // Relasi dengan tabel Stores
    // ====================================
    // ====================================
    // Relasi dengan tabel Stores
    // ====================================
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relasi belongsToMany ke model Product.
     * Merepresentasikan stok produk yang dimiliki oleh kasir ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('stock')
                    ->withTimestamps();
    }
}
