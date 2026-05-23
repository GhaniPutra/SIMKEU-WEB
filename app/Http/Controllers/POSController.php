<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;

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

        // Ambil daftar pelanggan untuk opsi customer pada POS
        $customers = Customer::orderBy('name')->get();

        // Tampilkan halaman POS dengan data produk dan pelanggan
        return view('pos.index', compact('products', 'customers'));
    }

    // ============================================================
    // STORE — Simpan transaksi POS baru dengan perhitungan
    //         pajak dan diskon server-side.
    // POST /pos
    //
    // Alur Perhitungan:
    //   Per Item:
    //     harga_setelah_diskon = sell_price × (1 - discount_rate/100)
    //     subtotal_item        = harga_setelah_diskon × quantity
    //     pajak_item           = subtotal_item × (tax_rate/100)
    //
    //   Total Transaksi:
    //     subtotal         = Σ subtotal_item (setelah diskon produk)
    //     total_pajak      = Σ pajak_item
    //     diskon_tambahan  = input kasir (Rp)
    //     grand_total      = subtotal + total_pajak - diskon_tambahan
    // ============================================================
    public function store(Request $request)
    {
        // Validasi data form POS sebelum diproses
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:tunai,transfer,qris'],
            'notes' => ['nullable', 'string', 'max:500'],
            'extra_discount' => ['nullable', 'numeric', 'min:0'],  // Diskon tambahan dari kasir (Rp)
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price_at_sale' => ['required', 'numeric', 'min:0'],
            'items.*.subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        // Ambil data produk sesuai produk yang dipilih di cart
        $productIds = collect($validated['items'])->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Diskon tambahan dari kasir (nilai Rupiah, bukan persen)
        $extraDiscount = round($validated['extra_discount'] ?? 0, 2);

        // Hitung ulang subtotal, pajak, dan diskon agar aman dari manipulasi client-side
        $subtotalBeforeDiscount = 0;  // Total harga asli × jumlah
        $totalProductDiscount = 0;    // Total diskon dari produk (%)
        $totalTax = 0;                // Total pajak dari produk (%)

        foreach ($validated['items'] as $item) {
            if (!isset($products[$item['product_id']])) {
                return back()->withErrors(['items' => 'Produk tidak valid.'])->withInput();
            }

            $product = $products[$item['product_id']];

            // Periksa ketersediaan stok sebelum menyimpan transaksi
            if ($product->stock < $item['quantity']) {
                return back()->withErrors(['items' => "Stok untuk produk {$product->name} tidak mencukupi."])->withInput();
            }

            // Hitung harga setelah diskon produk
            $rawSubtotal = round($product->sell_price * $item['quantity'], 2);
            $discountRate = $product->discount_rate ?? 0;
            $taxRate = $product->tax_rate ?? 0;

            // Diskon produk = harga asli × discount_rate%
            $itemDiscount = round($rawSubtotal * ($discountRate / 100), 2);
            // Subtotal setelah diskon
            $afterDiscount = $rawSubtotal - $itemDiscount;
            // Pajak dihitung setelah diskon
            $itemTax = round($afterDiscount * ($taxRate / 100), 2);

            $subtotalBeforeDiscount += $rawSubtotal;
            $totalProductDiscount += $itemDiscount;
            $totalTax += $itemTax;
        }

        // Subtotal final = subtotal setelah diskon produk
        $subtotal = $subtotalBeforeDiscount - $totalProductDiscount;

        // Total diskon = diskon produk + diskon tambahan kasir
        $totalDiscount = $totalProductDiscount + $extraDiscount;

        // Grand total = subtotal + pajak - diskon tambahan
        $grandTotal = max(0, $subtotal + $totalTax - $extraDiscount);

        // Gunakan transaksi database untuk menjamin konsistensi stok dan sale
        DB::transaction(function () use ($validated, $products, $subtotal, $totalTax, $totalDiscount, $grandTotal) {
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'customer_id' => $validated['customer_id'] ?? null,
                'invoice_number' => 'INV-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4)),
                'transaction_date' => now(),
                'subtotal' => $subtotal,
                'tax' => $totalTax,
                'discount' => $totalDiscount,
                'total' => $grandTotal,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = $products[$item['product_id']];

                // Hitung subtotal per item setelah diskon produk
                $rawSubtotal = round($product->sell_price * $item['quantity'], 2);
                $discountRate = $product->discount_rate ?? 0;
                $itemDiscount = round($rawSubtotal * ($discountRate / 100), 2);
                $afterDiscount = $rawSubtotal - $itemDiscount;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $product->sell_price,
                    'subtotal' => $afterDiscount,
                ]);

                // Kurangi stok produk sesuai jumlah penjualan
                $product->decrement('stock', $item['quantity']);
            }
        });

        // Redirect kembali ke halaman POS dengan pesan sukses
        return redirect()->route('pos.index')
            ->with('success', 'Transaksi POS berhasil dicatat dan stok berhasil diperbarui.');
    }
}
