<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

/**
 * CategoryController
 *
 * Controller untuk manajemen kategori produk pada aplikasi SIMKEU.
 * Admin dapat menambah, mengedit, dan menghapus kategori yang
 * digunakan untuk mengelompokkan produk / stok barang.
 */
class CategoryController extends Controller
{
    // =====================================================================
    // INDEX — Menampilkan daftar seluruh kategori
    // =====================================================================

    /**
     * Menampilkan halaman daftar kategori beserta jumlah produk per kategori.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $query = $request->query();
        if (isset($query['search']) && !isset($query['category_search'])) {
            $query['category_search'] = $query['search'];
            unset($query['search']);
        }
        if (isset($query['page']) && !isset($query['categories_page'])) {
            $query['categories_page'] = $query['page'];
            unset($query['page']);
        }

        return redirect()->route('products.index', array_merge($query, ['tab' => 'kategori']));
    }

    // =====================================================================
    // STORE — Menyimpan kategori baru ke database
    // =====================================================================

    /**
     * Menyimpan kategori baru ke database setelah validasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi input: nama wajib dan unik di tabel categories
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori sudah digunakan, silakan gunakan nama lain.',
            'name.max'      => 'Nama kategori maksimal 100 karakter.',
        ]);

        // Simpan kategori baru ke database
        Category::create($data);

        return $this->redirectToCategories($request)
            ->with('success', "Kategori \"{$data['name']}\" berhasil ditambahkan.");
    }

    // =====================================================================
    // UPDATE — Menyimpan perubahan data kategori
    // =====================================================================

    /**
     * Menyimpan perubahan data kategori yang diedit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category      $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        // Validasi: nama wajib, unik kecuali untuk kategori yang sedang diedit
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori sudah digunakan, silakan gunakan nama lain.',
            'name.max'      => 'Nama kategori maksimal 100 karakter.',
        ]);

        // Update data kategori dengan data yang sudah divalidasi
        $category->update($data);

        return $this->redirectToCategories($request)
            ->with('success', "Kategori \"{$category->name}\" berhasil diperbarui.");
    }

    // =====================================================================
    // DESTROY — Menghapus kategori dari database
    // =====================================================================

    /**
     * Menghapus kategori dari database.
     * Produk yang menggunakan kategori ini akan menjadi 'tanpa kategori'
     * karena kolom category_id di tabel products menggunakan onDelete('set null').
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, Category $category)
    {
        $name = $category->name;
        $productCount = $category->products()->count();

        // Hapus kategori (produk terkait will set null pada category_id secara otomatis)
        $category->delete();

        $message = "Kategori \"{$name}\" berhasil dihapus.";
        if ($productCount > 0) {
            $message .= " {$productCount} produk terkait kini tidak memiliki kategori.";
        }

        return $this->redirectToCategories($request)->with('success', $message);
    }

    private function redirectToCategories(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('products.index', array_merge(
            $request->only(['search', 'category_id', 'products_page', 'category_search', 'categories_page']),
            ['tab' => 'kategori']
        ));
    }
}
