{{-- ============================================================
     resources/views/pemasukan/show.blade.php
     Halaman detail satu transaksi pemasukan / penjualan
     Menampilkan informasi lengkap: header, item, dan total
============================================================ --}}
@extends('layouts.app')

{{-- Judul tab browser dengan nomor invoice --}}
@section('title', 'Detail Transaksi ' . $sale->invoice_number . ' - SIMKEU')

{{-- Judul halaman di topbar --}}
@section('page-title', 'Detail Transaksi')

{{-- Breadcrumb navigasi --}}
@section('breadcrumb', 'Beranda / Pemasukan / Detail')

@section('css')
<style>
    /* ============================================================
       KARTU DETAIL TRANSAKSI — Wrapper utama
    ============================================================ */
    .detail-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 20px;
    }

    /* Header kartu dengan judul dan aksi */
    .detail-card-header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .detail-card-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .detail-card-title i { color: var(--primary); }

    .detail-card-body { padding: 24px; }

    /* ============================================================
       INFO GRID — Grid dua kolom untuk info transaksi
    ============================================================ */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Dua kolom seimbang */
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 640px) {
        .info-grid { grid-template-columns: 1fr; } /* Satu kolom di HP */
    }

    /* Satu baris info: label di atas, nilai di bawah */
    .info-item {}
    .info-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    .info-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* ============================================================
       BADGE METODE PEMBAYARAN
    ============================================================ */
    .badge-metode {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .badge-tunai    { background: rgba(16,185,129,.12); color: var(--success); }
    .badge-transfer { background: rgba(79,70,229,.12);  color: var(--primary); }
    .badge-qris     { background: rgba(6,182,212,.12);  color: var(--accent);  }

    /* ============================================================
       TABEL ITEM — Daftar produk dalam transaksi ini
    ============================================================ */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.825rem;
    }

    .items-table thead th {
        padding: 10px 14px;
        background: #f8fafc;
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .items-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
        color: var(--text-primary);
        font-weight: 500;
        vertical-align: middle;
    }

    .items-table tbody tr:last-child td { border-bottom: none; }
    .items-table tbody tr:hover { background: #f8fafc; }

    /* ============================================================
       RINGKASAN TOTAL — Panel kanan bawah (subtotal, diskon, total)
    ============================================================ */
    .summary-box {
        background: #f8fafc;
        border-radius: 10px;
        padding: 20px;
        min-width: 280px; /* Lebar minimal box ringkasan */
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        font-size: 0.875rem;
        border-bottom: 1px solid var(--border);
    }
    .summary-row:last-child { border-bottom: none; }

    .summary-label { color: var(--text-muted); font-weight: 500; }
    .summary-value { font-weight: 700; color: var(--text-primary); }

    /* Baris total akhir — lebih besar dan berwarna */
    .summary-row.total-row {
        padding-top: 14px;
        margin-top: 4px;
    }
    .summary-row.total-row .summary-label {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .summary-row.total-row .summary-value {
        font-size: 1.1rem;
        color: var(--success); /* Warna hijau untuk total akhir */
    }

    /* ============================================================
       TOMBOL KEMBALI
    ============================================================ */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 16px;
        border-radius: 9px;
        font-size: 0.82rem;
        font-weight: 600;
        background: white;
        color: var(--text-primary);
        border: 1px solid var(--border);
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-back:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

</style>
@endsection

@section('content')

{{-- Tombol kembali ke daftar pemasukan --}}
<div style="margin-bottom:20px;" class="fade-up">
    <a href="{{ route('pemasukan.index', request()->query()) }}" class="btn-back">
        <i class="fas fa-arrow-left fa-xs"></i> Kembali ke Pemasukan
    </a>
</div>

{{-- ============================================================
     KARTU INFORMASI TRANSAKSI — Header dan detail transaksi
============================================================ --}}
<div class="detail-card fade-up">

    {{-- Header kartu: nomor invoice + badge status --}}
    <div class="detail-card-header">
        <div class="detail-card-title">
            <i class="fas fa-receipt"></i>
            Transaksi {{ $sale->invoice_number }}
        </div>
        {{-- Badge metode pembayaran --}}
        <span class="badge-metode badge-{{ $sale->payment_method }}">
            @if($sale->payment_method === 'tunai')
                <i class="fas fa-money-bill-wave fa-xs"></i> Tunai
            @elseif($sale->payment_method === 'transfer')
                <i class="fas fa-building-columns fa-xs"></i> Transfer Bank
            @else
                <i class="fas fa-qrcode fa-xs"></i> QRIS
            @endif
        </span>
    </div>

    <div class="detail-card-body">

        {{-- Grid informasi utama transaksi --}}
        <div class="info-grid">

            {{-- Nomor Invoice --}}
            <div class="info-item">
                <div class="info-label">Nomor Invoice</div>
                <div class="info-value" style="font-family:inherit;color:var(--primary);">
                    {{ $sale->invoice_number }}
                </div>
            </div>

            {{-- Tanggal & Waktu Transaksi --}}
            <div class="info-item">
                <div class="info-label">Tanggal Transaksi</div>
                <div class="info-value">
                    {{ $sale->transaction_date->translatedFormat('d F Y, H:i') }} WIB
                </div>
            </div>

            {{-- Nama Kasir yang memproses --}}
            <div class="info-item">
                <div class="info-label">Kasir / Diproses Oleh</div>
                <div class="info-value">{{ $sale->user?->name ?? '—' }}</div>
            </div>

            {{-- Nama Pelanggan --}}
            <div class="info-item">
                <div class="info-label">Pelanggan</div>
                <div class="info-value">
                    @if($sale->customer)
                        {{ $sale->customer->name }}
                        @if($sale->customer->phone)
                            <span style="font-size:.78rem;color:var(--text-muted);margin-left:6px;">
                                ({{ $sale->customer->phone }})
                            </span>
                        @endif
                    @else
                        <span style="color:var(--text-muted);">Ritel / Umum (Tanpa Pelanggan)</span>
                    @endif
                </div>
            </div>

            {{-- Catatan transaksi (jika ada) --}}
            @if($sale->notes)
            <div class="info-item" style="grid-column: 1 / -1;">
                {{-- span 2 kolom penuh --}}
                <div class="info-label">Catatan</div>
                <div class="info-value" style="font-weight:400;color:var(--text-muted);">
                    {{ $sale->notes }}
                </div>
            </div>
            @endif

        </div>{{-- /info-grid --}}
    </div>
</div>{{-- /detail-card informasi --}}

{{-- ============================================================
     KARTU DETAIL ITEM — Produk yang dibeli dalam transaksi ini
============================================================ --}}
<div class="detail-card fade-up">
    <div class="detail-card-header">
        <span class="detail-card-title">
            <i class="fas fa-box-open"></i> Detail Produk
        </span>
        {{-- Total jumlah item berbeda dalam transaksi ini --}}
        <span style="font-size:.78rem;color:var(--text-muted);">
            {{ $sale->saleItems->count() }} item produk
        </span>
    </div>

    {{-- Tabel item produk --}}
    <div style="overflow-x:auto;">
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>                    {{-- Nomor urut --}}
                    <th>Produk</th>               {{-- Nama produk --}}
                    <th class="text-end">Harga Satuan</th>  {{-- Harga per unit saat transaksi --}}
                    <th class="text-center">Qty</th>        {{-- Jumlah unit yang dibeli --}}
                    <th class="text-end">Subtotal</th>     {{-- qty × harga --}}
                </tr>
            </thead>
            <tbody>
                {{-- Loop setiap item dalam transaksi --}}
                @forelse($sale->saleItems as $index => $item)
                <tr>
                    {{-- Nomor urut item --}}
                    <td style="color:var(--text-muted);width:40px;">{{ $index + 1 }}</td>

                    {{-- Nama produk (atau "[Produk Dihapus]" jika relasi kosong) --}}
                    <td>
                        <div style="font-weight:600;">
                            {{ $item->product?->name ?? '[Produk Telah Dihapus]' }}
                        </div>
                        @if($item->product?->category)
                            {{-- Tampilkan nama kategori sebagai sub-teks --}}
                            <div style="font-size:.72rem;color:var(--text-muted);">
                                {{ $item->product->category->name }}
                            </div>
                        @endif
                    </td>

                    {{-- Harga satuan saat transaksi dilakukan (price_at_sale) --}}
                    <td class="text-end">Rp {{ number_format($item->price_at_sale, 0, ',', '.') }}</td>

                    {{-- Jumlah unit dengan satuan produk --}}
                    <td class="text-center">
                        {{ $item->quantity }}
                        <span style="font-size:.72rem;color:var(--text-muted);">
                            {{ $item->product?->unit ?? '' }}
                        </span>
                    </td>

                    {{-- Subtotal = qty × harga --}}
                    <td class="text-end">
                        <strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                    </td>
                </tr>
                @empty
                {{-- Tampilkan pesan jika tidak ada item (kasus edge case) --}}
                <tr>
                    <td colspan="5" class="text-center" style="padding:24px;color:var(--text-muted);">
                        <i class="fas fa-box-open" style="opacity:.3;margin-right:8px;"></i>
                        Tidak ada item produk dalam transaksi ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ---- Ringkasan Keuangan Transaksi ---- --}}
    <div style="padding:20px;display:flex;justify-content:flex-end;">
        <div class="summary-box">

            {{-- Subtotal sebelum pajak dan diskon --}}
            <div class="summary-row">
                <span class="summary-label">Subtotal</span>
                <span class="summary-value">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
            </div>

            {{-- Diskon (hanya tampil jika ada) --}}
            @if($sale->discount > 0)
            <div class="summary-row">
                <span class="summary-label">Diskon</span>
                <span class="summary-value" style="color:var(--danger);">
                    −Rp {{ number_format($sale->discount, 0, ',', '.') }}
                </span>
            </div>
            @endif

            {{-- Pajak (hanya tampil jika ada) --}}
            @if($sale->tax > 0)
            <div class="summary-row">
                <span class="summary-label">Pajak (PPN)</span>
                <span class="summary-value">+Rp {{ number_format($sale->tax, 0, ',', '.') }}</span>
            </div>
            @endif

            {{-- Total akhir yang dibayar --}}
            <div class="summary-row total-row">
                <span class="summary-label">Total Dibayar</span>
                <span class="summary-value">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
            </div>

        </div>
    </div>{{-- /ringkasan --}}

</div>{{-- /detail-card item --}}

@endsection
