@extends('layouts.app')

@section('title', 'Kelola Pegawai - ' . $employee->name . ' - SIMKEU')
@section('page-title', 'Kelola Pegawai')
@section('breadcrumb', 'Pegawai / ' . $employee->name . ' / Kelola')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employees/index.css') }}">
    <style>
        .employee-shell {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .employee-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 24px 28px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: linear-gradient(135deg, rgba(79,70,229,0.06), rgba(6,182,212,0.04));
        }

        .employee-meta {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .employee-avatar {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .employee-name {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .employee-sub {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .tab-nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 10px;
        }

        .tab-btn {
            border: 0;
            background: transparent;
            color: var(--text-muted);
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            background: rgba(79, 70, 229, 0.12);
            color: var(--primary);
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .summary-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .summary-card.primary::before { background: var(--primary); }
        .summary-card.success::before { background: var(--success); }
        .summary-card.warning::before { background: var(--warning); }
        .summary-card.danger::before { background: var(--danger); }

        .sc-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 1rem;
        }

        .summary-card.primary .sc-icon { background: rgba(79,70,229,0.12); color: var(--primary); }
        .summary-card.success .sc-icon { background: rgba(16,185,129,0.12); color: var(--success); }
        .summary-card.warning .sc-icon { background: rgba(245,158,11,0.12); color: var(--warning); }
        .summary-card.danger .sc-icon { background: rgba(239,68,68,0.12); color: var(--danger); }

        .sc-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .sc-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 6px;
        }

        .sc-note {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .panel-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .panel-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: #f8fafc;
        }

        .panel-card-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-card-body {
            padding: 20px;
        }

        .info-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .info-item {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #f8fafc;
        }

        .info-label {
            color: var(--text-muted);
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        @media (max-width: 900px) {
            .summary-grid,
            .info-list {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .summary-grid,
            .info-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
<div class="employee-shell">
    <div class="employee-header">
        <div class="employee-meta">
            <div class="employee-avatar"><i class="fas fa-user-tie"></i></div>
            <div>
                <h1 class="employee-name">{{ $employee->name }}</h1>
                <p class="employee-sub">{{ $employee->email }} · {{ $employee->role }}</p>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('employees.index') }}" class="btn-add" style="background:#e2e8f0;color:var(--text-primary);">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <nav class="tab-nav" aria-label="Tabs pegawai">
        <button type="button" class="tab-btn active" data-tab="overview">Ringkasan</button>
        <button type="button" class="tab-btn" data-tab="stock">Stok</button>
        <button type="button" class="tab-btn" data-tab="sales">Penjualan</button>
    </nav>

    <section class="tab-panel active" id="tab-overview">
        <div class="summary-grid">
            <div class="summary-card primary">
                <div class="sc-icon"><i class="fas fa-boxes"></i></div>
                <div class="sc-label">Stok Pegawai</div>
                <div class="sc-value">{{ $pageEmployeeStock }}</div>
                <div class="sc-note">Total unit stok yang dimiliki pegawai</div>
            </div>
            <div class="summary-card success">
                <div class="sc-icon"><i class="fas fa-warehouse"></i></div>
                <div class="sc-label">Stok Gudang</div>
                <div class="sc-value">{{ $pageMasterStock }}</div>
                <div class="sc-note">Total stok master yang tersedia</div>
            </div>
            <div class="summary-card warning">
                <div class="sc-icon"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="sc-label">Stok Rendah</div>
                <div class="sc-value">{{ $lowStockCount }}</div>
                <div class="sc-note">Produk dengan stok ≤ 10 unit</div>
            </div>
            <div class="summary-card danger">
                <div class="sc-icon"><i class="fas fa-cash-register"></i></div>
                <div class="sc-label">Pendapatan Bulan Ini</div>
                <div class="sc-value">Rp {{ number_format($currentMonthSales, 0, ',', '.') }}</div>
                <div class="sc-note">Ringkasan penjualan saat ini</div>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-card-header">
                <div class="panel-card-title"><i class="fas fa-circle-info"></i> Informasi Pegawai</div>
            </div>
            <div class="panel-card-body">
                <div class="info-list">
                    <div class="info-item">
                        <div class="info-label">Nama</div>
                        <div class="info-value">{{ $employee->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $employee->email }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Role</div>
                        <div class="info-value">{{ ucfirst($employee->role) }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Transaksi</div>
                        <div class="info-value">{{ $totalSales }} Transaksi</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="tab-panel" id="tab-stock">
        <div class="panel-card">
            <div class="panel-card-header">
                <div class="panel-card-title"><i class="fas fa-boxes-stacked"></i> Distribusi Stok</div>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary-subtle text-primary">Gudang: {{ $pageMasterStock }}</span>
                    <span class="badge bg-success-subtle text-success">Pegawai: {{ $pageEmployeeStock }}</span>
                </div>
            </div>
            <div class="panel-card-body">
                @if($products->count() > 0)
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Gudang</th>
                                <th>Pegawai</th>
                                <th>Transfer</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($products as $product)
                                @php
                                    $employeeProduct = $product->users->first();
                                    $employeeStock = $employeeProduct ? $employeeProduct->pivot->stock : 0;
                                    $isLow = $product->stock > 0 && $product->stock <= 10;
                                @endphp
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category ? $product->category->name : 'Tanpa kategori' }}</td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $product->stock }}</span>
                                        @if($isLow)
                                            <span class="badge bg-warning-subtle text-warning ms-1">Rendah</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-success-subtle text-success">{{ $employeeStock }}</span></td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="number" min="1" value="1" class="form-control form-control-sm" style="width:80px;" id="amount-{{ $product->id }}">
                                            <button type="button" class="btn-stock btn-transfer" data-product-id="{{ $product->id }}" data-action="push" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-arrow-right"></i> Berikan
                                            </button>
                                            <button type="button" class="btn-edit btn-transfer" data-product-id="{{ $product->id }}" data-action="pull" {{ $employeeStock <= 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-arrow-left"></i> Tarik
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($products->hasPages())
                        <div class="pagination-wrapper mt-3">
                            {{ $products->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-boxes-stacked"></i></div>
                        <div class="empty-state-text">Belum ada produk yang tersedia untuk distribusi.</div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="tab-panel" id="tab-sales">
        <div class="panel-card">
            <div class="panel-card-header">
                <div class="panel-card-title"><i class="fas fa-chart-line"></i> Riwayat Penjualan</div>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary-subtle text-primary">{{ $totalSales }} transaksi</span>
                    <span class="badge bg-success-subtle text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="panel-card-body">
                @if($sales->count() > 0)
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Tanggal</th>
                                <th>Item</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td>{{ $sale->invoice_number }}</td>
                                    <td>{{ $sale->customer ? $sale->customer->name : 'Umum' }}</td>
                                    <td>{{ $sale->transaction_date ? $sale->transaction_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $sale->saleItems->sum('quantity') }}</td>
                                    <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($sales->hasPages())
                        <div class="pagination-wrapper mt-3">
                            {{ $sales->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="empty-state-text">Belum ada penjualan untuk pegawai ini.</div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.tab-btn');
        const panels = document.querySelectorAll('.tab-panel');

        tabs.forEach((tab) => {
            tab.addEventListener('click', function () {
                const target = this.dataset.tab;

                tabs.forEach((btn) => btn.classList.toggle('active', btn === this));
                panels.forEach((panel) => {
                    panel.classList.toggle('active', panel.id === `tab-${target}`);
                });
            });
        });

        const transferButtons = document.querySelectorAll('.btn-transfer');
        transferButtons.forEach((button) => {
            button.addEventListener('click', async function () {
                const productId = this.dataset.productId;
                const action = this.dataset.action;
                const amountInput = document.getElementById(`amount-${productId}`);
                const amount = Number(amountInput?.value || 1);

                if (!productId || !Number.isFinite(amount) || amount <= 0) {
                    alert('Masukkan jumlah transfer yang valid.');
                    return;
                }

                const response = await fetch(`/employees/{{ $employee->id }}/stock/transfer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        product_id: Number(productId),
                        amount: action === 'pull' ? -amount : amount
                    })
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    alert(result.message || 'Transfer stok gagal.');
                    return;
                }

                window.location.reload();
            });
        });
    });
</script>
@endsection
