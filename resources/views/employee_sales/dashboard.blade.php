{{-- ====================================
Dashboard Penjualan Pegawai - SIMKEU
==================================== --}}
@extends('layouts.app')

@section('title', 'Dashboard Penjualan - ' . $employee->name . ' - SIMKEU')
@section('page-title', 'Dashboard Penjualan Pegawai')
@section('breadcrumb', 'Pegawai / ' . $employee->name . ' / Dashboard Penjualan')

@section('css')
    <style>
        /* ============================================================
           KPI CARDS
        ============================================================ */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* Grid 4 kolom untuk desktop */
            gap: 18px;
            margin-bottom: 28px;
        }

        /* Responsif untuk layar tablet */
        @media (max-width: 1200px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr); /* 2 kolom di tablet */
            }
        }

        /* Responsif untuk layar mobile */
        @media (max-width: 768px) {
            .kpi-grid {
                grid-template-columns: 1fr; /* 1 kolom di mobile */
            }
        }

        .kpi-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 22px 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .kpi-card.primary::before {
            background: var(--primary);
        }

        .kpi-card.success::before {
            background: var(--success);
        }

        .kpi-card.warning::before {
            background: var(--warning);
        }

        .kpi-card.danger::before {
            background: var(--danger);
        }

        .kpi-card.accent::before {
            background: var(--accent);
        }

        .kpi-card.indigo::before {
            background: #8A9C46;
        }

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

        .kpi-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .kpi-card.primary .kpi-icon {
            background: rgba(107,123,53,.12);
            color: var(--primary);
        }

        .kpi-card.success .kpi-icon {
            background: rgba(107,123,53,.12);
            color: var(--success);
        }

        .kpi-card.warning .kpi-icon {
            background: rgba(167,183,100,.12);
            color: var(--warning);
        }

        .kpi-card.danger .kpi-icon {
            background: rgba(58,66,37,.12);
            color: var(--danger);
        }

        .kpi-card.accent .kpi-icon {
            background: rgba(138,156,70,.12);
            color: var(--accent);
        }

        .kpi-card.indigo .kpi-icon {
            background: rgba(138,156,70,.12);
            color: #8A9C46;
        }

        .kpi-value {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 6px;
        }

        .kpi-sub {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* ============================================================
           SECTION TITLE
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

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ============================================================
           CONTENT CARDS (charts / tables / activity)
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
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .content-card-body {
            padding: 20px;
        }

        /* ============================================================
           DATA TABLE
        ============================================================ */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.825rem;
        }

        .data-table thead th {
            padding: 12px 16px;
            background: #F7F8ED;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
            text-align: left;
        }

        .data-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
        }

        .data-table tbody tr:hover {
            background: #F7F8ED;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: var(--text-muted);
            color: #F7F8ED;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: var(--text-primary);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .employee-info {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 20px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .employee-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F7F8ED;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .employee-details h3 {
            margin: 0 0 4px 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .employee-details p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .role-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .role-pegawai {
            background: rgba(138,156,70,0.12);
            color: #8A9C46;
        }

        .role-admin {
            background: rgba(58,66,37,0.12);
            color: #3A4225;
        }
    </style>
@endsection

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('employees.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pegawai
    </a>
</div>

<div class="employee-info">
    <div class="employee-avatar">
        {{ strtoupper(substr($employee->name, 0, 1)) }}
    </div>
    <div class="employee-details">
        <h3>{{ $employee->name }}</h3>
        <p>{{ $employee->email }}</p>
        <span class="role-badge role-{{ $employee->role }}">{{ $employee->role }}</span>
    </div>
</div>

{{-- KPI Cards --}}
<div class="kpi-grid">
    <div class="kpi-card primary">
        <div class="kpi-top">
            <div class="kpi-label">Penjualan {{ $periodLabel }}</div>
            <div class="kpi-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
        <div class="kpi-value">{{ number_format($totalSales) }}</div>
        <div class="kpi-sub">Transaksi pegawai ini</div>
    </div>

    <div class="kpi-card success">
        <div class="kpi-top">
            <div class="kpi-label">Pendapatan {{ $periodLabel }}</div>
            <div class="kpi-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
        <div class="kpi-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        <div class="kpi-sub">Transaksi pegawai ini</div>
    </div>

    <div class="kpi-card warning">
        <div class="kpi-top">
            <div class="kpi-label">Item terjual {{ $periodLabel }}</div>
            <div class="kpi-icon">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
        <div class="kpi-value">{{ number_format($totalItemsSold) }}</div>
        <div class="kpi-sub">Detail transaksi pegawai ini</div>
    </div>

    <div class="kpi-card accent">
        <div class="kpi-top">
            <div class="kpi-label">Periode Aktif</div>
            <div class="kpi-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
        <div class="kpi-value">{{ $periodLabel }}</div>
        <div class="kpi-sub">{{ $periodStart && $periodEnd ? $periodStart->format('d/m/Y') . '–' . $periodEnd->format('d/m/Y') : 'Seluruh riwayat transaksi' }}</div>
    </div>
</div>

{{-- Sales Table --}}
<div class="content-card">
    <div class="content-card-header">
        <div class="content-card-title">
            <i class="fas fa-table-list"></i> Riwayat Penjualan · {{ $periodLabel }}
        </div>
    </div>
    <div class="content-card-body">
        @if($sales->count() > 0)
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Tanggal</th>
                            <th width="20%">Customer</th>
                            <th width="20%">Produk</th>
                            <th width="10%">Qty</th>
                            <th width="15%">Total</th>
                            <th width="15%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr>
                                <td>{{ $loop->iteration + ($sales->currentPage()-1)*$sales->perPage() }}</td>
                                <td>{{ $sale->transaction_date?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>{{ $sale->customer ? $sale->customer->name : 'N/A' }}</td>
                                <td>
                                    @if($sale->saleItems->count() > 0)
                                        {{ $sale->saleItems->first()->product->name }}
                                        @if($sale->saleItems->count() > 1)
                                            <small style="color: var(--text-muted);">+{{ $sale->saleItems->count() - 1 }} lainnya</small>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $sale->saleItems->sum('quantity') }}</td>
                                <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                <td>
                                    <span class="role-badge role-pegawai">Selesai</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: center;">
                {{ $sales->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 40px 20px;">
                <div style="font-size: 3rem; color: var(--text-muted); margin-bottom: 12px;">
                    <i class="fas fa-inbox"></i>
                </div>
                <div style="color: var(--text-muted); font-size: 0.9rem;">
                    Belum ada penjualan untuk pegawai ini
                </div>
            </div>
        @endif
    </div>
</div>
@endsection