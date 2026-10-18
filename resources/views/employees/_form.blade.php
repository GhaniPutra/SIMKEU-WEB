{{-- Tampilkan ringkasan kesalahan validasi jika ada --}}
@if ($errors->any())
    <div class="validation-errors">
        <div class="errors-header">
            <i class="fas fa-circle-exclamation error-pulse"></i>
            <span>Ada {{ count($errors) }} kesalahan pada formulir Anda:</span>
        </div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Token CSRF untuk keamanan form Laravel --}}
@csrf

<div class="form-sections-container">
    {{-- Seksi 1: Informasi Pribadi --}}
    <div class="form-card-section">
        <h2 class="section-subtitle">
            <i class="fas fa-user-tie"></i>
            Informasi Pribadi
        </h2>
        <p class="section-desc">Atur data profil utama dan role akses pegawai.</p>
        
        {{-- Field nama pegawai --}}
        <div class="form-group">
            <label for="name" class="form-label">Nama Pegawai</label>
            <div class="input-with-icon">
                <i class="fas fa-user field-icon"></i>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $employee->name ?? '') }}"
                    placeholder="Masukkan nama lengkap pegawai"
                    class="form-control @error('name') is-invalid @enderror"
                    required
                    autocomplete="name"
                >
            </div>
            @error('name')
                <span class="form-error"><i class="fas fa-circle-info"></i> {{ $message }}</span>
            @enderror
        </div>

        {{-- Field email pegawai --}}
        <div class="form-group">
            <label for="email" class="form-label">Alamat Email</label>
            <div class="input-with-icon">
                <i class="fas fa-envelope field-icon"></i>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $employee->email ?? '') }}"
                    placeholder="pegawai@example.com"
                    class="form-control @error('email') is-invalid @enderror"
                    required
                    autocomplete="email"
                >
            </div>
            @error('email')
                <span class="form-error"><i class="fas fa-circle-info"></i> {{ $message }}</span>
            @enderror
        </div>

        {{-- Pilih role untuk user baru atau edit user --}}
        <div class="form-group">
            <label for="role" class="form-label">Role Akses</label>
            <div class="input-with-icon">
                <i class="fas fa-user-shield field-icon"></i>
                <select
                    id="role_id"
                    name="role_id"
                    class="form-control @error('role_id') is-invalid @enderror"
                    required
                >
                    <option value="">-- Pilih Role Akses --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) old('role_id', $employee->role_id ?? '') === (string) $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            @error('role_id')
                <span class="form-error"><i class="fas fa-circle-info"></i> {{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Seksi 2: Keamanan Akun --}}
    <div class="form-card-section bg-gradient-subtle">
        <h2 class="section-subtitle">
            <i class="fas fa-key"></i>
            Keamanan Akun
        </h2>
        <p class="section-desc">
            @if(isset($employee))
                Biarkan kolom ini kosong jika tidak ingin mengubah password pegawai.
            @else
                Atur kata sandi awal untuk akun login pegawai baru.
            @endif
        </p>

        {{-- Field password untuk akun pegawai --}}
        <div class="form-group">
            <label for="password" class="form-label">
                Password Baru
            </label>
            <div class="input-with-icon password-wrapper">
                <i class="fas fa-key field-icon"></i>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Minimal 8 karakter"
                    class="form-control @error('password') is-invalid @enderror"
                    {{ isset($employee) ? '' : 'required' }}
                    autocomplete="new-password"
                >
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password', this)" title="Lihat/Sembunyikan password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <span class="form-error"><i class="fas fa-circle-info"></i> {{ $message }}</span>
            @enderror
            <small class="form-text-info">Password minimal harus terdiri dari 8 karakter.</small>
        </div>

        {{-- Field konfirmasi password untuk memastikan input benar --}}
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <div class="input-with-icon password-wrapper">
                <i class="fas fa-lock field-icon"></i>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Ketik ulang password"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    {{ isset($employee) ? '' : 'required' }}
                    autocomplete="new-password"
                >
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password_confirmation', this)" title="Lihat/Sembunyikan password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('password_confirmation')
                <span class="form-error"><i class="fas fa-circle-info"></i> {{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
