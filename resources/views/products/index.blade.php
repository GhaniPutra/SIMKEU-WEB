{{-- ====================================================================
     Halaman: Manajemen Produk / Stok — SIMKEU
     Deskripsi: Halaman ini memungkinkan admin untuk melihat daftar
                seluruh produk beserta stok utama, menambah, mengedit,
                menghapus produk, serta menyesuaikan stok secara cepat.
     Extends: layouts.app (layout utama SIMKEU)
==================================================================== --}}
@extends('layouts.app')

{{-- =====================================================
     Judul halaman yang ditampilkan di tab browser
===================================================== --}}
@section('title', 'Produk & Stok — SIMKEU')

{{-- =====================================================
     Judul halaman dan breadcrumb di topbar
===================================================== --}}
@section('page-title', 'Produk & Stok')
@section('breadcrumb', 'Manajemen / Produk & Stok')

{{-- =====================================================
     CSS eksternal: Memuat stylesheet terpisah untuk halaman
     Daftar Produk & Stok. File CSS dipindahkan ke
     public/css/products/index.css agar mudah dikelola.
===================================================== --}}
@section('css')
    {{-- Link ke file CSS eksternal untuk halaman produk --}}
    <link rel="stylesheet" href="{{ asset('css/products/index.css') }}">
@endsection

{{-- =====================================================
     Konten Utama Halaman
===================================================== --}}
@section('content')

    @if($activeTab === 'produk')
    {{-- ============================================================
         KPI CARDS — Ringkasan statistik stok utama
         Data dikirim dari ProductController::index()
    ============================================================ --}}
    <div class="kpi-grid">

        {{-- Kartu: Total Produk --}}
        <div class="kpi-card primary fade-up">
            <div class="kpi-top">
                <span class="kpi-label">Total Produk</span>
                <div class="kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
            </div>
            {{-- Tampilkan jumlah total produk dari database --}}
            <div class="kpi-value">{{ number_format($totalProducts) }}</div>
            <div class="kpi-sub">Jenis produk terdaftar</div>
        </div>

        {{-- Kartu: Total Stok Keseluruhan --}}
        <div class="kpi-card success fade-up">
            <div class="kpi-top">
                <span class="kpi-label">Total Stok</span>
                <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
            </div>
            {{-- Total semua unit stok dari seluruh produk --}}
            <div class="kpi-value">{{ number_format($totalStock) }}</div>
            <div class="kpi-sub">Unit stok tersedia</div>
        </div>

        {{-- Kartu: Stok Rendah (peringatan) --}}
        <div class="kpi-card warning fade-up">
            <div class="kpi-top">
                <span class="kpi-label">Stok Rendah</span>
                <div class="kpi-icon"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
            {{-- Jumlah produk dengan stok ≤ 10 unit --}}
            <div class="kpi-value">{{ number_format($lowStockCount) }}</div>
            <div class="kpi-sub">Produk perlu restock (≤10 unit)</div>
        </div>

        {{-- Kartu: Nilai Inventori --}}
        <div class="kpi-card danger fade-up">
            <div class="kpi-top">
                <span class="kpi-label">Nilai Inventori</span>
                <div class="kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            {{-- Total nilai inventori = SUM(harga beli × stok) --}}
            <div class="kpi-value" style="font-size:1.1rem;">
                Rp {{ number_format($inventoryValue, 0, ',', '.') }}
            </div>
            <div class="kpi-sub">Nilai total stok (harga beli)</div>
        </div>

    </div>

    {{-- ============================================================
         TABEL DAFTAR PRODUK
    ============================================================ --}}
    <p class="section-title">
        <i class="fas fa-box-open" style="color:var(--icon-primary)"></i>
        Daftar Produk & Stok Utama
    </p>

    <div class="content-card fade-up">

        {{-- Header kartu: judul + tombol tambah produk baru --}}
        <div class="content-card-header">
            <span class="content-card-title">
                <i class="fas fa-table-list"></i>
                Stok Utama
            </span>
            {{-- Tombol untuk membuka halaman form tambah produk --}}
            <a href="{{ route('products.create') }}" class="btn-add">
                <i class="fas fa-plus"></i>
                Tambah Produk
            </a>
        </div>

        {{-- ============================================================
             TOOLBAR — Pencarian dan Filter
        ============================================================ --}}
        <form method="GET" action="{{ route('products.index') }}" class="toolbar">
            <input type="hidden" name="tab" value="produk">
            <input type="hidden" name="category_search" value="{{ $categorySearch }}">
            @if(request('categories_page'))
                <input type="hidden" name="categories_page" value="{{ request('categories_page') }}">
            @endif

            {{-- Input pencarian berdasarkan nama produk --}}
            <div class="toolbar-search">
                <i class="fas fa-magnifying-glass search-icon"></i>
                <input
                    type="text"
                    name="search"
                    placeholder="Cari nama produk..."
                    value="{{ $search }}" {{-- Pertahankan nilai pencarian sebelumnya --}}
                >
            </div>

            {{-- Dropdown filter berdasarkan kategori --}}
            <select name="category_id">
                <option value="">Semua Kategori</option>
                {{-- Iterasi semua kategori yang tersedia --}}
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{-- Tandai opsi yang sedang dipilih --}}
                        {{ $categoryFilter == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            {{-- Tombol terapkan filter --}}
            <button type="submit" class="btn-add" style="background:var(--text-muted);">
                <i class="fas fa-filter"></i> Filter
            </button>

            {{-- Tombol reset filter: hapus semua parameter pencarian --}}
            @if($search || $categoryFilter)
                <a href="{{ route('products.index', array_merge(request()->except(['search', 'category_id', 'products_page']), ['tab' => 'produk'])) }}" class="btn-add btn-reset">
                    <i class="fas fa-xmark"></i> Reset
                </a>
            @endif

        </form>

        {{-- ============================================================
             TABEL DATA PRODUK
        ============================================================ --}}
        <div class="content-card-body">
            <div style="overflow-x:auto;">
                <table class="data-table">

                    {{-- Header kolom tabel --}}
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>   {{-- Nomor urut --}}
                            <th>Produk</th>                   {{-- Nama & satuan --}}
                            <th>Kategori</th>                 {{-- Kategori produk --}}
                            <th>Harga Beli</th>               {{-- Harga modal pembelian --}}
                            <th>Harga Jual</th>               {{-- Harga yang ditawarkan ke pembeli --}}
                            <th>Stok</th>                     {{-- Jumlah stok saat ini --}}
                            <th style="width:120px;">Aksi</th>{{-- Tombol edit/hapus/stok --}}
                        </tr>
                    </thead>

                    <tbody>
                        {{-- ================================================
                             Tampilkan pesan jika tidak ada produk ditemukan
                        ================================================ --}}
                        @forelse($products as $index => $product)
                            <tr>
                                {{-- Nomor urut (mempertimbangkan halaman paginasi) --}}
                                <td style="color:var(--text-muted);font-size:0.75rem;">
                                    {{ $products->firstItem() + $index }}
                                </td>

                                {{-- Nama produk dengan avatar warna-warni --}}
                                <td>
                                    <div class="product-name-cell">
                                        {{-- Avatar: huruf pertama nama produk --}}
                                        <div class="product-avatar"
                                            data-hue="{{ crc32($product->name) % 360 }}">
                                            {{ strtoupper(substr($product->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="product-name-text">{{ $product->name }}</div>
                                            <div class="product-unit-text">Satuan: {{ $product->unit }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Nama kategori produk (dari relasi) --}}
                                <td>
                                    @if($product->category)
                                        {{-- Tampilkan nama kategori jika ada --}}
                                        <span style="
                                            display:inline-block;
                                            padding:3px 10px;
                                            border-radius:20px;
                                            font-size:0.72rem;
                                            font-weight:600;
                                            background:rgba(var(--primary-rgb),.08);
                                            color:var(--primary);
                                        ">{{ $product->category->name }}</span>
                                    @else
                                        {{-- Tampilkan tanda strip jika tidak ada kategori --}}
                                        <span style="color:var(--text-muted)">—</span>
                                    @endif
                                </td>

                                {{-- Harga beli dalam format Rupiah --}}
                                <td>Rp {{ number_format($product->buy_price, 0, ',', '.') }}</td>

                                {{-- Harga jual dalam format Rupiah --}}
                                <td>Rp {{ number_format($product->sell_price, 0, ',', '.') }}</td>

                                {{-- Badge stok dengan warna berbeda berdasarkan kondisi --}}
                                <td>
                                    @if($product->stock == 0)
                                        {{-- Stok habis: tampilkan badge abu --}}
                                        <span class="stock-badge stock-empty">
                                            <i class="fas fa-circle-xmark" style="font-size:.65rem;"></i>
                                            Habis
                                        </span>
                                    @elseif($product->stock <= 10)
                                        {{-- Stok rendah: tampilkan badge merah dengan jumlah --}}
                                        <span class="stock-badge stock-low">
                                            <i class="fas fa-triangle-exclamation" style="font-size:.65rem;"></i>
                                            {{ $product->stock }} {{ $product->unit }}
                                        </span>
                                    @else
                                        {{-- Stok normal: tampilkan badge hijau --}}
                                        <span class="stock-badge stock-ok">
                                            <i class="fas fa-circle-check" style="font-size:.65rem;"></i>
                                            {{ $product->stock }} {{ $product->unit }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom aksi: edit, sesuaikan stok, hapus --}}
                                <td>
                                    <div style="display:flex;gap:5px;align-items:center;">

                                        {{-- Tombol Edit: arahkan ke halaman form edit --}}
                                        <a href="{{ route('products.edit', $product->id) }}"
                                           class="btn-action edit"
                                           title="Edit Produk">
                                            <i class="fas fa-pencil"></i>
                                        </a>

                                        {{-- Tombol Sesuaikan Stok: buka modal --}}
                                        <button
                                            class="btn-action stock"
                                            title="Sesuaikan Stok"
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ e($product->name) }}"
                                            data-product-stock="{{ $product->stock }}"
                                            data-product-unit="{{ e($product->unit) }}"
                                        >
                                            <i class="fas fa-sliders"></i>
                                        </button>

                                        {{-- Tombol Hapus: kirim form DELETE dengan konfirmasi --}}
                                        <form method="POST"
                                              action="{{ route('products.destroy', $product->id) }}"
                                              id="delete-form-{{ $product->id }}"
                                              style="display:inline;">
                                            @csrf       {{-- Token CSRF untuk keamanan --}}
                                            @method('DELETE') {{-- Override method menjadi DELETE --}}
                                            <button
                                                type="button"
                                                class="btn-action delete"
                                                title="Hapus Produk"
                                                data-delete-form-id="delete-form-{{ $product->id }}"
                                                data-product-name="{{ e($product->name) }}"
                                                data-product-id="{{ $product->id }}"
                                            >
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        {{-- Tampilkan pesan jika hasil kosong --}}
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">
                                    <i class="fas fa-box-open" style="font-size:2rem; margin-bottom:12px; display:block; opacity:.4;"></i>
                                    @if($search || $categoryFilter)
                                        Tidak ada produk yang cocok dengan filter yang dipilih.
                                    @else
                                        Belum ada produk. <a href="{{ route('products.create') }}" style="color:var(--primary);">Tambah produk pertama</a>.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        {{-- ============================================================
             PAGINASI — Navigasi halaman
        ============================================================ --}}
        @if($products->hasPages())
            <div class="pagination-wrapper">
                {{-- Info rentang data yang ditampilkan --}}
                <span class="pagination-info">
                    Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }}
                    dari {{ $products->total() }} produk
                </span>
                {{-- Link paginasi Laravel Bootstrap --}}
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>{{-- /.content-card --}}

    {{-- ============================================================
         MODAL PENYESUAIAN STOK
         Ditampilkan saat admin mengklik ikon penyesuaian stok
    ============================================================ --}}
    <div class="modal-overlay" id="stockModal">
        <div class="modal-box">
            {{-- Judul modal --}}
            <p class="modal-title"><i class="fas fa-sliders" style="color:var(--icon-primary);margin-right:6px;"></i>Sesuaikan Stok</p>
            {{-- Subjudul: nama produk dan stok saat ini (diisi via JavaScript) --}}
            <p class="modal-sub" id="modal-product-info">—</p>

            {{-- Form penyesuaian stok menggunakan route adjust-stock --}}
            <form id="adjustStockForm" method="POST" action="">
                @csrf                   {{-- Token CSRF untuk keamanan form --}}
                @method('PATCH')        {{-- Override method HTTP menjadi PATCH --}}

                {{-- Label dan input angka penyesuaian --}}
                <label style="font-size:0.8rem;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block;">
                    Jumlah Penyesuaian
                </label>
                <input
                    type="number"
                    name="adjustment"
                    id="stockAdjustInput"
                    class="modal-input"
                    placeholder="Contoh: 50 (tambah) atau -10 (kurangi)"
                    step="1"
                    required
                >
                {{-- Petunjuk penggunaan input --}}
                <p class="modal-hint">
                    <i class="fas fa-circle-info"></i>
                    Masukkan angka <strong>positif</strong> untuk menambah stok, atau
                    angka <strong>negatif</strong> untuk mengurangi stok.
                </p>

                {{-- Tombol aksi modal --}}
                <div class="modal-actions">
                    {{-- Tombol batal: tutup modal tanpa menyimpan --}}
                    <button type="button" class="btn-modal-cancel">
                        Batal
                    </button>
                    {{-- Tombol simpan: submit form penyesuaian stok --}}
                    <button type="submit" class="btn-modal-save">
                        <i class="fas fa-check"></i> Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>

    @else
        @include('categories._panel')
    @endif

@endsection

{{-- =====================================================
     JavaScript Halaman Produk
===================================================== --}}
@section('js')
    {{-- Script eksternal halaman produk (defer agar DOM tersedia) --}}
    <script src="{{ asset('js/products/index.js') }}" defer></script>
@endsection
