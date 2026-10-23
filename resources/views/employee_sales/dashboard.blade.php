{{-- ====================================
Dashboard Penjualan Pegawai - SIMKEU
==================================== --}}
@extends('layouts.app')

@section('title', 'Dashboard Penjualan - ' . $employee->name . ' - SIMKEU')
@section('page-title', 'Dashboard Penjualan Pegawai')
@section('breadcrumb', 'Pegawai / ' . $employee->name . ' / Dashboard Penjualan')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employee_sales/dashboard.css') }}">
@endsection

@section('content')
{{-- Navigasi kembali ke daftar pegawai. --}}
<div class="dashboard-back-wrapper">
    <a href="{{ route('employees.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pegawai
    </a>
</div>

{{-- Identitas pegawai yang sedang dilihat. --}}
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

{{-- Kartu KPI: ringkasan penjualan dan pendapatan pegawai. --}}
<div class="kpi-grid">
    <div class="kpi-card primary">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-cart-shopping"></i></span>
            <div class="kpi-label">Penjualan {{ $periodLabel }}</div>
        </div>
        <div class="kpi-value">{{ number_format($totalSales) }}</div>
        <div class="kpi-sub">Transaksi pegawai ini</div>
    </div>

    <div class="kpi-card success">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-wallet"></i></span>
            <div class="kpi-label">Pendapatan {{ $periodLabel }}</div>
        </div>
        <div class="kpi-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        <div class="kpi-sub">Transaksi pegawai ini</div>
    </div>

    <div class="kpi-card warning">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-boxes-stacked"></i></span>
            <div class="kpi-label">Item terjual {{ $periodLabel }}</div>
        </div>
        <div class="kpi-value">{{ number_format($totalItemsSold) }}</div>
        <div class="kpi-sub">Detail transaksi pegawai ini</div>
    </div>

    <div class="kpi-card accent">
        <div class="kpi-top">
            <span class="kpi-icon"><i class="fas fa-calendar-days"></i></span>
            <div class="kpi-label">Periode Aktif</div>
        </div>
        <div class="kpi-value">{{ $periodLabel }}</div>
        <div class="kpi-sub">{{ $periodStart && $periodEnd ? $periodStart->format('d/m/Y') . '–' . $periodEnd->format('d/m/Y') : 'Seluruh riwayat transaksi' }}</div>
    </div>
</div>

{{-- Tabel riwayat penjualan pegawai pada periode terpilih. --}}
<div class="content-card">
    <div class="content-card-header">
        <div class="content-card-title">
            <i class="fas fa-table-list"></i> Riwayat Penjualan · {{ $periodLabel }}
        </div>
    </div>
    <div class="content-card-body">
        @if($sales->count() > 0)
            <div class="sales-table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="column-number">#</th>
                            <th class="column-date">Tanggal</th>
                            <th class="column-customer">Pelanggan</th>
                            <th class="column-product">Produk</th>
                            <th class="column-quantity">Qty</th>
                            <th class="column-total">Total</th>
                            <th class="column-status">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr>
                                <td>{{ $loop->iteration + ($sales->currentPage()-1)*$sales->perPage() }}</td>
                                <td>{{ $sale->transaction_date?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>Umum</td>
                                <td>
                                    @if($sale->saleItems->count() > 0)
                                        {{ $sale->saleItems->first()->product->name }}
                                        @if($sale->saleItems->count() > 1)
                                            <small class="sales-item-more">+{{ $sale->saleItems->count() - 1 }} lainnya</small>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $sale->saleItems->sum('quantity') }}</td>
                                <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                <td>
                                    <span class="role-badge status-complete status-color" data-status-color="completed">Selesai</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Navigasi halaman riwayat penjualan. --}}
            <div class="sales-pagination">
                {{ $sales->links() }}
            </div>
        @else
            {{-- Pesan ketika pegawai belum memiliki transaksi penjualan. --}}
            <div class="sales-empty-state">
                <div class="sales-empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="sales-empty-message">
                    Belum ada penjualan untuk pegawai ini
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
