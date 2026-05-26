{{-- Tampilkan ringkasan kesalahan validasi jika ada --}}
@if ($errors->any())
    <div class="validation-errors">
        <p>{{ count($errors) }} Kesalahan Validasi:</p>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Token CSRF untuk keamanan form Laravel --}}
@csrf

{{-- Field nama pegawai --}}
<div class="form-group">
    <label for="name" class="form-label">Nama Pegawai</label>
    <input
        id="name"
        type="text"
        name="name"
        value="{{ old('name', $employee->name ?? '') }}"
        placeholder="Masukkan nama pegawai"
        class="form-control @error('name') is-invalid @enderror"
        required
        autocomplete="name"
    >
    @error('name')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

{{-- Field email pegawai --}}
<div class="form-group">
    <label for="email" class="form-label">Email</label>
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
    @error('email')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

{{-- Field password untuk akun pegawai --}}
<div class="form-group">
    <label for="password" class="form-label">
        Password {{ isset($employee) ? '(kosongkan jika tidak ingin mengubah)' : '' }}
    </label>
    <input
        id="password"
        type="password"
        name="password"
        placeholder="Minimal 8 karakter"
        class="form-control @error('password') is-invalid @enderror"
        {{ isset($employee) ? '' : 'required' }}
        autocomplete="new-password"
    >
    @error('password')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

{{-- Field konfirmasi password untuk memastikan input benar --}}
<div class="form-group">
    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
    <input
        id="password_confirmation"
        type="password"
        name="password_confirmation"
        placeholder="Ulangi password"
        class="form-control @error('password_confirmation') is-invalid @enderror"
        {{ isset($employee) ? '' : 'required' }}
        autocomplete="new-password"
    >
    @error('password_confirmation')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

{{-- Pilih role untuk user baru atau edit user --}}
<div class="form-group">
    <label class="form-label">Role</label>
    <select
        name="role"
        class="form-control @error('role') is-invalid @enderror"
        required
    >
        <option value="">-- Pilih Role --</option>
        <option value="pegawai" {{ old('role', $employee->role ?? '') === 'pegawai' ? 'selected' : '' }}>
            👤 Pegawai
        </option>
        <option value="admin" {{ old('role', $employee->role ?? '') === 'admin' ? 'selected' : '' }}>
            🔐 Admin
        </option>
    </select>
    @error('role')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>
