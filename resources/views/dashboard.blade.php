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
            data-all-period-month-count="{{ $allPeriodMonthCount }}"
            data-trend-labels="{{ json_encode($trendLabels, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
            data-revenue-data="{{ json_encode($revenueTrendData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
            data-expense-data="{{ json_encode($expenseTrendData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
            data-expense-category-labels="{{ json_encode($expenseCategoryLabels, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
            data-expense-category-data="{{ json_encode($expenseCategoryData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
            data-expense-category-colors="{{ json_encode($expenseCategoryColors, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
            hidden></div>

        {{-- ============================================================
        KARTU INDIKATOR UTAMA
        ============================================================ --}}
        <div class="kpi-grid">
            {{-- Pendapatan --}}
            <div class="kpi-card success fade-up">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-wallet"></i></span>
                    <span class="kpi-label">Total Pemasukan</span>
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
                    <span class="kpi-label">Total Pengeluaran</span>
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
                    @foreach($alerts as $alert)
                        <span class="kpi-alert-note" role="status" aria-label="{{ $alert['message'] }}">
                            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            Melebihi pemasukan
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Selisih pemasukan dan pengeluaran --}}
            <div class="kpi-card primary fade-up">
                <div class="kpi-top">
                    <span class="kpi-icon"><i class="fas fa-scale-balanced"></i></span>
                    <span class="kpi-label">Selisih Pemasukan dan Pengeluaran</span>
                </div>
                <div class="kpi-value">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                <div class="kpi-sub">
                    @if($hasComparisonPeriod)
                        {{ $comparisonLabel }}
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
                    <span class="kpi-label">Jumlah Transaksi Penjualan</span>
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

        </div>

        {{-- ============================================================
        ANALISIS KEUANGAN
        ============================================================ --}}
        <p class="section-title"><i class="fas fa-chart-area dashboard-section-icon"></i> Analisis Keuangan</p>
        <div class="fade-up dashboard-chart-layout dashboard-chart-section">
            <div class="content-card dashboard-trend-card dashboard-chart-card dashboard-comparison-card">
                <div class="content-card-header">
                    <span class="content-card-title">
                        <i class="fas fa-chart-column"></i> Pemasukan vs Pengeluaran
                    </span>
                    <span class="dashboard-period-label">{{ $periodLabel }}</span>
                </div>
                <div class="content-card-body">
                    <div class="chart-wrapper">
                        <canvas id="financialComparisonChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="content-card dashboard-trend-card dashboard-chart-card dashboard-side-chart-card dashboard-revenue-trend-card">
                <div class="content-card-header">
                    <span class="content-card-title">
                        <i class="fas fa-chart-line"></i> Tren Pemasukan
                    </span>
                    <span class="dashboard-period-label">{{ $periodLabel }}</span>
                </div>
                <div class="content-card-body">
                    <div class="chart-wrapper">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="content-card dashboard-trend-card dashboard-chart-card dashboard-side-chart-card dashboard-expense-composition-card">
                <div class="content-card-header">
                    <span class="content-card-title">
                        <i class="fas fa-chart-pie"></i> Komposisi Pengeluaran
                    </span>
                    <span class="dashboard-period-label">{{ $periodLabel }}</span>
                </div>
                <div class="content-card-body dashboard-expense-legend-body">
                    <div class="dashboard-expense-donut-wrapper">
                        <canvas id="expenseCompositionChart"></canvas>
                    </div>
                    <div id="expenseCompositionLegend" class="dashboard-expense-legend-list" aria-label="Rincian komposisi pengeluaran"></div>
                </div>
            </div>
        </div>

        {{-- ============================================================
        RINGKASAN OPERASIONAL
        ============================================================ --}}
        <p class="section-title"><i class="fas fa-layer-group dashboard-section-icon"></i> Ringkasan Operasional</p>
        <div class="fade-up dashboard-operations-grid dashboard-chart-section">
            <div class="dashboard-operations-main">
                @if($canViewSales)
                    <div class="content-card dashboard-operations-card dashboard-sales-summary-card">
                        <div class="content-card-header">
                            <span class="content-card-title"><i class="fas fa-receipt"></i> Ringkasan Penjualan Terbaru</span>
                            <span class="dashboard-period-label">{{ $periodLabel }}</span>
                        </div>
                        <div class="content-card-body dashboard-sales-list-body">
                            @php
                                $paymentMethodLabels = ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'];
                            @endphp
                            <ul class="dashboard-sales-list" tabindex="0" aria-label="Daftar penjualan terbaru">
                                @forelse($recentSales as $sale)
                                    <li class="dashboard-sale-item">
                                        <div class="dashboard-sale-heading">
                                            <div class="dashboard-sale-employee-group">
                                                <span class="dashboard-sale-employee">{{ $sale->user?->name ?? 'Pegawai tidak tersedia' }}</span>
                                                <span class="dashboard-sale-invoice">{{ $sale->invoice_number }}</span>
                                            </div>
                                            <time datetime="{{ $sale->transaction_date->toIso8601String() }}">{{ $sale->transaction_date->format('d M Y, H:i') }}</time>
                                        </div>
                                        <div class="dashboard-sale-details">
                                            <span class="dashboard-sale-items">
                                                {{ number_format((int) ($sale->sale_items_sum_quantity ?? 0)) }} item · {{ number_format((int) ($sale->sale_items_count ?? 0)) }} produk
                                            </span>
                                            <span class="dashboard-payment-badge">{{ $paymentMethodLabels[$sale->payment_method] ?? ucfirst($sale->payment_method) }}</span>
                                            <strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong>
                                        </div>
                                    </li>
                                @empty
                                    <li class="dashboard-ops-empty">Belum ada penjualan pada {{ strtolower($periodLabel) }}.</li>
                                @endforelse
                            </ul>
                            <a href="{{ route('pemasukan.index', ['period' => $period]) }}" class="dashboard-ops-footer-link">
                                Buka Riwayat Penjualan <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                @endif

                @if($canViewStockAllocations)
                    <div class="content-card dashboard-operations-card dashboard-allocation-card">
                        <div class="content-card-header">
                            <span class="content-card-title"><i class="fas fa-boxes-packing"></i> Status Alokasi Stok</span>
                            @if($currentUser->hasPermission('employees.view'))
                                <a href="{{ route('employees.index') }}" class="dashboard-view-all">Daftar Pegawai</a>
                            @endif
                        </div>
                        <div class="content-card-body dashboard-allocation-body">
                            <div class="dashboard-allocation-metrics">
                                <div>
                                    <strong>{{ number_format($pendingAllocationCount) }}</strong>
                                    <span>{{ $isAllocationManager ? 'Menunggu konfirmasi pegawai' : 'Menunggu konfirmasi Anda' }}</span>
                                </div>
                                @if($isAllocationManager)
                                    <div class="needs-follow-up {{ $allocationFollowUpCount > 0 ? 'has-follow-up' : '' }}">
                                        <strong>{{ number_format($allocationFollowUpCount) }}</strong>
                                        <span>Perlu ditindaklanjuti</span>
                                    </div>
                                @endif
                            </div>
                            <ul class="dashboard-allocation-list" tabindex="0" aria-label="Daftar alokasi stok">
                                @forelse($allocationAttention as $allocation)
                                    <li>
                                        <div class="dashboard-allocation-item-main">
                                            <strong>{{ $allocation['product'] }}</strong>
                                            <span>{{ number_format($allocation['quantity']) }} {{ $allocation['unit'] }} ·
                                                @if($allocation['employeeUrl'])
                                                    <a href="{{ $allocation['employeeUrl'] }}">{{ $allocation['employee'] }}</a>
                                                @else
                                                    {{ $isAllocationManager ? $allocation['employee'] : 'konfirmasi Anda' }}
                                                @endif
                                            </span>
                                        </div>
                                        <span class="dashboard-allocation-status status-{{ $allocation['status'] }}">{{ $allocation['statusLabel'] }}</span>
                                    </li>
                                @empty
                                    <li class="dashboard-allocation-clear"><i class="fas fa-circle-check" aria-hidden="true"></i> Semua alokasi sudah ditindaklanjuti.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            <aside class="dashboard-operations-side">
                @if($canViewProducts)
                    <div class="content-card dashboard-operations-card dashboard-stock-summary-card">
                        <div class="content-card-header">
                            <span class="content-card-title"><i class="fas fa-boxes-stacked"></i> Ringkasan Stok</span>
                            <a href="{{ route('products.index', ['period' => $period]) }}" class="dashboard-view-all">Produk dan Persediaan</a>
                        </div>
                        <div class="content-card-body dashboard-stock-summary-body">
                            <div class="dashboard-stock-count {{ $lowStockCount > 0 ? 'has-low-stock' : 'stock-is-healthy' }}">
                                <strong>{{ number_format($lowStockCount) }}</strong>
                                <span>produk stok rendah <small>(≤ {{ $lowStockThreshold }})</small></span>
                            </div>
                            <ul class="dashboard-stock-list" tabindex="0" aria-label="Daftar produk stok rendah">
                                @forelse($lowStockProducts as $product)
                                    <li>
                                        <a href="{{ route('products.index', ['search' => $product->name, 'period' => $period]) }}">{{ $product->name }}</a>
                                        <span class="dashboard-stock-quantity {{ $product->stock <= 0 ? 'is-empty' : '' }}">{{ number_format($product->stock) }} {{ $product->unit }}</span>
                                    </li>
                                @empty
                                    <li class="dashboard-stock-clear"><i class="fas fa-circle-check" aria-hidden="true"></i> Tidak ada produk yang perlu diperiksa.</li>
                                @endforelse
                            </ul>
                            @if($lowStockCount > $lowStockProducts->count())
                                <span class="dashboard-stock-more">+ {{ number_format($lowStockCount - $lowStockProducts->count()) }} produk lainnya perlu diperiksa</span>
                            @endif
                        </div>
                    </div>
                @endif

                @if($canShowActivitySummary)
                    <div class="content-card dashboard-operations-card dashboard-activity-card">
                        <div class="content-card-header">
                            <span class="content-card-title"><i class="fas fa-clock-rotate-left"></i> Aktivitas Terbaru</span>
                            @if($canViewActivityHistory)
                                <a href="{{ route('activities.index', ['period' => $period]) }}" class="dashboard-view-all">Riwayat Aktivitas</a>
                            @endif
                        </div>
                        <div class="content-card-body activity-card-body">
                            <ul class="activity-list" tabindex="0" aria-label="Aktivitas terbaru">
                                @forelse($latestActivities as $activity)
                                    @php
                                        $activityDotClass = match ($activity['type']) {
                                            'sale' => 'activity-sale',
                                            'expense' => 'activity-expense',
                                            'product' => 'activity-product',
                                            'allocation' => 'activity-allocation',
                                            default => 'activity-default',
                                        };
                                    @endphp
                                    <li class="activity-item">
                                        <div class="activity-dot {{ $activityDotClass }}"></div>
                                        <div class="activity-content">
                                            <p class="activity-text">{{ $activity['message'] }}</p>
                                            <p class="activity-time"><i class="fas fa-clock fa-xs"></i> {{ $activity['date']->format('d M Y, H:i') }} · {{ $activity['date']->diffForHumans() }}</p>
                                        </div>
                                    </li>
                                @empty
                                    <li class="dashboard-ops-empty">Belum ada aktivitas pada {{ strtolower($periodLabel) }}.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                @endif
            </aside>
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
