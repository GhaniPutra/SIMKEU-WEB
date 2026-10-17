<?php

namespace App\Models;

// ============================================================
// Import class-class yang dibutuhkan oleh model ini
// ============================================================
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Product;
use App\Models\Concerns\BelongsToStore;

class Expense extends Model
{
    use BelongsToStore;

    // ============================================================
    // FILLABLE — Kolom yang boleh diisi secara massal (mass assignment)
    // Sesuai dengan kolom di tabel 'expenses' pada migrasi
    // ============================================================
    protected $fillable = [
        'user_id',      // ID user (kasir/admin) yang mencatat pengeluaran
        'expense_date', // Tanggal pengeluaran (tipe: date)
        'amount',       // Jumlah nominal pengeluaran
        'category',     // Kategori: beli_stok, gaji, listrik, air, sewa, transport, lainnya
        'description',  // Keterangan / deskripsi pengeluaran
    ];

    // ============================================================
    // CASTS — Konversi otomatis tipe data kolom
    // ============================================================
    protected $casts = [
        'expense_date' => 'date',       // Otomatis dikonversi ke objek Carbon
        'amount'       => 'decimal:2',  // Selalu 2 angka desimal
    ];

    // ============================================================
    // RELASI — User yang mencatat pengeluaran ini
    // Setiap pengeluaran dimiliki oleh satu user
    // ============================================================
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
