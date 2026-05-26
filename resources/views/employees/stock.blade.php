@extends('layouts.app')

{{-- Halaman distribusi stok pegawai --}}
@section('title', 'Atur Stok Pegawai - SIMKEU')
@section('page-title', 'Distribusi Stok: ' . $employee->name)
@section('breadcrumb', 'Pegawai / Atur Stok')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employees/stock.css') }}"> {{-- Muat stylesheet stock pegawai --}}
@endsection

@section('content')
@php
    $pageMasterStock   = 0; // Total stok gudang pada halaman ini
    $pageEmployeeStock = 0; // Total stok pegawai pada halaman ini
    $lowStockCount     = 0; // Hitung produk stok rendah

    foreach ($products as $product) {
        $pageMasterStock += $product->stock; // Tambahkan stok master
        $empProd = $product->users->first(); // Ambil relasi pengguna pertama
        $pageEmployeeStock += $empProd ? $empProd->pivot->stock : 0; // Tambahkan stok pegawai jika ada
        if ($product->stock > 0 && $product->stock <= 10) $lowStockCount++; // Jika stok rendah
    }
@endphp

<div class="stock-page" data-employee-id="{{ $employee->id }}"> {{-- Root container dengan data pegawai --}}

    {{-- Header halaman stock --}}
    <div class="stock-header">
        <div>
            <div class="stock-eyebrow"><i class="fas fa-boxes-stacking"></i> Distribusi Stok Pegawai</div>
            <h1 class="stock-title">{{ $employee->name }}</h1>
            <p class="stock-subtitle">
                Kelola distribusi stok dari gudang utama ke pegawai. Berikan atau tarik stok per produk secara real-time tanpa perlu refresh halaman.
            </p>
        </div>

        <a href="{{ route('employees.index') }}" class="btn-back fade-up">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Ringkasan statistik stok --}}
    <div class="summary-grid">
        <div class="summary-card c-products">
            <div class="sc-icon"><i class="fas fa-layer-group"></i></div>
            <div class="sc-label">Total Produk</div>
            <div class="sc-value">{{ $products->total() }}</div>
            <div class="sc-note">{{ $products->count() }} ditampilkan di halaman ini</div>
        </div>

        <div class="summary-card c-master">
            <div class="sc-icon"><i class="fas fa-warehouse"></i></div>
            <div class="sc-label">Stok Gudang</div>
            <div class="sc-value">{{ $pageMasterStock }}</div>
            <div class="sc-note">Total stok master halaman ini</div>
        </div>

        <div class="summary-card c-employee">
            <div class="sc-icon"><i class="fas fa-user-check"></i></div>
            <div class="sc-label">Stok Pegawai</div>
            <div class="sc-value">{{ $pageEmployeeStock }}</div>
            <div class="sc-note">Total stok dimiliki {{ $employee->name }}</div>
        </div>

        <div class="summary-card c-lowstock">
            <div class="sc-icon"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="sc-label">Stok Rendah</div>
            <div class="sc-value">{{ $lowStockCount }}</div>
            <div class="sc-note">Produk dengan gudang ≤ 10 unit</div>
        </div>
    </div>

    {{-- Kartu tabel daftar produk --}}
    <div class="table-card">
        <div class="table-card-header">
            <div>
                <div class="table-card-title"><i class="fas fa-table-list"></i> Daftar Produk & Stok</div>
                <div class="table-card-meta">Halaman {{ $products->currentPage() }} · Menampilkan {{ $products->count() }} dari {{ $products->total() }} produk</div>
            </div>
        </div>

        <div class="table-scroll">
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Stok Gudang</th>
                        <th>Stok Pegawai</th>
                        <th>Transfer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        @php
                            $empProd  = $product->users->first(); // Ambil data relasi user produk
                            $empStock = $empProd ? $empProd->pivot->stock : 0; // Stok pegawai untuk produk ini
                            $isLow    = $product->stock > 0 && $product->stock <= 10; // Stok master rendah
                            $isEmpty  = $product->stock === 0; // Stok master habis
                            $barPct   = $product->stock > 0 ? min(100, round($empStock / ($product->stock + $empStock) * 100)) : 0; // Persentase bar
                        @endphp
                        <tr id="row-{{ $product->id }}">
                            <td>
                                <div class="product-name">{{ $product->name }}</div>
                                <div class="product-meta">{{ $product->unit }} &middot; {{ $product->category ? $product->category->name : 'Tanpa kategori' }}</div>
                            </td>
                            <td>
                                <span class="product-category-label">
                                    {{ $product->category ? $product->category->name : '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-master" id="master-stock-{{ $product->id }}">
                                    <i class="fas fa-warehouse badge-icon"></i>
                                    {{ $product->stock }}
                                </span>
                                @if($isEmpty)
                                    <span class="badge badge-zero badge-status"><i class="fas fa-xmark badge-icon"></i> Kosong</span>
                                @elseif($isLow)
                                    <span class="badge badge-low badge-status"><i class="fas fa-exclamation badge-icon"></i> Rendah</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-employee" id="emp-stock-{{ $product->id }}">
                                    <i class="fas fa-user badge-icon"></i>
                                    {{ $empStock }}
                                </span>
                                <div class="stock-bar-wrap">
                                    <div class="stock-bar">
                                        <div class="stock-bar-fill {{ $isLow ? 'low' : ($isEmpty ? 'empty' : '') }}"
                                             id="bar-{{ $product->id }}"
                                             data-fill="{{ $barPct }}"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="actions-wrap">
                                    <input type="number"
                                           id="amount-{{ $product->id }}"
                                           class="qty-input"
                                           min="1"
                                           value="1"
                                           aria-label="Jumlah transfer">
                                    <button type="button"
                                            class="btn-give btn-transfer"
                                            id="btn-give-{{ $product->id }}"
                                            data-product-id="{{ $product->id }}"
                                            data-action="push"
                                            {{ $isEmpty ? 'disabled' : '' }}>
                                        <i class="fas fa-arrow-right"></i> Berikan
                                    </button>
                                    <button type="button"
                                            class="btn-pull btn-transfer"
                                            id="btn-pull-{{ $product->id }}"
                                            data-product-id="{{ $product->id }}"
                                            data-action="pull"
                                            {{ $empStock <= 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-arrow-left"></i> Tarik
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="footer-hint">
                <i class="fas fa-circle-info"></i>
                Perubahan stok berlaku secara langsung tanpa reload halaman.
            </div>

            <div>{{ $lowStockCount }} produk stok rendah di halaman ini</div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrap">{{ $products->links() }}</div>
</div>

{{-- Toast notification --}}
<div id="stock-toast">
    <div class="toast-icon"><i class="fas fa-check" id="toast-icon-el"></i></div>
    <div class="toast-body">
        <div class="toast-title" id="toast-title">Berhasil</div>
        <div class="toast-msg" id="toast-msg">Transfer stok berhasil diproses.</div>
    </div>
</div>
@endsection

@section('js')
    <script src="{{ asset('js/employees/stock.js') }}"></script> {{-- Muat skrip stock pegawai --}}
@endsection
