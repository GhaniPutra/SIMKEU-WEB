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
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Ambil kata kunci pencarian dari query string (opsional)
        $search = $request->input('search');

        // Query kategori dengan hitungan produk (withCount untuk menghindari N+1)
        $query = Category::withCount('products')->orderBy('name', 'asc');

        // Terapkan filter pencarian jika ada
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Paginate: 12 kategori per halaman
        $categories = $query->paginate(12)->withQueryString();

        // Total kategori yang ada di sistem
        $totalCategories = Category::count();

        // Total kategori yang sudah memiliki setidaknya 1 produk
        $totalCategorized = Category::has('products')->count();

        return view('categories.index', compact(
            'categories',
            'totalCategories',
            'totalCategorized',
            'search'
        ));
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

        return redirect()->route('categories.index')
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

        return redirect()->route('categories.index')
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
    public function destroy(Category $category)
    {
        $name = $category->name;
        $productCount = $category->products()->count();

        // Hapus kategori (produk terkait will set null pada category_id secara otomatis)
        $category->delete();

        $message = "Kategori \"{$name}\" berhasil dihapus.";
        if ($productCount > 0) {
            $message .= " {$productCount} produk terkait kini tidak memiliki kategori.";
        }

        return redirect()->route('categories.index')->with('success', $message);
    }
}
