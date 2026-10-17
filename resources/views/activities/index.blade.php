@extends('layouts.app')

@section('title', 'Semua Aktivitas - SIMKEU')
@section('page-title', 'Semua Aktivitas')
@section('breadcrumb', 'Beranda / Aktivitas & Data')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
    <div class="content-card">
        <div class="content-card-header">
            <span class="content-card-title"><i class="fas fa-clock-rotate-left"></i> Aktivitas & Transaksi</span>
            <span class="text-muted small">{{ $periodLabel }}</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Rincian</th>
                        <th>Dicatat oleh</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td>{{ $activity->formattedDate }}</td>
                            <td>
                                <span class="type-badge type-{{ $activity->activity_type === 'sale' ? 'revenue' : 'expense' }}">
                                    {{ $activity->activity_type === 'sale' ? 'Pemasukan' : 'Pengeluaran' }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $activity->title }}</strong>
                                @if ($activity->description)<div class="text-muted small">{{ $activity->description }}</div>@endif
                            </td>
                            <td>{{ $activity->actor }}</td>
                            <td>Rp {{ number_format($activity->amount, 0, ',', '.') }}</td>
                            <td><a href="{{ $activity->detailUrl }}" class="btn-detail"><i class="fas fa-eye fa-xs me-1"></i>Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Belum ada aktivitas pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($activities->hasPages())
            <div class="p-3">{{ $activities->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
