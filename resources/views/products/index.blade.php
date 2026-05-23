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
     CSS Khusus Halaman Produk
     Menggunakan CSS variables dari layout utama agar
     tampilan konsisten dengan dashboard.
===================================================== --}}
@section('css')
<style>
    /* ============================================================
       KPI CARDS — Kartu ringkasan statistik stok
    ============================================================ */
    .kpi-grid {
        /* Grid responsif: otomatis menyesuaikan kolom */
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }

    .kpi-card {
        background: var(--card-bg);         /* Warna kartu dari CSS variable */
        border-radius: var(--radius);       /* Sudut melengkung */
        padding: 22px 20px;
        box-shadow: var(--shadow-sm);       /* Bayangan halus */
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
        /* Animasi hover agar terasa interaktif */
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    /* Efek angkat saat kartu di-hover */
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    /* Garis warna di atas kartu sebagai aksen visual */
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: var(--radius) var(--radius) 0 0;
    }

    /* Warna garis berdasarkan kelas kartu */
    .kpi-card.primary::before { background: var(--primary); }
    .kpi-card.success::before { background: var(--success); }
    .kpi-card.warning::before { background: var(--warning); }
    .kpi-card.danger::before  { background: var(--danger); }

    .kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .kpi-label {
        /* Label kecil di atas kartu */
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-muted);
    }

    /* Ikon di sudut kanan atas kartu */
    .kpi-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    /* Warna ikon disesuaikan dengan tipe kartu */
    .kpi-card.primary .kpi-icon { background: rgba(79,70,229,.12); color: var(--primary); }
    .kpi-card.success .kpi-icon { background: rgba(16,185,129,.12); color: var(--success); }
    .kpi-card.warning .kpi-icon { background: rgba(245,158,11,.12); color: var(--warning); }
    .kpi-card.danger .kpi-icon  { background: rgba(239,68,68,.12);  color: var(--danger);  }

    /* Angka utama di kartu KPI */
    .kpi-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 6px;
    }

    .kpi-sub {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    /* ============================================================
       SECTION TITLE — Judul bagian dengan garis pemisah
    ============================================================ */
    .section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Garis panjang setelah teks section title */
    .section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* ============================================================
       CONTENT CARD — Kartu konten untuk tabel dan filter
    ============================================================ */
    .content-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .content-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .content-card-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .content-card-title i { color: var(--primary); }

    .content-card-body { padding: 0; }

    /* ============================================================
       TOOLBAR — Area pencarian dan tombol aksi
    ============================================================ */
    .toolbar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }

    /* Input pencarian produk */
    .toolbar-search {
        flex: 1;
        min-width: 200px;
        position: relative;
    }

    .toolbar-search input {
        width: 100%;
        padding: 8px 12px 8px 36px; /* Padding kiri untuk ikon */
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 0.82rem;
        font-family: inherit;
        color: var(--text-primary);
        background: white;
        transition: border-color 0.2s;
    }

    .toolbar-search input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79,70,229,0.08);
    }

    /* Ikon kaca pembesar di dalam input pencarian */
    .toolbar-search .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.8rem;
        pointer-events: none; /* Agar tidak mengganggu klik pada input */
    }

    /* Dropdown filter kategori */
    .toolbar select {
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 0.82rem;
        font-family: inherit;
        color: var(--text-primary);
        background: white;
        cursor: pointer;
    }

    /* ============================================================
       TOMBOL AKSI — Warna dan gaya tombol
    ============================================================ */
    .btn-add {
        /* Tombol tambah produk baru */
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 9px;
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .btn-add:hover {
        background: var(--primary-dark);
        color: white;
    }

    /* ============================================================
       TABEL DATA PRODUK
    ============================================================ */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    /* Header tabel */
    .data-table thead th {
        padding: 11px 14px;
        background: #f8fafc;
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    /* Sel data tabel */
    .data-table tbody td {
        padding: 13px 14px;
        border-bottom: 1px solid var(--border);
        color: var(--text-primary);
        font-weight: 500;
        vertical-align: middle;
    }

    /* Baris terakhir tidak punya border bawah */
    .data-table tbody tr:last-child td { border-bottom: none; }

    /* Efek hover pada baris tabel */
    .data-table tbody tr:hover { background: #f8fafc; }

    /* ============================================================
       BADGE STATUS STOK
    ============================================================ */
    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    /* Stok normal: hijau */
    .stock-ok {
        background: rgba(16,185,129,.12);
        color: var(--success);
    }

    /* Stok rendah (≤ 10): merah */
    .stock-low {
        background: rgba(239,68,68,.12);
        color: var(--danger);
    }

    /* Stok habis (0): abu */
    .stock-empty {
        background: rgba(100,116,139,.12);
        color: var(--text-muted);
    }

    /* ============================================================
       TOMBOL AKSI TABEL (Edit, Hapus, Stok)
    ============================================================ */
    .btn-action {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: 1px solid var(--border);
        background: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    /* Hover edit: biru */
    .btn-action.edit:hover  { background: var(--primary); color: white; border-color: var(--primary); }
    /* Hover hapus: merah */
    .btn-action.delete:hover{ background: var(--danger);  color: white; border-color: var(--danger);  }
    /* Hover stok: hijau */
    .btn-action.stock:hover { background: var(--success); color: white; border-color: var(--success); }

    /* ============================================================
       MODAL PENYESUAIAN STOK
       Digunakan saat admin mengklik tombol + / - di tabel
    ============================================================ */
    .modal-overlay {
        /* Latar belakang gelap semi-transparan */
        display: none; /* Tersembunyi secara default */
        position: fixed;
        inset: 0; /* Menutupi seluruh layar */
        background: rgba(15,23,42,0.55);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(3px); /* Efek blur pada latar */
    }

    /* Tampilkan modal saat memiliki kelas '.open' */
    .modal-overlay.open {
        display: flex;
        animation: fadeIn 0.2s ease; /* Animasi muncul */
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    .modal-box {
        background: white;
        border-radius: var(--radius);
        padding: 28px 28px 24px;
        width: 100%;
        max-width: 400px;
        box-shadow: var(--shadow-lg);
        animation: slideUp 0.25s ease; /* Animasi naik dari bawah */
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    .modal-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 6px;
    }

    .modal-sub {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-bottom: 20px;
    }

    /* Input penyesuaian angka (positif / negatif) */
    .modal-input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 0.9rem;
        font-family: inherit;
        color: var(--text-primary);
        margin-bottom: 16px;
        transition: border-color 0.2s;
    }

    .modal-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79,70,229,0.08);
    }

    /* Area tombol di dalam modal */
    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    /* Tombol batal di modal */
    .btn-modal-cancel {
        padding: 8px 18px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: white;
        color: var(--text-muted);
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-modal-cancel:hover { background: #f1f5f9; }

    /* Tombol simpan di modal */
    .btn-modal-save {
        padding: 8px 18px;
        border: none;
        border-radius: 8px;
        background: var(--primary);
        color: white;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-modal-save:hover { background: var(--primary-dark); }

    /* Pesan bantuan di bawah input modal */
    .modal-hint {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: -10px;
        margin-bottom: 14px;
    }

    /* ============================================================
       NAMA PRODUK — Avatar berwarna otomatis
    ============================================================ */
    .product-avatar {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: white;
        flex-shrink: 0;
        /* Gradien warna default, bisa dioverride via style= */
        background: linear-gradient(135deg, var(--primary), var(--accent));
    }

    .product-name-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-name-text {
        font-weight: 600;
        color: var(--text-primary);
    }

    .product-unit-text {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-top: 1px;
    }

    /* ============================================================
       ANIMASI FADE-UP — Untuk elemen yang muncul saat scroll
    ============================================================ */
    .fade-up {
        opacity: 0;
        transform: translateY(18px);
        transition: opacity 0.45s ease, transform 0.45s ease;
    }

    .fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* ============================================================
       PAGINATION — Gaya tombol paginasi
    ============================================================ */
    .pagination-wrapper {
        padding: 14px 20px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .pagination-info {
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    /* Override Bootstrap pagination agar sesuai desain */
    .pagination .page-link {
        border-radius: 7px !important;
        border: 1px solid var(--border);
        color: var(--text-primary);
        font-size: 0.8rem;
        padding: 5px 11px;
        margin: 0 2px;
    }

    .pagination .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    /* ============================================================
       RESPONSIF — Penyesuaian tampilan di layar kecil
    ============================================================ */
    @media (max-width: 768px) {
        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .toolbar-search { min-width: unset; }
    }

    @media (max-width: 480px) {
        .kpi-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

{{-- =====================================================
     Konten Utama Halaman
===================================================== --}}
@section('content')

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
        <i class="fas fa-box-open" style="color:var(--primary)"></i>
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
                <a href="{{ route('products.index') }}" class="btn-add" style="background:#e2e8f0;color:var(--text-primary);">
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
                                             style="background: {{ sprintf('hsl(%d, 60%%, 50%%)', crc32($product->name) % 360) }};">
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
                                            background:rgba(79,70,229,.08);
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
                                            onclick="openStockModal(
                                                {{ $product->id }},
                                                '{{ addslashes($product->name) }}',
                                                {{ $product->stock }},
                                                '{{ $product->unit }}'
                                            )">
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
                                                onclick="confirmDelete({{ $product->id }}, '{{ addslashes($product->name) }}')">
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
            <p class="modal-title"><i class="fas fa-sliders" style="color:var(--primary);margin-right:6px;"></i>Sesuaikan Stok</p>
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
                    <button type="button" class="btn-modal-cancel" onclick="closeStockModal()">
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

@endsection

{{-- =====================================================
     JavaScript Halaman Produk
===================================================== --}}
@section('js')
<script>
    // ============================================================
    // Animasi fade-up: jalankan saat halaman pertama dimuat
    // Elemen dengan kelas '.fade-up' akan muncul secara animasi
    // ============================================================
    document.addEventListener('DOMContentLoaded', function () {
        // Ambil semua elemen yang memiliki kelas fade-up
        const fadeEls = document.querySelectorAll('.fade-up');

        // Buat IntersectionObserver untuk mendeteksi elemen yang masuk viewport
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Tambahkan kelas 'visible' agar animasi berjalan
                    entry.target.classList.add('visible');
                    // Berhenti mengamati elemen ini (animasi hanya sekali)
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.05 }); // Trigger saat 5% elemen terlihat

        // Mulai amati setiap elemen fade-up dengan delay bertahap
        fadeEls.forEach((el, i) => {
            // Delay bertahap agar animasi tidak muncul bersamaan
            setTimeout(() => observer.observe(el), i * 60);
        });
    });

    // ============================================================
    // MODAL PENYESUAIAN STOK
    // Fungsi untuk membuka modal dan mengisi data produk yang dipilih
    // ============================================================

    /**
     * Membuka modal penyesuaian stok dan mengisi informasi produk.
     *
     * @param {number} productId   - ID produk yang akan disesuaikan stoknya
     * @param {string} productName - Nama produk untuk ditampilkan di modal
     * @param {number} currentStock - Stok produk saat ini
     * @param {string} unit        - Satuan produk (pcs, kg, dll)
     */
    function openStockModal(productId, productName, currentStock, unit) {
        // Tampilkan informasi produk di subjudul modal
        document.getElementById('modal-product-info').textContent =
            productName + ' — Stok saat ini: ' + currentStock + ' ' + unit;

        // Set action form ke URL adjust-stock dengan ID produk yang benar
        document.getElementById('adjustStockForm').action =
            '/products/' + productId + '/adjust-stock';

        // Kosongkan input agar pengguna tidak salah input
        document.getElementById('stockAdjustInput').value = '';

        // Tampilkan modal dengan menambahkan kelas 'open'
        document.getElementById('stockModal').classList.add('open');

        // Fokus ke input agar langsung bisa diketik
        setTimeout(() => document.getElementById('stockAdjustInput').focus(), 100);
    }

    /**
     * Menutup modal penyesuaian stok.
     * Dipanggil saat user mengklik tombol "Batal" atau klik di luar modal.
     */
    function closeStockModal() {
        // Sembunyikan modal dengan menghapus kelas 'open'
        document.getElementById('stockModal').classList.remove('open');
    }

    // Tutup modal jika user mengklik di luar kotak modal (klik overlay)
    document.getElementById('stockModal').addEventListener('click', function(e) {
        // Cek apakah yang diklik adalah overlay (bukan isi modal)
        if (e.target === this) {
            closeStockModal();
        }
    });

    // Tutup modal jika user menekan tombol Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStockModal();
        }
    });

    // ============================================================
    // KONFIRMASI HAPUS PRODUK
    // Tampilkan dialog konfirmasi sebelum menghapus produk
    // ============================================================

    /**
     * Menampilkan konfirmasi sebelum menghapus produk.
     * Jika dikonfirmasi, submit form DELETE untuk produk tersebut.
     *
     * @param {number} productId   - ID produk yang akan dihapus
     * @param {string} productName - Nama produk untuk konfirmasi
     */
    function confirmDelete(productId, productName) {
        // Tampilkan dialog konfirmasi bawaan browser
        const confirmed = confirm(
            'Apakah Anda yakin ingin menghapus produk "' + productName + '"?\n\n' +
            'Tindakan ini tidak dapat dibatalkan.'
        );

        // Jika user menekan OK, submit form hapus produk
        if (confirmed) {
            document.getElementById('delete-form-' + productId).submit();
        }
    }
</script>
@endsection
