@extends('layouts.app') {{-- Gunakan layout utama aplikasi --}}

{{-- Halaman daftar pegawai dan manajemen pegawai --}}
@section('title', 'Pegawai - SIMKEU') {{-- Judul laman di tab browser --}}
@section('page-title', 'Manajemen Pegawai') {{-- Judul utama halaman --}}
@section('breadcrumb', 'Pengaturan / Pegawai') {{-- Jejak navigasi breadcrumb --}}

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employees/index.css') }}"> {{-- Muat stylesheet khusus halaman index pegawai --}}
@endsection

@section('content') {{-- Mulai konten halaman utama --}}
{{-- Statistik ringkas pegawai --}}
<div class="kpi-grid"> {{-- Grid untuk kartu statistik --}}
    <div class="kpi-card primary fade-up"> {{-- Kartu total user --}}
        <div class="kpi-top"> {{-- Baris atas kartu --}}
            <span class="kpi-label">Total User</span> {{-- Label metrik --}}
            <div class="kpi-icon"><i class="fas fa-users"></i></div> {{-- Ikon metrik --}}</div>
        <div class="kpi-value">{{ $totalUsers }}</div> {{-- Angka total user --}}
        <div class="kpi-sub">Jumlah total akun terdaftar</div> {{-- Deskripsi singkat --}}
    </div>
    <div class="kpi-card success fade-up"> {{-- Kartu jumlah pegawai --}}
        <div class="kpi-top"> {{-- Baris atas kartu --}}
            <span class="kpi-label">Pegawai</span>
            <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
        </div>
        <div class="kpi-value">{{ $totalPegawai }}</div>
        <div class="kpi-sub">Jumlah pegawai aktif</div>
    </div>
    <div class="kpi-card danger fade-up"> {{-- Kartu jumlah admin --}}
        <div class="kpi-top"> {{-- Baris atas kartu --}}
            <span class="kpi-label">Admin</span>
            <div class="kpi-icon"><i class="fas fa-user-shield"></i></div>
        </div>
        <div class="kpi-value">{{ $totalAdmin }}</div>
        <div class="kpi-sub">Jumlah akun admin</div>
    </div>
</div>

<p class="section-title"><i class="fas fa-table-list" style="color:var(--primary)"></i> Daftar Pegawai</p> {{-- Judul section daftar pegawai --}}

<div class="content-card"> {{-- Kartu utama konten daftar pegawai --}}
    <div class="content-card-header"> {{-- Header kartu dengan judul dan tombol --}}
        <div class="content-card-title"><i class="fas fa-table-list"></i> Daftar Pegawai</div> {{-- Judul tabel --}}
        <a href="{{ route('employees.create') }}" class="btn-add"> {{-- Tombol tambah pegawai --}}
            <i class="fas fa-plus"></i> Tambah Pegawai
        </a>
    </div>
    <div class="content-card-body"> {{-- Badan konten kartu --}}
        <form method="GET" action="{{ route('employees.index') }}" class="toolbar"> {{-- Form filter dan pencarian --}}
            <div class="toolbar-search"> {{-- Wrapper input pencarian --}}
                <i class="fas fa-magnifying-glass search-icon"></i> {{-- Ikon pencarian --}}
                <input
                    type="text"
                    name="search"
                    placeholder="Cari nama atau email pegawai..."
                    value="{{ $search ?? '' }}" {{-- Isi dengan nilai pencarian saat ini --}}
                >
            </div>

            <select name="role"> {{-- Dropdown filter role --}}
                <option value="">Semua Role</option>
                <option value="pegawai" {{ (isset($roleFilter) && $roleFilter === 'pegawai') ? 'selected' : '' }}>Pegawai</option>
                <option value="admin" {{ (isset($roleFilter) && $roleFilter === 'admin') ? 'selected' : '' }}>Admin</option>
            </select>

            <button type="submit" class="btn-add"> {{-- Tombol submit filter --}}
                <i class="fas fa-filter"></i> Filter
            </button>

            @if(!empty($search) || !empty($roleFilter)) {{-- Tampilkan reset saat filter aktif --}}
                <a href="{{ route('employees.index') }}" class="btn-add" style="background:#e2e8f0;color:var(--text-primary);"> {{-- Tombol reset filter --}}
                    <i class="fas fa-xmark"></i> Reset
                </a>
            @endif
        </form>

        @if($employees->count() > 0) {{-- Tampilkan tabel jika ada data pegawai --}}
            <div class="table-scroll"> {{-- Wrapper untuk tabel scroll horizontal --}}
                <table class="data-table"> {{-- Tabel daftar pegawai --}}
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
                        @foreach($employees as $employee) {{-- Iterasi setiap pegawai --}}
                            <tr>
                                <td>{{ $loop->iteration + ($employees->currentPage()-1)*$employees->perPage() }}</td> {{-- Nomor urut tabel --}}
                                <td>
                                    @if($employee->role === 'pegawai') {{-- Jika pegawai, buat link dashboard penjualan --}}
                                        <a href="{{ route('employees.sales-dashboard', $employee) }}" class="employee-link">
                                            {{ $employee->name }}
                                        </a>
                                    @else {{-- Jika bukan pegawai, tampilkan nama biasa --}}
                                        <strong>{{ $employee->name }}</strong>
                                    @endif
                                </td>
                                <td>{{ $employee->email }}</td> {{-- Email pegawai --}}
                                <td>
                                    <span class="role-badge role-{{ $employee->role }}">{{ $employee->role }}</span> {{-- Badge role --}}
                                </td>
                                <td>
                                    <div class="table-actions"> {{-- Tombol aksi baris --}}
                                        @if($employee->role === 'pegawai') {{-- Jika pegawai, tampilkan tombol atur stok --}}
                                            <a href="{{ route('employees.stock', $employee) }}" class="btn-stock">
                                                <i class="fas fa-boxes"></i> Atur Stok
                                            </a>
                                        @endif
                                        <a href="{{ route('employees.edit', $employee) }}" class="btn-edit"> {{-- Tombol edit --}}
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline-form delete-form"> {{-- Form hapus pegawai --}}
                                            @csrf {{-- Token CSRF untuk keamanan --}}
                                            @method('DELETE') {{-- Metode DELETE untuk resource --}}
                                            <button type="submit" class="btn-delete"> {{-- Tombol hapus --}}
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

            <div class="pagination-wrapper"> {{-- Navigasi pagination --}}
                {{ $employees->links() }}
            </div>
        @else {{-- Jika tidak ada pegawai, tampilkan state kosong --}}
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                <div class="empty-state-text">Belum ada pegawai terdaftar</div>
                <a href="{{ route('employees.create') }}" class="btn-add"> {{-- Tombol tambah pegawai pertama --}}
                    <i class="fas fa-plus"></i> Tambah Pegawai Pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('js')
    <script src="{{ asset('js/employees/index.js') }}"></script> {{-- Muat skrip khusus halaman index pegawai --}}
@endsection
