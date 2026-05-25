@extends('layouts.app')

{{-- Halaman daftar pegawai dan manajemen pegawai --}}
@section('title', 'Pegawai - SIMKEU')
@section('page-title', 'Manajemen Pegawai')
@section('breadcrumb', 'Pengaturan / Pegawai')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employees.css') }}">
@endsection

@section('content')
{{-- Statistik ringkas pegawai --}}
<div class="kpi-grid">
    <div class="kpi-card primary fade-up">
        <div class="kpi-top">
            <span class="kpi-label">Total User</span>
            <div class="kpi-icon"><i class="fas fa-users"></i></div>
        </div>
        <div class="kpi-value">{{ $totalUsers }}</div>
        <div class="kpi-sub">Jumlah total akun terdaftar</div>
    </div>
    <div class="kpi-card success fade-up">
        <div class="kpi-top">
            <span class="kpi-label">Pegawai</span>
            <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
        </div>
        <div class="kpi-value">{{ $totalPegawai }}</div>
        <div class="kpi-sub">Jumlah pegawai aktif</div>
    </div>
    <div class="kpi-card danger fade-up">
        <div class="kpi-top">
            <span class="kpi-label">Admin</span>
            <div class="kpi-icon"><i class="fas fa-user-shield"></i></div>
        </div>
        <div class="kpi-value">{{ $totalAdmin }}</div>
        <div class="kpi-sub">Jumlah akun admin</div>
    </div>
</div>

<p class="section-title"><i class="fas fa-table-list" style="color:var(--primary)"></i> Daftar Pegawai</p>

<div class="content-card">
    <div class="content-card-header">
        <div class="content-card-title"><i class="fas fa-table-list"></i> Daftar Pegawai</div>
        <a href="{{ route('employees.create') }}" class="btn-add">
            <i class="fas fa-plus"></i> Tambah Pegawai
        </a>
    </div>
    <div class="content-card-body">
        <form method="GET" action="{{ route('employees.index') }}" class="toolbar">
            <div class="toolbar-search">
                <i class="fas fa-magnifying-glass search-icon"></i>
                <input
                    type="text"
                    name="search"
                    placeholder="Cari nama atau email pegawai..."
                    value="{{ $search ?? '' }}"
                >
            </div>

            <select name="role">
                <option value="">Semua Role</option>
                <option value="pegawai" {{ (isset($roleFilter) && $roleFilter === 'pegawai') ? 'selected' : '' }}>Pegawai</option>
                <option value="admin" {{ (isset($roleFilter) && $roleFilter === 'admin') ? 'selected' : '' }}>Admin</option>
            </select>

            <button type="submit" class="btn-add">
                <i class="fas fa-filter"></i> Filter
            </button>

            @if(!empty($search) || !empty($roleFilter))
                <a href="{{ route('employees.index') }}" class="btn-add" style="background:#e2e8f0;color:var(--text-primary);">
                    <i class="fas fa-xmark"></i> Reset
                </a>
            @endif
        </form>

        @if($employees->count() > 0)
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Nama</th>
                            <th width="30%">Email</th>
                            <th width="15%">Role</th>
                            <th width="25%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td>{{ $loop->iteration + ($employees->currentPage()-1)*$employees->perPage() }}</td>
                                <td>
                                    @if($employee->role === 'pegawai')
                                        <a href="{{ route('employees.sales-dashboard', $employee) }}" class="employee-link">
                                            {{ $employee->name }}
                                        </a>
                                    @else
                                        <strong>{{ $employee->name }}</strong>
                                    @endif
                                </td>
                                <td>{{ $employee->email }}</td>
                                <td>
                                    <span class="role-badge role-{{ $employee->role }}">{{ $employee->role }}</span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        @if($employee->role === 'pegawai')
                                            <a href="{{ route('employees.stock', $employee) }}" class="btn-stock">
                                                <i class="fas fa-boxes"></i> Atur Stok
                                            </a>
                                        @endif
                                        <a href="{{ route('employees.edit', $employee) }}" class="btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline-form delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $employees->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                <div class="empty-state-text">Belum ada pegawai terdaftar</div>
                <a href="{{ route('employees.create') }}" class="btn-add">
                    <i class="fas fa-plus"></i> Tambah Pegawai Pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('js')
    <script src="{{ asset('js/employees.js') }}"></script>
@endsection
