{{-- ====================================================================
Halaman: Tambah Produk Baru — SIMKEU
Deskripsi: Form untuk menambahkan produk baru ke dalam sistem
manajemen stok SIMKEU.
Extends: layouts.app (layout utama SIMKEU)
==================================================================== --}}
@extends('layouts.app')

@section('title', 'Tambah Produk — SIMKEU')
@section('page-title', 'Tambah Produk')
@section('breadcrumb', 'Manajemen / Produk & Stok / Tambah')

@section('css')
    <style>
        .product-create-page { max-width: 1080px; margin: 0 auto; }
        .product-create-intro {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin: 0 auto 22px;
        }
        .product-create-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--primary);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 7px;
        }
        .product-create-title {
            margin: 0;
            color: var(--text-primary);
            font-size: clamp(1.45rem, 2.2vw, 1.9rem);
            font-weight: 800;
            letter-spacing: -.035em;
        }
        .product-create-copy { margin: 7px 0 0; color: var(--text-muted); font-size: .86rem; }
        .product-create-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 0 15px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: #fff;
            color: var(--text-primary);
            font-size: .8rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: background .2s, border-color .2s, color .2s;
        }
        .product-create-back:hover { background: var(--blue-50); border-color: var(--blue-200); color: var(--primary); }
        #main-content .product-create-eyebrow,
        #main-content .product-create-mark,
        #main-content .form-section-number,
        #main-content .product-create-tag,
        #main-content .product-create-back:hover,
        #main-content .category-shortcut a { color: var(--primary) !important; }
        #main-content .product-create-copy,
        #main-content .product-create-page .form-card-header p,
        #main-content .category-shortcut,
        #main-content .field-help,
        #main-content .image-hint,
        #main-content .image-preview-box .upload-meta { color: var(--text-muted) !important; }

        .product-create-page .form-card {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: #fff;
            box-shadow: 0 10px 30px rgba(25, 43, 82, .06);
        }
        #main-content .product-create-page .form-card { box-shadow: 0 10px 30px rgba(25, 43, 82, .06) !important; }
        .product-create-page .form-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 22px 28px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(110deg, #fff 0%, #f7fbff 100%);
        }
        .product-create-mark {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            border-radius: 6px;
            background: var(--blue-100);
            color: var(--primary);
            font-size: 1.05rem;
        }
        .product-create-page .form-card-header h2 { margin: 0; color: var(--text-primary); font-size: 1rem; font-weight: 700; }
        .product-create-page .form-card-header p { margin: 4px 0 0; color: var(--text-muted); font-size: .78rem; }
        .product-create-tag {
            margin-left: auto;
            padding: 6px 10px;
            border: 1px solid var(--blue-100);
            border-radius: 5px;
            background: #fff;
            color: var(--blue-800);
            font-size: .7rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .product-create-page .form-card-body { padding: 28px; }
        .product-create-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 22px; }
        .product-create-form > .form-section-heading,
        .product-create-form > .form-row,
        .product-create-form > .form-group.full-width,
        .product-create-form > .form-actions { grid-column: 1 / -1; }
        .form-section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 5px 0 16px;
            color: var(--text-primary);
            font-size: .86rem;
            font-weight: 700;
        }
        .form-section-heading:not(:first-child) { padding-top: 20px; border-top: 1px solid var(--border); }
        .form-section-number {
            display: grid;
            place-items: center;
            width: 25px;
            height: 25px;
            border-radius: 5px;
            background: var(--blue-50);
            color: var(--primary);
            font-size: .7rem;
            font-weight: 800;
        }
        .product-create-form .form-group { min-width: 0; margin-bottom: 18px; }
        .product-create-form .form-label {
            display: block;
            margin-bottom: 7px;
            color: var(--text-primary);
            font-size: .77rem;
            font-weight: 600;
        }
        .required-mark { color: var(--danger); margin-left: 3px; }
        .product-create-form .form-input,
        .product-create-form .form-select {
            width: 100%;
            min-height: 44px;
            padding: 10px 13px;
            border: 1px solid #d8e1ee;
            border-radius: 5px;
            background: #fff;
            color: var(--text-primary);
            font: inherit;
            font-size: .85rem;
            transition: border-color .18s, box-shadow .18s;
        }
        .product-create-form .form-input::placeholder { color: #93a1b5; }
        .product-create-form .form-input:focus,
        .product-create-form .form-select:focus { outline: 0; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .12); }
        #main-content .product-create-form .form-input:focus,
        #main-content .product-create-form .form-select:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .12) !important;
        }
        .product-create-form .form-input.is-invalid,
        .product-create-form .form-select.is-invalid { border-color: var(--danger); }
        #main-content .product-create-form .form-input.is-invalid,
        #main-content .product-create-form .form-select.is-invalid { border-color: var(--danger) !important; }
        .field-error { display: flex; align-items: flex-start; gap: 6px; margin: 6px 0 0; color: var(--danger); font-size: .75rem; }
        .form-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .field-help { display: flex; align-items: flex-start; gap: 6px; margin: 6px 0 0; color: var(--text-muted); font-size: .72rem; line-height: 1.5; }
        .category-shortcut { margin: 7px 0 0; color: var(--text-muted); font-size: .74rem; }
        .category-shortcut a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .category-shortcut a:hover { text-decoration: underline; }

        .image-preview-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 168px;
            padding: 18px;
            overflow: hidden;
            border: 1px dashed #a9bedc;
            border-radius: 6px;
            background: #f8fbff;
            color: var(--text-muted);
            font-size: .8rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .image-preview-box:hover,
        .image-preview-box:focus-visible { border-color: var(--primary); background: var(--blue-50); outline: none; }
        #main-content .image-preview-box:hover,
        #main-content .image-preview-box:focus-visible { border-color: var(--primary) !important; }
        .image-preview-box > i { color: var(--primary); font-size: 1.5rem !important; opacity: .8 !important; }
        .image-preview-box .upload-title { color: var(--text-primary); font-weight: 600; }
        .image-preview-box .upload-meta { color: var(--text-muted); font-size: .7rem; }
        .image-preview-box img { width: 100%; height: 190px; object-fit: contain; border-radius: 4px; }
        .image-hint { margin: 7px 0 0; color: var(--text-muted); font-size: .72rem; }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 8px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        .btn-submit,
        .product-create-page .btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 0 18px;
            border: 1px solid transparent;
            border-radius: 5px;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background .2s, border-color .2s, color .2s, transform .2s;
        }
        .btn-submit { background: var(--primary); color: #fff; }
        .btn-submit:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .product-create-page .btn-cancel { border-color: var(--border); background: #fff; color: var(--text-muted); }
        .product-create-page .btn-cancel:hover { border-color: var(--blue-200); background: var(--blue-50); color: var(--primary); }

        @media (max-width: 760px) {
            .product-create-page .form-card-header { padding: 18px 20px; }
            .product-create-page .form-card-body { padding: 22px 20px; }
            .product-create-form { grid-template-columns: minmax(0, 1fr); }
            .product-create-form > * { grid-column: 1 / -1; }
        }
        @media (max-width: 520px) {
            .product-create-intro { align-items: flex-start; flex-direction: column; gap: 14px; margin-bottom: 16px; }
            .product-create-tag { display: none; }
            .product-create-page .form-card-header { gap: 11px; padding: 16px; }
            .product-create-mark { width: 38px; height: 38px; flex-basis: 38px; }
            .product-create-page .form-card-body { padding: 18px 16px; }
            .form-row { grid-template-columns: minmax(0, 1fr); gap: 0; }
            .form-actions { flex-direction: column-reverse; }
            .form-actions > * { width: 100%; }
        }
    </style>
@endsection

@section('content')

    {{-- ============================================================
    FORM TAMBAH PRODUK
    ============================================================ --}}
    <div class="product-create-page">

        <div class="product-create-intro">
            <div>
                <span class="product-create-eyebrow"><i class="fas fa-box-open"></i> Katalog Produk</span>
                <h1 class="product-create-title">Tambah Produk</h1>
                <p class="product-create-copy">Lengkapi informasi produk untuk mulai mengelola stok dan penjualan.</p>
            </div>
            <a href="{{ route('products.index') }}" class="product-create-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Produk
            </a>
        </div>

    <div class="form-card">

        {{-- Header kartu —— judul form --}}
        <div class="form-card-header">
            <div class="product-create-mark"><i class="fas fa-plus"></i></div>
            <div>
                <h2>Informasi Produk</h2>
                <p>Field bertanda <span class="required-mark">*</span> wajib diisi.</p>
            </div>
            <span class="product-create-tag"><i class="fas fa-shield-halved"></i> Data produk baru</span>
        </div>

        <div class="form-card-body">

            {{-- Form dikirim via POST ke route products.store --}}
            <form class="product-create-form" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                @csrf {{-- Token CSRF wajib ada untuk keamanan form --}}

                <div class="form-section-heading"><span class="form-section-number">01</span> Identitas Produk</div>

                {{-- ================================================
                FIELD: Nama Produk
                ================================================ --}}
                <div class="form-group">
                    <label for="name" class="form-label">
                        Nama Produk <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                        class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Contoh: Beras Premium 5kg" value="{{ old('name') }}" {{-- Pertahankan nilai jika
                        validasi gagal --}} required maxlength="200" autofocus>
                    {{-- Tampilkan pesan error validasi jika ada --}}
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
                        {{-- Iterasi semua kategori yang tersedia dari database --}}
                        @forelse($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @empty
                            <option disabled>Belum ada kategori — tambahkan dulu</option>
                        @endforelse
                    </select>
                    {{-- Shortcut: link ke halaman Kategori untuk menambah kategori baru --}}
                    <p class="category-shortcut">
                        <i class="fas fa-circle-info" style="color:var(--icon-accent);"></i>
                        Kategori belum ada?
                        <a href="{{ route('categories.index') }}" target="_blank">
                            Tambah kategori di sini <i class="fas fa-arrow-up-right-from-square"
                                style="font-size:.65rem;"></i>
                        </a>
                    </p>
                    @error('category_id')
                        <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- ================================================
                FIELD: Harga Beli & Harga Jual (dua kolom)
                ================================================ --}}
                <div class="form-section-heading"><span class="form-section-number">02</span> Harga dan Tarif</div>
                <div class="form-row">

                    {{-- Harga Beli: harga modal pembelian produk --}}
                    <div class="form-group">
                        <label for="buy_price" class="form-label">
                            Harga Beli (Rp) <span class="required-mark">*</span>
                        </label>
                        <input type="number" id="buy_price" name="buy_price"
                            class="form-input {{ $errors->has('buy_price') ? 'is-invalid' : '' }}" placeholder="0"
                            value="{{ old('buy_price', 0) }}" min="0" step="100" required>
                        @error('buy_price')
                            <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Harga Jual: harga yang ditawarkan kepada pembeli --}}
                    <div class="form-group">
                        <label for="sell_price" class="form-label">
                            Harga Jual (Rp) <span class="required-mark">*</span>
                        </label>
                        <input type="number" id="sell_price" name="sell_price"
                            class="form-input {{ $errors->has('sell_price') ? 'is-invalid' : '' }}" placeholder="0"
                            value="{{ old('sell_price', 0) }}" min="0" step="100" required>
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
                        <input type="number" id="tax_rate" name="tax_rate"
                            class="form-input {{ $errors->has('tax_rate') ? 'is-invalid' : '' }}"
                            placeholder="0"
                            value="{{ old('tax_rate', 0) }}"
                            min="0" max="100" step="0.01">
                        <p class="field-help">
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
                        <input type="number" id="discount_rate" name="discount_rate"
                            class="form-input {{ $errors->has('discount_rate') ? 'is-invalid' : '' }}"
                            placeholder="0"
                            value="{{ old('discount_rate', 0) }}"
                            min="0" max="100" step="0.01">
                        <p class="field-help">
                            <i class="fas fa-circle-info"></i> Diskon default produk. Contoh: 5 = diskon 5%.
                        </p>
                        @error('discount_rate')
                            <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- ================================================
                FIELD: Stok Awal & Satuan (dua kolom)
                ================================================ --}}
                <div class="form-section-heading"><span class="form-section-number">03</span> Stok dan Gambar</div>
                <div class="form-row">

                    {{-- Stok Awal: jumlah unit saat produk pertama kali ditambahkan --}}
                    <div class="form-group">
                        <label for="stock" class="form-label">
                            Stok Awal <span class="required-mark">*</span>
                        </label>
                        <input type="number" id="stock" name="stock"
                            class="form-input {{ $errors->has('stock') ? 'is-invalid' : '' }}" placeholder="0"
                            value="{{ old('stock', 0) }}" min="0" required>
                        @error('stock')
                            <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Satuan: unit ukuran produk --}}
                    <div class="form-group">
                        <label for="unit" class="form-label">
                            Satuan <span class="required-mark">*</span>
                        </label>
                        <select id="unit" name="unit" class="form-select {{ $errors->has('unit') ? 'is-invalid' : '' }}">
                            {{-- Opsi satuan yang umum digunakan --}}
                            <option value="pcs" {{ old('unit', 'pcs') == 'pcs' ? 'selected' : '' }}>pcs (unit)</option>
                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg (kilogram)</option>
                            <option value="gram" {{ old('unit') == 'gram' ? 'selected' : '' }}>gram</option>
                            <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>liter</option>
                            <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>ml (mililiter)</option>
                            <option value="dus" {{ old('unit') == 'dus' ? 'selected' : '' }}>dus (kotak)</option>
                            <option value="lusin" {{ old('unit') == 'lusin' ? 'selected' : '' }}>lusin</option>
                            <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>pack</option>
                            <option value="botol" {{ old('unit') == 'botol' ? 'selected' : '' }}>botol</option>
                            <option value="porsi" {{ old('unit') == 'porsi' ? 'selected' : '' }}>porsi</option>
                        </select>
                        @error('unit')
                            <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- ================================================
                FIELD: Gambar Produk (opsional)
                ================================================ --}}
                <div class="form-group full-width">
                    <label class="form-label" for="imageInput">Gambar Produk <span style="color:var(--text-muted);font-weight:400;">(opsional)</span></label>

                    {{-- Kotak preview gambar: klik untuk pilih file --}}
                    <div class="image-preview-box" id="imagePreviewBox" role="button" tabindex="0"
                        aria-controls="imageInput" aria-label="Pilih gambar produk"
                        onclick="document.getElementById('imageInput').click()"
                        onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();document.getElementById('imageInput').click()}">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <span class="upload-title">Klik untuk memilih gambar</span>
                        <span class="upload-meta">JPEG, PNG, WEBP · Maks. 2 MB</span>
                    </div>

                    {{-- Input file tersembunyi, diakses via klik pada preview box --}}
                    <input type="file" id="imageInput" name="image" accept="image/jpeg,image/png,image/jpg,image/webp"
                        style="display:none;" onchange="previewImage(this)">
                    <p class="image-hint"><i class="fas fa-circle-info"></i> Gambar digunakan sebagai thumbnail produk.
                    </p>
                    @error('image')
                        <p class="field-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- ================================================
                TOMBOL AKSI
                ================================================ --}}
                <div class="form-actions">
                    {{-- Tombol batal: kembali ke halaman daftar produk --}}
                    <a href="{{ route('products.index') }}" class="btn-cancel">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    {{-- Tombol simpan: submit form --}}
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-floppy-disk"></i> Simpan Produk
                    </button>
                </div>

            </form>
        </div>{{-- /.form-card-body --}}
    </div>{{-- /.form-card --}}
    </div>{{-- /.product-create-page --}}

@endsection

@section('js')
    <script>
        /**
         * Menampilkan preview gambar yang dipilih user sebelum diunggah.
         * Saat user memilih file, gambar akan langsung ditampilkan di kotak preview.
         *
         * @param {HTMLInputElement} input - Elemen input file yang berisi gambar
         */
        function previewImage(input) {
            // Pastikan ada file yang dipilih
            if (input.files && input.files[0]) {
                const reader = new FileReader();

                // Setelah file selesai dibaca, tampilkan sebagai gambar
                reader.onload = function (e) {
                    const box = document.getElementById('imagePreviewBox');

                    // Bersihkan isi kotak preview dan ganti dengan gambar
                    box.innerHTML = '<img src="' + e.target.result + '" alt="Preview Gambar Produk">';
                };

                // Mulai membaca file sebagai URL data
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
