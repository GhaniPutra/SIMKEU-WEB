@extends('layouts.app')

@section('title', 'Riwayat Aktivitas - SIMKEU')
@section('page-title', 'Riwayat Aktivitas')
@section('breadcrumb', 'Beranda / Aktivitas & Data')

@section('css')
    {{-- Gunakan komponen kartu dan tabel bersama dari gaya dashboard. --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
@endsection

@section('content')
    <div class="content-card">
        <div class="content-card-header">
            <span class="content-card-title"><i class="fas fa-clock-rotate-left"></i> Aktivitas Operasional dan Keuangan</span>
            <span class="text-muted small">{{ $periodLabel }}</span>
        </div>
        <div class="dashboard-table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Rincian</th>
                        <th>Pegawai / dicatat oleh</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td>{{ $activity->formattedDate }}</td>
                            <td><span class="type-badge {{ $activity->typeClass }}">{{ $activity->typeLabel }}</span></td>
                            <td>
                                <strong>{{ $activity->title }}</strong>
                                @if ($activity->description)<div class="text-muted small">{{ $activity->description }}</div>@endif
                            </td>
                            <td>{{ $activity->actor ?? '—' }}</td>
                            <td>{{ $activity->formattedAmount }}</td>
                            <td>
                                @if($activity->detailUrl)
                                    <a href="{{ $activity->detailUrl }}" class="btn-detail"><i class="fas fa-eye fa-xs me-1"></i>Detail</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
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
