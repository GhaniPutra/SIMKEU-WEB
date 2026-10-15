{{-- ====================================================================
     Halaman: Edit Produk — SIMKEU
     Deskripsi: Form untuk mengedit data produk yang sudah ada.
                Semua field diisi otomatis dengan data produk saat ini.
     Extends: layouts.app (layout utama SIMKEU)
==================================================================== --}}
@extends('layouts.app')

@section('title', 'Edit Produk: ' . $product->name . ' — SIMKEU')
@section('page-title', 'Edit Produk')
@section('breadcrumb', 'Manajemen / Produk & Stok / Edit')

@section('css')
<style>
    /* ============================================================
       FORM CARD — Kartu pembungkus form
       Sama dengan halaman create, styling disalin untuk konsistensi
    ============================================================ */
    .form-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        max-width: 720px;
        margin: 0 auto;
    }

    .form-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f0f7fe;
    }

    .form-card-header i { color: var(--icon-primary); font-size: 1.1rem; }

    .form-card-header h2 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    /* Chip nama produk di header —— menampilkan produk yang sedang diedit */
    .product-chip {
        margin-left: auto;
        background: rgba(var(--primary-rgb),.08);
        color: var(--primary);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    .form-card-body { padding: 28px 24px; }

    /* ============================================================
       FIELD FORM
    ============================================================ */
    .form-group { margin-bottom: 20px; }

    .form-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 7px;
    }

    .required-mark { color: var(--danger); margin-left: 3px; }

    .form-input,
    .form-select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 0.875rem;
        font-family: inherit;
        color: var(--text-primary);
        background: #f0f7fe;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb),0.08);
    }

    /* Tampilan field yang gagal validasi */
    .form-input.is-invalid,
    .form-select.is-invalid { border-color: var(--danger); }

    /* Pesan error per field */
    .field-error {
        font-size: 0.75rem;
        color: var(--danger);
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Grid dua kolom untuk field berdampingan */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 600px) {
        .form-row { grid-template-columns: 1fr; }
    }

    /* ============================================================
       INFORMASI STOK SAAT INI — Banner info stok aktual
    ============================================================ */
    .stock-info-banner {
        background: rgba(var(--primary-rgb),.06);
        border: 1px solid rgba(var(--primary-rgb),.15);
        border-radius: 9px;
        padding: 12px 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.82rem;
    }

    .stock-info-banner i {
        color: var(--primary);
        font-size: 1rem;
    }

    .stock-info-banner strong {
        color: var(--text-primary);
        font-weight: 700;
    }

    /* ============================================================
       TOMBOL AKSI
    ============================================================ */
    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }

    .btn-submit {
        padding: 10px 24px;
        background: var(--primary);
        color: #f0f7fe;
        border: none;
        border-radius: 9px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit:hover { background: var(--primary-dark); }

    .btn-cancel {
        padding: 10px 24px;
        background: #f0f7fe;
        color: var(--text-muted);
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-cancel:hover { background: #f0f7fe; color: var(--text-primary); }

    /* ============================================================
       PREVIEW GAMBAR PRODUK
    ============================================================ */
    .image-preview-box {
        width: 100%;
        height: 140px;
        border: 2px dashed var(--border);
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: var(--text-muted);
        font-size: 0.8rem;
        cursor: pointer;
        transition: border-color 0.2s;
        margin-bottom: 8px;
        overflow: hidden;
    }

    .image-preview-box:hover { border-color: var(--primary); }

    .image-preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }

    .image-hint {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-top: 4px;
    }
</style>
@endsection

@section('content')

    {{-- ============================================================
         FORM EDIT PRODUK
    ============================================================ --}}
    <div class="form-card">

        {{-- Header: judul form + chip nama produk yang diedit --}}
        <div class="form-card-header">
            <i class="fas fa-pencil"></i>
            <h2>Edit Data Produk</h2>
            {{-- Chip menampilkan nama produk yang sedang diedit --}}
            <span class="product-chip" title="{{ $product->name }}">{{ $product->name }}</span>
        </div>

        <div class="form-card-body">

            {{-- ================================================
                 BANNER INFO STOK SAAT INI
                 Informasi stok aktual sebelum diedit
            ================================================ --}}
            <div class="stock-info-banner">
                <i class="fas fa-circle-info"></i>
                <div>
                    Stok <strong>{{ $product->name }}</strong> saat ini:
                    <strong>{{ $product->stock }} {{ $product->unit }}</strong>.
                    Untuk menyesuaikan stok secara cepat, gunakan tombol
                    <strong>Sesuaikan Stok</strong> di halaman daftar produk.
                </div>
            </div>

            {{-- Form dikirim via PUT ke route products.update dengan ID produk --}}
            <form
                method="POST"
                action="{{ route('products.update', $product->id) }}"
                enctype="multipart/form-data">
                @csrf            {{-- Token CSRF untuk keamanan form --}}
                @method('PUT')   {{-- Override method HTTP menjadi PUT untuk update --}}

                {{-- ================================================
                     FIELD: Nama Produk
                ================================================ --}}
                <div class="form-group">
                    <label for="name" class="form-label">
                        Nama Produk <span class="required-mark">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Contoh: Beras Premium 5kg"
                        {{-- old() mengembalikan input lama jika validasi gagal,
                             jika tidak gunakan nilai dari database --}}
                        value="{{ old('name', $product->name) }}"
                        required
                        maxlength="200"
                        autofocus
                    >
                    @error('name')
                        <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- ================================================
                     FIELD: Kategori Produk
                ================================================ --}}
                <div class="form-group">
                    <label for="category_id" class="form-label">Kategori</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">— Tidak Ada Kategori —</option>
                        @forelse($categories as $category)
                            <option value="{{ $category->id }}"
                                    {{-- Pilih kategori yang sesuai dengan data produk --}}
                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @empty
                            <option disabled>Belum ada kategori — tambahkan dulu</option>
                        @endforelse
                    </select>
                    {{-- Shortcut: link ke halaman Kategori untuk menambah kategori baru --}}
                    <p style="font-size:0.74rem;color:var(--text-muted);margin-top:5px;">
                        <i class="fas fa-circle-info" style="color:var(--icon-accent);"></i>
                        Kelola kategori di
                        <a href="{{ route('categories.index') }}" target="_blank"
                           style="color:var(--primary);font-weight:600;text-decoration:none;">
                            halaman Kategori <i class="fas fa-arrow-up-right-from-square" style="font-size:0.65rem;"></i>
                        </a>
                    </p>
                    @error('category_id')
                        <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- ================================================
                     FIELD: Harga Beli & Harga Jual (dua kolom)
                ================================================ --}}
                <div class="form-row">

                    {{-- Harga Beli: diisi dengan nilai dari database --}}
                    <div class="form-group">
                        <label for="buy_price" class="form-label">
                            Harga Beli (Rp) <span class="required-mark">*</span>
                        </label>
                        <input
                            type="number"
                            id="buy_price"
                            name="buy_price"
                            class="form-input {{ $errors->has('buy_price') ? 'is-invalid' : '' }}"
                            value="{{ old('buy_price', $product->buy_price) }}"
                            min="0"
                            step="100"
                            required
                        >
                        @error('buy_price')
                            <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Harga Jual: diisi dengan nilai dari database --}}
                    <div class="form-group">
                        <label for="sell_price" class="form-label">
                            Harga Jual (Rp) <span class="required-mark">*</span>
                        </label>
                        <input
                            type="number"
                            id="sell_price"
                            name="sell_price"
                            class="form-input {{ $errors->has('sell_price') ? 'is-invalid' : '' }}"
                            value="{{ old('sell_price', $product->sell_price) }}"
                            min="0"
                            step="100"
                            required
                        >
                        @error('sell_price')
                            <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- ================================================
                     FIELD: Pajak & Diskon (dua kolom)
                ================================================ --}}
                <div class="form-row">

                    {{-- Tarif Pajak: persentase pajak untuk produk ini --}}
                    <div class="form-group">
                        <label for="tax_rate" class="form-label">
                            Pajak (%) <span style="color:var(--text-muted);font-weight:400;">(opsional)</span>
                        </label>
                        <input
                            type="number"
                            id="tax_rate"
                            name="tax_rate"
                            class="form-input {{ $errors->has('tax_rate') ? 'is-invalid' : '' }}"
                            value="{{ old('tax_rate', $product->tax_rate ?? 0) }}"
                            min="0" max="100" step="0.01"
                        >
                        <p style="font-size:0.72rem;color:var(--text-muted);margin-top:4px;">
                            <i class="fas fa-circle-info"></i> Tarif pajak dalam persen. Contoh: 10 = PPN 10%.
                        </p>
                        @error('tax_rate')
                            <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tarif Diskon: persentase diskon default untuk produk ini --}}
                    <div class="form-group">
                        <label for="discount_rate" class="form-label">
                            Diskon (%) <span style="color:var(--text-muted);font-weight:400;">(opsional)</span>
                        </label>
                        <input
                            type="number"
                            id="discount_rate"
                            name="discount_rate"
                            class="form-input {{ $errors->has('discount_rate') ? 'is-invalid' : '' }}"
                            value="{{ old('discount_rate', $product->discount_rate ?? 0) }}"
                            min="0" max="100" step="0.01"
                        >
                        <p style="font-size:0.72rem;color:var(--text-muted);margin-top:4px;">
                            <i class="fas fa-circle-info"></i> Diskon default produk. Contoh: 5 = diskon 5%.
                        </p>
                        @error('discount_rate')
                            <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- ================================================
                     FIELD: Stok & Satuan (dua kolom)
                     Catatan: edit stok melalui form ini mengganti nilai
                     stok secara langsung. Untuk penyesuaian bertahap,
                     gunakan fitur Sesuaikan Stok.
                ================================================ --}}
                <div class="form-row">

                    {{-- Stok: diisi dengan nilai stok saat ini dari database --}}
                    <div class="form-group">
                        <label for="stock" class="form-label">
                            Stok <span class="required-mark">*</span>
                        </label>
                        <input
                            type="number"
                            id="stock"
                            name="stock"
                            class="form-input {{ $errors->has('stock') ? 'is-invalid' : '' }}"
                            value="{{ old('stock', $product->stock) }}"
                            min="0"
                            required
                        >
                        @error('stock')
                            <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Satuan: dropdown dengan pilihan yang sudah diisi sesuai data produk --}}
                    <div class="form-group">
                        <label for="unit" class="form-label">
                            Satuan <span class="required-mark">*</span>
                        </label>
                        <select id="unit" name="unit" class="form-select {{ $errors->has('unit') ? 'is-invalid' : '' }}">
                            {{-- Setiap opsi dicek: apakah nilainya sama dengan satuan produk saat ini? --}}
                            <option value="pcs"   {{ old('unit', $product->unit) == 'pcs'   ? 'selected' : '' }}>pcs (unit)</option>
                            <option value="kg"    {{ old('unit', $product->unit) == 'kg'    ? 'selected' : '' }}>kg (kilogram)</option>
                            <option value="gram"  {{ old('unit', $product->unit) == 'gram'  ? 'selected' : '' }}>gram</option>
                            <option value="liter" {{ old('unit', $product->unit) == 'liter' ? 'selected' : '' }}>liter</option>
                            <option value="ml"    {{ old('unit', $product->unit) == 'ml'    ? 'selected' : '' }}>ml (mililiter)</option>
                            <option value="dus"   {{ old('unit', $product->unit) == 'dus'   ? 'selected' : '' }}>dus (kotak)</option>
                            <option value="lusin" {{ old('unit', $product->unit) == 'lusin' ? 'selected' : '' }}>lusin</option>
                            <option value="pack"  {{ old('unit', $product->unit) == 'pack'  ? 'selected' : '' }}>pack</option>
                            <option value="botol" {{ old('unit', $product->unit) == 'botol' ? 'selected' : '' }}>botol</option>
                            <option value="porsi" {{ old('unit', $product->unit) == 'porsi' ? 'selected' : '' }}>porsi</option>
                        </select>
                        @error('unit')
                            <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- ================================================
                     FIELD: Gambar Produk (opsional)
                     Menampilkan gambar saat ini jika ada
                ================================================ --}}
                <div class="form-group">
                    <label class="form-label">
                        Gambar Produk <span style="color:var(--text-muted);font-weight:400;">(opsional — kosongkan jika tidak ingin diganti)</span>
                    </label>

                    {{-- Kotak preview: tampilkan gambar lama atau placeholder --}}
                    <div class="image-preview-box" id="imagePreviewBox" onclick="document.getElementById('imageInput').click()">
                        @if($product->image)
                            {{-- Tampilkan gambar produk yang sudah ada --}}
                            <img src="{{ asset('storage/' . $product->image) }}" alt="Gambar {{ $product->name }}">
                        @else
                            {{-- Placeholder jika tidak ada gambar --}}
                            <i class="fas fa-image" style="font-size:1.8rem;opacity:.35;"></i>
                            <span>Klik untuk mengganti gambar</span>
                            <span style="font-size:0.7rem;opacity:.6;">JPEG, PNG, WEBP — Maks. 2MB</span>
                        @endif
                    </div>

                    {{-- Input file tersembunyi --}}
                    <input
                        type="file"
                        id="imageInput"
                        name="image"
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        style="display:none;"
                        onchange="previewImage(this)"
                    >
                    <p class="image-hint">
                        <i class="fas fa-circle-info"></i>
                        @if($product->image)
                            Pilih file baru hanya jika ingin mengganti gambar yang ada.
                        @else
                            Pilih file gambar produk jika ingin menambahkan gambar.
                        @endif
                    </p>
                    @error('image')
                        <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- ================================================
                     TOMBOL AKSI
                ================================================ --}}
                <div class="form-actions">
                    {{-- Tombol batal: kembali ke daftar produk tanpa menyimpan --}}
                    <a href="{{ route('products.index') }}" class="btn-cancel">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    {{-- Tombol simpan: submit form update --}}
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>{{-- /.form-card-body --}}
    </div>{{-- /.form-card --}}

@endsection

@section('js')
<script>
    /**
     * Menampilkan preview gambar baru yang dipilih user.
     * Menggantikan preview gambar lama (atau placeholder) dengan gambar baru.
     *
     * @param {HTMLInputElement} input - Elemen input file yang berisi gambar baru
     */
    function previewImage(input) {
        // Pastikan ada file yang dipilih
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            // Setelah file selesai dibaca, tampilkan sebagai gambar di kotak preview
            reader.onload = function(e) {
                const box = document.getElementById('imagePreviewBox');
                // Ganti isi kotak preview dengan tag gambar baru
                box.innerHTML = '<img src="' + e.target.result + '" alt="Preview Gambar Baru">';
            };

            // Mulai membaca file sebagai URL data (base64)
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
