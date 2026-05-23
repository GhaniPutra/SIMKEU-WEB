@extends('layouts.app')

@section('title', 'POS - Point of Sale | SIMKEU')
@section('page-title', 'Point of Sale')
@section('breadcrumb', 'Keuangan / POS')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
@endsection

@section('content')

    {{-- ================================================================
         PAGE HEADER
    ================================================================ --}}
    <div class="pos-header fade-up">
        <div class="pos-header-left">
            <div class="pos-header-icon">
                <i class="fas fa-cash-register"></i>
            </div>
            <div>
                <h1 class="pos-title">Kasir POS</h1>
                <p class="pos-subtitle">Pilih produk, atur jumlah, dan simpan transaksi dengan cepat.</p>
            </div>
        </div>
        <div class="pos-header-right">
            <a href="{{ route('pemasukan.index') }}" class="btn-qa btn-qa-outline" id="btn-lihat-penjualan">
                <i class="fas fa-file-invoice"></i>
                <span>Lihat Penjualan</span>
            </a>
        </div>
    </div>

    {{-- ================================================================
         POS FORM
    ================================================================ --}}
    <form action="{{ route('pos.store') }}" method="POST" id="posForm" class="pos-form fade-up" style="animation-delay:0.08s;">
        @csrf

        <div class="pos-grid">

            {{-- ============================================================
                 PANEL KIRI
            ============================================================ --}}
            <div class="pos-panel pos-panel-left">

                {{-- Tambah Item --}}
                <div class="content-card">
                    <div class="content-card-header">
                        <span class="content-card-title">
                            <i class="fas fa-cube"></i> Tambah Item
                        </span>
                    </div>
                    <div class="content-card-body">

                        <div class="form-group">
                            <label class="form-label" for="productSelect">Pilih Produk</label>
                            <div class="select-wrapper">
                                <select id="productSelect" class="form-control" data-products='@json($products)'>
                                    <option value="">— Pilih produk —</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                                data-name="{{ $product->name }}"
                                                data-price="{{ $product->sell_price }}"
                                                data-stock="{{ $product->stock }}"
                                                data-unit="{{ $product->unit }}"
                                                data-tax="{{ $product->tax_rate ?? 0 }}"
                                                data-discount="{{ $product->discount_rate ?? 0 }}">
                                            {{ $product->name }} — Rp {{ number_format($product->sell_price, 0, ',', '.') }} / {{ $product->unit }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down select-arrow"></i>
                            </div>
                        </div>

                        {{-- Info produk yang dipilih --}}
                        <div class="pos-product-info" id="productInfo" style="display:none;">
                            <div class="product-info-row">
                                <div class="product-info-item">
                                    <span class="info-label">Harga Satuan</span>
                                    <span class="info-value price-value" id="itemPrice">Rp 0</span>
                                </div>
                                <div class="product-info-item">
                                    <span class="info-label">Stok Tersedia</span>
                                    <span class="info-value" id="itemStock">—</span>
                                </div>
                            </div>
                            <div class="pos-product-badges" id="productBadges">
                                <span class="badge-tax" id="badgeTax">
                                    <i class="fas fa-receipt"></i> Pajak: 0%
                                </span>
                                <span class="badge-discount" id="badgeDiscount">
                                    <i class="fas fa-tag"></i> Diskon: 0%
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="itemQuantity">Jumlah</label>
                            <div class="qty-input-wrapper">
                                <button type="button" class="qty-btn" id="qtyMinus"><i class="fas fa-minus"></i></button>
                                <input id="itemQuantity" type="number" class="form-control qty-input" min="1" value="1" />
                                <button type="button" class="qty-btn" id="qtyPlus"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>

                        <button type="button" id="addItemBtn" class="btn-qa btn-qa-primary w-100" style="margin-top:4px;" id="btn-tambah-keranjang">
                            <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                        </button>

                    </div>
                </div>

                {{-- Informasi Pembeli --}}
                <div class="content-card">
                    <div class="content-card-header">
                        <span class="content-card-title">
                            <i class="fas fa-user-circle"></i> Informasi Transaksi
                        </span>
                    </div>
                    <div class="content-card-body">

                        <div class="form-group">
                            <label class="form-label" for="customerSelect">Pelanggan</label>
                            <div class="select-wrapper">
                                <select id="customerSelect" name="customer_id" class="form-control">
                                    <option value="">Umum / Tanpa pelanggan</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down select-arrow"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="paymentMethod">Metode Pembayaran</label>
                            <div class="payment-method-group" id="paymentMethodGroup">
                                <label class="payment-option active" for="pay-tunai">
                                    <input type="radio" id="pay-tunai" name="payment_method" value="tunai" checked hidden>
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Tunai</span>
                                </label>
                                <label class="payment-option" for="pay-transfer">
                                    <input type="radio" id="pay-transfer" name="payment_method" value="transfer" hidden>
                                    <i class="fas fa-university"></i>
                                    <span>Transfer</span>
                                </label>
                                <label class="payment-option" for="pay-qris">
                                    <input type="radio" id="pay-qris" name="payment_method" value="qris" hidden>
                                    <i class="fas fa-qrcode"></i>
                                    <span>QRIS</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="notes">Catatan</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3"
                                      placeholder="Catatan tambahan transaksi…"></textarea>
                        </div>

                    </div>
                </div>

            </div>{{-- end panel kiri --}}

            {{-- ============================================================
                 PANEL KANAN — Keranjang
            ============================================================ --}}
            <div class="pos-panel pos-panel-right">
                <div class="content-card panel-cart" style="position:sticky;top:calc(var(--topbar-h) + 20px);">
                    <div class="content-card-header">
                        <span class="content-card-title">
                            <i class="fas fa-shopping-cart"></i> Keranjang
                        </span>
                        <span class="cart-count-badge" id="cartCount">0 item</span>
                    </div>

                    {{-- Tabel keranjang --}}
                    <div class="cart-table-wrapper">
                        <table class="pos-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cartBody">
                                <tr class="empty-row" id="emptyRow">
                                    <td colspan="4">
                                        <div class="cart-empty-state">
                                            <i class="fas fa-cart-shopping"></i>
                                            <p>Keranjang masih kosong</p>
                                            <small>Tambahkan produk dari panel kiri</small>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="cart-footer" id="cartFooter">

                        {{-- Diskon tambahan --}}
                        <div class="extra-discount-section">
                            <label for="extraDiscount">
                                <i class="fas fa-percent"></i> Diskon Tambahan (Rp)
                            </label>
                            <input type="number" id="extraDiscount" name="extra_discount"
                                   class="form-control" min="0" value="0" step="100" placeholder="0">
                            <p class="extra-discount-hint">
                                <i class="fas fa-circle-info"></i> Diskon manual kasir, diterapkan ke total.
                            </p>
                        </div>

                        {{-- Ringkasan --}}
                        <div class="cart-summary">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <strong id="cartSubtotal">Rp 0</strong>
                            </div>
                            <div class="summary-row summary-discount">
                                <span><i class="fas fa-tag"></i> Diskon Produk</span>
                                <strong id="cartDiscountProduct">− Rp 0</strong>
                            </div>
                            <div class="summary-row summary-tax">
                                <span><i class="fas fa-receipt"></i> Pajak</span>
                                <strong id="cartTax">+ Rp 0</strong>
                            </div>
                            <div class="summary-row summary-discount">
                                <span><i class="fas fa-percent"></i> Diskon Tambahan</span>
                                <strong id="cartDiscountExtra">− Rp 0</strong>
                            </div>
                            <div class="summary-row cart-summary-final">
                                <span>Total Bayar</span>
                                <strong id="cartGrandTotal">Rp 0</strong>
                            </div>
                        </div>

                        <div id="cartInputs"></div>

                        <button type="submit" class="btn-qa btn-qa-success btn-submit" id="btn-simpan-transaksi">
                            <i class="fas fa-check-circle"></i> Simpan Transaksi
                        </button>

                    </div>{{-- end cart-footer --}}
                </div>
            </div>{{-- end panel kanan --}}

        </div>{{-- end pos-grid --}}
    </form>

@endsection

@section('js')
    <script src="{{ asset('js/pos.js') }}"></script>
    <script>
        // Payment method toggle visual
        document.querySelectorAll('.payment-option').forEach(label => {
            label.addEventListener('click', function () {
                document.querySelectorAll('.payment-option').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Qty buttons
        document.getElementById('qtyMinus').addEventListener('click', function () {
            const input = document.getElementById('itemQuantity');
            if (parseInt(input.value) > 1) input.value = parseInt(input.value) - 1;
        });
        document.getElementById('qtyPlus').addEventListener('click', function () {
            const input = document.getElementById('itemQuantity');
            input.value = parseInt(input.value) + 1;
        });

        // Show product info panel on select
        document.getElementById('productSelect').addEventListener('change', function () {
            const info = document.getElementById('productInfo');
            info.style.display = this.value ? 'block' : 'none';
        });
    </script>
@endsection
