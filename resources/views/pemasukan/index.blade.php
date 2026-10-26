{{-- ============================================================
     resources/views/pemasukan/index.blade.php
     Halaman daftar pemasukan / penjualan SIMKEU
     Mewarisi layout utama dari layouts/app.blade.php
============================================================ --}}
@extends('layouts.app')

{{-- Judul tab browser --}}
@section('title', 'Pemasukan - SIMKEU')

{{-- Judul halaman di topbar --}}
@section('page-title', 'Pemasukan')

{{-- Breadcrumb di bawah judul topbar --}}
@section('breadcrumb', 'Beranda / Pemasukan')

{{-- ============================================================
     SECTION CSS — Style khusus halaman pemasukan
============================================================ --}}
@section('css')
<style>
    /* ============================================================
       KPI CARDS — Kartu statistik ringkasan pemasukan
    ============================================================ */
    .kpi-grid {
        display: grid;
        /* Tetapkan 4 kolom tetap untuk halaman Pemasukan */
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;          /* Jarak antar kartu */
        margin-bottom: 28px; /* Margin bawah grid */
    }

    .kpi-card {
        background: var(--card-bg);          /* Warna latar kartu */
        border-radius: var(--radius);        /* Sudut melengkung */
        padding: 22px 20px;                  /* Jarak dalam kartu */
        box-shadow: var(--shadow-sm);        /* Bayangan halus */
        border: 1px solid var(--border);    /* Garis tepi tipis */
        position: relative;
        overflow: hidden;
        /* Animasi saat hover */
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    /* Efek hover: naik sedikit dan bayangan lebih gelap */
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    /* Baris atas kartu: label dan ikon */
    .kpi-top {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }

    .kpi-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-muted); /* Warna abu-abu */
    }

    /* Ikon KPI diletakkan di samping kiri label */
    .kpi-icon {
        flex: 0 0 30px;
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        background: rgba(var(--primary-rgb),.1);
        color: var(--icon-primary);
    }

    /* Nilai utama kartu (angka besar) */
    .kpi-value {
        font-size: clamp(1.5rem, 1.8vw, 1.9rem);
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 6px;
        overflow-wrap: anywhere;
    }

    /* Sub-teks di bawah nilai utama */
    .kpi-sub {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    /* Badge tren naik/turun */
    .kpi-trend {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 6px;
        margin-left: 4px;
    }
    .trend-up      { background: var(--green-50); color: var(--green-400); }
    .trend-down    { background: var(--red-50); color: var(--red-400); }
    .trend-neutral { background: var(--grey-100); color: var(--grey-400); }

    .financial-data-note {
        margin: -10px 0 24px;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--surface-light);
        color: var(--text-muted);
        font-size: .78rem;
    }

    /* ============================================================
       SECTION TITLE — Judul antar seksi dengan garis
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
    /* Garis horizontal setelah teks judul seksi */
    .section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* ============================================================
       CONTENT CARD — Kartu konten (chart, tabel, filter)
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
    .content-card-title i { color: var(--icon-primary); }

    .content-card-body { padding: 20px; }

    /* Baris analitik memakai tinggi lapang dan tetap; legenda tidak mengubah ruang chart. */
    .analytics-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
        height: 360px;
        min-height: 360px;
        max-height: 360px;
        overflow: hidden;
    }
    .payment-method-card {
        height: 360px;
        min-height: 360px;
        max-height: 360px;
    }
    .analytics-card .content-card-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }
    .analytics-card .chart-body {
        display: flex;
        align-items: stretch;
        padding: 20px;
    }
    .analytics-card .chart-body > .chart-wrapper {
        flex: 1 1 auto;
        min-height: 0;
        height: auto;
    }
    .analytics-card > .analytics-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }
    .analytics-card > .chart-legend-body {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        align-items: center;
        gap: 20px;
        overflow: hidden;
    }
    .chart-legend-body .chart-wrapper-sm {
        width: 100%;
        height: 210px;
        min-height: 0;
        flex: initial;
        margin: 0 !important;
    }
    .chart-legend-list { min-width: 0; }

    /* ============================================================
       CHART WRAPPER — Pembungkus canvas chart
    ============================================================ */
    .chart-wrapper    { position: relative; height: 240px; min-height: 240px; }
    .chart-wrapper-sm { position: relative; height: 260px; min-height: 260px; }
    .chart-wrapper canvas,
    .chart-wrapper-sm canvas { display: block; }

    /* ============================================================
       GRID DUA KOLOM (produk terlaris + metode pembayaran)
    ============================================================ */
    .two-col-chart {
        display: grid;
        grid-template-columns: minmax(0, 7fr) minmax(0, 4fr);
        align-items: stretch;
        gap: 18px;
        margin-bottom: 28px;
    }

    /* Responsif: satu kolom di layar kecil */
    @media (max-width: 1100px) {
        .two-col-chart { grid-template-columns: minmax(0, 1fr); }
        .analytics-card { height: 360px; min-height: 360px; max-height: 360px; }
        .payment-method-card { height: 360px; min-height: 360px; max-height: 360px; }
        .analytics-card > .analytics-scroll { max-height: none; }
        .analytics-card > .chart-legend-body { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
    }

    @media (max-width: 480px) {
        .analytics-card { height: 360px; min-height: 360px; max-height: 360px; }
        .payment-method-card { height: 360px; min-height: 360px; max-height: 360px; }
        .chart-wrapper { height: 220px; min-height: 220px; }
        .chart-legend-body { gap: 10px; }
        .chart-legend-body .chart-wrapper-sm { height: 175px; }
        .legend-item { gap: 6px; padding: 10px 0; font-size: .72rem; }
    }

    @media (min-width: 769px) and (max-width: 1100px) {
        .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* ============================================================
       FILTER BAR — Panel filter pencarian dan dropdown
    ============================================================ */
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap; /* Pindah baris di layar kecil */
    }

    .filter-bar .form-control,
    .filter-bar .form-select {
        font-size: 0.825rem;
        border-radius: 6px;
        border-color: var(--border);
        height: 36px;
    }

    .filter-bar .form-control:focus,
    .filter-bar .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb),.12);
    }

    /* ============================================================
       TABEL DATA — Tabel daftar transaksi pemasukan
    ============================================================ */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.825rem;
    }

    .data-table thead th {
        padding: 10px 14px;
        background: var(--surface-light);   /* Latar header tabel */
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .data-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
        color: var(--text-primary);
        font-weight: 500;
        vertical-align: middle; /* Konten vertikal tengah */
    }

    /* Hilangkan border di baris terakhir */
    .data-table tbody tr:last-child td { border-bottom: none; }

    /* Highlight saat hover baris */
    .data-table tbody tr:hover { background: var(--surface-light); }

    /* ============================================================
       BADGE — Label jenis / metode pembayaran
    ============================================================ */
    .badge-metode {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    /* Warna badge per metode */
    .badge-tunai    { background: rgba(var(--primary-rgb),.18); color: var(--primary-dark); }
    .badge-transfer { background: rgba(var(--accent-rgb),.18); color: var(--primary-dark); }
    .badge-qris     { background: rgba(var(--accent-soft-rgb),.18); color: var(--primary-dark); }

    /* ============================================================
       TOMBOL AKSI — Tombol lihat detail di tabel
    ============================================================ */
    .btn-detail {
        padding: 5px 12px;
        border-radius: 5px;
        border: 1px solid var(--border);
        background: #f0f7fe;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }
    .btn-detail:hover {
        background: var(--blue-300);
        color: var(--blue-950);
        border-color: var(--blue-300) !important;
    }

    /* ============================================================
       PAGINATION — Kontrol halaman
    ============================================================ */
    .pagination-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-top: 1px solid var(--border);
        font-size: 0.8rem;
        color: var(--text-muted);
        flex-wrap: wrap;
        gap: 10px;
    }

    .pagination-wrapper .pagination {
        margin: 0; /* Hilangkan margin default Bootstrap */
    }

    .pagination-wrapper .page-link {
        font-size: 0.78rem;
        padding: 5px 10px;
        border-radius: 4px !important;
        border-color: var(--border);
        color: var(--text-primary);
        transition: all 0.2s;
    }

    .pagination-wrapper .page-item.active .page-link {
        background: var(--blue-600);
        border-color: var(--blue-600);
        color: #f0f7fe;
    }

    .filter-bar .btn-primary {
        background-color: var(--blue-600);
        border-color: var(--blue-600);
    }
    .filter-bar .btn-primary:hover,
    .filter-bar .btn-primary:focus {
        background-color: var(--blue-700);
        border-color: var(--blue-700);
    }
    .filter-bar .btn-primary:focus {
        box-shadow: 0 0 0 .25rem rgba(44, 116, 228, .32);
    }

    .pagination-wrapper .page-link:hover {
        background: var(--border);
        color: var(--text-primary);
    }

    /* ============================================================
       EMPTY STATE — Tampilan saat tidak ada data
    ============================================================ */
    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: var(--text-muted);
    }
    .empty-state i {
        font-size: 3rem;
        opacity: 0.25;
        margin-bottom: 12px;
        display: block;
    }
    .empty-state p {
        font-size: 0.875rem;
        margin: 0;
    }

    /* ============================================================
       LEGEND METODE — Keterangan warna di bawah pie chart
    ============================================================ */
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
        font-size: 0.8rem;
        flex-wrap: wrap;
    }
    .legend-item:last-child { border-bottom: none; }
    .legend-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .legend-name  { flex: 1; color: var(--text-muted); }
    .legend-value { font-weight: 700; color: var(--text-primary); }
    .legend-percent {
        width: 100%;
        padding-left: 18px;
        margin-top: -5px;
        color: var(--text-muted);
        font-size: .72rem;
        text-align: right;
    }

    .content-card-header,
    .content-card-body { min-width: 0; }
    .content-card .table-responsive,
    .content-card [style*="overflow-x:auto"] { max-width: 100%; }

    /* ============================================================
       RESPONSIVE — Penyesuaian layar kecil
    ============================================================ */
    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .kpi-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

{{-- ============================================================
     SECTION CONTENT — Isi utama halaman pemasukan
============================================================ --}}
@section('content')

{{-- ============================================================
     KPI CARDS — Kartu ringkasan statistik pemasukan periode terpilih
============================================================ --}}
<div class="kpi-grid" data-realtime-fragment="sales-kpis" data-realtime-resource="sales">

    {{-- ---- Kartu Total Pemasukan Bersih ---- --}}
    <div class="kpi-card primary fade-up">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-wallet"></i></span>
            <span class="kpi-label">Total Pemasukan</span>
        </div>
        {{-- Tampilkan total bersih dengan format rupiah --}}
        <div class="kpi-value">Rp {{ number_format($totalBersih, 0, ',', '.') }}</div>
        <div class="kpi-sub">
            @if($totalPeriodLalu !== null)
                vs Periode Lalu: <strong>Rp {{ number_format($totalPeriodLalu, 0, ',', '.') }}</strong>
                <span class="kpi-trend {{ $persentasePerubahan > 0 ? 'trend-up' : ($persentasePerubahan < 0 ? 'trend-down' : 'trend-neutral') }}">
                    <i class="fas fa-{{ $persentasePerubahan > 0 ? 'caret-up' : ($persentasePerubahan < 0 ? 'caret-down' : 'minus') }}"></i>
                    {{ number_format(abs($persentasePerubahan), 1, ',', '.') }}%
                </span>
            @else
                Seluruh riwayat transaksi
            @endif
        </div>
    </div>

    {{-- ---- Kartu Jumlah Transaksi ---- --}}
    <div class="kpi-card warning fade-up">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-receipt"></i></span>
            <span class="kpi-label">Transaksi</span>
        </div>
        {{-- Total jumlah transaksi bulan ini --}}
        <div class="kpi-value">{{ number_format($jumlahTransaksi) }}</div>
        <div class="kpi-sub">Rata-rata: <strong>Rp {{ number_format($rataRataTransaksi, 0, ',', '.') }}</strong></div>
    </div>

    {{-- ---- Kartu Total Diskon ---- --}}
    <div class="kpi-card danger fade-up">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-tags"></i></span>
            <span class="kpi-label">Total Diskon</span>
        </div>
        {{-- Total diskon yang diberikan bulan ini --}}
        <div class="kpi-value">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</div>
        <div class="kpi-sub">Dari subtotal Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</div>
    </div>

    {{-- ---- Kartu Total Pajak ---- --}}
    <div class="kpi-card accent fade-up">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-percent"></i></span>
            <span class="kpi-label">Total Pajak</span>
        </div>
        {{-- Total pajak yang dikumpulkan bulan ini --}}
        <div class="kpi-value">Rp {{ number_format($totalPajak, 0, ',', '.') }}</div>
        <div class="kpi-sub">PPN / Pajak penjualan</div>
    </div>

</div>{{-- /kpi-grid --}}

@if($hasIncompleteItemFinancials || $hasUnreconciledFinancials)
    <div class="financial-data-note" role="note">
        Sebagian transaksi memiliki rincian item yang tidak lengkap atau memakai definisi subtotal lama. Ringkasan memakai nilai yang tersimpan pada transaksi; riwayat tidak dihitung ulang berdasarkan tarif produk saat ini.
    </div>
@endif

{{-- ============================================================
     CHART ROW — Produk dengan nilai penjualan tertinggi + metode pembayaran
============================================================ --}}
<p class="section-title">
    <i class="fas fa-chart-area" style="color:var(--icon-primary)"></i>
    Analitik Pemasukan — {{ $periodLabel }}
</p>

<div class="two-col-chart fade-up">

    {{-- ---- Grafik Tren Harian ---- --}}
    <div class="content-card analytics-card">
        <div class="content-card-header">
            <span class="content-card-title">
                <i class="fas fa-ranking-star"></i> Produk dengan Pemasukan Tertinggi
            </span>
            {{-- Label periode yang sedang ditampilkan --}}
            <span style="font-size:.75rem;color:var(--text-muted);">
                {{ $periodLabel }}
            </span>
        </div>
        <div class="content-card-body chart-body">
            {{-- Grafik produk dengan nilai penjualan tertinggi pada periode aktif. --}}
            <div class="chart-wrapper">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ---- Pie Chart Metode Pembayaran ---- --}}
    <div class="content-card analytics-card payment-method-card">
        <div class="content-card-header">
            <span class="content-card-title">
                <i class="fas fa-credit-card"></i> Metode Pembayaran
            </span>
        </div>
        <div class="content-card-body chart-legend-body">
            {{-- Doughnut chart dengan total pemasukan di tengah --}}
            <div class="chart-wrapper-sm">
                <canvas id="metodeChart"></canvas>
            </div>
            {{-- Legend warna per metode --}}
            <div class="chart-legend-list">
                @php($totalMetode = array_sum($metodeData))
                @foreach([['Tunai', 'var(--blue-600)', $metodeData[0] ?? 0], ['Transfer', 'var(--blue-500)', $metodeData[1] ?? 0], ['QRIS', 'var(--blue-400)', $metodeData[2] ?? 0]] as [$namaMetode, $warna, $nilai])
                    <div class="legend-item">
                        <div class="legend-dot" data-bg="{{ $warna }}"></div>
                        <span class="legend-name">{{ $namaMetode }}</span>
                        <span class="legend-value">Rp {{ number_format($nilai, 0, ',', '.') }}</span>
                        <span class="legend-percent">{{ $totalMetode > 0 ? number_format(($nilai / $totalMetode) * 100, 1, ',', '.') : '0' }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>{{-- /two-col-chart --}}

{{-- ============================================================
     TABEL TRANSAKSI — Daftar pemasukan + filter pencarian
============================================================ --}}
<p class="section-title">
    <i class="fas fa-table-list" style="color:var(--icon-primary)"></i>
    Daftar Transaksi Pemasukan
</p>

<div class="content-card fade-up">

    {{-- ---- Header: Filter Pencarian & Dropdown ---- --}}
    <div class="content-card-header" style="flex-wrap:wrap; gap:12px;">
        <span class="content-card-title">
            <i class="fas fa-magnifying-glass"></i> Filter Transaksi
        </span>

        {{-- Form filter — method GET agar URL mengandung parameter filter --}}
        <form method="GET" action="{{ route('pemasukan.index') }}" class="filter-bar" id="filterForm">
            <input type="hidden" name="period" value="{{ $period }}">

            {{-- Dropdown filter metode pembayaran --}}
            <select name="metode" class="form-select" style="width:130px;"
                    onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Metode</option>
                <option value="tunai"    {{ $metode === 'tunai'    ? 'selected' : '' }}>Tunai</option>
                <option value="transfer" {{ $metode === 'transfer' ? 'selected' : '' }}>Transfer</option>
                <option value="qris"     {{ $metode === 'qris'     ? 'selected' : '' }}>QRIS</option>
            </select>

            {{-- Input pencarian nomor invoice atau catatan --}}
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Cari invoice / catatan…"
                   value="{{ $search }}"
                   style="width:200px;">

            {{-- Tombol submit filter --}}
            <button type="submit" class="btn btn-primary btn-sm" style="height:36px;border-radius:6px;font-size:.8rem;font-weight:600;">
                <i class="fas fa-magnifying-glass me-1"></i> Cari
            </button>

            {{-- Tombol reset filter (hapus parameter pencarian) --}}
            @if($search || $metode)
            <a href="{{ route('pemasukan.index', ['period' => request('period', 'month')]) }}" class="btn btn-outline-secondary btn-sm reset-button"
               style="height:36px;border-radius:6px;font-size:.8rem;font-weight:600;">
                <i class="fas fa-xmark me-1"></i> Reset
            </a>
            @endif

        </form>
    </div>{{-- /content-card-header --}}

    {{-- ---- Tabel Data Transaksi ---- --}}
    <div style="overflow-x:auto;">
        <table class="data-table" id="tabelPemasukan">
            <thead>
                <tr>
                    <th>No. Invoice</th>       {{-- Nomor unik transaksi --}}
                    <th>Tanggal</th>           {{-- Tanggal transaksi --}}
                    <th>Kasir</th>             {{-- User yang memproses transaksi --}}
                    <th>Metode</th>            {{-- Metode pembayaran --}}
                    <th class="text-end">Subtotal</th>  {{-- Total harga item sebelum diskon --}}
                    <th class="text-end">Diskon</th>    {{-- Total potongan yang diterapkan --}}
                    <th class="text-end">Pajak</th>     {{-- Pajak yang diterapkan --}}
                    <th class="text-end">Total</th>     {{-- Total akhir yang dibayar --}}
                    <th class="text-center">Aksi</th>   {{-- Tombol detail --}}
                </tr>
            </thead>
            <tbody data-realtime-fragment="sales-table" data-realtime-resource="sales">
                {{-- Loop setiap transaksi penjualan --}}
                @forelse($penjualanList as $sale)
                <tr>
                    {{-- Nomor invoice --}}
                    <td>
                        <span style="font-family:inherit;font-size:.8rem;color:var(--primary);font-weight:600;">
                            {{ $sale->invoice_number }}
                        </span>
                    </td>

                    {{-- Tanggal dan waktu transaksi --}}
                    <td>
                        <div style="font-weight:600;">{{ $sale->transaction_date->format('d M Y') }}</div>
                        <div style="font-size:.72rem;color:var(--text-muted);">{{ $sale->transaction_date->format('H:i') }}</div>
                    </td>

                    {{-- Nama kasir yang memproses --}}
                    <td>{{ $sale->user?->name ?? '—' }}</td>

                    {{-- Badge metode pembayaran dengan warna berbeda --}}
                    <td>
                        <span class="badge-metode badge-{{ $sale->payment_method }}">
                            @if($sale->payment_method === 'tunai')
                                <i class="fas fa-money-bill-wave fa-xs"></i> Tunai
                            @elseif($sale->payment_method === 'transfer')
                                <i class="fas fa-building-columns fa-xs"></i> Transfer
                            @else
                                <i class="fas fa-qrcode fa-xs"></i> QRIS
                            @endif
                        </span>
                    </td>

                    {{-- Subtotal sebelum diskon/pajak --}}
                    <td class="text-end">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>

                    {{-- Diskon; tampilkan dengan warna merah jika ada --}}
                    <td class="text-end">
                        @if($sale->discount > 0)
                            <span style="color:var(--danger);">
                                −Rp {{ number_format($sale->discount, 0, ',', '.') }}
                            </span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>

                    {{-- Pajak yang benar-benar tersimpan pada transaksi --}}
                    <td class="text-end">
                        @if($sale->tax > 0)
                            Rp {{ number_format($sale->tax, 0, ',', '.') }}
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>

                    {{-- Total akhir yang dibayar dengan gaya tebal --}}
                    <td class="text-end">
                        <strong style="color:var(--success);">
                            Rp {{ number_format($sale->total, 0, ',', '.') }}
                        </strong>
                    </td>

                    {{-- Tombol lihat detail transaksi --}}
                    <td class="text-center">
                        <a href="{{ route('pemasukan.show', array_merge(request()->query(), ['sale' => $sale->id, 'period' => $period])) }}" class="btn-detail">
                            <i class="fas fa-eye fa-xs me-1"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                {{-- Tampilan saat tidak ada data yang cocok --}}
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Tidak ada transaksi pemasukan ditemukan.</p>
                            @if($search || $metode)
                                <p style="margin-top:6px;font-size:.78rem;">Coba ubah filter pencarian.</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{-- /overflow-x:auto --}}

    {{-- ---- Pagination (Laravel Paginator) ---- --}}
    @if($penjualanList->hasPages())
    <div class="pagination-wrapper">
        {{-- Info jumlah data yang ditampilkan --}}
        <span>
            Menampilkan {{ $penjualanList->firstItem() }}–{{ $penjualanList->lastItem() }}
            dari {{ $penjualanList->total() }} transaksi
        </span>
        {{-- Render link pagination Bootstrap --}}
        {{ $penjualanList->links('pagination::bootstrap-5') }}
    </div>
    @endif

</div>{{-- /content-card tabel --}}

<div id="pemasukan-chart-data" style="display:none" data-period="{{ $period }}"
     data-product-labels="{{ json_encode($topProductLabels) }}"
     data-product-values="{{ json_encode($topProductValues) }}"
     data-product-units="{{ json_encode($topProductUnits) }}"
     data-metode-labels='{{ json_encode($metodeLabels) }}'
     data-metode-data='{{ json_encode($metodeData) }}'>
</div>

@endsection

{{-- ============================================================
     SECTION JS — Script chart.js dan animasi
============================================================ --}}
@section('js')
{{-- Load library Chart.js dari CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Jalankan setelah DOM selesai dirender
document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // STYLE DINAMIS — Terapkan warna dan lebar dari data attributes
    // ============================================================
    document.querySelectorAll('[data-bg]').forEach(el => {
        el.style.background = el.dataset.bg;
    });
    document.querySelectorAll('[data-width]').forEach(el => {
        el.style.width = el.dataset.width + '%';
    });

    // ============================================================
    // Warna seri chart mengikuti palet UI.
    // ============================================================
    const chartColors = getComputedStyle(document.documentElement);
    const productColor = chartColors.getPropertyValue('--blue-400').trim();
    const tunai = chartColors.getPropertyValue('--blue-600').trim();
    const transfer = chartColors.getPropertyValue('--blue-500').trim();
    const qris = chartColors.getPropertyValue('--blue-400').trim();

    // ============================================================
    // DATA DARI HTML — Ambil nilai JSON dari atribut data
    // ============================================================
    const pemasukanChartData = document.getElementById('pemasukan-chart-data');
    const productLabels = pemasukanChartData ? JSON.parse(pemasukanChartData.dataset.productLabels || '[]') : [];
    const productValues = pemasukanChartData ? JSON.parse(pemasukanChartData.dataset.productValues || '[]') : [];
    const productUnits  = pemasukanChartData ? JSON.parse(pemasukanChartData.dataset.productUnits || '[]') : [];
    const metodeLabels = pemasukanChartData ? JSON.parse(pemasukanChartData.dataset.metodeLabels || '[]') : [];
    const metodeData   = pemasukanChartData ? JSON.parse(pemasukanChartData.dataset.metodeData || '[]') : [];

    // ============================================================
    // OPSI DEFAULT CHART.JS
    // Dipakai bersama antara line chart dan doughnut chart
    // ============================================================
    const tooltipDefaults = {
        backgroundColor: '#192b52',          // Latar tooltip gelap
        titleFont: { family: 'Poppins', size: 12 },
        bodyFont:  { family: 'Poppins', size: 11 },
        padding: 10,
        cornerRadius: 8,
    };

    // ============================================================
    // BAR CHART — Produk dengan pemasukan tertinggi
    // ============================================================
    const topProductsCtx = document.getElementById('topProductsChart');
    if (topProductsCtx) {
        if (productValues.length === 0) {
            topProductsCtx.insertAdjacentHTML('afterend', '<p class="chart-empty-state">Belum ada detail penjualan produk pada periode ini.</p>');
            topProductsCtx.remove();
        } else {
        new Chart(topProductsCtx, {
            type: 'bar',
            data: {
                labels: productLabels,
                datasets: [{
                    label: 'Nilai Penjualan',
                    backgroundColor: productColor,
                    data: productValues,
                    borderRadius: 4,
                    maxBarThickness: 18,
                    categoryPercentage: 0.8,
                    barPercentage: 0.85,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: {
                            title: (items) => items[0].label,
                            label: (ctx) => 'Penjualan: Rp ' + ctx.parsed.x.toLocaleString('id-ID'),
                            afterLabel: (ctx) => 'Terjual: ' + (productUnits[ctx.dataIndex] ?? 0).toLocaleString('id-ID') + ' unit'
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(193,224,252,.6)' },
                        ticks: {
                            font: { size: 10, family: 'Poppins' },
                            callback: (value) => value >= 1000000
                                ? `Rp ${(value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`
                                : `Rp ${value.toLocaleString('id-ID')}`
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, family: 'Poppins' },
                            callback: (value) => {
                                const label = this.chart.data.labels[value] ?? '';
                                return label.length > 24 ? `${label.slice(0, 21)}…` : label;
                            }
                        }
                    }
                }
            }
        });
        }
    }

    // ============================================================
    // DOUGHNUT CHART — Distribusi metode pembayaran
    // ============================================================
    const metodeCtx = document.getElementById('metodeChart');
    if (metodeCtx) {
        const methodColors = [tunai, transfer, qris];
        const methods = metodeLabels.map((label, index) => ({
            label,
            value: metodeData[index] ?? 0,
            color: methodColors[index]
        }));

        new Chart(metodeCtx, {
            type: 'doughnut', // Tipe chart donut
            data: {
                labels: methods.map((method) => method.label),
                datasets: [{
                    data: methods.map((method) => method.value),
                    backgroundColor: methods.map((method) => method.color),
                    borderWidth: 2,
                    borderColor: getComputedStyle(metodeCtx.closest('.content-card')).backgroundColor,
                    hoverOffset: 4,
                }]
            },
            plugins: [{
                id: 'metodeCenterText',
                afterDraw(chart) {
                    const { ctx, chartArea } = chart;
                    if (!chartArea) return;
                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top + chartArea.bottom) / 2;
                    const total = chart.data.datasets[0].data.reduce((sum, value) => sum + Number(value || 0), 0);
                    const cardStyle = getComputedStyle(chart.canvas.closest('.content-card'));

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = cardStyle.getPropertyValue('--text-primary').trim() || '#192b52';
                    const totalLabel = `Rp ${total.toLocaleString('id-ID')}`;
                    const totalFontSize = Math.max(12, Math.min(16, chartArea.width * 0.11));
                    ctx.font = `700 ${totalFontSize}px Poppins, sans-serif`;
                    ctx.fillText(totalLabel, centerX, centerY - 8, chartArea.width * 0.82);
                    ctx.fillStyle = cardStyle.getPropertyValue('--text-muted').trim() || '#718096';
                    ctx.font = '11px Poppins, sans-serif';
                    ctx.fillText('Total Pemasukan', centerX, centerY + 11, chartArea.width * 0.82);
                    ctx.restore();
                }
            }],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%', // Ruang tengah untuk total pemasukan
                plugins: {
                    legend: { display: false }, // Legend ditampilkan manual di bawah chart
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: {
                            // Format tooltip sebagai rupiah
                            label: (ctx) => `${ctx.label}: Rp ${ctx.parsed.toLocaleString('id-ID')}`
                        }
                    }
                }
            }
        });
    }

}); // DOMContentLoaded
</script>
@endsection
