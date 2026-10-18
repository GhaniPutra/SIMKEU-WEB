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
<link rel="stylesheet" href="{{ asset('css/pengeluaran/index.css') }}">
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
            <span class="kpi-icon"><i class="fas fa-wallet"></i></span>
            <span class="kpi-label">Total Pengeluaran</span>
        </div>
        {{-- Total nominal pengeluaran bulan ini --}}
        <div class="kpi-value">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</div>
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

    {{-- ---- Kartu Jumlah Entri ---- --}}
    <div class="kpi-card warning fade-up">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-receipt"></i></span>
            <span class="kpi-label">Jumlah Entri</span>
        </div>
        {{-- Total jumlah baris pengeluaran bulan ini --}}
        <div class="kpi-value">{{ number_format($jumlahEntri) }}</div>
        <div class="kpi-sub">Tercatat bulan ini</div>
    </div>

    {{-- ---- Kartu Rata-rata per Entri ---- --}}
    <div class="kpi-card primary fade-up">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-calculator"></i></span>
            <span class="kpi-label">Rata-rata / Entri</span>
        </div>
        {{-- Rata-rata nilai setiap entri pengeluaran --}}
        <div class="kpi-value">Rp {{ number_format($rataRata, 0, ',', '.') }}</div>
        <div class="kpi-sub">Nilai rata-rata per transaksi</div>
    </div>

    {{-- ---- Kartu Pengeluaran Terbesar ---- --}}
    <div class="kpi-card accent fade-up">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-arrow-up"></i></span>
            <span class="kpi-label">Pengeluaran Terbesar</span>
        </div>
        {{-- Nilai single entry terbesar di bulan ini --}}
        <div class="kpi-value">Rp {{ number_format($terbesarAmount, 0, ',', '.') }}</div>
        <div class="kpi-sub">Entri terbesar bulan ini</div>
    </div>

</div>{{-- /kpi-grid --}}

<div class="fade-up quick-entry">
    <div class="content-card">
        <div class="content-card-body quick-entry-body">
            <div>
                <p class="content-card-title quick-entry-title">
                    <i class="fas fa-circle-plus"></i> Tambah Pengeluaran Cepat
                </p>
                <p class="quick-entry-description">
                    Buka form input pengeluaran baru untuk mencatat biaya operasional.
                </p>
            </div>
            @if(Auth::user()->hasPermission('expenses.create'))<a href="{{ route('pengeluaran.create') }}" class="btn-qa btn-qa-primary">
                <i class="fas fa-plus"></i> Input Pengeluaran
            </a>@endif
        </div>
    </div>
</div>

{{-- ============================================================
     CHART ROW — Tren harian + breakdown kategori
============================================================ --}}
<p class="section-title">
    <i class="fas fa-chart-area section-title-icon"></i>
    Analitik Pengeluaran —
    {{ $periodLabel }}
</p>

<div class="two-col-chart fade-up">

    {{-- ---- Line Chart: Tren Harian Pengeluaran ---- --}}
    <div class="content-card analytics-card">
        <div class="content-card-header">
            <span class="content-card-title">
                <i class="fas fa-chart-line"></i> Tren Pengeluaran
            </span>
            {{-- Label periode aktif --}}
            <span class="period-label">
                {{ $periodLabel }}
            </span>
        </div>
        <div class="content-card-body chart-body">
            {{-- Canvas chart — diinisialisasi di section JS --}}
            <div class="chart-wrapper">
                <canvas id="trendHarianChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ---- Ranking pengeluaran per kategori ---- --}}
    <div class="content-card analytics-card">
        <div class="content-card-header">
            <span class="content-card-title">
                <i class="fas fa-chart-bar"></i> Per Kategori
            </span>
            <span class="period-label">{{ $periodLabel }}</span>
        </div>
        <div class="content-card-body analytics-scroll">
            {{-- Data agregat periode aktif, sudah diurutkan dari total terbesar --}}
            @forelse($ringkasanKategori as $kat)
            <div class="kategori-item">
                <div class="kategori-topline">
                    <div class="kategori-name">{{ $kat['label'] }}</div>
                    <div class="kategori-metrics">
                        <span class="kategori-amount">Rp {{ number_format($kat['total'], 0, ',', '.') }}</span>
                        <span class="kategori-percent">{{ number_format($kat['persen'], 1, ',', '.') }}%</span>
                    </div>
                </div>
                <div class="kategori-bar" aria-label="{{ number_format($kat['persen'], 1, ',', '.') }}% dari total pengeluaran">
                    <div class="kategori-bar-fill"
                         data-width="{{ min(100, max(0, $kat['persen'])) }}"
                         data-bg="{{ $kat['warna'] }}"></div>
                </div>
            </div>
            @empty
            {{-- Tampilkan pesan jika belum ada pengeluaran same sekali --}}
            <div class="category-empty-state">
                <i class="fas fa-inbox category-empty-icon"></i>
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
    <i class="fas fa-table-list section-title-icon"></i>
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
            <select name="kategori" class="form-select filter-category">
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
                   class="form-control filter-search"
                   placeholder="Cari deskripsi…"
                   value="{{ $search }}">

            {{-- Tombol submit --}}
            <button type="submit" class="btn btn-danger btn-sm filter-submit">
                <i class="fas fa-magnifying-glass me-1"></i> Cari
            </button>

            {{-- Tombol reset (hanya muncul jika ada filter aktif) --}}
            @if($search || $kategori)
            <a href="{{ route('pengeluaran.index', ['period' => request('period', 'month')]) }}" class="btn btn-outline-secondary btn-sm reset-button filter-reset">
                <i class="fas fa-xmark me-1"></i> Reset
            </a>
            @endif

        </form>
    </div>{{-- /header filter --}}

    {{-- ---- Tabel Data Pengeluaran ---- --}}
    <div class="table-scroll">
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
                        <div class="expense-date">
                            {{ $expense->expense_date->format('d M Y') }}
                        </div>
                        {{-- Hari dalam pekan --}}
                        <div class="expense-weekday">
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
                                'warna' => '#ff2c48',
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
                            <span class="muted-placeholder">—</span>
                        @endif
                    </td>

                    {{-- Nama user yang mencatat pengeluaran ini --}}
                    <td>{{ $expense->user?->name ?? '—' }}</td>

                    {{-- Nominal pengeluaran dengan warna merah --}}
                    <td class="text-end">
                        <strong class="expense-amount">
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
                                <p class="filter-empty-hint">
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

<div id="pengeluaran-chart-data" class="chart-data" data-period="{{ $period }}"
     data-chart-labels='{{ json_encode($chartLabels) }}'
     data-chart-data='{{ json_encode($chartData) }}'
     >
</div>

@endsection

{{-- ============================================================
     SECTION JS — Memuat pustaka grafik dan skrip halaman
============================================================ --}}
@section('js')
{{-- Chart.js dimuat lebih dulu sebagai dependensi grafik. --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/pengeluaran/index.js') }}"></script>
@endsection
