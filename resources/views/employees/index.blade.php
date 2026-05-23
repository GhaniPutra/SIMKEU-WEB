@extends('layouts.app')

{{-- Halaman daftar pegawai dan manajemen pegawai --}}
@section('title', 'Pegawai - SIMKEU')
@section('page-title', 'Manajemen Pegawai')
@section('breadcrumb', 'Pengaturan / Pegawai')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employees.css') }}">
@endsection

@section('content')
{{-- Header halaman pegawai dan tombol aksi utama --}}
<div class="employees-header">
    <div class="employees-title">
        <i class="fas fa-users employees-icon"></i>
        <h1>Manajemen Pegawai</h1>
    </div>
    <a href="{{ route('employees.create') }}" class="btn-add">
        <i class="fas fa-plus"></i> Tambah Pegawai
    </a>
</div>

{{-- Statistik ringkas pegawai --}}
<div class="employees-stats">
    <div class="stat-card">
        <div class="stat-card-value">{{ $totalUsers }}</div>
        <div class="stat-card-label">Total User</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-value">{{ $totalPegawai }}</div>
        <div class="stat-card-label">Pegawai</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-value">{{ $totalAdmin }}</div>
        <div class="stat-card-label">Admin</div>
    </div>
</div>

<div class="content-card">
    <div class="content-card-header">
        <div class="content-card-title"><i class="fas fa-table-list"></i> Daftar Pegawai</div>
    </div>
    <div class="content-card-body">
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
