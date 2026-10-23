@extends('layouts.app')

@section('title', 'Role & Izin - SIMKEU')
@section('page-title', 'Role & Izin')
@section('breadcrumb', 'Pengaturan / Pegawai / Role & Izin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/roles/roles.css') }}">
@endsection

@section('content')
    @php
        $permissionLabels = collect(\App\Support\RolePermissions::groups())
            ->flatMap(fn ($group) => $group)
            ->all();
        $roleProfiles = \App\Support\RolePermissions::profiles();
        $assignedEmployees = $roles->sum('users_count');
        $rolesInUse = $roles->filter(fn ($role) => $role->users_count > 0)->count();
    @endphp

    <div class="roles-page">
        <section class="roles-intro">
            <div class="roles-intro-copy">
                <span class="roles-eyebrow"><i class="fas fa-shield-halved"></i> Pengaturan akses</span>
                <h1>Atur akses tim dengan mudah</h1>
                <p>Buat role sesuai tanggung jawab, lalu pilih fitur yang boleh digunakan setiap pegawai.</p>
            </div>
            <a href="{{ route('roles.create') }}" class="roles-primary-button">
                <i class="fas fa-plus" aria-hidden="true"></i>
                <span>Buat role</span>
            </a>
        </section>

        <section class="roles-stats" aria-label="Ringkasan role">
            <article class="roles-stat-card">
                <span class="roles-stat-icon roles-stat-icon-blue"><i class="fas fa-layer-group"></i></span>
                <div><span class="roles-stat-label">Total role</span><strong>{{ number_format($roles->count()) }}</strong></div>
            </article>
            <article class="roles-stat-card">
                <span class="roles-stat-icon roles-stat-icon-green"><i class="fas fa-user-check"></i></span>
                <div><span class="roles-stat-label">Pegawai dengan role</span><strong>{{ number_format($assignedEmployees) }}</strong></div>
            </article>
            <article class="roles-stat-card">
                <span class="roles-stat-icon roles-stat-icon-violet"><i class="fas fa-users-gear"></i></span>
                <div><span class="roles-stat-label">Role yang digunakan</span><strong>{{ number_format($rolesInUse) }}</strong></div>
            </article>
        </section>

        <section class="roles-list-section">
            <div class="roles-section-heading">
                <div>
                    <h2>Daftar role</h2>
                    <p>Atur hak akses dan lihat pegawai yang menggunakan setiap role.</p>
                </div>
                <span class="roles-count-badge">{{ number_format($roles->count()) }} role</span>
            </div>

            @if($roles->isNotEmpty())
                <div class="roles-grid">
                    @foreach($roles as $role)
                        @php
                            $profileKey = \App\Support\RolePermissions::profileFor($role->permissions ?? []);
                            $profile = $roleProfiles[$profileKey] ?? null;
                            $rolePermissions = \App\Support\RolePermissions::canonicalize($role->permissions ?? []);
                            $permissionCount = count(array_unique($rolePermissions));
                            $previewPermissions = collect($rolePermissions)
                                ->map(fn ($permission) => $permissionLabels[$permission] ?? null)
                                ->filter()
                                ->unique()
                                ->take(4);
                            $deleteDisabled = $role->is_system || $role->users_count > 0;
                        @endphp
                        <article class="role-card">
                            <div class="role-card-top">
                                <span class="role-card-icon"><i class="fas fa-user-shield"></i></span>
                                @if($role->is_system)
                                    <span class="role-status role-status-default status-color" data-status-color="default"><i class="fas fa-lock"></i> Bawaan</span>
                                @else
                                    <span class="role-status role-status-custom status-color" data-status-color="custom">Role khusus</span>
                                @endif
                            </div>

                            <div class="role-card-title-row">
                                <div>
                                    <h3>{{ $role->name }}</h3>
                                    <p>{{ $profile['description'] ?? 'Role dengan izin yang diatur khusus sesuai kebutuhan toko.' }}</p>
                                </div>
                            </div>

                            <div class="role-card-metrics">
                                <div><i class="fas fa-key"></i><span><strong>{{ $permissionCount }}</strong> izin aktif</span></div>
                                <div><i class="fas fa-users"></i><span><strong>{{ number_format($role->users_count) }}</strong> pegawai</span></div>
                            </div>

                            @if($previewPermissions->isNotEmpty())
                                <div class="role-permission-preview" aria-label="Contoh izin pada role">
                                    @foreach($previewPermissions as $permissionLabel)
                                        <span>{{ $permissionLabel }}</span>
                                    @endforeach
                                    @if($permissionCount > $previewPermissions->count())
                                        <span class="role-permission-more">+{{ $permissionCount - $previewPermissions->count() }} lainnya</span>
                                    @endif
                                </div>
                            @else
                                <div class="role-permission-empty"><i class="fas fa-circle-info"></i> Belum ada izin yang dipilih</div>
                            @endif

                            <div class="role-card-actions">
                                <a href="{{ route('roles.edit', $role) }}" class="role-edit-button">
                                    <i class="fas fa-pen-to-square"></i> Ubah role
                                </a>
                                <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Hapus role ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="role-delete-button" @disabled($deleteDisabled)
                                            title="{{ $role->is_system ? 'Role bawaan tidak dapat dihapus' : ($role->users_count > 0 ? 'Pindahkan pegawai dari role ini sebelum menghapus' : 'Hapus role') }}"
                                            aria-label="Hapus role {{ $role->name }}">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="roles-empty-state">
                    <span class="roles-empty-icon"><i class="fas fa-user-shield"></i></span>
                    <h3>Belum ada role</h3>
                    <p>Buat role pertama agar akses pegawai dapat diatur sesuai tanggung jawabnya.</p>
                    <a href="{{ route('roles.create') }}" class="roles-primary-button"><i class="fas fa-plus"></i> Buat role pertama</a>
                </div>
            @endif
        </section>

        <aside class="roles-owner-note">
            <span><i class="fas fa-circle-info"></i></span>
            <p><strong>Pemilik utama tidak memakai role pegawai</strong> dan selalu memiliki akses penuh. Role di halaman ini hanya mengatur akses akun pegawai. Ubah data diri atau data usaha melalui <a href="{{ route('profile.edit') }}">Profil & Usaha</a>. Role bawaan tidak dapat dihapus, tetapi izinnya bisa disesuaikan.</p>
        </aside>
    </div>
@endsection
