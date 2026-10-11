@extends('layouts.app')

@section('title', 'Kelola Pegawai - ' . $employee->name . ' - SIMKEU')
@section('page-title', 'Kelola Pegawai')
@section('breadcrumb', 'Pegawai / ' . $employee->name . ' / Kelola')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employees/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/employees/manage.css') }}">
@endsection

@section('content')
<div class="employee-manage"
    data-employee-id="{{ $employee->id }}"
    data-stock-transfer-url="{{ route('employees.stock.transfer', $employee) }}"
    data-trend-labels="{{ json_encode($trendLabels, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
    data-trend-revenue="{{ json_encode($trendRevenue, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}"
    data-trend-full-labels="{{ json_encode($trendFullLabels, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}">
    <header class="employee-manage-header">
        <div class="employee-identity">
            <div class="employee-avatar" aria-hidden="true"><i class="fas fa-user-tie"></i></div>
            <div class="employee-identity-copy">
                <h1>{{ $employee->name }}</h1>
                <div class="employee-identity-meta">
                    <span>{{ $employee->email }}</span>
                    <span class="employee-role">{{ ucfirst($employee->role) }}</span>
                    @if($employee->email_verified_at)
                        <span class="employee-verification is-verified"><i class="fas fa-circle-check"></i> Email terverifikasi</span>
                    @else
                        <span class="employee-verification is-unverified"><i class="fas fa-circle-exclamation"></i> Email belum terverifikasi</span>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('employees.index') }}" class="employee-back">
            <i class="fas fa-arrow-left"></i><span>Kembali</span>
        </a>
    </header>

    <nav class="employee-tabs" role="tablist" aria-label="Operasional pegawai">
        <button type="button" id="tab-button-overview" class="employee-tab {{ $activeTab === 'overview' ? 'active' : '' }}" role="tab" aria-selected="{{ $activeTab === 'overview' ? 'true' : 'false' }}" aria-controls="tab-overview" data-tab="overview">
            <i class="fas fa-gauge-high"></i> Ringkasan
        </button>
        <button type="button" id="tab-button-stock" class="employee-tab {{ $activeTab === 'stock' ? 'active' : '' }}" role="tab" aria-selected="{{ $activeTab === 'stock' ? 'true' : 'false' }}" aria-controls="tab-stock" data-tab="stock">
            <i class="fas fa-boxes-stacked"></i> Stok
            @if($stockSummary->empty_stock_count + $stockSummary->low_stock_count > 0)
                <span class="tab-count">{{ $stockSummary->empty_stock_count + $stockSummary->low_stock_count }}</span>
            @endif
        </button>
        <button type="button" id="tab-button-sales" class="employee-tab {{ $activeTab === 'sales' ? 'active' : '' }}" role="tab" aria-selected="{{ $activeTab === 'sales' ? 'true' : 'false' }}" aria-controls="tab-sales" data-tab="sales">
            <i class="fas fa-chart-line"></i> Penjualan
        </button>
    </nav>

    <section class="employee-tab-panel {{ $activeTab === 'overview' ? 'active' : '' }}" id="tab-overview" role="tabpanel" aria-labelledby="tab-button-overview" tabindex="0">
        <div class="employee-kpis">
            <article class="employee-kpi kpi-revenue">
                <div class="employee-kpi-icon"><i class="fas fa-wallet"></i></div>
                <div class="employee-kpi-label">Pendapatan {{ $periodLabel }}</div>
                <div class="employee-kpi-value">Rp {{ number_format((float) $periodTotals->revenue, 0, ',', '.') }}</div>
                <div class="employee-kpi-note">Transaksi pegawai ini</div>
            </article>
            <article class="employee-kpi kpi-transactions">
                <div class="employee-kpi-icon"><i class="fas fa-receipt"></i></div>
                <div class="employee-kpi-label">Transaksi {{ $periodLabel }}</div>
                <div class="employee-kpi-value">{{ number_format((int) $periodTotals->transactions) }}</div>
                <div class="employee-kpi-note">Transaksi pegawai ini</div>
            </article>
            <article class="employee-kpi kpi-units">
                <div class="employee-kpi-icon"><i class="fas fa-cubes"></i></div>
                <div class="employee-kpi-label">Unit terjual {{ $periodLabel }}</div>
                <div class="employee-kpi-value">{{ number_format((int) $periodItemsSold) }}</div>
                <div class="employee-kpi-note">Dari detail transaksi pegawai ini</div>
            </article>
            <a class="employee-kpi kpi-attention" href="{{ route('employees.manage', ['employee' => $employee, 'tab' => 'stock', 'period' => $analyticsPeriod]) }}">
                <div class="employee-kpi-icon"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="employee-kpi-label">Stok Perlu Perhatian</div>
                <div class="employee-kpi-value">{{ number_format($stockSummary->empty_stock_count + $stockSummary->low_stock_count) }}</div>
                <div class="employee-kpi-note">{{ $stockSummary->empty_stock_count }} kosong · {{ $stockSummary->low_stock_count }} rendah <span>Lihat stok <i class="fas fa-arrow-right"></i></span></div>
            </a>
        </div>

        <div class="employee-content-grid overview-grid">
            <div class="employee-card">
                <div class="employee-card-header">
                    <div>
                        <h2><i class="fas fa-chart-area"></i> Tren Pendapatan</h2>
                        <p>Nilai transaksi berdasarkan tanggal transaksi</p>
                    </div>
                    <span class="employee-period-label">{{ $periodLabel }}</span>
                </div>
                <div class="employee-card-body">
                    @include('employees._trend-chart', ['chartId' => 'overview-sales-chart'])
                </div>
            </div>

            <div class="employee-card">
                <div class="employee-card-header">
                    <div>
                        <h2><i class="fas fa-ranking-star"></i> Produk Terlaris</h2>
                        <p>{{ $periodLabel }} · berdasarkan unit</p>
                    </div>
                </div>
                <div class="employee-card-body compact-card-body">
                    @forelse($topProducts as $index => $product)
                        <div class="ranked-product">
                            <span class="product-rank">{{ $index + 1 }}</span>
                            <div class="ranked-product-copy">
                                <strong>{{ $product->name }}</strong>
                                <span>{{ $product->unit ?: 'Unit' }}</span>
                            </div>
                            <strong class="product-units">{{ number_format((int) $product->units_sold) }}</strong>
                        </div>
                    @empty
                        <div class="employee-empty compact-empty">
                            <i class="fas fa-basket-shopping"></i>
                            <strong>Belum ada penjualan pada periode ini</strong>
                            <span>Coba periode lain atau periksa kembali setelah transaksi tercatat.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="employee-card">
                <div class="employee-card-header">
                    <div>
                        <h2><i class="fas fa-box-open"></i> Kondisi Stok Pegawai</h2>
                        <p>Stok kosong = 0 · rendah 1–{{ $lowStockThreshold }}</p>
                    </div>
                    <a class="employee-card-link" href="{{ route('employees.manage', ['employee' => $employee, 'tab' => 'stock', 'period' => $analyticsPeriod]) }}">Kelola <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="employee-card-body compact-card-body">
                    @forelse($stockAttention as $product)
                        @php
                            $employeeStock = (int) ($product->employee_stock ?? 0);
                        @endphp
                        <div class="attention-product">
                            <div class="attention-product-copy">
                                <strong>{{ $product->name }}</strong>
                                <span>{{ $product->unit ?: 'Unit' }}</span>
                            </div>
                            <span class="stock-status {{ $employeeStock <= 0 ? 'status-empty' : 'status-low' }}">
                                {{ $employeeStock <= 0 ? 'Kosong' : 'Rendah' }} · {{ $employeeStock }}
                            </span>
                        </div>
                    @empty
                        <div class="employee-empty compact-empty">
                            <i class="fas fa-circle-check"></i>
                            <strong>Tidak ada stok pegawai yang kosong atau rendah</strong>
                            <span>Produk yang belum dialokasikan ditampilkan terpisah.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="employee-card">
                <div class="employee-card-header">
                    <div>
                        <h2><i class="fas fa-clock-rotate-left"></i> Transaksi Terbaru</h2>
                        <p>Aktivitas penjualan yang tercatat</p>
                    </div>
                    <button type="button" class="employee-card-link as-button" data-open-tab="sales">Semua transaksi <i class="fas fa-arrow-right"></i></button>
                </div>
                <div class="employee-table-wrap">
                    @if($recentSales->isNotEmpty())
                        <table class="data-table employee-table">
                            <thead><tr><th>Transaksi</th><th>Tanggal</th><th>Unit</th><th class="text-end">Pendapatan</th></tr></thead>
                            <tbody>
                                @foreach($recentSales as $sale)
                                    <tr>
                                        <td><strong>{{ $sale->invoice_number }}</strong><span class="table-subtext">{{ $sale->customer->name ?? 'Umum' }}</span></td>
                                        <td>{{ $sale->transaction_date?->format('d/m/Y H:i') ?? '—' }}</td>
                                        <td>{{ number_format((int) ($sale->sale_items_sum_quantity ?? 0)) }}</td>
                                        <td class="text-end">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="employee-empty">
                            <i class="fas fa-receipt"></i>
                            <strong>Belum ada transaksi penjualan</strong>
                            <span>Transaksi baru akan muncul di sini setelah tercatat.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="employee-tab-panel {{ $activeTab === 'stock' ? 'active' : '' }}" id="tab-stock" role="tabpanel" aria-labelledby="tab-button-stock" tabindex="0">
        <div class="stock-overview">
            <div class="stock-total"><span>Stok pegawai</span><strong>{{ number_format((int) $stockSummary->employee_stock_total) }} <small>unit</small></strong></div>
            <div class="stock-total"><span>Stok gudang</span><strong>{{ number_format((int) $stockSummary->master_stock_total) }} <small>unit</small></strong></div>
            <div class="stock-total stock-total-unallocated"><span>Belum dialokasikan</span><strong>{{ number_format((int) $stockSummary->unallocated_stock_count) }}</strong></div>
            <div class="stock-total stock-total-empty"><span>Produk kosong</span><strong>{{ number_format((int) $stockSummary->empty_stock_count) }}</strong></div>
            <div class="stock-total stock-total-low"><span>Produk rendah</span><strong>{{ number_format((int) $stockSummary->low_stock_count) }}</strong></div>
            <div class="stock-total stock-total-normal"><span>Produk normal</span><strong>{{ number_format((int) $stockSummary->normal_stock_count) }}</strong></div>
        </div>

        <div class="employee-card">
            <div class="employee-card-header stock-card-header">
                <div>
                    <h2><i class="fas fa-boxes-stacked"></i> Alokasi & Pemantauan Stok</h2>
                    <p>Stok gudang dan pegawai ditampilkan terpisah. Stok rendah pegawai: 1–{{ $lowStockThreshold }} unit.</p>
                </div>
                <span class="stock-result-count">{{ $products->total() }} produk</span>
            </div>
            <form method="GET" action="{{ route('employees.manage', $employee) }}" class="stock-toolbar">
                <input type="hidden" name="tab" value="stock">
                <input type="hidden" name="period" value="{{ $analyticsPeriod }}">
                <label class="stock-search">
                    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                    <span class="visually-hidden">Cari produk atau kategori</span>
                    <input type="search" name="stock_search" value="{{ $stockSearch }}" placeholder="Cari produk atau kategori...">
                </label>
                <label class="stock-status-filter">
                    <span class="visually-hidden">Filter kondisi stok pegawai</span>
                    <select name="stock_status">
                        <option value="">Semua kondisi stok</option>
                        <option value="unallocated" {{ $stockStatus === 'unallocated' ? 'selected' : '' }}>Belum dialokasikan</option>
                        <option value="empty" {{ $stockStatus === 'empty' ? 'selected' : '' }}>Kosong</option>
                        <option value="low" {{ $stockStatus === 'low' ? 'selected' : '' }}>Rendah (1–{{ $lowStockThreshold }})</option>
                        <option value="normal" {{ $stockStatus === 'normal' ? 'selected' : '' }}>Normal (&gt; {{ $lowStockThreshold }})</option>
                    </select>
                </label>
                <button type="submit" class="btn-add"><i class="fas fa-filter"></i> Terapkan</button>
                @if($stockSearch !== '' || $stockStatus !== '')
                    <a href="{{ route('employees.manage', ['employee' => $employee, 'tab' => 'stock', 'period' => $analyticsPeriod]) }}" class="stock-reset">Reset</a>
                @endif
            </form>

            <div class="employee-table-wrap">
                @if($products->isNotEmpty())
                    <table class="data-table employee-table stock-table">
                        <thead>
                            <tr><th>Produk</th><th>Kategori</th><th>Stok gudang</th><th>Stok pegawai</th><th>Jumlah transfer</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                @php
                                    $isEmployeeStockAllocated = $product->employee_stock !== null;
                                    $employeeStock = (int) ($product->employee_stock ?? 0);
                                    $masterStock = (int) $product->stock;
                                @endphp
                                <tr id="row-{{ $product->id }}">
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        <span class="table-subtext">{{ $product->unit ?: 'Unit' }}</span>
                                    </td>
                                    <td>{{ $product->category->name ?? 'Tanpa kategori' }}</td>
                                    <td>
                                        <span class="stock-balance master-balance" id="master-stock-{{ $product->id }}">{{ number_format($masterStock) }}</span>
                                        @if($masterStock <= 0)
                                            <span class="stock-status status-empty">Kosong</span>
                                        @elseif($masterStock <= $lowStockThreshold)
                                            <span class="stock-status status-low">Rendah</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="stock-balance employee-balance" id="emp-stock-{{ $product->id }}">{{ $isEmployeeStockAllocated ? number_format($employeeStock) : '—' }}</span>
                                        @if(!$isEmployeeStockAllocated)
                                            <span class="stock-status status-unallocated" id="employee-stock-status-{{ $product->id }}">Belum dialokasikan</span>
                                        @elseif($employeeStock <= 0)
                                            <span class="stock-status status-empty" id="employee-stock-status-{{ $product->id }}">Kosong</span>
                                        @elseif($employeeStock <= $lowStockThreshold)
                                            <span class="stock-status status-low" id="employee-stock-status-{{ $product->id }}">Rendah</span>
                                        @else
                                            <span class="stock-status status-normal" id="employee-stock-status-{{ $product->id }}">Normal</span>
                                        @endif
                                    </td>
                                    <td><input type="number" id="amount-{{ $product->id }}" class="stock-quantity" min="1" step="1" value="1" aria-label="Jumlah transfer {{ $product->name }}"></td>
                                    <td>
                                        <div class="stock-actions">
                                            <button type="button" class="btn-stock btn-transfer" id="btn-give-{{ $product->id }}" data-product-id="{{ $product->id }}" data-action="push" {{ $masterStock <= 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-arrow-right"></i> Berikan
                                            </button>
                                            <button type="button" class="btn-edit btn-transfer" id="btn-pull-{{ $product->id }}" data-product-id="{{ $product->id }}" data-action="pull" {{ $employeeStock <= 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-arrow-left"></i> Tarik
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="employee-empty">
                        <i class="fas fa-box-open"></i>
                        <strong>{{ $stockSearch !== '' || $stockStatus !== '' ? 'Tidak ada produk yang cocok' : 'Belum ada produk' }}</strong>
                        <span>{{ $stockSearch !== '' || $stockStatus !== '' ? 'Ubah kata pencarian atau filter kondisi stok.' : 'Produk akan tampil di sini setelah tersedia di katalog.' }}</span>
                    </div>
                @endif
            </div>
            @if($products->hasPages())
                <div class="employee-pagination">
                    <span>Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ $products->total() }}</span>
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </section>

    <section class="employee-tab-panel {{ $activeTab === 'sales' ? 'active' : '' }}" id="tab-sales" role="tabpanel" aria-labelledby="tab-button-sales" tabindex="0">
        <div class="sales-period-heading">
            <div>
                <h2>Analisis Penjualan</h2>
                <p>{{ $periodLabel }}{{ $periodStart && $periodEnd ? ' · ' . $periodStart->format('d/m/Y') . '–' . $periodEnd->format('d/m/Y') : '' }} · tanggal transaksi pegawai ini</p>
            </div>
        </div>

        <div class="employee-kpis sales-kpis">
            <article class="employee-kpi kpi-revenue">
                <div class="employee-kpi-icon"><i class="fas fa-wallet"></i></div>
                <div class="employee-kpi-label">Pendapatan {{ $periodLabel }}</div>
                <div class="employee-kpi-value">Rp {{ number_format((float) $periodTotals->revenue, 0, ',', '.') }}</div>
                <div class="employee-kpi-note">Transaksi pegawai ini</div>
            </article>
            <article class="employee-kpi kpi-transactions">
                <div class="employee-kpi-icon"><i class="fas fa-receipt"></i></div>
                <div class="employee-kpi-label">Transaksi {{ $periodLabel }}</div>
                <div class="employee-kpi-value">{{ number_format((int) $periodTotals->transactions) }}</div>
                <div class="employee-kpi-note">Transaksi pegawai ini</div>
            </article>
            <article class="employee-kpi kpi-units">
                <div class="employee-kpi-icon"><i class="fas fa-cubes"></i></div>
                <div class="employee-kpi-label">Unit terjual {{ $periodLabel }}</div>
                <div class="employee-kpi-value">{{ number_format((int) $periodItemsSold) }}</div>
                <div class="employee-kpi-note">Detail transaksi pegawai ini</div>
            </article>
            <article class="employee-kpi kpi-month">
                <div class="employee-kpi-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="employee-kpi-label">Cakupan analisis</div>
                <div class="employee-kpi-value">{{ $periodLabel }}</div>
                <div class="employee-kpi-note">Filter global navbar</div>
            </article>
        </div>

        <div class="employee-card sales-trend-card">
            <div class="employee-card-header">
                <div><h2><i class="fas fa-chart-area"></i> Tren Pendapatan</h2><p>{{ $periodLabel }} · transaksi pegawai ini</p></div>
            </div>
            <div class="employee-card-body">
                @include('employees._trend-chart', ['chartId' => 'sales-tab-chart'])
            </div>
        </div>

        <div class="employee-card sales-table-card">
            <div class="employee-card-header">
                <div>
                    <h2><i class="fas fa-list"></i> Transaksi</h2>
                    <p>{{ $sales->total() }} transaksi pada periode yang dipilih</p>
                </div>
            </div>
            <div class="employee-table-wrap">
                @if($sales->isNotEmpty())
                    <table class="data-table employee-table">
                        <thead><tr><th>Invoice</th><th>Pelanggan</th><th>Tanggal transaksi</th><th>Metode</th><th>Unit</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td><strong>{{ $sale->invoice_number }}</strong></td>
                                    <td>{{ $sale->customer->name ?? 'Umum' }}</td>
                                    <td>{{ $sale->transaction_date?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>{{ ucfirst($sale->payment_method ?? '—') }}</td>
                                    <td>{{ number_format((int) ($sale->sale_items_sum_quantity ?? 0)) }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="employee-empty">
                        <i class="fas fa-chart-line"></i>
                        <strong>Tidak ada transaksi pada periode ini</strong>
                        <span>Coba periode lain atau kembali setelah penjualan tercatat.</span>
                    </div>
                @endif
            </div>
            @if($sales->hasPages())
                <div class="employee-pagination">
                    <span>Menampilkan {{ $sales->firstItem() }}–{{ $sales->lastItem() }} dari {{ $sales->total() }}</span>
                    {{ $sales->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </section>

    <div id="stock-toast" class="employee-toast" role="status" aria-live="polite">
        <span class="employee-toast-icon"><i class="fas fa-check" id="toast-icon-el"></i></span>
        <span><strong id="toast-title"></strong><small id="toast-msg"></small></span>
    </div>
</div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/employees/stock.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = [...document.querySelectorAll('.employee-tab')];
            const panels = [...document.querySelectorAll('.employee-tab-panel')];
            const chartInstances = new Map();
            const activateTab = (name) => {
                const activeName = ['overview', 'stock', 'sales'].includes(name) ? name : 'overview';
                tabs.forEach((tab) => {
                    const active = tab.dataset.tab === activeName;
                    tab.classList.toggle('active', active);
                    tab.setAttribute('aria-selected', String(active));
                });
                panels.forEach((panel) => panel.classList.toggle('active', panel.id === `tab-${activeName}`));
                window.requestAnimationFrame(() => chartInstances.get(activeName)?.resize());
            };

            tabs.forEach((tab) => tab.addEventListener('click', () => {
                activateTab(tab.dataset.tab);
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab.dataset.tab);
                window.history.replaceState({}, '', url);
            }));
            document.querySelectorAll('[data-open-tab]').forEach((link) => link.addEventListener('click', () => {
                const tab = link.dataset.openTab;
                activateTab(tab);
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
            }));

            const currency = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
            const chartData = document.querySelector('.employee-manage').dataset;
            const trendLabels = JSON.parse(chartData.trendLabels);
            const trendRevenue = JSON.parse(chartData.trendRevenue);
            const trendFullLabels = JSON.parse(chartData.trendFullLabels);
            ['overview-sales-chart', 'sales-tab-chart'].forEach((chartId) => {
                const canvas = document.getElementById(chartId);
                if (!canvas) return;
                if (typeof Chart === 'undefined') {
                    canvas.insertAdjacentHTML('afterend', '<p class="chart-error" role="alert">Grafik tidak dapat dimuat. Periksa koneksi lalu muat ulang halaman.</p>');
                    canvas.remove();
                    return;
                }
                const chart = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: trendLabels,
                        datasets: [{
                            label: 'Pendapatan',
                            data: trendRevenue,
                            borderColor: '#6B7B35',
                            backgroundColor: 'transparent',
                            pointBackgroundColor: '#6B7B35',
                            pointRadius: 2,
                            pointHoverRadius: 4,
                            fill: false,
                            tension: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: (items) => trendFullLabels[items[0].dataIndex],
                                    label: (item) => `Pendapatan: ${currency.format(item.parsed.y)}`
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { maxTicksLimit: 8, autoSkip: true, color: '#434D27' } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#434D27',
                                    callback: (value) => value >= 1000000
                                        ? `Rp ${(value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`
                                        : value >= 1000
                                            ? `Rp ${(value / 1000).toLocaleString('id-ID', { maximumFractionDigits: 0 })} rb`
                                            : currency.format(value)
                                },
                                grid: { color: 'rgba(67,77,39,.18)' }
                            }
                        }
                    }
                });
                chartInstances.set(chartId === 'overview-sales-chart' ? 'overview' : 'sales', chart);
            });

            document.querySelectorAll('.stock-quantity').forEach((input) => {
                input.addEventListener('input', () => {
                    input.setCustomValidity(input.value !== '' && Number(input.value) > 0 && Number.isInteger(Number(input.value)) ? '' : 'Masukkan jumlah unit bulat minimal 1.');
                });
            });
        });
    </script>
@endsection
