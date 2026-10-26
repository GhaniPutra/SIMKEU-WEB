<?php

namespace App\Http\Controllers;

use App\Events\StoreDataChanged;
use App\Models\Category; // Model kategori produk
use App\Models\Product;  // Model produk utama
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * ProductController
 *
 * Controller untuk manajemen produk / stok utama pada aplikasi SIMKEU.
 * Admin dapat menambah, mengedit, menghapus, dan menyesuaikan stok produk
 * sebelum stok didistribusikan ke karyawan.
 */
class ProductController extends Controller
{
    // =====================================================================
    // INDEX — Menampilkan daftar seluruh produk beserta ringkasan KPI stok
    // =====================================================================

    /**
     * Menampilkan halaman daftar produk beserta statistik stok.
     *
     * @param  \Illuminate\Http\Request  $request  Request dari client (bisa berisi query pencarian)
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $activeTab = $request->input('tab') === 'kategori' ? 'kategori' : 'produk';
        $search = $request->input('search');
        $categoryFilter = $request->input('category_id');
        $categorySearch = $request->input('category_search');

        if ($activeTab === 'kategori') {
            abort_unless($request->user()->hasPermission('categories.view') || $request->user()->hasPermission('categories.create'), 403);
            $categoryQuery = Category::withCount('products')->orderBy('name', 'asc');
            if ($categorySearch) {
                $categoryQuery->where(function ($q) use ($categorySearch) {
                    $q->where('name', 'like', '%' . $categorySearch . '%')
                      ->orWhere('description', 'like', '%' . $categorySearch . '%');
                });
            }

            $categoryList = $categoryQuery->paginate(12, ['*'], 'categories_page')->withQueryString();
            $totalCategories = Category::count();
            $totalCategorized = Category::has('products')->count();

            return view('products.index', compact(
                'activeTab',
                'categoryList',
                'categorySearch',
                'totalCategories',
                'totalCategorized',
                'search',
                'categoryFilter'
            ));
        }

        abort_unless($request->user()->hasPermission('products.view'), 403);

        // Ambil kata kunci pencarian dari query string (opsional)
        // Ambil filter kategori dari query string (opsional)
        // Query produk dengan eager loading kategori agar tidak N+1 query
        $query = Product::with('category')->orderBy('name', 'asc');

        // Terapkan filter pencarian jika ada input dari user
        if ($search) {
            // Cari produk berdasarkan nama (case-insensitive)
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Terapkan filter kategori jika dipilih user
        if ($categoryFilter) {
            $query->where('category_id', $categoryFilter);
        }

        // Paginate hasil query: 15 produk per halaman
        $products = $query->paginate(
            15,
            ['*'],
            'products_page',
            $request->query('products_page', $request->query('page'))
        )->withQueryString();

        // ---- KPI / Statistik ----

        // Total jumlah produk yang ada di sistem
        $totalProducts = Product::count();

        // Total keseluruhan stok dari semua produk
        $totalStock = Product::sum('stock');

        // Jumlah produk dengan stok rendah (≤ 10 unit)
        $lowStockCount = Product::where('stock', '<=', 10)->count();

        // Nilai total inventori: SUM(harga_beli × stok) untuk semua produk
        $inventoryValue = Product::selectRaw('SUM(buy_price * stock) as total')->value('total') ?? 0;

        // Ambil semua kategori untuk dropdown filter
        $categories = Category::orderBy('name')->get();

        // Kirim semua data ke view halaman produk
        return view('products.index', compact(
            'products',
            'totalProducts',
            'totalStock',
            'lowStockCount',
            'inventoryValue',
            'categories',
            'search',
            'categoryFilter',
            'categorySearch',
            'activeTab'
        ));
    }

    // =====================================================================
    // CREATE — Menampilkan form tambah produk baru
    // =====================================================================

    /**
     * Menampilkan form untuk membuat produk baru.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Ambil semua kategori untuk ditampilkan di dropdown form
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    // =====================================================================
    // STORE — Menyimpan produk baru ke database
    // =====================================================================

    /**
     * Menyimpan data produk baru ke database setelah validasi.
     *
     * @param  \Illuminate\Http\Request  $request  Data form yang dikirim user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi input form sebelum menyimpan ke database
        $data = $request->validate([
            'name' => 'required|string|max:200',           // Nama produk wajib diisi, maks 200 karakter
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('store_id', $request->user()->store_id)],
            'buy_price' => 'required|numeric|min:0',            // Harga beli wajib, angka, tidak boleh negatif
            'sell_price' => 'required|numeric|min:0',            // Harga jual wajib, angka, tidak boleh negatif
            'tax_rate' => 'nullable|numeric|min:0|max:100',   // Tarif pajak opsional, 0–100%
            'discount_rate' => 'nullable|numeric|min:0|max:100',   // Tarif diskon opsional, 0–100%
            'stock' => 'required|integer|min:0',            // Stok wajib, bilangan bulat, tidak boleh negatif
            'unit' => 'required|string|max:20',            // Satuan wajib diisi (pcs, kg, dll)
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Gambar opsional, maks 2MB
        ]);

        abort_if((int) $data['stock'] > 0 && ! $request->user()->hasPermission('products.stock.add'), 403);

        // Pastikan tax_rate dan discount_rate memiliki nilai default 0 jika kosong
        $data['tax_rate'] = $data['tax_rate'] ?? 0;
        $data['discount_rate'] = $data['discount_rate'] ?? 0;

        // Proses upload gambar jika ada file yang dikirim
        if ($request->hasFile('image')) {
            // Simpan gambar di folder public/products dan simpan path-nya
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // Buat produk baru di database dengan data yang sudah divalidasi
        Product::create($data);
        StoreDataChanged::dispatch($request->user()->store_id, ['inventory']);

        // Redirect ke halaman daftar produk dengan pesan sukses
        return redirect()->route($request->user()->hasPermission('products.view') ? 'products.index' : 'products.create')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    // =====================================================================
    // EDIT — Menampilkan form edit produk
    // =====================================================================

    /**
     * Menampilkan form untuk mengedit produk yang sudah ada.
     *
     * @param  \App\Models\Product  $product  Produk yang akan diedit (route model binding)
     * @return \Illuminate\View\View
     */
    public function edit(Product $product)
    {
        // Ambil semua kategori untuk dropdown
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    // =====================================================================
    // UPDATE — Menyimpan perubahan data produk
    // =====================================================================

    /**
     * Menyimpan perubahan data produk yang sudah diedit.
     *
     * @param  \Illuminate\Http\Request  $request  Data form yang dikirim user
     * @param  \App\Models\Product       $product  Produk yang akan diupdate (route model binding)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Product $product)
    {
        // Validasi input form (sama seperti store, tapi image opsional)
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('store_id', $request->user()->store_id)],
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',   // Tarif pajak opsional, 0–100%
            'discount_rate' => 'nullable|numeric|min:0|max:100',   // Tarif diskon opsional, 0–100%
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ((int) $data['stock'] !== (int) $product->stock) {
            $stockPermission = (int) $data['stock'] > (int) $product->stock
                ? 'products.stock.add'
                : 'products.stock.update';
            abort_unless($request->user()->hasPermission($stockPermission), 403);
        }

        // Pastikan tax_rate dan discount_rate memiliki nilai default 0 jika kosong
        $data['tax_rate'] = $data['tax_rate'] ?? 0;
        $data['discount_rate'] = $data['discount_rate'] ?? 0;

        // Proses upload gambar baru jika ada file yang dikirim
        if ($request->hasFile('image')) {
            // Simpan gambar baru dan ganti path lama dengan yang baru
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // Update data produk di database dengan data yang sudah divalidasi
        $product->update($data);
        StoreDataChanged::dispatch($request->user()->store_id, ['inventory']);

        // Redirect ke halaman daftar produk dengan pesan sukses
        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    // =====================================================================
    // DESTROY — Menghapus produk dari database
    // =====================================================================

    /**
     * Menghapus produk dari database secara permanen.
     *
     * @param  \App\Models\Product  $product  Produk yang akan dihapus (route model binding)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Product $product)
    {
        // Hapus produk dari database
        $storeId = $product->store_id;
        $product->delete();
        StoreDataChanged::dispatch($storeId, ['inventory']);

        // Redirect kembali ke halaman daftar produk dengan pesan sukses
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    // =====================================================================
    // ADJUST STOCK — Menyesuaikan stok produk (tambah atau kurangi)
    // =====================================================================

    /**
     * Menyesuaikan jumlah stok produk (tambah atau kurangi) secara instan.
     * Digunakan ketika admin ingin mengontrol stok tanpa membuka form edit penuh.
     *
     * @param  \Illuminate\Http\Request  $request  Berisi 'adjustment' (angka + atau -)
     * @param  \App\Models\Product       $product  Produk yang stoknya disesuaikan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adjustStock(Request $request, Product $product)
    {
        // Validasi input penyesuaian stok
        $data = $request->validate([
            // Angka penyesuaian: boleh positif (tambah) atau negatif (kurangi)
            'adjustment' => 'required|integer|not_in:0',
        ]);

        $stockPermission = (int) $data['adjustment'] > 0 ? 'products.stock.add' : 'products.stock.update';
        abort_unless(
            $request->user()->hasPermission($stockPermission)
                || $request->user()->hasPermission('products.stock.adjust'),
            403,
        );

        $newStock = DB::transaction(function () use ($product, $data) {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $newStock = $lockedProduct->stock + $data['adjustment'];
            if ($newStock < 0) {
                return null;
            }

            $lockedProduct->update(['stock' => $newStock]);
            return $newStock;
        });

        if ($newStock === null) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi. Pengurangan melebihi stok yang tersedia.');
        }
        StoreDataChanged::dispatch($request->user()->store_id, ['inventory']);

        // Tentukan keterangan aksi: penambahan atau pengurangan
        $action = $data['adjustment'] > 0
            ? 'ditambah ' . $data['adjustment']
            : 'dikurangi ' . abs($data['adjustment']);

        // Redirect ke halaman produk dengan pesan sukses
        return redirect()->route('products.index')
            ->with('success', "Stok produk \"{$product->name}\" berhasil {$action}. Stok sekarang: {$newStock}.");
    }
}
