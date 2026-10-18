@extends('layouts.app')

@section('title', 'Tambah Produk — SIMKEU')
@section('page-title', 'Tambah Produk')
@section('breadcrumb', 'Manajemen / Produk & Stok / Tambah')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/products/create.css') }}">
@endsection

@section('content')
<div class="product-create-page">
    <header class="pc-page-header">
        <div>
            <div class="pc-breadcrumb-label"><i class="fas fa-boxes-stacked" aria-hidden="true"></i> Katalog Produk</div>
            <h1 class="pc-page-title">Tambah produk baru</h1>
            <p class="pc-page-description">Masukkan informasi produk, harga, dan stok awal agar produk siap dikelola dan dijual.</p>
        </div>
        <a href="{{ Auth::user()->hasPermission('products.view') ? route('products.index') : route('home') }}" class="pc-button">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            Kembali ke Produk
        </a>
    </header>

    <form class="pc-form" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="pc-form-layout">
            <div class="pc-main-column">
                <section class="pc-card" aria-labelledby="pc-identity-heading">
                    <div class="pc-card-header">
                        <span class="pc-section-icon"><i class="fas fa-tag" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="pc-section-heading" id="pc-identity-heading">Identitas produk</h2>
                            <p class="pc-section-description">Nama dan kategori untuk mengenali produk di katalog.</p>
                        </div>
                    </div>
                    <div class="pc-card-body">
                        <div class="pc-field-grid pc-identity-grid">
                            <div class="pc-field">
                                <label for="name" class="pc-field-label">Nama produk <span class="pc-required">*</span></label>
                                <input type="text" id="name" name="name"
                                    class="pc-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    placeholder="Contoh: Beras Premium 5 kg"
                                    value="{{ old('name') }}" required maxlength="200" autofocus
                                    @if($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
                                @error('name')
                                    <p class="pc-field-error" id="name-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pc-field">
                                <label for="category_id" class="pc-field-label">Kategori <span class="pc-optional">Opsional</span></label>
                                <select id="category_id" name="category_id" class="pc-select {{ $errors->has('category_id') ? 'is-invalid' : '' }}"
                                    @if($errors->has('category_id')) aria-invalid="true" aria-describedby="category-error" @endif>
                                    <option value="">Tanpa kategori</option>
                                    @forelse($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @empty
                                        <option disabled>Belum ada kategori</option>
                                    @endforelse
                                </select>
                                @error('category_id')
                                    <p class="pc-field-error" id="category-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</p>
                                @enderror
                                <p class="pc-category-shortcut">
                                    Kategori belum tersedia?
                                    <a href="{{ route('categories.index') }}" target="_blank" rel="noopener">
                                        Tambah kategori <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pc-card" aria-labelledby="pc-pricing-heading">
                    <div class="pc-card-header">
                        <span class="pc-section-icon"><i class="fas fa-tags" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="pc-section-heading" id="pc-pricing-heading">Harga dan tarif</h2>
                            <p class="pc-section-description">Atur harga modal, harga jual, serta tarif tambahan bila diperlukan.</p>
                        </div>
                    </div>
                    <div class="pc-card-body">
                        <div class="pc-field-grid">
                            <div class="pc-field">
                                <label for="buy_price" class="pc-field-label">Harga beli <span class="pc-required">*</span></label>
                                <div class="pc-input-group">
                                    <input type="number" id="buy_price" name="buy_price"
                                        class="pc-input {{ $errors->has('buy_price') ? 'is-invalid' : '' }}"
                                        placeholder="0" value="{{ old('buy_price', 0) }}" min="0" step="100" required
                                        @if($errors->has('buy_price')) aria-invalid="true" aria-describedby="buy-price-error" @endif>
                                    <span class="pc-input-suffix" aria-hidden="true">Rp</span>
                                </div>
                                @error('buy_price')
                                    <p class="pc-field-error" id="buy-price-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pc-field">
                                <label for="sell_price" class="pc-field-label">Harga jual <span class="pc-required">*</span></label>
                                <div class="pc-input-group">
                                    <input type="number" id="sell_price" name="sell_price"
                                        class="pc-input {{ $errors->has('sell_price') ? 'is-invalid' : '' }}"
                                        placeholder="0" value="{{ old('sell_price', 0) }}" min="0" step="100" required
                                        @if($errors->has('sell_price')) aria-invalid="true" aria-describedby="sell-price-error" @endif>
                                    <span class="pc-input-suffix" aria-hidden="true">Rp</span>
                                </div>
                                @error('sell_price')
                                    <p class="pc-field-error" id="sell-price-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pc-field">
                                <label for="tax_rate" class="pc-field-label">Pajak <span class="pc-optional">Opsional</span></label>
                                <div class="pc-input-group">
                                    <input type="number" id="tax_rate" name="tax_rate"
                                        class="pc-input {{ $errors->has('tax_rate') ? 'is-invalid' : '' }}"
                                        placeholder="0" value="{{ old('tax_rate', 0) }}" min="0" max="100" step="0.01"
                                        @if($errors->has('tax_rate')) aria-invalid="true" aria-describedby="tax-rate-error" @endif>
                                    <span class="pc-input-suffix" aria-hidden="true">%</span>
                                </div>
                                <p class="pc-field-help"><i class="fas fa-circle-info" aria-hidden="true"></i> Tarif pajak, misalnya 10 untuk PPN 10%.</p>
                                @error('tax_rate')
                                    <p class="pc-field-error" id="tax-rate-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pc-field">
                                <label for="discount_rate" class="pc-field-label">Diskon <span class="pc-optional">Opsional</span></label>
                                <div class="pc-input-group">
                                    <input type="number" id="discount_rate" name="discount_rate"
                                        class="pc-input {{ $errors->has('discount_rate') ? 'is-invalid' : '' }}"
                                        placeholder="0" value="{{ old('discount_rate', 0) }}" min="0" max="100" step="0.01"
                                        @if($errors->has('discount_rate')) aria-invalid="true" aria-describedby="discount-rate-error" @endif>
                                    <span class="pc-input-suffix" aria-hidden="true">%</span>
                                </div>
                                <p class="pc-field-help"><i class="fas fa-circle-info" aria-hidden="true"></i> Diskon otomatis untuk produk ini.</p>
                                @error('discount_rate')
                                    <p class="pc-field-error" id="discount-rate-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="pc-side-column" aria-label="Stok dan gambar produk">
                <section class="pc-card" aria-labelledby="pc-stock-heading">
                    <div class="pc-card-header">
                        <span class="pc-section-icon"><i class="fas fa-boxes-stacked" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="pc-section-heading" id="pc-stock-heading">Stok awal</h2>
                            <p class="pc-section-description">Jumlah dan satuan inventori.</p>
                        </div>
                    </div>
                    <div class="pc-card-body">
                        <div class="pc-field-grid pc-stock-grid">
                            <div class="pc-field">
                                <label for="stock" class="pc-field-label">Jumlah <span class="pc-required">*</span></label>
                                <input type="number" id="stock" name="stock"
                                    class="pc-input {{ $errors->has('stock') ? 'is-invalid' : '' }}"
                                    placeholder="0" value="{{ old('stock', 0) }}" min="0" required
                                    @if($errors->has('stock')) aria-invalid="true" aria-describedby="stock-error" @endif>
                                @error('stock')
                                    <p class="pc-field-error" id="stock-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            <div class="pc-field">
                                <label for="unit" class="pc-field-label">Satuan <span class="pc-required">*</span></label>
                                <select id="unit" name="unit" class="pc-select {{ $errors->has('unit') ? 'is-invalid' : '' }}"
                                    required @if($errors->has('unit')) aria-invalid="true" aria-describedby="unit-error" @endif>
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
                                    <p class="pc-field-error" id="unit-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="pc-stock-note">
                            <i class="fas fa-circle-info" aria-hidden="true"></i>
                            <span>Stok awal dapat diperbarui kembali setelah produk dibuat.</span>
                        </div>
                    </div>
                </section>

                <section class="pc-card" aria-labelledby="pc-image-heading">
                    <div class="pc-card-header">
                        <span class="pc-section-icon"><i class="fas fa-image" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="pc-section-heading" id="pc-image-heading">Gambar produk</h2>
                            <p class="pc-section-description">Tambahkan foto agar mudah dikenali.</p>
                        </div>
                    </div>
                    <div class="pc-card-body">
                        <label class="pc-upload-zone" id="imagePreviewBox" for="imageInput">
                            <span class="pc-upload-content" id="imagePreviewContent">
                                <span class="pc-upload-icon"><i class="fas fa-cloud-arrow-up" aria-hidden="true"></i></span>
                                <span class="pc-upload-title">Pilih gambar produk</span>
                                <span class="pc-upload-description">JPEG, PNG, atau WEBP<br>Maksimal 2 MB</span>
                            </span>
                            <input class="pc-file-input" type="file" id="imageInput" name="image"
                                accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewImage(this)"
                                @if($errors->has('image')) aria-invalid="true" aria-describedby="image-error" @endif>
                        </label>
                        @error('image')
                            <p class="pc-field-error" id="image-error"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</p>
                        @enderror
                    </div>
                </section>
            </aside>
        </div>

        <div class="pc-form-actions">
            <a href="{{ Auth::user()->hasPermission('products.view') ? route('products.index') : route('home') }}" class="pc-action-button pc-action-button--secondary">
                Batal
            </a>
            <button type="submit" class="pc-action-button pc-action-button--primary">
                <i class="fas fa-floppy-disk" aria-hidden="true"></i>
                Simpan produk
            </button>
        </div>
    </form>
</div>
@endsection

@section('js')
    <script>
        function previewImage(input) {
            if (!input.files || !input.files[0]) return;

            const reader = new FileReader();
            reader.onload = function (event) {
                const preview = document.getElementById('imagePreviewContent');
                preview.innerHTML = '';

                const image = document.createElement('img');
                image.className = 'pc-upload-preview';
                image.src = event.target.result;
                image.alt = 'Pratinjau gambar produk';
                preview.appendChild(image);
            };
            reader.readAsDataURL(input.files[0]);
        }
    </script>
@endsection
