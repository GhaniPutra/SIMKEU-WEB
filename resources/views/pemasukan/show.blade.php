@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $sale->invoice_number . ' - SIMKEU')
@section('page-title', 'Detail Transaksi')
@section('breadcrumb', 'Beranda / Pemasukan / Detail')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/finance-details.css') }}">
@endsection

@section('content')
@php
    $paymentDetails = match ($sale->payment_method) {
        'tunai' => ['label' => 'Tunai', 'icon' => 'fa-money-bill-wave'],
        'transfer' => ['label' => 'Transfer Bank', 'icon' => 'fa-building-columns'],
        'qris' => ['label' => 'QRIS', 'icon' => 'fa-qrcode'],
        default => ['label' => ucfirst($sale->payment_method), 'icon' => 'fa-wallet'],
    };
@endphp

<div class="finance-detail finance-detail--income">
    <div class="fd-toolbar">
        <a href="{{ route('pemasukan.index', request()->query()) }}" class="fd-back-link">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            <span>Kembali ke Pemasukan</span>
        </a>
    </div>

    <section class="fd-hero fd-hero--income" aria-labelledby="transaction-title">
        <div class="fd-hero-main">
            <div class="fd-eyebrow">
                <span class="fd-eyebrow-icon"><i class="fas fa-receipt" aria-hidden="true"></i></span>
                Transaksi Penjualan
            </div>
            <h1 class="fd-hero-title" id="transaction-title">{{ $sale->invoice_number }}</h1>
            <div class="fd-inline-meta">
                <span class="fd-inline-meta-item">
                    <i class="fas fa-calendar-day" aria-hidden="true"></i>
                    {{ $sale->transaction_date->translatedFormat('d F Y, H:i') }} WIB
                </span>
                <span class="fd-meta-dot" aria-hidden="true"></span>
                <span class="fd-payment-chip">
                    <i class="fas {{ $paymentDetails['icon'] }}" aria-hidden="true"></i>
                    {{ $paymentDetails['label'] }}
                </span>
            </div>
        </div>

        <div class="fd-hero-total">
            <span class="fd-total-label">Total dibayar</span>
            <strong class="fd-total-value">Rp {{ number_format($sale->total, 0, ',', '.') }}</strong>
            <span class="fd-total-caption">Nilai akhir transaksi</span>
        </div>
    </section>

    <section class="fd-card" aria-label="Ringkasan transaksi">
        <div class="fd-overview-grid">
            <div class="fd-overview-item">
                <div class="fd-overview-label">Pelanggan</div>
                <div class="fd-overview-value">
                    {{ $sale->customer?->name ?? 'Ritel / Umum' }}
                    @if($sale->customer?->phone)
                        <small>{{ $sale->customer->phone }}</small>
                    @elseif(!$sale->customer)
                        <small>Transaksi tanpa pelanggan</small>
                    @endif
                </div>
            </div>
            <div class="fd-overview-item">
                <div class="fd-overview-label">Kasir</div>
                <div class="fd-overview-value">{{ $sale->user?->name ?? '—' }}</div>
            </div>
            <div class="fd-overview-item">
                <div class="fd-overview-label">Metode pembayaran</div>
                <div class="fd-overview-value">{{ $paymentDetails['label'] }}</div>
            </div>
        </div>
    </section>

    @if($sale->notes)
        <aside class="fd-note" aria-label="Catatan transaksi">
            <span class="fd-note-icon"><i class="fas fa-note-sticky" aria-hidden="true"></i></span>
            <div>
                <span class="fd-note-label">Catatan transaksi</span>
                <p class="fd-note-text">{{ $sale->notes }}</p>
            </div>
        </aside>
    @endif

    <section class="fd-card" aria-labelledby="items-title">
        <div class="fd-card-header">
            <h2 class="fd-section-heading" id="items-title">
                <span class="fd-section-icon"><i class="fas fa-box-open" aria-hidden="true"></i></span>
                Produk dalam transaksi
            </h2>
            <span class="fd-card-caption">{{ $sale->saleItems->count() }} produk</span>
        </div>

        <div class="fd-table-wrap">
            <table class="fd-table">
                <caption class="visually-hidden">Daftar produk dalam transaksi {{ $sale->invoice_number }}</caption>
                <thead>
                    <tr>
                        <th scope="col" style="width:64px;">No.</th>
                        <th scope="col">Produk</th>
                        <th scope="col" class="fd-align-right">Harga satuan</th>
                        <th scope="col" class="fd-align-center">Jumlah</th>
                        <th scope="col" class="fd-align-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sale->saleItems as $index => $item)
                        <tr>
                            <td class="fd-item-index">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <span class="fd-product-name">{{ $item->product?->name ?? '[Produk Telah Dihapus]' }}</span>
                                @if($item->product?->category)
                                    <span class="fd-product-meta">{{ $item->product->category->name }}</span>
                                @endif
                            </td>
                            <td class="fd-align-right">Rp {{ number_format($item->price_at_sale, 0, ',', '.') }}</td>
                            <td class="fd-align-center">
                                <span class="fd-quantity">{{ $item->quantity }}</span>
                                @if($item->product?->unit)
                                    <span class="fd-product-meta">{{ $item->product->unit }}</span>
                                @endif
                            </td>
                            <td class="fd-align-right"><strong class="fd-table-total">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="fd-empty">Belum ada produk pada transaksi ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="fd-summary-area">
            <div class="fd-summary" aria-label="Rincian pembayaran">
                <div class="fd-summary-row">
                    <span>Subtotal</span>
                    <strong>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</strong>
                </div>
                @if($sale->discount > 0)
                    <div class="fd-summary-row">
                        <span>Diskon</span>
                        <strong class="fd-discount">−Rp {{ number_format($sale->discount, 0, ',', '.') }}</strong>
                    </div>
                @endif
                @if($sale->tax > 0)
                    <div class="fd-summary-row">
                        <span>Pajak (PPN)</span>
                        <strong>+Rp {{ number_format($sale->tax, 0, ',', '.') }}</strong>
                    </div>
                @endif
                <div class="fd-summary-row fd-summary-row--total">
                    <span>Total dibayar</span>
                    <strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
