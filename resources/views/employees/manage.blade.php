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
    data-pending-allocations-url="{{ route('employees.stock.allocations.pending-status') }}"
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
                <div class="employee-kpi-label"><span class="employee-kpi-icon"><i class="fas fa-wallet"></i></span>Pendapatan {{ $periodLabel }}</div>
                <div class="employee-kpi-value">Rp {{ number_format((float) $periodTotals->revenue, 0, ',', '.') }}</div>
                <div class="employee-kpi-note">Transaksi pegawai ini</div>
            </article>
            <article class="employee-kpi kpi-transactions">
                <div class="employee-kpi-label"><span class="employee-kpi-icon"><i class="fas fa-receipt"></i></span>Transaksi {{ $periodLabel }}</div>
                <div class="employee-kpi-value">{{ number_format((int) $periodTotals->transactions) }}</div>
                <div class="employee-kpi-note">Transaksi pegawai ini</div>
            </article>
            <article class="employee-kpi kpi-units">
                <div class="employee-kpi-label"><span class="employee-kpi-icon"><i class="fas fa-boxes-stacked"></i></span>Unit terjual {{ $periodLabel }}</div>
                <div class="employee-kpi-value">{{ number_format((int) $periodItemsSold) }}</div>
                <div class="employee-kpi-note">Dari detail transaksi pegawai ini</div>
            </article>
            <a class="employee-kpi kpi-attention" href="{{ route('employees.manage', ['employee' => $employee, 'tab' => 'stock', 'period' => $analyticsPeriod]) }}">
                <div class="employee-kpi-label"><span class="employee-kpi-icon"><i class="fas fa-triangle-exclamation"></i></span>Stok Perlu Perhatian</div>
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
                            <span class="stock-status status-color {{ $employeeStock <= 0 ? 'status-empty' : 'status-low' }}" data-status-color="{{ $employeeStock <= 0 ? 'empty' : 'low' }}">
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
                                        <td><strong>{{ $sale->invoice_number }}</strong></td>
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
        @if(session('success'))
            <div class="employee-card" role="status"><div class="employee-card-body status-text" data-status-color="success">{{ session('success') }}</div></div>
        @endif
        @if($allocationsNeedingReview->isNotEmpty())
            <div class="employee-card allocation-review-card">
                <div class="employee-card-header allocation-review-header">
                    <div class="allocation-review-heading">
                        <span class="allocation-review-icon" aria-hidden="true"><i class="fas fa-triangle-exclamation"></i></span>
                        <div>
                            <h2>Perlu ditindaklanjuti</h2>
                            <p>Periksa jumlah yang dilaporkan sebelum memilih tindakan.</p>
                        </div>
                    </div>
                    <button type="button" class="allocation-review-toggle" aria-expanded="true" aria-controls="allocation-review-list-{{ $employee->id }}" aria-label="Sembunyikan daftar laporan">
                        <span class="stock-result-count"><i class="far fa-file-alt" aria-hidden="true"></i> {{ $allocationsNeedingReview->count() }} laporan</span>
                        <i class="fas fa-chevron-up allocation-review-chevron" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="employee-card-body" id="allocation-review-list-{{ $employee->id }}">
                    @foreach($allocationsNeedingReview as $allocation)
                        @php
                            $requested = (int) $allocation->requested_quantity;
                            $actual = (int) ($allocation->actual_received_quantity ?? 0);
                            $shortfall = max(0, $requested - $actual);
                            $difference = $actual - $requested;
                            $reason = trim((string) $allocation->decline_reason);
                            $hasReason = preg_match('/[\p{L}\p{N}]/u', $reason) === 1;
                            $product = $allocation->product;
                            $unit = $product?->unit ?: 'unit';
                            $cancelFormId = 'cancel-allocation-' . $allocation->id;
                        @endphp
                        <article class="allocation-review-item">
                            <div class="allocation-review-product">
                                <span class="allocation-product-thumb" aria-hidden="true">
                                    @if($product?->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="">
                                    @else
                                        <i class="fas fa-mug-hot"></i>
                                    @endif
                                </span>
                                <div class="allocation-product-copy">
                                    <strong>{{ $product?->name ?? 'Produk' }}</strong>
                                    @if($product?->category?->name)
                                        <span class="allocation-category-pill"><i class="fas fa-tag" aria-hidden="true"></i> {{ $product->category->name }}</span>
                                    @endif
                                    <span class="allocation-review-reason">{{ $hasReason ? $reason : 'Laporan selisih stok pegawai' }}</span>
                                </div>
                            </div>
                            <div class="allocation-review-counts" aria-label="Jumlah alokasi dan diterima">
                                <div class="allocation-review-count">
                                    <span><i class="fas fa-box" aria-hidden="true"></i> Alokasi</span>
                                    <strong>{{ number_format($requested) }}</strong>
                                    <small>{{ $unit }}</small>
                                </div>
                                <i class="fas fa-arrow-right allocation-review-arrow" aria-hidden="true"></i>
                                <div class="allocation-review-count">
                                    <span><i class="far fa-calendar-check" aria-hidden="true"></i> Diterima</span>
                                    <strong>{{ number_format($actual) }}</strong>
                                    <small>{{ $unit }}</small>
                                </div>
                                @if($difference !== 0)
                                    <span class="allocation-review-difference {{ $difference > 0 ? 'is-extra' : 'is-short' }}" title="Selisih {{ $difference > 0 ? '+' : '' }}{{ number_format($difference) }} {{ $unit }}" aria-label="Selisih {{ $difference > 0 ? '+' : '' }}{{ number_format($difference) }} {{ $unit }}">
                                        {{ $difference > 0 ? '+' : '' }}{{ number_format($difference) }}
                                    </span>
                                @endif
                            </div>
                            <div class="allocation-review-actions">
                                <div class="allocation-return-field">
                                    <label for="returned-quantity-{{ $allocation->id }}">Dikembalikan</label>
                                    <span class="allocation-return-stepper">
                                        <button type="button" data-return-step="-1" aria-label="Kurangi jumlah dikembalikan">−</button>
                                        <input id="returned-quantity-{{ $allocation->id }}" type="number" class="stock-quantity" name="returned_quantity" form="{{ $cancelFormId }}" min="0" max="{{ $actual }}" value="{{ $actual }}" step="1" required data-return-quantity>
                                        <button type="button" data-return-step="1" aria-label="Tambah jumlah dikembalikan">+</button>
                                    </span>
                                </div>
                                @if($actual > 0)
                                    <form class="allocation-review-action-form allocation-accept-form" method="POST" action="{{ route('employees.stock.allocations.follow-up', [$employee, $allocation]) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="accept_actual">
                                        <button type="submit" class="btn-stock" title="{{ $actual > $requested ? 'Selisih +' . number_format($difference) . ' akan mengurangi stok gudang.' : 'Pegawai perlu mengonfirmasi jumlah ini di aplikasi.' }}">
                                            <i class="fas fa-check" aria-hidden="true"></i> Setujui {{ number_format($actual) }}
                                        </button>
                                    </form>
                                @endif
                                <form id="{{ $cancelFormId }}" class="allocation-review-action-form allocation-cancel-form" method="POST" action="{{ route('employees.stock.allocations.follow-up', [$employee, $allocation]) }}" onsubmit="return confirm('Batalkan alokasi? Pastikan stok yang diterima dan dicatat sebagai dikembalikan sudah benar.');">
                                    @csrf
                                    <input type="hidden" name="action" value="cancel">
                                    <button type="submit" class="btn-delete">
                                        <i class="fas fa-ban" aria-hidden="true"></i> Batalkan
                                    </button>
                                </form>
                                @if($shortfall > 0)
                                    <form class="allocation-review-action-form allocation-resend-form" method="POST" action="{{ route('employees.stock.allocations.follow-up', [$employee, $allocation]) }}" onsubmit="return confirm('Pastikan pengiriman susulan {{ $shortfall }} {{ $unit }} sudah disiapkan. Pegawai akan diminta mengonfirmasi penerimaan di aplikasi.');">
                                        @csrf
                                        <input type="hidden" name="action" value="resend">
                                        <button type="submit" class="btn-edit">
                                            <i class="fas fa-truck-fast" aria-hidden="true"></i> Kirim {{ number_format($shortfall) }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        @php
            $emptyStockCount = (int) $stockSummary->empty_stock_count;
            $lowStockCount = (int) $stockSummary->low_stock_count;
            $normalStockCount = (int) $stockSummary->normal_stock_count;
            $allocatedProductCount = $emptyStockCount + $lowStockCount + $normalStockCount;
            $stockIssueCount = $emptyStockCount + $lowStockCount;
            $emptyStockPercent = $allocatedProductCount > 0 ? ($emptyStockCount / $allocatedProductCount) * 100 : 0;
            $lowStockPercent = $allocatedProductCount > 0 ? ($lowStockCount / $allocatedProductCount) * 100 : 0;
            $normalStockPercent = $allocatedProductCount > 0 ? ($normalStockCount / $allocatedProductCount) * 100 : 0;
        @endphp
        <div class="stock-overview">
            <div class="stock-health-card">
                <div class="stock-health-heading">
                    <div>
                        <h2>Kondisi stok pegawai</h2>
                        <p>{{ number_format($allocatedProductCount) }} produk sudah dialokasikan</p>
                    </div>
                    <span class="stock-health-alert status-color {{ $stockIssueCount > 0 ? 'has-issues' : 'all-clear' }}" data-status-color="{{ $stockIssueCount > 0 ? 'warning' : 'success' }}">
                        <i class="fas fa-{{ $stockIssueCount > 0 ? 'triangle-exclamation' : 'circle-check' }}" aria-hidden="true"></i>
                        {{ number_format($stockIssueCount) }} perlu perhatian
                    </span>
                </div>

                @if($allocatedProductCount > 0)
                    <div class="stock-health-progress" role="img" aria-label="Komposisi stok pegawai: {{ $emptyStockCount }} kosong, {{ $lowStockCount }} rendah, {{ $normalStockCount }} cukup">
                        @if($emptyStockCount > 0)<span class="empty-segment" data-width="{{ $emptyStockPercent }}"></span>@endif
                        @if($lowStockCount > 0)<span class="low-segment" data-width="{{ $lowStockPercent }}"></span>@endif
                        @if($normalStockCount > 0)<span class="normal-segment" data-width="{{ $normalStockPercent }}"></span>@endif
                    </div>
                @else
                    <p class="stock-health-empty">Belum ada stok yang dialokasikan ke pegawai.</p>
                @endif

                <div class="stock-health-breakdown">
                    <a class="stock-health-item status-empty status-color" data-status-color="empty" href="{{ route('employees.manage', ['employee' => $employee, 'tab' => 'stock', 'period' => $analyticsPeriod, 'stock_status' => 'empty']) }}">
                        <span class="stock-health-item-label"><i class="fas fa-circle-xmark" aria-hidden="true"></i> Kosong</span>
                        <strong>{{ number_format($emptyStockCount) }} <small>produk</small></strong>
                    </a>
                    <a class="stock-health-item status-low status-color" data-status-color="low" href="{{ route('employees.manage', ['employee' => $employee, 'tab' => 'stock', 'period' => $analyticsPeriod, 'stock_status' => 'low']) }}">
                        <span class="stock-health-item-label"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i> Rendah</span>
                        <strong>{{ number_format($lowStockCount) }} <small>produk</small></strong>
                    </a>
                    <a class="stock-health-item status-normal status-color" data-status-color="normal" href="{{ route('employees.manage', ['employee' => $employee, 'tab' => 'stock', 'period' => $analyticsPeriod, 'stock_status' => 'normal']) }}">
                        <span class="stock-health-item-label"><i class="fas fa-circle-check" aria-hidden="true"></i> Cukup</span>
                        <strong>{{ number_format($normalStockCount) }} <small>produk</small></strong>
                    </a>
                </div>
            </div>

            <div class="stock-overview-metrics">
                <div class="stock-total">
                    <span><i class="fas fa-boxes-stacked" aria-hidden="true"></i> Stok pegawai</span>
                    <strong>{{ number_format((int) $stockSummary->employee_stock_total) }} <small>unit</small></strong>
                </div>
                <div class="stock-total">
                    <span><i class="fas fa-warehouse" aria-hidden="true"></i> Stok gudang</span>
                    <strong>{{ number_format((int) $stockSummary->master_stock_total) }} <small>unit</small></strong>
                </div>
                <div class="stock-total stock-total-unallocated">
                    <span><i class="fas fa-box-open" aria-hidden="true"></i> Belum dialokasikan</span>
                    <strong>{{ number_format((int) $stockSummary->unallocated_stock_count) }} <small>produk</small></strong>
                </div>
            </div>
        </div>

        <div class="employee-card">
            <div class="employee-card-header stock-card-header">
                <div>
                    <h2><i class="fas fa-boxes-stacked"></i> Saldo Stok Pegawai</h2>
                    <p>Saldo gudang dan saldo aktif pegawai ditampilkan terpisah. Alokasi baru menambah saldo pegawai setelah diterima; batas stok rendah: 1–{{ $lowStockThreshold }} unit.</p>
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
                    <a href="{{ route('employees.manage', ['employee' => $employee, 'tab' => 'stock', 'period' => $analyticsPeriod]) }}" class="stock-reset reset-button"><i class="fas fa-xmark" aria-hidden="true"></i> Reset</a>
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
                                        <span class="stock-balance master-balance" id="master-stock-{{ $product->id }}">
                                            <span data-stock-value>{{ number_format($masterStock) }}</span>
                                            <i class="fas fa-warehouse pending-allocation-icon"
                                               data-pending-allocation-icon
                                               data-product-id="{{ $product->id }}"
                                               title="Alokasi menunggu konfirmasi"
                                               aria-label="Alokasi menunggu konfirmasi"
                                               @if(!in_array((int) $product->id, $pendingProductIds, true)) hidden @endif></i>
                                        </span>
                                        @if($masterStock <= 0)
                                            <span class="stock-status status-empty status-color" data-status-color="empty">Kosong</span>
                                        @elseif($masterStock <= $lowStockThreshold)
                                            <span class="stock-status status-low status-color" data-status-color="low">Rendah</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="stock-balance employee-balance" id="emp-stock-{{ $product->id }}">
                                            <span data-stock-value>{{ $isEmployeeStockAllocated ? number_format($employeeStock) : '—' }}</span>
                                        </span>
                                        @if(!$isEmployeeStockAllocated)
                                            <span class="stock-status status-unallocated status-color" data-status-color="unallocated" id="employee-stock-status-{{ $product->id }}">Belum dialokasikan</span>
                                        @elseif($employeeStock <= 0)
                                            <span class="stock-status status-empty status-color" data-status-color="empty" id="employee-stock-status-{{ $product->id }}">Kosong</span>
                                        @elseif($employeeStock <= $lowStockThreshold)
                                            <span class="stock-status status-low status-color" data-status-color="low" id="employee-stock-status-{{ $product->id }}">Rendah</span>
                                        @else
                                            <span class="stock-status status-normal status-color" data-status-color="normal" id="employee-stock-status-{{ $product->id }}">Normal</span>
                                        @endif
                                    </td>
                                    <td><input type="number" id="amount-{{ $product->id }}" class="stock-quantity" min="1" step="1" value="1" aria-label="Jumlah transfer {{ $product->name }}"></td>
                                    <td>
                                        @if(auth()->user()?->hasPermission('stock_allocations.transfer'))
                                            <div class="stock-actions">
                                                <button type="button" class="btn-stock btn-transfer" id="btn-give-{{ $product->id }}" data-product-id="{{ $product->id }}" data-action="push" {{ $masterStock <= 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-arrow-right"></i> Berikan
                                                </button>
                                                <button type="button" class="btn-edit btn-transfer" id="btn-pull-{{ $product->id }}" data-product-id="{{ $product->id }}" data-action="pull" {{ $employeeStock <= 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-arrow-left"></i> Tarik
                                                </button>
                                            </div>
                                        @else
                                            <span class="table-subtext">Tidak memiliki akses transfer stok</span>
                                        @endif
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
                <p>{{ $periodLabel }}{{ $periodStart && $periodEnd ? ' · ' . $periodStart->format('d/m/Y') . '–' . $periodEnd->format('d/m/Y') : '' }} · tanggal transaksi pegawai ini.</p>
            </div>
        </div>

        <div class="employee-kpis sales-kpis">
            <article class="employee-kpi kpi-revenue">
                <div class="employee-kpi-label"><span class="employee-kpi-icon"><i class="fas fa-wallet"></i></span>Pendapatan {{ $periodLabel }}</div>
                <div class="employee-kpi-value">Rp {{ number_format((float) $periodTotals->revenue, 0, ',', '.') }}</div>
                <div class="employee-kpi-note">Transaksi pegawai ini</div>
            </article>
            <article class="employee-kpi kpi-transactions">
                <div class="employee-kpi-label"><span class="employee-kpi-icon"><i class="fas fa-receipt"></i></span>Transaksi {{ $periodLabel }}</div>
                <div class="employee-kpi-value">{{ number_format((int) $periodTotals->transactions) }}</div>
                <div class="employee-kpi-note">Transaksi pegawai ini</div>
            </article>
            <article class="employee-kpi kpi-units">
                <div class="employee-kpi-label"><span class="employee-kpi-icon"><i class="fas fa-boxes-stacked"></i></span>Unit terjual {{ $periodLabel }}</div>
                <div class="employee-kpi-value">{{ number_format((int) $periodItemsSold) }}</div>
                <div class="employee-kpi-note">Detail transaksi pegawai ini</div>
            </article>
            <article class="employee-kpi kpi-month">
                <div class="employee-kpi-label"><span class="employee-kpi-icon"><i class="fas fa-calendar-days"></i></span>Cakupan analisis</div>
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
                        <thead><tr><th>Invoice</th><th>Tanggal transaksi</th><th>Metode</th><th>Unit</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td><strong>{{ $sale->invoice_number }}</strong></td>
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

</div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/employees/stock.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Terapkan proporsi segmen stok dari data Blade tanpa CSS inline.
            document.querySelectorAll('.stock-health-progress [data-width]').forEach((segment) => {
                segment.style.width = `${segment.dataset.width}%`;
            });

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
            const employeeChartBlue = '#65b1f5';
            ['overview-sales-chart', 'sales-tab-chart'].forEach((chartId) => {
                const canvas = document.getElementById(chartId);
                if (!canvas) return;
                if (typeof Chart === 'undefined') {
                    canvas.insertAdjacentHTML('afterend', '<p class="chart-error" role="alert">Grafik tidak dapat dimuat. Periksa koneksi lalu muat ulang halaman.</p>');
                    canvas.remove();
                    return;
                }
                const context = canvas.getContext('2d');
                const areaGradient = context.createLinearGradient(0, 0, 0, 250);
                areaGradient.addColorStop(0, 'rgba(101, 177, 245, 0.28)');
                areaGradient.addColorStop(1, 'rgba(101, 177, 245, 0)');
                const chart = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: trendLabels,
                        datasets: [{
                            label: 'Pendapatan',
                            data: trendRevenue,
                            borderColor: employeeChartBlue,
                            borderWidth: 2,
                            backgroundColor: areaGradient,
                            pointBackgroundColor: employeeChartBlue,
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1,
                            pointRadius: 2.5,
                            pointHoverRadius: 4,
                            fill: true,
                            tension: 0.25
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
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: { maxTicksLimit: 8, autoSkip: true, color: '#8aa3b8' }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#8aa3b8',
                                    callback: (value) => value >= 1000000
                                        ? `Rp ${(value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`
                                        : value >= 1000
                                            ? `Rp ${(value / 1000).toLocaleString('id-ID', { maximumFractionDigits: 0 })} rb`
                                            : currency.format(value)
                                },
                                border: { display: false },
                                grid: { color: 'rgba(193,224,252,.35)' }
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
