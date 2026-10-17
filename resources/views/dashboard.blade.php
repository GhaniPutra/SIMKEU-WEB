{{-- ====================================
    Halaman Dashboard - SIMKEU
    Menampilkan ringkasan KPI keuangan, grafik tren,
    daftar aktivitas terbaru, dan tabel transaksi.
    Data difilter berdasarkan periode yang dipilih di topbar.
==================================== --}}
@extends('layouts.app')

{{-- Judul tab browser --}}
@section('title', 'Dashboard - SIMKEU')

{{-- Judul halaman di topbar --}}
@section('page-title', 'Dashboard Bisnis')

{{-- Breadcrumb di topbar --}}
@section('breadcrumb', 'Beranda / Dashboard')

@section('css')
    {{-- Muat gaya khusus dashboard dari folder aset dashboard. --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
@endsection

@section('content')
    @auth
        {{-- Kirim data grafik dari controller ke JavaScript melalui atribut HTML. --}}
        <div id="dashboard-data"
            data-period="{{ $period }}"
            data-period-label="{{ $periodLabel }}"
            data-month-day-count="{{ now()->daysInMonth }}"
            data-trend-labels="{{ json_encode($trendLabels, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
            data-revenue-data="{{ json_encode($revenueTrendData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
            data-expense-data="{{ json_encode($expenseTrendData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
            hidden></div>

        {{-- ============================================================
        BANNER PERINGATAN
        ============================================================ --}}
        @foreach($alerts as $alert)
            <div class="alert-banner alert-{{ $alert['type'] }} fade-up">
                <i class="fas fa-exclamation-circle"></i>
                {{ $alert['message'] }}
            </div>
        @endforeach

        {{-- ============================================================
        KARTU INDIKATOR UTAMA
        ============================================================ --}}
        <div class="kpi-grid">
            {{-- Pendapatan --}}
            <div class="kpi-card success fade-up">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-wallet"></i></span>
                    <span class="kpi-label">Pendapatan</span>
                </div>
                <div class="kpi-value">Rp {{ number_format($totalRevenueMonth, 0, ',', '.') }}</div>
                <div class="kpi-sub">
                    @if($hasComparisonPeriod)
                        {{ $comparisonLabel }}
                        <span class="kpi-trend {{ $revenueTrendLabel }}">
                            <i class="fas fa-{{ $revenueTrendLabel === 'trend-up' ? 'caret-up' : ($revenueTrendLabel === 'trend-down' ? 'caret-down' : 'minus') }}"></i>
                            {{ $revenueTrendText }}
                        </span>
                    @else
                        {{ $periodLabel }}
                    @endif
                </div>
            </div>

            {{-- Pengeluaran --}}
            <div class="kpi-card danger fade-up">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-money-bill-wave"></i></span>
                    <span class="kpi-label">Pengeluaran</span>
                </div>
                <div class="kpi-value">Rp {{ number_format($totalExpenseMonth, 0, ',', '.') }}</div>
                <div class="kpi-sub">
                    @if($hasComparisonPeriod)
                        {{ $comparisonLabel }}
                        <span class="kpi-trend {{ $expenseTrendLabel }}">
                            <i class="fas fa-{{ $expenseTrendLabel === 'trend-up' ? 'caret-up' : ($expenseTrendLabel === 'trend-down' ? 'caret-down' : 'minus') }}"></i>
                            {{ $expenseTrendText }}
                        </span>
                    @else
                        {{ $periodLabel }}
                    @endif
                </div>
            </div>

            {{-- Laba Bersih --}}
            <div class="kpi-card primary fade-up">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-chart-line"></i></span>
                    <span class="kpi-label">Laba Bersih</span>
                </div>
                <div class="kpi-value">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                <div class="kpi-sub">
                    Kotor: <strong>Rp {{ number_format($grossProfit, 0, ',', '.') }}</strong>
                    @if($hasComparisonPeriod)
                        · {{ $comparisonLabel }}
                        <span class="kpi-trend {{ $netProfitTrendLabel }}">
                            <i class="fas fa-{{ $netProfitTrendLabel === 'trend-up' ? 'caret-up' : ($netProfitTrendLabel === 'trend-down' ? 'caret-down' : 'minus') }}"></i>
                            {{ $netProfitTrendText }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Transaksi --}}
            <div class="kpi-card warning fade-up">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-receipt"></i></span>
                    <span class="kpi-label">Transaksi</span>
                </div>
                <div class="kpi-value">{{ $transactionsPeriod }}</div>
                <div class="kpi-sub">
                    @if($hasComparisonPeriod)
                        {{ $comparisonLabel }}
                        <span class="kpi-trend {{ $transactionsTrendLabel }}">
                            <i class="fas fa-{{ $transactionsTrendLabel === 'trend-up' ? 'caret-up' : ($transactionsTrendLabel === 'trend-down' ? 'caret-down' : 'minus') }}"></i>
                            {{ $transactionsTrendText }}
                        </span>
                    @else
                        {{ $periodLabel }}
                    @endif
                </div>
            </div>

            {{-- Pegawai --}}
            <div class="kpi-card accent fade-up">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-users"></i></span>
                    <span class="kpi-label">Pegawai Terdaftar</span>
                </div>
                <div class="kpi-value">{{ $activeEmployees }}</div>
                <div class="kpi-sub">Akun dengan peran pegawai</div>
            </div>

        </div>

        {{-- ============================================================
        GRAFIK TREN KEUANGAN
        ============================================================ --}}
        <p class="section-title"><i class="fas fa-chart-area dashboard-section-icon"></i> Tren Keuangan</p>
        <div class="fade-up dashboard-chart-section">
            <div class="content-card dashboard-trend-card">
                <div class="content-card-header">
                    <span class="content-card-title">
                        <i class="fas fa-chart-line"></i> Pendapatan vs Pengeluaran
                    </span>
                    {{-- Label periode: menunjukkan rentang data yang sedang ditampilkan --}}
                    <span class="dashboard-period-label">
                        {{ $periodLabel }}
                    </span>
                </div>
                <div class="content-card-body">
                    <div class="chart-wrapper">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
        DAFTAR AKTIVITAS DAN TABEL DATA
        ============================================================ --}}
        <p class="section-title"><i class="fas fa-table-list dashboard-section-icon"></i> Aktivitas & Data</p>
        <div class="two-col fade-up">
            {{-- Aktivitas Terbaru --}}
            <div class="content-card">
                <div class="content-card-header">
                    <span class="content-card-title">
                        <i class="fas fa-clock-rotate-left"></i> Aktivitas Terbaru
                    </span>
                    <a href="{{ route('activities.index', ['period' => $period]) }}" class="dashboard-view-all">
                        Lihat semua
                    </a>
                </div>
                <div class="content-card-body activity-card-body">
                    <ul class="activity-list">
                        @forelse($latestActivities as $activity)
                            <li class="activity-item">
                                <div class="activity-dot {{ $activity['type'] === 'sale' ? 'activity-sale' : 'activity-expense' }}"></div>
                                <div class="activity-content">
                                    <p class="activity-text">{{ $activity['message'] }}</p>
                                    <p class="activity-time"><i class="fas fa-clock fa-xs"></i> {{ $activity['date']->diffForHumans() }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="activity-item">
                                <div class="activity-dot activity-default"></div>
                                <div class="activity-content">
                                    <p class="activity-text">Belum ada aktivitas terbaru.</p>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Data Transaksi Terbaru --}}
            <div class="content-card">
                <div class="content-card-header">
                    <span class="content-card-title">
                        <i class="fas fa-receipt"></i> Transaksi Terbaru
                    </span>
                    <a href="{{ route('activities.index', ['period' => $period]) }}" class="dashboard-view-all">
                        Lihat semua
                    </a>
                </div>
                <div class="dashboard-table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Jumlah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction['date'] }}</td>
                                    <td><span class="type-badge type-{{ $transaction['type'] }}">{{ $transaction['label'] }}</span></td>
                                    <td>Rp {{ number_format($transaction['amount'], 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ $transaction['url'] }}" class="btn-detail">
                                            <i class="fas fa-eye fa-xs me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada transaksi terbaru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else
        {{-- Belum login --}}
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            Anda harus login terlebih dahulu.
            <a href="{{ route('login') }}" class="ms-2 fw-bold">Login di sini</a>
        </div>
    @endauth
@endsection

@section('js')
    {{-- Muat Chart.js sebelum script grafik dashboard. --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- Jalankan pengolahan data, sinkronisasi filter, dan grafik dashboard. --}}
    <script src="{{ asset('js/dashboard/dashboard.js') }}"></script>
@endsection
