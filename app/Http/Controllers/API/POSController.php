<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\SaleCalculator;

class POSController extends Controller
{
    /**
     * Mendapatkan daftar item untuk POS
     * GET /api/pos/items
     */
    public function getItems(Request $request)
    {
        try {
            $search = $request->input('search', '');
            
            $query = auth()->user()->products()->wherePivot('stock', '>', 0)->orderBy('name');

            if ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            }

            $items = $query->get()->map(function ($product) {
                $product->stock = $product->pivot->stock;
                return $product;
            });

            return response()->json([
                'success' => true,
                'message' => 'Daftar item POS berhasil diambil',
                'data' => $items
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membuat transaksi POS baru
     * POST /api/pos/transaction
     */
    public function createTransaction(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'payment_method' => 'required|in:tunai,transfer,qris',
                'notes' => 'nullable|string|max:500',
                'extra_discount' => ['nullable', 'numeric', 'min:0'],
                'items' => 'required|array|min:1',
                'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('store_id', $request->user()->store_id)],
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]);

            // Ambil data produk
            $productIds = collect($validated['items'])->pluck('product_id')->unique();
            $products = auth()->user()->products()->whereIn('product_id', $productIds)->get()->keyBy('id');

            // Validasi stok sekaligus siapkan harga dan tarif produk dari server.
            $requiredStock = [];
            $calculationInputs = [];
            foreach ($validated['items'] as $item) {
                if (!isset($products[$item['product_id']])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Produk tidak valid atau tidak tersedia untuk Anda'
                    ], 400);
                }

                $product = $products[$item['product_id']];

                $requiredStock[$product->id] = ($requiredStock[$product->id] ?? 0) + $item['quantity'];
                $calculationInputs[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->sell_price,
                    'discount_rate' => $product->discount_rate,
                    'tax_rate' => $product->tax_rate,
                ];
            }

            foreach ($requiredStock as $productId => $quantity) {
                $product = $products[$productId];
                if ($product->pivot->stock < $quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok untuk produk {$product->name} tidak mencukupi. Stok tersedia: {$product->pivot->stock}"
                    ], 400);
                }
            }

            $calculation = SaleCalculator::calculate($calculationInputs, $validated['extra_discount'] ?? 0);

            // Buat transaksi dalam database transaction
            $sale = DB::transaction(function () use ($validated, $products, $calculation) {
                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'invoice_number' => 'INV-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4)),
                    'transaction_date' => now(),
                    'subtotal' => $calculation['subtotal'],
                    'tax' => $calculation['tax'],
                    'discount' => $calculation['discount'],
                    'total' => $calculation['total'],
                    'payment_method' => $validated['payment_method'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Simpan snapshot item dan kurangi stok dengan jumlah tervalidasi.
                foreach ($calculation['items'] as $index => $item) {
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

                    $deducted = DB::table('product_user')
                        ->where('user_id', auth()->id())
                        ->where('product_id', $product->id)
                        ->where('stock', '>=', $item['quantity'])
                        ->decrement('stock', $item['quantity'], ['updated_at' => now()]);
                    if ($deducted !== 1) {
                        throw ValidationException::withMessages([
                            "items.{$index}.quantity" => "Stok untuk produk {$product->name} tidak mencukupi.",
                        ]);
                    }
                }

                return $sale;
            });

            $sale->load(['user', 'saleItems.product']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi POS berhasil dibuat',
                'data' => $sale
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyelesaikan transaksi POS
     * POST /api/pos/transaction/{id}/complete
     */
    public function completeTransaction(Request $request, Sale $sale)
    {
        try {
            if ($sale->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi bukan milik Anda'
                ], 403);
            }

            // Validasi input
            $validated = $request->validate([
                'payment_method' => 'sometimes|in:tunai,transfer,qris',
                'discount' => 'sometimes|numeric|min:0',
                'tax' => 'sometimes|numeric|min:0',
            ]);

            foreach (['discount', 'tax'] as $amountField) {
                if (isset($validated[$amountField])) {
                    $validated[$amountField] = round((float) $validated[$amountField], 2);
                }
            }

            if (isset($validated['discount']) && (float) $validated['discount'] > (float) $sale->subtotal) {
                throw ValidationException::withMessages([
                    'discount' => 'Diskon tidak boleh melebihi subtotal transaksi.',
                ]);
            }
            if (isset($validated['discount']) && (float) $validated['discount'] < (float) $sale->saleItems()->sum('discount')) {
                throw ValidationException::withMessages([
                    'discount' => 'Diskon transaksi tidak boleh lebih kecil dari diskon produk yang sudah diterapkan.',
                ]);
            }

            // API completion values are final transaction-level amounts. When a
            // tax override no longer matches the item snapshots, mark the
            // per-item tax breakdown as unavailable instead of showing stale data.
            if (array_key_exists('tax', $validated)) {
                $itemTax = (float) $sale->saleItems()->sum('tax');
                if (abs($itemTax - (float) $validated['tax']) >= 0.005) {
                    $sale->saleItems()->update(['tax' => null]);
                }
            }

            $sale->fill($validated);
            $sale->total = round(
                (float) $sale->subtotal - (float) $sale->discount + (float) $sale->tax,
                2
            );
            $sale->save();

            $sale->load(['user', 'saleItems.product']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi POS berhasil diselesaikan',
                'data' => $sale
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menambah item ke transaksi
     * POST /api/pos/items/{product}/add
     */
    public function addItem(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $userProduct = auth()->user()->products()->where('product_id', $product->id)->first();
            if (!$userProduct || $userProduct->pivot->stock < $validated['quantity']) {
                $available = $userProduct ? $userProduct->pivot->stock : 0;
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak mencukupi. Stok tersedia: {$available}"
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan',
                'data' => [
                    'product' => $product,
                    'quantity' => $validated['quantity'],
                    'subtotal' => $product->sell_price * $validated['quantity']
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus item dari transaksi
     * DELETE /api/pos/items/{item}
     */
    public function removeItem(SaleItem $item)
    {
        try {
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
