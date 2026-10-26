@extends('layouts.app') {{-- Gunakan layout utama aplikasi --}}

{{-- Halaman daftar pegawai dan manajemen pegawai --}}
@section('title', 'Pegawai - SIMKEU') {{-- Judul laman di tab browser --}}
@section('page-title', 'Manajemen Pegawai') {{-- Judul utama halaman --}}
@section('breadcrumb', 'Pengaturan / Pegawai') {{-- Jejak navigasi breadcrumb --}}

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employees/index.css') }}?v=3"> {{-- Muat stylesheet khusus halaman index pegawai --}}
@endsection

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/employees/form.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('css/settings/profile.css') }}?v=13">
@endsection

@section('content') {{-- Mulai konten halaman utama --}}
{{-- Statistik ringkas pegawai --}}
<div class="kpi-grid" data-realtime-fragment="employees-kpis" data-realtime-resource="employees"> {{-- Grid untuk kartu statistik --}}
    <div class="kpi-card primary fade-up"> {{-- Kartu total user --}}
        <div class="kpi-top"> {{-- Baris atas kartu --}}
            <span class="kpi-icon"><i class="fas fa-users"></i></span>
            <span class="kpi-label">Total User</span> {{-- Label metrik --}}
        </div>
        <div class="kpi-value">{{ $totalUsers }}</div> {{-- Angka total user --}}
        <div class="kpi-sub">Jumlah total akun terdaftar</div> {{-- Deskripsi singkat --}}
    </div>
    <div class="kpi-card success fade-up"> {{-- Kartu jumlah pegawai --}}
        <div class="kpi-top"> {{-- Baris atas kartu --}}
            <span class="kpi-icon"><i class="fas fa-user-tie"></i></span>
            <span class="kpi-label">Pegawai</span>
        </div>
        <div class="kpi-value">{{ $totalPegawai }}</div>
        <div class="kpi-sub">Jumlah pegawai aktif</div>
    </div>
    <div class="kpi-card danger fade-up"> {{-- Kartu jumlah admin --}}
        <div class="kpi-top"> {{-- Baris atas kartu --}}
            <span class="kpi-icon"><i class="fas fa-user-shield"></i></span>
            <span class="kpi-label">Pemilik</span>
        </div>
        <div class="kpi-value">{{ $totalAdmin }}</div>
        <div class="kpi-sub">Jumlah akun admin</div>
    </div>
</div>

<p class="section-title"><i class="fas fa-table-list" style="color:var(--icon-primary)"></i> Daftar Pegawai</p> {{-- Judul section daftar pegawai --}}

<div class="content-card"> {{-- Kartu utama konten daftar pegawai --}}
    <div class="content-card-header"> {{-- Header kartu dengan judul dan tombol --}}
        <div class="content-card-title"><i class="fas fa-table-list"></i> Daftar Pegawai</div> {{-- Judul tabel --}}
        @if(Auth::user()->hasPermission('employees.create'))<button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#employeeCreateModal">
            <i class="fas fa-plus" aria-hidden="true"></i> Tambah Pegawai
        </button>@endif
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
                <option value="owner" @selected($roleFilter === 'owner')>Pemilik</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected((string) $roleFilter === (string) $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-add"> {{-- Tombol submit filter --}}
                <i class="fas fa-filter"></i> Filter
            </button>

            @if(!empty($search) || !empty($roleFilter)) {{-- Tampilkan reset saat filter aktif --}}
                <a href="{{ route('employees.index') }}" class="btn-add btn-reset reset-button"> {{-- Tombol reset filter --}}
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
                            <th width="15%" class="role-column">Role</th>
                            <th width="25%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody data-realtime-fragment="employees-table" data-realtime-resource="employees">
                        @foreach($employees as $employee) {{-- Iterasi setiap pegawai --}}
                            <tr class="employee-row-clickable" tabindex="0"
                                aria-label="{{ $employee->role_name }}: {{ $employee->name }}"
                                data-href="{{ $employee->isOwner() ? route('employees.index') : (Auth::user()->hasPermission('employees.sales.view') ? route('employees.manage', $employee) : (Auth::user()->hasPermission('employees.update') ? route('employees.edit', $employee) : '#')) }}">
                                <td>{{ $loop->iteration + ($employees->currentPage()-1)*$employees->perPage() }}</td> {{-- Nomor urut tabel --}}
                                <td>
                                    <div class="employee-table-person">
                                        <span class="employee-table-avatar" aria-hidden="true">
                                            @if($employee->avatar)
                                                <img src="{{ $employee->avatar }}" alt="" loading="lazy">
                                            @else
                                                {{ mb_strtoupper(mb_substr($employee->name, 0, 1)) }}
                                            @endif
                                        </span>
                                        <strong>{{ $employee->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $employee->email }}</td> {{-- Email pegawai --}}
                                <td class="role-column">
                                    <span class="role-badge role-{{ $employee->role }}">{{ $employee->role_name }}</span>
                                </td>
                                <td>
                                    <div class="table-actions"> {{-- Tombol aksi baris --}}
                                        @if(!$employee->isOwner() && Auth::user()->hasPermission('employees.update'))<a href="{{ route('employees.edit', $employee) }}" class="btn-edit">
                                            <i class="fas fa-edit" aria-hidden="true"></i> Edit
                                        </a>@endif
                                        @if(!$employee->isOwner() && Auth::user()->hasPermission('employees.delete'))<form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline-form delete-form">
                                            @csrf {{-- Token CSRF untuk keamanan --}}
                                            @method('DELETE') {{-- Metode DELETE untuk resource --}}
                                            <button type="submit" class="btn-delete"> {{-- Tombol hapus --}}
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>@endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else {{-- Jika tidak ada pegawai, tampilkan state kosong --}}
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                <div class="empty-state-text">Belum ada pegawai terdaftar</div>
                @if(Auth::user()->hasPermission('employees.create'))<button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#employeeCreateModal">
                    <i class="fas fa-plus" aria-hidden="true"></i> Tambah Pegawai Pertama
                </button>@endif
            </div>
        @endif
    </div>

    @if($employees->hasPages())
        <div class="pagination-wrapper"> {{-- Navigasi pagination --}}
            <span class="pagination-info">
                Menampilkan {{ $employees->firstItem() }}–{{ $employees->lastItem() }}
                dari {{ $employees->total() }} pegawai
            </span>
            {{ $employees->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@if(Auth::user()->hasPermission('employees.create'))
    <div class="modal fade employee-create-modal" id="employeeCreateModal" tabindex="-1" aria-labelledby="employee-create-title" aria-hidden="true">
        <div class="modal-dialog employee-create-drawer">
            <div class="modal-content">
                <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <header class="employee-create-modal-header">
                        <div><span class="employee-page-eyebrow">AKUN BARU</span><h2 id="employee-create-title">Tambah pegawai</h2><p>Buat akun dan atur akses pegawai.</p></div>
                        <button type="button" class="employee-modal-close" data-bs-dismiss="modal" aria-label="Tutup"><i class="fas fa-xmark" aria-hidden="true"></i></button>
                    </header>
                    <div class="modal-body employee-create-modal-body">
                        <div class="employee-form-page employee-create-modal-form">
                            @php($employee = null)
                            @include('employees._form')
                        </div>
                    </div>
                    <footer class="employee-create-modal-footer">
                        <button type="button" class="employee-button employee-button-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="employee-button employee-button-primary"><i class="fas fa-user-plus" aria-hidden="true"></i> Buat akun pegawai</button>
                    </footer>
                </form>
            </div>
        </div>
    </div>
    <div class="employee-cropper-host">@include('settings._image_cropper')</div>
@endif

@endsection

@section('js')
    <script src="{{ asset('js/employees/index.js') }}"></script> {{-- Muat skrip khusus halaman index pegawai --}}
    @if(Auth::user()->hasPermission('employees.create'))
        <script src="{{ asset('js/settings/image-cropper.js') }}?v=6"></script>
        <script src="{{ asset('js/employees/form.js') }}?v=1"></script>
    @endif
@endsection
