{{-- ============================================================
     resources/views/pengeluaran/index.blade.php
     Halaman daftar & statistik pengeluaran operasional SIMKEU
     Mewarisi layout utama dari layouts/app.blade.php
============================================================ --}}
@extends('layouts.app')

{{-- Judul tab browser --}}
@section('title', 'Pengeluaran - SIMKEU')

{{-- Judul halaman di topbar --}}
@section('page-title', 'Pengeluaran')

{{-- Breadcrumb navigasi --}}
@section('breadcrumb', 'Beranda / Pengeluaran')

{{-- ============================================================
     SECTION CSS — Style khusus halaman pengeluaran
============================================================ --}}
@section('css')
<style>
    /* ============================================================
       KPI CARDS — Kartu ringkasan statistik
    ============================================================ */
    .kpi-grid {
        display: grid;
        /* Tetapkan 4 kolom tetap untuk halaman Pengeluaran */
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 28px;
    }

    .kpi-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        padding: 22px 20px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease; /* Animasi hover */
    }

    /* Efek hover: terangkat ke atas */
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    /* Garis warna di bagian atas kartu (accent bar) */
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: var(--radius) var(--radius) 0 0;
    }

    /* Warna accent bar per tema */
    .kpi-card.primary::before { background: var(--primary); }
    .kpi-card.danger::before  { background: var(--danger);  }
    .kpi-card.warning::before { background: var(--warning); }
    .kpi-card.accent::before  { background: var(--accent);  }

    /* Bagian atas kartu: label + ikon di kanan */
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
        color: var(--text-muted);
    }

    /* Kotak ikon per kartu */
    .kpi-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    /* Warna ikon sesuai tema */
    .kpi-card.primary .kpi-icon { background: rgba(79,70,229,.12);  color: var(--primary); }
    .kpi-card.danger  .kpi-icon { background: rgba(239,68,68,.12);  color: var(--danger);  }
    .kpi-card.warning .kpi-icon { background: rgba(245,158,11,.12); color: var(--warning); }
    .kpi-card.accent  .kpi-icon { background: rgba(6,182,212,.12);  color: var(--accent);  }

    /* Nilai utama kartu (angka besar) */
    .kpi-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 6px;
    }

    /* Sub-teks informatif di bawah nilai */
    .kpi-sub {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    /* Badge tren naik / turun */
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
    /* Pengeluaran naik = merah (buruk), turun = hijau (baik) */
    .trend-naik  { background: rgba(239,68,68,.12);  color: var(--danger);  }
    .trend-turun { background: rgba(16,185,129,.12); color: var(--success); }

    /* ============================================================
       SECTION TITLE — Judul seksi dengan garis horizontal
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
    /* Garis setelah teks judul */
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
        flex-wrap: wrap;
    }

    .content-card-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .content-card-title i { color: var(--danger); } /* Ikon merah = pengeluaran */

    .content-card-body { padding: 20px; }

    /* ============================================================
       WRAPPER CANVAS CHART
    ============================================================ */
    .chart-wrapper    { position: relative; height: 230px; }
    .chart-wrapper-sm { height: 195px; }

    /* ============================================================
       GRID DUA KOLOM (chart tren + chart kategori)
    ============================================================ */
    .two-col-chart {
        display: grid;
        grid-template-columns: 3fr 2fr; /* Tren lebih lebar dari kategori */
        gap: 18px;
        margin-bottom: 28px;
    }

    /* Satu kolom di layar kecil */
    @media (max-width: 1100px) {
        .two-col-chart { grid-template-columns: 1fr; }
    }

    /* ============================================================
       BREAKDOWN KATEGORI — Panel ringkasan per kategori
    ============================================================ */
    .kategori-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
    }
    .kategori-item:last-child { border-bottom: none; }

    /* Ikon kategori dalam kotak berwarna */
    .kategori-icon {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
        color: white;
    }

    .kategori-info  { flex: 1; min-width: 0; }
    .kategori-name  { font-size: 0.825rem; font-weight: 600; color: var(--text-primary); }
    .kategori-count { font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; }

    .kategori-amount  { font-size: 0.85rem; font-weight: 700; color: var(--text-primary); }

    /* Progress bar persentase kategori */
    .kategori-bar {
        height: 3px;
        background: var(--border);
        border-radius: 3px;
        margin-top: 5px;
        overflow: hidden;
    }
    .kategori-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.6s ease;
    }

    /* ============================================================
       FILTER BAR — Panel filter pencarian
    ============================================================ */
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
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
        border-color: var(--danger); /* Fokus merah sesuai tema pengeluaran */
        box-shadow: 0 0 0 3px rgba(239,68,68,.1);
    }

    /* ============================================================
       TABEL DATA PENGELUARAN
    ============================================================ */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.825rem;
    }

    .data-table thead th {
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

    .data-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
        color: var(--text-primary);
        font-weight: 500;
        vertical-align: middle;
    }

    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover { background: #f8fafc; }

    /* ============================================================
       BADGE KATEGORI — Label warna per jenis pengeluaran
    ============================================================ */
    .badge-kategori {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
    }

    /* ============================================================
       TOMBOL AKSI — Lihat detail
    ============================================================ */
    .btn-detail {
        padding: 5px 12px;
        border-radius: 7px;
        border: 1px solid var(--border);
        background: white;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }
    .btn-detail:hover {
        background: var(--danger); /* Hover merah untuk halaman pengeluaran */
        color: white;
        border-color: var(--danger);
    }

    .btn-qa {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 14px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-qa-primary {
        background: var(--danger);
        color: white;
        border: 1px solid transparent;
    }

    .btn-qa-primary:hover {
        opacity: 0.95;
    }

    /* ============================================================
       PAGINATION
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
    .pagination-wrapper .pagination { margin: 0; }
    .pagination-wrapper .page-link {
        font-size: 0.78rem;
        padding: 5px 10px;
        border-radius: 6px !important;
        border-color: var(--border);
        color: var(--text-primary);
        transition: all 0.2s;
    }
    .pagination-wrapper .page-item.active .page-link {
        background: var(--danger); /* Paginasi aktif merah */
        border-color: var(--danger);
        color: white;
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
    .empty-state p { font-size: 0.875rem; margin: 0; }

    /* ============================================================
       RESPONSIVE — Penyesuaian HP
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
     SECTION CONTENT — Isi utama halaman pengeluaran
============================================================ --}}
@section('content')

{{-- ============================================================
     KPI CARDS — Ringkasan statistik pengeluaran bulan ini
============================================================ --}}
<div class="kpi-grid">

    {{-- ---- Kartu Total Pengeluaran ---- --}}
    <div class="kpi-card danger fade-up">
        <div class="kpi-top">
            <span class="kpi-label">Total Pengeluaran</span>
            <div class="kpi-icon"><i class="fas fa-arrow-trend-down"></i></div>
        </div>
        {{-- Total nominal pengeluaran bulan ini --}}
        <div class="kpi-value">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</div>
        <div class="kpi-sub">
            @if($totalPeriodLalu !== null)
                vs Periode Lalu: <strong>Rp {{ number_format($totalPeriodLalu, 0, ',', '.') }}</strong>
                <span class="kpi-trend {{ $persentasePerubahan >= 0 ? 'trend-naik' : 'trend-turun' }}">
                    <i class="fas fa-caret-{{ $persentasePerubahan >= 0 ? 'up' : 'down' }}"></i>
                    {{ number_format(abs($persentasePerubahan), 1, ',', '.') }}%
                </span>
            @else
                Seluruh riwayat transaksi
            @endif
        </div>
    </div>

    {{-- ---- Kartu Jumlah Entri ---- --}}
    <div class="kpi-card warning fade-up">
        <div class="kpi-top">
            <span class="kpi-label">Jumlah Entri</span>
            <div class="kpi-icon"><i class="fas fa-list-check"></i></div>
        </div>
        {{-- Total jumlah baris pengeluaran bulan ini --}}
        <div class="kpi-value">{{ number_format($jumlahEntri) }}</div>
        <div class="kpi-sub">Tercatat bulan ini</div>
    </div>

    {{-- ---- Kartu Rata-rata per Entri ---- --}}
    <div class="kpi-card primary fade-up">
        <div class="kpi-top">
            <span class="kpi-label">Rata-rata / Entri</span>
            <div class="kpi-icon"><i class="fas fa-calculator"></i></div>
        </div>
        {{-- Rata-rata nilai setiap entri pengeluaran --}}
        <div class="kpi-value">Rp {{ number_format($rataRata, 0, ',', '.') }}</div>
        <div class="kpi-sub">Nilai rata-rata per transaksi</div>
    </div>

    {{-- ---- Kartu Pengeluaran Terbesar ---- --}}
    <div class="kpi-card accent fade-up">
        <div class="kpi-top">
            <span class="kpi-label">Pengeluaran Terbesar</span>
            <div class="kpi-icon"><i class="fas fa-triangle-exclamation"></i></div>
        </div>
        {{-- Nilai single entry terbesar di bulan ini --}}
        <div class="kpi-value">Rp {{ number_format($terbesarAmount, 0, ',', '.') }}</div>
        <div class="kpi-sub">Entri terbesar bulan ini</div>
    </div>

</div>{{-- /kpi-grid --}}

<div class="fade-up" style="margin-bottom: 24px;">
    <div class="content-card">
        <div class="content-card-body" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">
            <div>
                <p class="content-card-title" style="margin-bottom:6px;">
                    <i class="fas fa-circle-plus"></i> Tambah Pengeluaran Cepat
                </p>
                <p style="margin:0;color:var(--text-muted);font-size:.9rem;">
                    Buka form input pengeluaran baru untuk mencatat biaya operasional.
                </p>
            </div>
            <a href="{{ route('pengeluaran.create') }}" class="btn-qa btn-qa-primary" style="border:none;">
                <i class="fas fa-plus"></i> Input Pengeluaran
            </a>
        </div>
    </div>
</div>

{{-- ============================================================
     CHART ROW — Tren harian + breakdown kategori
============================================================ --}}
<p class="section-title">
    <i class="fas fa-chart-area" style="color:var(--danger)"></i>
    Analitik Pengeluaran —
    {{ $periodLabel }}
</p>

<div class="two-col-chart fade-up">

    {{-- ---- Line Chart: Tren Harian Pengeluaran ---- --}}
    <div class="content-card">
        <div class="content-card-header">
            <span class="content-card-title">
                <i class="fas fa-chart-line"></i> Tren Pengeluaran
            </span>
            {{-- Label periode aktif --}}
            <span style="font-size:.75rem;color:var(--text-muted);">
                {{ $periodLabel }}
            </span>
        </div>
        <div class="content-card-body">
            {{-- Canvas chart — diinisialisasi di section JS --}}
            <div class="chart-wrapper">
                <canvas id="trendHarianChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ---- Bar Chart: Distribusi per Kategori ---- --}}
    <div class="content-card">
        <div class="content-card-header">
            <span class="content-card-title">
                <i class="fas fa-chart-bar"></i> Per Kategori
            </span>
        </div>
        <div class="content-card-body" style="padding-bottom:10px;">
            {{-- Canvas bar chart --}}
            <div class="chart-wrapper-sm" style="margin-bottom:14px;">
                <canvas id="kategoriChart"></canvas>
            </div>

            {{-- Breakdown list kategori (urutkan dari terbesar) --}}
            @forelse($ringkasanKategori as $kat)
            <div class="kategori-item">
                {{-- Ikon kategori dengan warna khas --}}
                <div class="kategori-icon" data-bg="{{ $kat['warna'] }}">
                    <i class="fas {{ $kat['icon'] }} fa-xs"></i>
                </div>
                <div class="kategori-info">
                    <div class="kategori-name">{{ $kat['label'] }}</div>
                    {{-- Jumlah entri kategori ini --}}
                    <div class="kategori-count">{{ $kat['jumlah'] }} entri</div>
                    {{-- Progress bar: persentase dari total --}}
                    <div class="kategori-bar">
                        <div class="kategori-bar-fill"
                             data-width="{{ number_format($kat['persen'], 1) }}"
                             data-bg="{{ $kat['warna'] }}"></div>
                    </div>
                </div>
                {{-- Nominal total kategori ini --}}
                <div class="kategori-amount">
                    Rp {{ number_format($kat['total'], 0, ',', '.') }}
                </div>
            </div>
            @empty
            {{-- Tampilkan pesan jika belum ada pengeluaran same sekali --}}
            <div style="text-align:center;padding:20px;color:var(--text-muted);font-size:.82rem;">
                <i class="fas fa-inbox" style="opacity:.25;margin-right:6px;"></i>
                Belum ada data pengeluaran.
            </div>
            @endforelse
        </div>
    </div>

</div>{{-- /two-col-chart --}}

{{-- ============================================================
     TABEL — Daftar entri pengeluaran + filter
============================================================ --}}
<p class="section-title">
    <i class="fas fa-table-list" style="color:var(--danger)"></i>
    Daftar Pengeluaran
</p>

<div class="content-card fade-up">

    {{-- ---- Header: Filter Pencarian & Dropdown ---- --}}
    <div class="content-card-header">
        <span class="content-card-title">
            <i class="fas fa-magnifying-glass"></i> Filter Pengeluaran
        </span>

        <form method="GET" action="{{ route('pengeluaran.index') }}" class="filter-bar" id="filterForm">
            <input type="hidden" name="period" value="{{ request('period', 'month') }}">

            {{-- Dropdown filter kategori pengeluaran --}}
            <select name="kategori" class="form-select" style="width:150px;"
                    onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Kategori</option>
                @foreach($kategoriLabel as $key => $info)
                    <option value="{{ $key }}" {{ $kategori === $key ? 'selected' : '' }}>
                        {{ $info['label'] }}
                    </option>
                @endforeach
            </select>

            {{-- Input pencarian deskripsi --}}
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Cari deskripsi…"
                   value="{{ $search }}"
                   style="width:190px;">

            {{-- Tombol submit --}}
            <button type="submit" class="btn btn-danger btn-sm"
                    style="height:36px;border-radius:8px;font-size:.8rem;font-weight:600;">
                <i class="fas fa-magnifying-glass me-1"></i> Cari
            </button>

            {{-- Tombol reset (hanya muncul jika ada filter aktif) --}}
            @if($search || $kategori)
            <a href="{{ route('pengeluaran.index', ['period' => request('period', 'month')]) }}" class="btn btn-outline-secondary btn-sm"
               style="height:36px;border-radius:8px;font-size:.8rem;font-weight:600;">
                <i class="fas fa-xmark me-1"></i> Reset
            </a>
            @endif

        </form>
    </div>{{-- /header filter --}}

    {{-- ---- Tabel Data Pengeluaran ---- --}}
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th>         {{-- Tanggal pengeluaran --}}
                    <th>Kategori</th>        {{-- Jenis kategori --}}
                    <th>Deskripsi</th>       {{-- Keterangan pengeluaran --}}
                    <th>Dicatat Oleh</th>    {{-- Nama user yang mencatat --}}
                    <th class="text-end">Jumlah</th> {{-- Nominal pengeluaran --}}
                    <th class="text-center">Aksi</th> {{-- Tombol detail --}}
                </tr>
            </thead>
            <tbody>
                {{-- Loop setiap entri pengeluaran --}}
                @forelse($pengeluaranList as $expense)
                <tr>
                    {{-- Tanggal pengeluaran --}}
                    <td>
                        <div style="font-weight:600;">
                            {{ $expense->expense_date->format('d M Y') }}
                        </div>
                        {{-- Hari dalam pekan --}}
                        <div style="font-size:.72rem;color:var(--text-muted);">
                            {{ $expense->expense_date->translatedFormat('l') }}
                        </div>
                    </td>

                    {{-- Badge kategori pengeluaran --}}
                    <td>
                        @php
                            // Ambil info (label, icon, warna) dari mapping kategori
                            $katInfo = $kategoriLabel[$expense->category] ?? [
                                'label' => ucfirst($expense->category),
                                'icon'  => 'fa-ellipsis',
                                'warna' => '#94a3b8',
                            ];
                        @endphp
                        <span class="badge-kategori" data-bg="{{ $katInfo['warna'] }}">
                            <i class="fas {{ $katInfo['icon'] }} fa-xs"></i>
                            {{ $katInfo['label'] }}
                        </span>
                    </td>

                    {{-- Deskripsi / keterangan pengeluaran --}}
                    <td>
                        @if($expense->description)
                            {{-- Potong teks panjang dan tampilkan tooltip asli --}}
                            <span title="{{ $expense->description }}">
                                {{ \Illuminate\Support\Str::limit($expense->description, 60) }}
                            </span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>

                    {{-- Nama user yang mencatat pengeluaran ini --}}
                    <td>{{ $expense->user?->name ?? '—' }}</td>

                    {{-- Nominal pengeluaran dengan warna merah --}}
                    <td class="text-end">
                        <strong style="color:var(--danger);">
                            Rp {{ number_format($expense->amount, 0, ',', '.') }}
                        </strong>
                    </td>

                    {{-- Tombol lihat detail --}}
                    <td class="text-center">
                        <a href="{{ route('pengeluaran.show', array_merge(request()->query(), ['expense' => $expense->id, 'period' => $period])) }}" class="btn-detail">
                            <i class="fas fa-eye fa-xs me-1"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                {{-- Tampilan kosong saat tidak ada data --}}
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Tidak ada catatan pengeluaran ditemukan.</p>
                            @if($search || $kategori)
                                <p style="margin-top:6px;font-size:.78rem;">
                                    Coba ubah filter pencarian.
                                </p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{-- /overflow-x:auto --}}

    {{-- ---- Pagination Laravel ---- --}}
    @if($pengeluaranList->hasPages())
    <div class="pagination-wrapper">
        {{-- Info jumlah data tampil --}}
        <span>
            Menampilkan {{ $pengeluaranList->firstItem() }}–{{ $pengeluaranList->lastItem() }}
            dari {{ $pengeluaranList->total() }} entri
        </span>
        {{-- Render link pagination Bootstrap 5 --}}
        {{ $pengeluaranList->links('pagination::bootstrap-5') }}
    </div>
    @endif

</div>{{-- /content-card tabel --}}

<div id="pengeluaran-chart-data" style="display:none" data-period="{{ $period }}"
     data-chart-labels='{{ json_encode($chartLabels) }}'
     data-chart-data='{{ json_encode($chartData) }}'
     data-kategori-labels='{{ json_encode($kategoriChartLabels) }}'
     data-kategori-data='{{ json_encode($kategoriChartData) }}'
     data-kategori-colors='{{ json_encode($kategoriChartColors) }}'>
</div>

@endsection

{{-- ============================================================
     SECTION JS — Inisialisasi Chart.js dan animasi
============================================================ --}}
@section('js')
{{-- Load Chart.js dari CDN --}}
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
    // PALET WARNA — Konsisten dengan tema SIMKEU
    // ============================================================
    // ============================================================
    // DATA DARI HTML — Ambil nilai JSON dari atribut data
    // ============================================================
    const pengeluaranChartData = document.getElementById('pengeluaran-chart-data');
    const period = pengeluaranChartData?.dataset.period;
    const chartLabels        = pengeluaranChartData ? JSON.parse(pengeluaranChartData.dataset.chartLabels || '[]') : [];
    const chartData          = pengeluaranChartData ? JSON.parse(pengeluaranChartData.dataset.chartData || '[]') : [];
    const kategoriLabels     = pengeluaranChartData ? JSON.parse(pengeluaranChartData.dataset.kategoriLabels || '[]') : [];
    const kategoriData       = pengeluaranChartData ? JSON.parse(pengeluaranChartData.dataset.kategoriData || '[]') : [];
    const kategoriColors     = pengeluaranChartData ? JSON.parse(pengeluaranChartData.dataset.kategoriColors || '[]') : [];

    // ============================================================
    // Konfigurasi tooltip bersama untuk semua chart
    // ============================================================
    const tooltipDefaults = {
        backgroundColor: '#1e293b',         // Latar tooltip gelap
        titleFont: { family: 'Poppins', size: 12 },
        bodyFont:  { family: 'Poppins', size: 11 },
        padding: 10,
        cornerRadius: 8,
    };

    // ============================================================
    // BAR CHART — Pengeluaran per periode.
    // ============================================================
    const trendCtx = document.getElementById('trendHarianChart');
    if (trendCtx) {
        const trendLabels = period === 'today' ? ['Hari ini'] : chartLabels;
        const trendValues = period === 'today'
            ? [chartData.reduce((sum, value) => sum + value, 0)]
            : chartData;

        if (!trendValues.some((value) => value > 0)) {
            trendCtx.insertAdjacentHTML('afterend', '<p class="chart-empty-state">Belum ada pengeluaran pada periode ini.</p>');
            trendCtx.remove();
        } else {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Pengeluaran',
                    backgroundColor: 'rgba(239,68,68,.72)',
                    data: trendValues,
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
                            title: (items) => trendLabels[items[0].dataIndex],
                            label: (ctx) => `Pengeluaran: Rp ${ctx.parsed.y.toLocaleString('id-ID')}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, family: 'Poppins' },
                            maxTicksLimit: trendValues.length > 12 ? 8 : undefined,
                            autoSkip: true,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, .18)' },
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
    // BAR CHART HORIZONTAL — Distribusi pengeluaran per kategori
    // ============================================================
    const kategoriCtx = document.getElementById('kategoriChart');
    if (kategoriCtx) {
        const categories = kategoriLabels.map((label, index) => ({
            label,
            value: kategoriData[index] ?? 0,
            color: kategoriColors[index],
        })).filter((category) => category.value > 0)
            .sort((a, b) => b.value - a.value);

        if (!categories.length) {
            kategoriCtx.insertAdjacentHTML('afterend', '<p class="chart-empty-state">Belum ada pengeluaran per kategori pada periode ini.</p>');
            kategoriCtx.remove();
        } else {
        new Chart(kategoriCtx, {
            type: 'bar', // Chart batang
            data: {
                labels: categories.map((category) => category.label),
                datasets: [{
                    label: 'Total Pengeluaran',
                    data: categories.map((category) => category.value),
                    backgroundColor: categories.map((category) => `${category.color}cc`),
                    borderColor: categories.map((category) => category.color),
                    borderWidth: 1,
                    borderRadius: 4,
                    maxBarThickness: 20,
                }]
            },
            options: {
                indexAxis: 'y', // Horizontal bar chart (kategori di sumbu Y)
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }, // Sembunyikan legend
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: {
                            label: ctx => ' Rp ' + ctx.parsed.x.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, .18)' },
                        ticks: {
                            font: { size: 9, family: 'Poppins' },
                            callback: (value) => value >= 1000000
                                ? `Rp ${(value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`
                                : `Rp ${value.toLocaleString('id-ID')}`
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 10, family: 'Poppins' } }
                    }
                }
            }
        });
        }
    }

}); // DOMContentLoaded
</script>
@endsection
