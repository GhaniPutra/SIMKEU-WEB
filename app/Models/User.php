<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;

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
        'role_id',      // Role fleksibel per toko; admin tetap pemilik
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

    public function accessRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isOwner(): bool
    {
        return $this->role === 'admin';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        if ($this->accessRole && (int) $this->accessRole->store_id === (int) $this->store_id) {
            return $this->accessRole->hasPermission($permission);
        }

        if ($this->role_id !== null) {
            return false;
        }

        // Keep older accounts usable if they have not yet been assigned a role.
        return $this->role === 'pegawai'
            && in_array($permission, \App\Support\RolePermissions::legacyEmployee(), true);
    }

    public function getRoleNameAttribute(): string
    {
        $role = $this->accessRole;

        if ($this->isOwner()) {
            return 'Pemilik';
        }

        if ($this->role_id !== null && (! $role || (int) $role->store_id !== (int) $this->store_id)) {
            return 'Role tidak valid';
        }

        return $role?->name ?? 'Pegawai';
    }

    public function canAssignRole(Role $role): bool
    {
        if ($this->isOwner()) {
            return $role->store_id === $this->store_id;
        }

        return $role->store_id === $this->store_id
            && collect($role->permissions ?? [])->every(fn ($permission) => $this->hasPermission($permission));
    }

    public function resolveRouteBindingQuery($query, $value, $field = null): Builder
    {
        $query = parent::resolveRouteBindingQuery($query, $value, $field);
        $storeId = auth()->user()?->store_id;

        return $query->where('store_id', $storeId ?? 0);
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
