@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok - SIMKEU')
@section('page-title', 'Riwayat Pergerakan Stok')
@section('breadcrumb', 'Beranda / Distribusi Stok / Riwayat Pergerakan Stok')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stock_allocations/history.css') }}?v=9">
@endsection

@section('content')
    <section class="distribution-history content-card" aria-labelledby="distribution-history-title">
        <header class="distribution-history__header">
            <div class="distribution-history__heading">
                <span class="distribution-history__icon" aria-hidden="true"><i class="fas fa-truck-fast"></i></span>
                <h2 id="distribution-history-title">Riwayat Pergerakan Stok</h2>
            </div>
            <span class="distribution-history__count">{{ number_format($allocations->total()) }} data</span>
        </header>

        <form method="GET" action="{{ route('stock-allocations.history') }}" class="distribution-history__filters">
            <label class="distribution-history__field">
                <span>Pegawai</span>
                <select name="employee_id">
                    <option value="">Semua pegawai</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) ($filters['employee_id'] ?? '') === (string) $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="distribution-history__field">
                <span>Jenis pergerakan</span>
                <select name="type">
                    <option value="">Semua jenis</option>
                    <option value="allocation" @selected(($filters['type'] ?? '') === 'allocation')>Alokasi</option>
                    <option value="manual_withdrawal" @selected(($filters['type'] ?? '') === 'manual_withdrawal')>Penarikan manual</option>
                    <option value="auto_return" @selected(($filters['type'] ?? '') === 'auto_return')>Pengembalian otomatis</option>
                </select>
            </label>
            <label class="distribution-history__field">
                <span>Status</span>
                <select name="status">
                    <option value="">Semua status</option>
                    @foreach([
                        'pending' => 'Menunggu konfirmasi',
                        'accepted' => 'Diterima',
                        'needs_review' => 'Perlu tindak lanjut',
                        'declined' => 'Dilaporkan pegawai',
                        'cancelled' => 'Dibatalkan',
                        'completed' => 'Selesai',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="distribution-history__field">
                <span>Dari tanggal</span>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
            </label>
            <label class="distribution-history__field">
                <span>Sampai tanggal</span>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
            </label>
            <button type="submit" class="distribution-history__apply"><i class="fas fa-filter" aria-hidden="true"></i> Terapkan</button>
            @if(collect($filters)->filter()->isNotEmpty())
                <a class="distribution-history__reset" href="{{ route('stock-allocations.history') }}">Reset</a>
            @endif
        </form>

        <div class="distribution-history__table-wrap">
            <table class="distribution-history__table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pegawai</th>
                        <th>Produk</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Pelaku</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allocations as $allocation)
                        @php
                            $status = match ($allocation->status) {
                                'pending' => 'Menunggu konfirmasi',
                                'accepted' => 'Diterima',
                                'needs_review' => 'Perlu tindak lanjut',
                                'declined' => 'Dilaporkan pegawai',
                                'cancelled' => 'Dibatalkan',
                                'completed' => 'Selesai',
                                default => $allocation->status,
                            };
                            $typeLabel = match ($allocation->movement_type) {
                                'manual_withdrawal' => 'Penarikan manual',
                                'auto_return' => 'Pengembalian otomatis',
                                default => 'Alokasi',
                            };
                        @endphp
                        <tr>
                            <td>{{ $allocation->occurred_at ? \Illuminate\Support\Carbon::parse($allocation->occurred_at, 'UTC')->timezone('Asia/Jakarta')->format('d M Y, H:i') : '—' }}</td>
                            <td>{{ $allocation->employee_name ?? 'Pegawai tidak tersedia' }}</td>
                            <td>{{ $allocation->product_name ?? 'Produk tidak tersedia' }}</td>
                            <td>{{ $typeLabel }}</td>
                            <td>{{ number_format((int) $allocation->quantity) }} {{ $allocation->product_unit }}</td>
                            <td><span class="distribution-history__badge distribution-history__badge--{{ $allocation->status }} status-color" data-status-color="{{ $allocation->status }}">{{ $status }}</span></td>
                            <td>{{ $allocation->actor_name ?? ($allocation->movement_type === 'auto_return' ? 'Sistem' : '—') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="distribution-history__empty">Belum ada riwayat pergerakan stok yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($allocations->hasPages())
            <div class="distribution-history__pagination">{{ $allocations->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
@endsection
