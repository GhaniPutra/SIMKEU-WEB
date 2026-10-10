{{-- ====================================
    Halaman Login - SIMKEU
    Extends dari layout utama app.blade.php
    ==================================== --}}
@extends('layouts.app')

{{-- Title halaman --}}
@section('title', 'Login - SIMKEU')

{{-- CSS tambahan untuk halaman login --}}

@section('css')
    <style>
        /* Dashboard-aligned fullscreen login - clean/modern */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 24px;
            background: linear-gradient(135deg, var(--body-bg) 0%, rgba(var(--primary-rgb, 79 70 229), 0.04) 100%);
        }

        .login-box {
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
        .login-box::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .login-header {
            text-align: center;
            margin-bottom: 36px;
        }
        .login-header h1 {
            color: var(--text-primary);
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .login-header h1 i {
            color: var(--primary);
            font-size: 2rem;
        }
        .login-header p {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Form fields - dashboard form-control */
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

        /* Checkbox */
        .form-check {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        .form-check-input {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .form-check-label {
            color: var(--text-primary);
            font-weight: 500;
            cursor: pointer;
        }

        /* Submit button - btn-qa-primary dashboard style */
        .btn-login {
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
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        .btn-login i {
            margin-right: 8px;
        }

        /* Links */
        .register-link, .login-divider {
            text-align: center;
            margin-top: 28px;
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        .register-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: color var(--transition);
        }
        .register-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Divider */
        .login-divider {
            position: relative;
            font-weight: 500;
            margin: 32px 0;
        }
        .login-divider::before,
        .login-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: var(--border);
        }
        .login-divider::before { left: 0; }
        .login-divider::after { right: 0; }

        /* Input icons */
        .input-group-icon {
            position: relative;
        }
        .input-group-icon i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
            z-index: 2;
        }
        .input-group-icon .form-control {
            padding-left: 56px;
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

        /* Container override for fullscreen */
        main .container { padding: 0; max-width: 100%; }

        /* Responsive */
        @media (max-width: 576px) {
            .login-box { margin: 16px; padding: 32px 24px; }
            .login-header h1 { font-size: 1.875rem; }
        }
    </style>
@endsection


{{-- Content halaman --}}
@section('content')
    {{-- Jika sudah login, jangan tampilkan halaman login --}}
    @auth
        <div class="alert alert-info">
            Anda sudah login. <a href="{{ route('dashboard') }}">Ke Dashboard</a>
        </div>
    @else
        {{-- Container untuk login form --}}
        <div class="login-container">
            <div class="login-box">
                {{-- Header login --}}
                <div class="login-header">
                    <h1><i class="fas fa-lock"></i> Login</h1>
                    <p>Masuk ke akun SIMKEU Anda</p>
                </div>

                {{-- Form login --}}
                <form action="{{ route('login') }}" method="POST">
                    {{-- CSRF Token untuk keamanan form --}}
                    @csrf

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
                    </div>

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
                                   placeholder="Masukkan password Anda"
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
                    </div>

                    {{-- Field Remember Me --}}
                    <div class="mb-3 form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="remember" 
                               name="remember">
                        <label class="form-check-label" for="remember">
                            Ingat saya
                        </label>
                    </div>

                    {{-- Tombol Login --}}
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                {{-- Divider --}}
                <div class="login-divider">atau</div>

                {{-- Link Register --}}
                <div class="register-link">
                    Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
                </div>
            </div>
        </div>

        {{-- Hapus padding container bawaan karena login-container sudah fullscreen --}}
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
