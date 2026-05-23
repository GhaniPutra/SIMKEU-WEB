<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar semua kategori
     * GET /api/categories
     */
    public function index(Request $request)
    {
        try {
            $query = Category::withCount('products')->orderBy('name', 'asc');

            // Filter pencarian
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            // Pagination
            $perPage = $request->input('per_page', 12);
            $categories = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Daftar kategori berhasil diambil',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu kategori
     * GET /api/categories/{id}
     */
    public function show(Category $category)
    {
        try {
            $category->loadCount('products');

            return response()->json([
                'success' => true,
                'message' => 'Detail kategori berhasil diambil',
                'data' => $category
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan kategori baru
     * POST /api/categories
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:categories,name',
                'description' => 'nullable|string|max:500',
            ], [
                'name.required' => 'Nama kategori wajib diisi.',
                'name.unique' => 'Nama kategori sudah digunakan.',
                'name.max' => 'Nama kategori maksimal 100 karakter.',
            ]);

            // Buat kategori baru
            $category = Category::create($validated);
            $category->loadCount('products');

            return response()->json([
                'success' => true,
                'message' => "Kategori \"{$category->name}\" berhasil ditambahkan",
                'data' => $category
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
     * Memperbarui kategori
     * PUT /api/categories/{id}
     */
    public function update(Request $request, Category $category)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'sometimes|string|max:100|unique:categories,name,' . $category->id,
                'description' => 'nullable|string|max:500',
            ], [
                'name.unique' => 'Nama kategori sudah digunakan.',
                'name.max' => 'Nama kategori maksimal 100 karakter.',
            ]);

            // Update kategori
            $category->update($validated);
            $category->loadCount('products');

            return response()->json([
                'success' => true,
                'message' => "Kategori \"{$category->name}\" berhasil diperbarui",
                'data' => $category
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
     * Menghapus kategori
     * DELETE /api/categories/{id}
     */
    public function destroy(Category $category)
    {
        try {
            $name = $category->name;
            $productCount = $category->products()->count();

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => "Kategori \"{$name}\" berhasil dihapus",
                'data' => [
                    'deleted_category' => $name,
                    'affected_products' => $productCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
