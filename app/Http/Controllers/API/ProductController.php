<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar semua produk dengan pagination
     * GET /api/products
     */
    public function index(Request $request)
    {
        try {
            $query = auth()->user()->products()->with('category')->wherePivot('stock', '>', 0)->orderBy('name', 'asc');

            // Filter pencarian
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->input('search') . '%');
            }

            // Filter kategori
            if ($request->has('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $products = $query->paginate($perPage);

            // Map pivot stock to main stock attribute
            $products->getCollection()->transform(function ($product) {
                $product->stock = $product->pivot->stock;
                return $product;
            });

            return response()->json([
                'success' => true,
                'message' => 'Daftar produk berhasil diambil',
                'data' => $products
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu produk
     * GET /api/products/{id}
     */
    public function show(Product $product)
    {
        try {
            $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Detail produk berhasil diambil',
                'data' => $product
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan produk baru
     * POST /api/products
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:200',
                'category_id' => 'nullable|exists:categories,id',
                'buy_price' => 'required|numeric|min:0',
                'sell_price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'unit' => 'required|string|max:20',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // Proses upload gambar jika ada
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            // Buat produk baru
            $product = Product::create($validated);
            $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan',
                'data' => $product
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
     * Memperbarui produk
     * PUT /api/products/{id}
     */
    public function update(Request $request, Product $product)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'sometimes|string|max:200',
                'category_id' => 'nullable|exists:categories,id',
                'buy_price' => 'sometimes|numeric|min:0',
                'sell_price' => 'sometimes|numeric|min:0',
                'stock' => 'sometimes|integer|min:0',
                'unit' => 'sometimes|string|max:20',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // Proses upload gambar baru jika ada
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            // Update produk
            $product->update($validated);
            $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui',
                'data' => $product
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
     * Menghapus produk
     * DELETE /api/products/{id}
     */
    public function destroy(Product $product)
    {
        try {
            $productName = $product->name;
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => "Produk '$productName' berhasil dihapus"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mencari produk berdasarkan query
     * GET /api/products/search/{query}
     */
    public function search(Request $request, $query)
    {
        try {
            $products = auth()->user()->products()->where('name', 'like', '%' . $query . '%')
                ->orWhere('unit', 'like', '%' . $query . '%')
                ->with('category')
                ->wherePivot('stock', '>', 0)
                ->orderBy('name', 'asc')
                ->limit(20)
                ->get()
                ->map(function ($product) {
                    $product->stock = $product->pivot->stock;
                    return $product;
                });

            return response()->json([
                'success' => true,
                'message' => 'Hasil pencarian produk',
                'data' => $products
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui stok produk
     * PUT /api/products/{id}/stock
     */
    public function updateStock(Request $request, Product $product)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'adjustment' => 'required|integer|not_in:0',
                'notes' => 'nullable|string|max:500',
            ]);

            // Hitung stok baru
            $newStock = $product->stock + $validated['adjustment'];

            // Pastikan stok tidak negatif
            if ($newStock < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Pengurangan melebihi stok yang tersedia.',
                    'current_stock' => $product->stock,
                    'requested_adjustment' => $validated['adjustment']
                ], 400);
            }

            // Update stok
            $product->update(['stock' => $newStock]);

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil diperbarui',
                'data' => [
                    'product' => $product,
                    'previous_stock' => $product->stock - $validated['adjustment'],
                    'adjustment' => $validated['adjustment'],
                    'new_stock' => $newStock
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
}
