<?php

namespace App\Http\Controllers;

use App\Events\StoreDataChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\SaleCalculator;

class POSController extends Controller
{
    // ============================================================
    // INDEX — Halaman point of sale untuk input transaksi kasir.
    // GET /pos
    // ============================================================
    public function index()
    {
        // Ambil produk yang tersedia stoknya lebih dari nol
        // Sertakan field tax_rate dan discount_rate untuk perhitungan di POS
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('products'));
    }

    // ============================================================
    // STORE — Simpan transaksi POS baru dengan perhitungan
    //         pajak dan diskon server-side.
    // POST /pos
    //
    // Subtotal selalu bruto sebelum diskon; semua nilai item dihitung ulang
    // di server dengan aturan yang sama seperti transaksi POS mobile.
    // ============================================================
    public function store(Request $request)
    {
        // Validasi data form POS sebelum diproses
        $validated = $request->validate([
            'payment_method' => ['required', 'in:tunai,transfer,qris'],
            'notes' => ['nullable', 'string', 'max:500'],
            'extra_discount' => ['nullable', 'numeric', 'min:0'],  // Diskon tambahan dari kasir (Rp)
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->where('store_id', $request->user()->store_id)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price_at_sale' => ['required', 'numeric', 'min:0'],
            'items.*.subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        // Ambil data produk sesuai produk yang dipilih di cart
        $productIds = collect($validated['items'])->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $calculationInputs = [];

        foreach ($validated['items'] as $item) {
            if (!isset($products[$item['product_id']])) {
                return back()->withErrors(['items' => 'Produk tidak valid.'])->withInput();
            }

            $product = $products[$item['product_id']];

            // Periksa ketersediaan stok sebelum menyimpan transaksi
            if ($product->stock < $item['quantity']) {
                return back()->withErrors(['items' => "Stok untuk produk {$product->name} tidak mencukupi."])->withInput();
            }

            $calculationInputs[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->sell_price,
                'discount_rate' => $product->discount_rate,
                'tax_rate' => $product->tax_rate,
            ];
        }

        $calculation = SaleCalculator::calculate($calculationInputs, $validated['extra_discount'] ?? 0);

        // Gunakan transaksi database untuk menjamin konsistensi stok dan sale
        DB::transaction(function () use ($validated, $products, $calculation) {
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'invoice_number' => 'INV-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4)),
                'transaction_date' => now(),
                'subtotal' => $calculation['subtotal'],
                'tax' => $calculation['tax'],
                'discount' => $calculation['discount'],
                'total' => $calculation['total'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($calculation['items'] as $item) {
                $product = $products[$item['product_id']];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $item['price_at_sale'],
                    'subtotal' => $item['subtotal'],
                    'discount' => $item['discount'],
                    'tax' => $item['tax'],
                ]);

                $deducted = DB::table('products')
                    ->where('id', $product->id)
                    ->where('stock', '>=', $item['quantity'])
                    ->decrement('stock', $item['quantity'], ['updated_at' => now()]);
                if ($deducted !== 1) {
                    throw ValidationException::withMessages([
                        'items' => "Stok untuk produk {$product->name} tidak mencukupi.",
                    ]);
                }
            }
        });
        StoreDataChanged::dispatch($request->user()->store_id, ['sales', 'inventory']);

        // Redirect kembali ke halaman POS dengan pesan sukses
        return redirect()->route('pos.index')
            ->with('success', 'Transaksi POS berhasil dicatat dan stok berhasil diperbarui.');
    }
}
