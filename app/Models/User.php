<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, MustVerifyEmailTrait;

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
    ];

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
