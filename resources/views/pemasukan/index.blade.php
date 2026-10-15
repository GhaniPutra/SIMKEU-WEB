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
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .kpi-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-muted); /* Warna abu-abu */
    }

    /* Kotak ikon di pojok kanan kartu */
    .kpi-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
    }

    /* Warna ikon per tema */
    .kpi-card.primary .kpi-icon { background: rgba(var(--primary-rgb),.12);  color: var(--primary); }
    .kpi-card.success .kpi-icon { background: var(--success-bg); color: var(--success); }
    .kpi-card.warning .kpi-icon { background: var(--warning-bg); color: var(--warning); }
    .kpi-card.danger  .kpi-icon { background: var(--danger-bg);  color: var(--danger);  }
    .kpi-card.accent  .kpi-icon { background: rgba(var(--accent-rgb),.12);  color: var(--accent);  }
    .kpi-card.indigo  .kpi-icon { background: rgba(var(--accent-rgb),.12); color: var(--accent); }

    /* Nilai utama kartu (angka besar) */
    .kpi-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 6px;
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
        border-radius: 20px;
        margin-left: 4px;
    }
    .trend-up   { background: var(--success-bg); color: var(--success); }
    .trend-down { background: var(--danger-bg);  color: var(--danger);  }

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
    .content-card-title i { color: var(--primary); }

    .content-card-body { padding: 20px; }

    /* ============================================================
       CHART WRAPPER — Pembungkus canvas chart
    ============================================================ */
    .chart-wrapper    { position: relative; height: 240px; }
    .chart-wrapper-sm { height: 190px; }

    /* ============================================================
       GRID DUA KOLOM (chart tren + pie chart metode)
    ============================================================ */
    .two-col-chart {
        display: grid;
        grid-template-columns: 2fr 1fr; /* Kiri lebih lebar */
        gap: 18px;
        margin-bottom: 28px;
    }

    /* Responsif: satu kolom di layar kecil */
    @media (max-width: 1100px) {
        .two-col-chart { grid-template-columns: 1fr; }
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
        border-radius: 8px;
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
        border-radius: 20px;
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
        border-radius: 7px;
        border: 1px solid var(--border);
        background: #F7F8ED;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }
    .btn-detail:hover {
        background: var(--primary);
        color: #F7F8ED;
        border-color: var(--primary);
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
        border-radius: 6px !important;
        border-color: var(--border);
        color: var(--text-primary);
        transition: all 0.2s;
    }

    .pagination-wrapper .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: #F7F8ED;
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
        padding: 8px 0;
        border-bottom: 1px solid var(--border);
        font-size: 0.8rem;
    }
    .legend-item:last-child { border-bottom: none; }
    .legend-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .legend-name  { flex: 1; color: var(--text-muted); }
    .legend-value { font-weight: 700; color: var(--text-primary); }

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
     KPI CARDS — Kartu ringkasan statistik pemasukan bulan ini
============================================================ --}}
<div class="kpi-grid">

    {{-- ---- Kartu Total Pemasukan Bersih ---- --}}
    <div class="kpi-card primary fade-up">
        <div class="kpi-top">
            <span class="kpi-label">Total Pemasukan</span>
            {{-- Ikon dompet --}}
            <div class="kpi-icon"><i class="fas fa-money-bill-trend-up"></i></div>
        </div>
        {{-- Tampilkan total bersih dengan format rupiah --}}
        <div class="kpi-value">Rp {{ number_format($totalBersih, 0, ',', '.') }}</div>
        <div class="kpi-sub">
            @if($totalPeriodLalu !== null)
                vs Periode Lalu: <strong>Rp {{ number_format($totalPeriodLalu, 0, ',', '.') }}</strong>
                <span class="kpi-trend {{ $persentasePerubahan >= 0 ? 'trend-up' : 'trend-down' }}">
                    <i class="fas fa-caret-{{ $persentasePerubahan >= 0 ? 'up' : 'down' }}"></i>
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
            <span class="kpi-label">Transaksi</span>
            <div class="kpi-icon"><i class="fas fa-receipt"></i></div>
        </div>
        {{-- Total jumlah transaksi bulan ini --}}
        <div class="kpi-value">{{ number_format($jumlahTransaksi) }}</div>
        <div class="kpi-sub">Rata-rata: <strong>Rp {{ number_format($rataRataTransaksi, 0, ',', '.') }}</strong></div>
    </div>

    {{-- ---- Kartu Total Diskon ---- --}}
    <div class="kpi-card danger fade-up">
        <div class="kpi-top">
            <span class="kpi-label">Total Diskon</span>
            <div class="kpi-icon"><i class="fas fa-tags"></i></div>
        </div>
        {{-- Total diskon yang diberikan bulan ini --}}
        <div class="kpi-value">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</div>
        <div class="kpi-sub">Dari subtotal Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</div>
    </div>

    {{-- ---- Kartu Total Pajak ---- --}}
    <div class="kpi-card accent fade-up">
        <div class="kpi-top">
            <span class="kpi-label">Total Pajak</span>
            <div class="kpi-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
        {{-- Total pajak yang dikumpulkan bulan ini --}}
        <div class="kpi-value">Rp {{ number_format($totalPajak, 0, ',', '.') }}</div>
        <div class="kpi-sub">PPN / Pajak penjualan</div>
    </div>

</div>{{-- /kpi-grid --}}

{{-- ============================================================
     CHART ROW — Grafik tren harian + pie chart metode pembayaran
============================================================ --}}
<p class="section-title">
    <i class="fas fa-chart-area" style="color:var(--primary)"></i>
    Analitik Pemasukan — {{ $periodLabel }}
</p>

<div class="two-col-chart fade-up">

    {{-- ---- Grafik Tren Harian ---- --}}
    <div class="content-card">
        <div class="content-card-header">
            <span class="content-card-title">
                <i class="fas fa-chart-line"></i> Tren Pemasukan
            </span>
            {{-- Label periode yang sedang ditampilkan --}}
            <span style="font-size:.75rem;color:var(--text-muted);">
                {{ $periodLabel }}
            </span>
        </div>
        <div class="content-card-body">
            {{-- Canvas untuk line chart — diinisialisasi di section JS --}}
            <div class="chart-wrapper">
                <canvas id="trendHarianChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ---- Pie Chart Metode Pembayaran ---- --}}
    <div class="content-card">
        <div class="content-card-header">
            <span class="content-card-title">
                <i class="fas fa-chart-pie"></i> Metode Pembayaran
            </span>
        </div>
        <div class="content-card-body">
            {{-- Canvas untuk doughnut chart --}}
            <div class="chart-wrapper-sm" style="margin-bottom:14px;">
                <canvas id="metodeChart"></canvas>
            </div>
            {{-- Legend warna per metode --}}
            <div>
                @if(array_sum($metodeData) > 0)
                    @foreach([['Tunai', '#827148', $metodeData[0]], ['Transfer', '#988956', $metodeData[1]], ['QRIS', '#aa9c68', $metodeData[2]]] as [$namaMetode, $warna, $nilai])
                        @if($nilai > 0)
                            <div class="legend-item">
                                <div class="legend-dot" data-bg="{{ $warna }}"></div>
                                <span class="legend-name">{{ $namaMetode }}</span>
                                <span class="legend-value">Rp {{ number_format($nilai, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>

</div>{{-- /two-col-chart --}}

{{-- ============================================================
     TABEL TRANSAKSI — Daftar pemasukan + filter pencarian
============================================================ --}}
<p class="section-title">
    <i class="fas fa-table-list" style="color:var(--primary)"></i>
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
            <button type="submit" class="btn btn-primary btn-sm" style="height:36px;border-radius:8px;font-size:.8rem;font-weight:600;">
                <i class="fas fa-magnifying-glass me-1"></i> Cari
            </button>

            {{-- Tombol reset filter (hapus parameter pencarian) --}}
            @if($search || $metode)
            <a href="{{ route('pemasukan.index', ['period' => request('period', 'month')]) }}" class="btn btn-outline-secondary btn-sm"
               style="height:36px;border-radius:8px;font-size:.8rem;font-weight:600;">
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
                    <th>Pelanggan</th>         {{-- Nama pelanggan (atau ritel) --}}
                    <th>Kasir</th>             {{-- User yang memproses transaksi --}}
                    <th>Metode</th>            {{-- Metode pembayaran --}}
                    <th class="text-end">Subtotal</th>  {{-- Subtotal sebelum pajak --}}
                    <th class="text-end">Diskon</th>    {{-- Potongan harga --}}
                    <th class="text-end">Total</th>     {{-- Total akhir yang dibayar --}}
                    <th class="text-center">Aksi</th>   {{-- Tombol detail --}}
                </tr>
            </thead>
            <tbody>
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

                    {{-- Nama pelanggan; tampilkan "Ritel / Umum" jika tidak ada --}}
                    <td>{{ $sale->customer?->name ?? '— Ritel / Umum' }}</td>

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
     data-chart-labels='{{ json_encode($chartLabels) }}'
     data-chart-data='{{ json_encode($chartData) }}'
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
    // PALET WARNA — Metode pembayaran mengikuti zaitun
    // ============================================================
    const tunai = '#827148';
    const transfer = '#988956';
    const qris = '#aa9c68';

    // ============================================================
    // DATA DARI HTML — Ambil nilai JSON dari atribut data
    // ============================================================
    const pemasukanChartData = document.getElementById('pemasukan-chart-data');
    const chartLabels  = pemasukanChartData ? JSON.parse(pemasukanChartData.dataset.chartLabels || '[]') : [];
    const chartData    = pemasukanChartData ? JSON.parse(pemasukanChartData.dataset.chartData || '[]') : [];
    const metodeLabels = pemasukanChartData ? JSON.parse(pemasukanChartData.dataset.metodeLabels || '[]') : [];
    const metodeData   = pemasukanChartData ? JSON.parse(pemasukanChartData.dataset.metodeData || '[]') : [];

    // ============================================================
    // OPSI DEFAULT CHART.JS
    // Dipakai bersama antara line chart dan doughnut chart
    // ============================================================
    const tooltipDefaults = {
        backgroundColor: '#382719',          // Latar tooltip gelap
        titleFont: { family: 'Poppins', size: 12 },
        bodyFont:  { family: 'Poppins', size: 11 },
        padding: 10,
        cornerRadius: 8,
    };

    // ============================================================
    // BAR CHART — Perbandingan pemasukan per periode
    // ============================================================
    const trendCtx = document.getElementById('trendHarianChart');
    if (trendCtx) {
        if (!chartData.some((value) => value > 0)) {
            trendCtx.insertAdjacentHTML('afterend', '<p class="chart-empty-state">Belum ada pemasukan pada periode ini.</p>');
            trendCtx.remove();
        } else {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Pemasukan',
                    backgroundColor: 'rgba(99,55,25,.78)',
                    data: chartData,
                    borderRadius: 4,
                    maxBarThickness: 28,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: {
                            title: (items) => chartLabels[items[0].dataIndex],
                            label: (ctx) => 'Pemasukan: Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, family: 'Poppins' },
                            maxTicksLimit: chartData.length > 12 ? 8 : undefined,
                            autoSkip: true,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(140,122,107,.18)' },
                        ticks: {
                            font: { size: 10, family: 'Poppins' },
                            callback: (value) => value >= 1000000
                                ? `Rp ${(value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`
                                : `Rp ${value.toLocaleString('id-ID')}`
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
        })).filter((method) => method.value > 0);

        if (!methods.length) {
            metodeCtx.insertAdjacentHTML('afterend', '<p class="chart-empty-state">Belum ada transaksi pada periode ini.</p>');
            metodeCtx.remove();
        } else new Chart(metodeCtx, {
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
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%', // Ukuran lubang tengah donut
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
