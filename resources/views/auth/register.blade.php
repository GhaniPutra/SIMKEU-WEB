{{-- ====================================
    Halaman Register - SIMKEU
    Extends dari layout utama app.blade.php
    ==================================== --}}
@extends('layouts.app')

{{-- Title halaman --}}
@section('title', 'Register - SIMKEU')

{{-- CSS tambahan untuk halaman register --}}

@section('css')
    <style>
        /* Dashboard-aligned fullscreen register - matches login */
        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 24px;
            background: linear-gradient(135deg, var(--body-bg) 0%, rgba(var(--primary-rgb, 79 70 229), 0.04) 100%);
        }

        .register-box {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            padding: 48px;
            width: 100%;
            max-width: 480px;
            position: relative;
            overflow: hidden;
        }
        .register-box::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .register-header {
            text-align: center;
            margin-bottom: 36px;
        }
        .register-header h1 {
            color: var(--text-primary);
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .register-header h1 i {
            color: var(--primary);
            font-size: 2rem;
        }
        .register-header p {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Form fields */
        .mb-3 {
            margin-bottom: 20px;
        }
        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-control {
            padding: 16px 20px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all var(--transition);
            background: var(--body-bg);
            font-weight: 500;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(var(--primary-rgb, 79 70 229), 0.1);
            background: white;
            transform: translateY(-1px);
        }
        .invalid-feedback {
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: 6px;
        }

        /* Helper text */
        .form-text {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 8px;
            font-style: italic;
        }

        /* Submit button - dashboard style */
        .btn-register {
            padding: 16px 24px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border: none;
            border-radius: var(--radius);
            color: white;
            font-weight: 700;
            font-size: 1.05rem;
            transition: all var(--transition);
            width: 100%;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        .btn-register i {
            margin-right: 8px;
        }

        /* Links */
        .login-link {
            text-align: center;
            margin-top: 28px;
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        .login-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: color var(--transition);
        }
        .login-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Password view/hide toggle wrapper */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .password-wrapper .form-control {
            padding-right: 52px;
            width: 100%;
        }
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            font-size: 1.15rem;
            transition: all var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .password-toggle:hover {
            color: var(--primary);
            transform: translateY(-50%) scale(1.08);
        }
        .password-toggle:focus {
            outline: none;
        }

        /* Container override */
        main .container { padding: 0; max-width: 100%; }

        /* Responsive */
        @media (max-width: 576px) {
            .register-box { margin: 16px; padding: 32px 24px; }
            .register-header h1 { font-size: 1.875rem; }
        }
    </style>
@endsection


{{-- Content halaman --}}
@section('content')
    {{-- Jika sudah login, jangan tampilkan halaman register --}}
    @auth
        <div class="alert alert-info">
            Anda sudah login. <a href="{{ route('dashboard') }}">Ke Dashboard</a>
        </div>
    @else
        {{-- Container untuk register form --}}
        <div class="register-container">
            <div class="register-box">
                {{-- Header register --}}
                <div class="register-header">
                    <h1><i class="fas fa-user-plus"></i> Daftar</h1>
                    <p>Buat akun SIMKEU baru</p>
                </div>

                {{-- Form register --}}
                <form action="{{ route('register') }}" method="POST">
                    {{-- CSRF Token untuk keamanan form --}}
                    @csrf

                    {{-- Field Nama --}}
                    <div class="mb-3">
                        {{-- Label nama --}}
                        <label for="name" class="form-label">
                            <i class="fas fa-user"></i> Nama Lengkap
                        </label>

                        {{-- Input nama --}}
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               placeholder="Masukkan nama lengkap Anda"
                               required>

                        {{-- Tampilkan error nama jika ada --}}
                        @error('name')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Field Email --}}
                    <div class="mb-3">
                        {{-- Label email --}}
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>

                        {{-- Input email --}}
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               placeholder="Masukkan email Anda"
                               required>

                        {{-- Tampilkan error email jika ada --}}
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                        {{-- Helper text --}}
                        <small class="form-text">Gunakan email yang valid dan belum terdaftar</small>
                    </div>

                    {{-- tidak ada field toko/cabang karena semua user adalah admin utama --}}
                    {{-- jika nanti diperlukan, dapat ditambahkan kembali --}}
                    {{-- Field Password --}}
                    <div class="mb-3">
                        {{-- Label password --}}
                        <label for="password" class="form-label">
                            <i class="fas fa-key"></i> Password
                        </label>

                        {{-- Input password dengan toggle lihat password --}}
                        <div class="password-wrapper">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Masukkan password"
                                   required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password', this)" title="Lihat/Sembunyikan password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        {{-- Tampilkan error password jika ada --}}
                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                        {{-- Helper text --}}
                        <small class="form-text">Minimal 6 karakter</small>
                    </div>

                    {{-- Field Konfirmasi Password --}}
                    <div class="mb-3">
                        {{-- Label konfirmasi password --}}
                        <label for="password_confirmation" class="form-label">
                            <i class="fas fa-check-circle"></i> Konfirmasi Password
                        </label>

                        {{-- Input konfirmasi password dengan toggle lihat password --}}
                        <div class="password-wrapper">
                            <input type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Ketik ulang password Anda"
                                   required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password_confirmation', this)" title="Lihat/Sembunyikan password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        {{-- Tampilkan error password_confirmation jika ada --}}
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                        {{-- Helper text --}}
                        <small class="form-text">Password harus sama dengan field di atas</small>
                    </div>

                    {{-- Tombol Register --}}
                    <button type="submit" class="btn btn-register">
                        <i class="fas fa-user-plus"></i> Daftar Akun
                    </button>
                </form>

                {{-- Link Login --}}
                <div class="login-link">
                    Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
                </div>
            </div>
        </div>

        {{-- Hapus padding container bawaan karena register-container sudah fullscreen --}}
        <style>
            main .container {
                padding: 0;
            }
        </style>
    @endauth
@endsection

@section('js')
    <script>
        function togglePasswordVisibility(inputId, toggleBtn) {
            const passwordInput = document.getElementById(inputId);
            const icon = toggleBtn.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
@endsection
