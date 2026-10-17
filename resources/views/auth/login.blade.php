{{-- Halaman login SIMKEU --}}
@extends('layouts.app')

@section('title', 'Masuk - SIMKEU')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
    @auth
        <div class="auth-already-signed-in">
            <i class="fas fa-circle-check" aria-hidden="true"></i>
            <span>Anda sudah login.</span>
            <a href="{{ route('dashboard') }}">Buka dashboard <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    @else
        <div class="auth-layout auth-layout-login">
            <aside class="auth-story" aria-label="Tentang SIMKEU">
                <a class="auth-brand" href="{{ route('home') }}" aria-label="SIMKEU, halaman utama">
                    <span class="auth-brand-mark"><i class="fas fa-chart-pie" aria-hidden="true"></i></span>
                    <span class="auth-brand-name">SIMKEU</span>
                    <span class="auth-brand-divider" aria-hidden="true"></span>
                    <span class="auth-brand-caption">Keuangan usaha</span>
                </a>

                <div class="auth-story-copy">
                    <span class="auth-eyebrow"><span></span> KENDALI USAHA DALAM SATU TEMPAT</span>
                    <h1>Keuangan lebih rapi.<br><em>Usaha makin pasti.</em></h1>
                    <p>Pantau penjualan, pemasukan, dan pengeluaran dengan lebih mudah. Semua yang Anda butuhkan untuk memahami kondisi usaha, dalam satu ruang.</p>
                    <div class="auth-feature-list" aria-label="Fitur SIMKEU">
                        <span><i class="fas fa-chart-line" aria-hidden="true"></i> Tren usaha</span>
                        <span><i class="fas fa-wallet" aria-hidden="true"></i> Arus kas</span>
                        <span><i class="fas fa-boxes-stacked" aria-hidden="true"></i> Stok produk</span>
                    </div>
                </div>

                <div class="auth-preview" aria-hidden="true">
                    <div class="auth-preview-top">
                        <span class="auth-preview-title"><span class="auth-preview-dot"></span> Ringkasan bulan ini</span>
                        <i class="fas fa-ellipsis" aria-hidden="true"></i>
                    </div>
                    <div class="auth-preview-value">Rp 28.450.000</div>
                    <div class="auth-preview-change"><i class="fas fa-arrow-trend-up"></i> 12,8% <span>dari bulan lalu</span></div>
                    <svg class="auth-preview-chart" viewBox="0 0 440 94" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="authChartFill" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#65b1f5" stop-opacity=".34" />
                                <stop offset="100%" stop-color="#65b1f5" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <path d="M0 72 C28 64 36 53 66 58 S105 75 132 47 S174 35 198 44 S239 63 267 36 S312 21 334 34 S371 51 398 19 S426 16 440 9 V94 H0Z" fill="url(#authChartFill)" />
                        <path d="M0 72 C28 64 36 53 66 58 S105 75 132 47 S174 35 198 44 S239 63 267 36 S312 21 334 34 S371 51 398 19 S426 16 440 9" fill="none" stroke="#83c4ff" stroke-width="3" stroke-linecap="round" />
                        <circle cx="267" cy="36" r="4" fill="#fff" stroke="#83c4ff" stroke-width="3" />
                        <circle cx="398" cy="19" r="4" fill="#fff" stroke="#83c4ff" stroke-width="3" />
                    </svg>
                    <div class="auth-preview-axis"><span>1 Jun</span><span>10 Jun</span><span>20 Jun</span><span>30 Jun</span></div>
                </div>

                <div class="auth-story-footer"><span>Lebih jelas melihat angka, lebih tenang mengambil langkah.</span><span>© {{ date('Y') }} SIMKEU</span></div>
            </aside>

            <section class="auth-panel">
                <div class="auth-form-wrap">
                    <div class="auth-form-heading">
                        <span class="auth-form-kicker">SELAMAT DATANG KEMBALI</span>
                        <h2>Masuk ke akun Anda</h2>
                        <p>Masukkan email dan kata sandi untuk melanjutkan.</p>
                    </div>

                    <form class="auth-form" action="{{ route('login') }}" method="POST">
                        @csrf

                        <div class="auth-field-group">
                            <label for="email">Email</label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-envelope auth-input-icon" aria-hidden="true"></i>
                                <input class="auth-input @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@bisnisanda.com" autocomplete="username" required autofocus>
                            </div>
                            @error('email')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="auth-field-group">
                            <div class="auth-label-row"><label for="password">Kata sandi</label></div>
                            <div class="auth-input-wrap">
                                <i class="fas fa-lock auth-input-icon" aria-hidden="true"></i>
                                <input class="auth-input @error('password') is-invalid @enderror" id="password" type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                                <button class="auth-password-toggle" type="button" onclick="togglePasswordVisibility('password', this)" aria-label="Tampilkan kata sandi" title="Tampilkan/sembunyikan kata sandi"><i class="fas fa-eye" aria-hidden="true"></i></button>
                            </div>
                            @error('password')<div class="auth-field-error">{{ $message }}</div>@enderror
                        </div>

                        <label class="auth-remember" for="remember">
                            <input id="remember" type="checkbox" name="remember" value="1">
                            <span class="auth-checkmark" aria-hidden="true"><i class="fas fa-check"></i></span>
                            <span>Ingat saya di perangkat ini</span>
                        </label>

                        <button class="auth-submit" type="submit"><span>Masuk ke SIMKEU</span><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                    </form>

                    <div class="auth-switch"><span>Belum memiliki akun?</span> <a href="{{ route('register') }}">Buat akun baru</a></div>
                    <div class="auth-secure-note"><i class="fas fa-shield-halved" aria-hidden="true"></i> Informasi akun Anda terlindungi dengan aman</div>
                </div>
            </section>
        </div>
    @endauth
@endsection

@section('js')
    <script>
        function togglePasswordVisibility(inputId, toggleBtn) {
            const passwordInput = document.getElementById(inputId);
            const icon = toggleBtn.querySelector('i');
            const showPassword = passwordInput.type === 'password';

            passwordInput.type = showPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !showPassword);
            icon.classList.toggle('fa-eye-slash', showPassword);
            toggleBtn.setAttribute('aria-label', showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        }
    </script>
@endsection
