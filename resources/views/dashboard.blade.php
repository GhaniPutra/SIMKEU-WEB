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
    {{-- CSS khusus halaman dashboard (chart wrappers, KPI cards, dll) --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
    @auth
        {{-- ============================================================
        ALERT BANNERS
        ============================================================ --}}
        @foreach($alerts as $alert)
            <div class="alert-banner alert-{{ $alert['type'] }} fade-up">
                <i class="fas fa-exclamation-circle"></i>
                {{ $alert['message'] }}
            </div>
        @endforeach

        {{-- ============================================================
        KPI CARDS GRID
        ============================================================ --}}
        <div class="kpi-grid">
            {{-- Pendapatan --}}
            <div class="kpi-card success fade-up">
                <div class="kpi-top">
                    <span class="kpi-label">Pendapatan</span>
                    <div class="kpi-icon"><i class="fas fa-arrow-trend-up"></i></div>
                </div>
                <div class="kpi-value">Rp {{ number_format($totalRevenueMonth, 0, ',', '.') }}</div>
                <div class="kpi-sub">
                    @if($period === 'today')
                        Hari ini dibanding kemarin
                        <span class="kpi-trend {{ $revenueTrendLabel }}">
                            <i class="fas fa-caret-{{ $revenueTrendLabel === 'trend-up' ? 'up' : 'down' }}"></i>
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
                    <span class="kpi-label">Pengeluaran</span>
                    <div class="kpi-icon"><i class="fas fa-arrow-trend-down"></i></div>
                </div>
                <div class="kpi-value">Rp {{ number_format($totalExpenseMonth, 0, ',', '.') }}</div>
                <div class="kpi-sub">
                    @if($period === 'today')
                        Hari ini dibanding kemarin
                        <span class="kpi-trend {{ $expenseTrendLabel }}">
                            <i class="fas fa-caret-{{ $expenseTrendLabel === 'trend-up' ? 'up' : 'down' }}"></i>
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
                    <span class="kpi-label">Laba Bersih</span>
                    <div class="kpi-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                </div>
                <div class="kpi-value">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                <div class="kpi-sub">
                    Kotor: <strong>Rp {{ number_format($grossProfit, 0, ',', '.') }}</strong>
                </div>
            </div>

            {{-- Transaksi --}}
            <div class="kpi-card warning fade-up">
                <div class="kpi-top">
                    <span class="kpi-label">Transaksi</span>
                    <div class="kpi-icon"><i class="fas fa-receipt"></i></div>
                </div>
                <div class="kpi-value">{{ $transactionsPeriod }}</div>
                <div class="kpi-sub">
                    @if($period === 'today')
                        Hari ini dibanding kemarin
                        <span class="kpi-trend {{ $transactionsTrendLabel }}">
                            <i class="fas fa-caret-{{ $transactionsTrendLabel === 'trend-up' ? 'up' : 'down' }}"></i>
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
                    <span class="kpi-label">Pegawai Aktif</span>
                    <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
                </div>
                <div class="kpi-value">{{ $activeEmployees }}</div>
                <div class="kpi-sub">Karyawan terdaftar aktif</div>
            </div>

            <!-- KPI "Cabang" dihapus -->
        </div>

        {{-- ============================================================
        QUICK ACTIONS — Tombol akses cepat ke POS
        ============================================================ --}}
        <div class="fade-up" style="margin-bottom: 24px; display: none">
            <div class="content-card">
                <div class="content-card-body" style="display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between;">
                    <div>
                        <p class="content-card-title" style="margin-bottom:6px;">
                            <i class="fas fa-bolt"></i> Aksi Cepat
                        </p>
                        <p style="margin:0;color:var(--text-muted);font-size:.9rem;">
                            Mulai transaksi POS cepat tanpa harus membuka menu pemasukan.
                        </p>
                    </div>
                    <a href="{{ route('pos.index') }}" class="btn-qa btn-qa-primary">
                        <i class="fas fa-cash-register"></i> Buka POS
                    </a>
                </div>
            </div>
        </div>

        {{-- ============================================================
        CHART ROW — Trend Full Width
        ============================================================ --}}
        <p class="section-title"><i class="fas fa-chart-area" style="color:var(--icon-primary)"></i> Tren Keuangan</p>
        <div class="fade-up" style="margin-bottom: 28px;">
            <div class="content-card">
                <div class="content-card-header">
                    <span class="content-card-title">
                        <i class="fas fa-chart-line"></i> Pendapatan vs Pengeluaran
                    </span>
                    {{-- Label periode: menunjukkan rentang data yang sedang ditampilkan --}}
                    <span style="font-size:.75rem;color:var(--text-muted);">
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
        ACTIVITY LOG + DATA TABLE
        ============================================================ --}}
        <p class="section-title"><i class="fas fa-table-list" style="color:var(--icon-primary)"></i> Aktivitas & Data</p>
        <div class="two-col fade-up">
            {{-- Aktivitas Terbaru --}}
            <div class="content-card">
                <div class="content-card-header">
                    <span class="content-card-title">
                        <i class="fas fa-clock-rotate-left"></i> Aktivitas Terbaru
                    </span>
                    <a href="#" style="font-size:.75rem;color:var(--primary);text-decoration:none;font-weight:600;">
                        Lihat semua
                    </a>
                </div>
                <div class="content-card-body" style="padding-top:8px;">
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
                    <a href="#" style="font-size:.75rem;color:var(--primary);text-decoration:none;font-weight:600;">
                        Lihat semua
                    </a>
                </div>
                <div style="overflow-x:auto;">
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
    {{-- Library Chart.js untuk menggambar grafik tren --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div id="dashboard-data"
        data-period="{{ $period }}"
        data-period-label="{{ $periodLabel }}"
        data-trend-labels="{{ json_encode($trendLabels, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
        data-revenue-data="{{ json_encode($revenueTrendData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
        data-expense-data="{{ json_encode($expenseTrendData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
        hidden></div>
    <script>
        // ============================================================
        // Kirim data tren dari PHP (server) ke JavaScript (client)
        // Data ini digunakan oleh dashboard.js untuk menggambar grafik
        // ============================================================
        const dashboardData = document.getElementById('dashboard-data').dataset;
        window.dashboardData = {
            period: dashboardData.period,
            periodLabel: dashboardData.periodLabel,
            trendLabels: JSON.parse(dashboardData.trendLabels),
            revenueData: JSON.parse(dashboardData.revenueData),
            expenseData: JSON.parse(dashboardData.expenseData),
        };

        // ============================================================
        // Sinkronisasi nilai select #global-period dengan periode
        // yang aktif saat ini ($period dari DashboardController).
        // Ini memastikan select menampilkan pilihan yang benar setelah
        // halaman di-reload dengan ?period=<nilai>.
        // ============================================================
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil elemen select filter periode di topbar
            const periodSelect = document.getElementById('global-period');

            if (periodSelect) {
                // Paksa nilai select sesuai $period yang dikirim dari controller
                // Ini menimpa logika localStorage di app.blade.php untuk halaman ini
                periodSelect.value = dashboardData.period;

                // Simpan juga ke localStorage agar konsisten saat pindah halaman
                localStorage.setItem('dashboardPeriod', dashboardData.period);
            }
        });
    </script>

    {{-- File JS utama dashboard: animasi fade-up dan inisialisasi grafik --}}
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endsection