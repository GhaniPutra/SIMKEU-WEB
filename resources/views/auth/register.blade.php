{{-- Halaman pendaftaran SIMKEU --}}
@extends('layouts.app')

@section('title', 'Buat Akun - SIMKEU')

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
        <div class="auth-layout auth-layout-register">
            <aside class="auth-story" aria-label="Tentang SIMKEU">
                <a class="auth-brand" href="{{ route('home') }}" aria-label="SIMKEU, halaman utama">
                    <span class="auth-brand-mark"><i class="fas fa-chart-pie" aria-hidden="true"></i></span>
                    <span class="auth-brand-name">SIMKEU</span>
                    <span class="auth-brand-divider" aria-hidden="true"></span>
                    <span class="auth-brand-caption">Keuangan usaha</span>
                </a>

                <div class="auth-story-copy">
                    <span class="auth-eyebrow"><span></span> LANGKAH BARU UNTUK USAHA ANDA</span>
                    <h1>Mulai kelola usaha<br><em>dengan lebih terarah.</em></h1>
                    <p>Buat akun SIMKEU dan satukan pencatatan transaksi, pemantauan stok, serta ringkasan keuangan dalam satu tempat.</p>
                    <div class="auth-feature-list" aria-label="Fitur SIMKEU">
                        <span><i class="fas fa-chart-line" aria-hidden="true"></i> Tren usaha</span>
                        <span><i class="fas fa-wallet" aria-hidden="true"></i> Arus kas</span>
                        <span><i class="fas fa-boxes-stacked" aria-hidden="true"></i> Stok produk</span>
                    </div>
                </div>

                <div class="auth-preview auth-preview-register" aria-hidden="true">
                    <div class="auth-preview-top">
                        <span class="auth-preview-title"><span class="auth-preview-dot"></span> Satu pandangan untuk bisnis Anda</span>
                        <i class="fas fa-sparkles" aria-hidden="true"></i>
                    </div>
                    <div class="auth-register-benefits">
                        <div><span class="auth-benefit-icon"><i class="fas fa-receipt"></i></span><span><strong>Catat transaksi</strong><small>Penjualan dan pengeluaran tersusun rapi</small></span><i class="fas fa-check auth-benefit-check"></i></div>
                        <div><span class="auth-benefit-icon"><i class="fas fa-chart-simple"></i></span><span><strong>Pahami perkembangan</strong><small>Lihat ringkasan usaha kapan saja</small></span><i class="fas fa-check auth-benefit-check"></i></div>
                        <div><span class="auth-benefit-icon"><i class="fas fa-box-open"></i></span><span><strong>Pantau persediaan</strong><small>Ketahui stok produk dengan mudah</small></span><i class="fas fa-check auth-benefit-check"></i></div>
                    </div>
                </div>

                <div class="auth-story-footer"><span>Mulai dari pencatatan sederhana, bertumbuh dengan percaya diri.</span><span>© {{ date('Y') }} SIMKEU</span></div>
            </aside>

            <section class="auth-panel auth-panel-register">
                <div class="auth-form-wrap">
                    <div class="auth-form-heading">
                        <span class="auth-form-kicker">DAFTAR GRATIS</span>
                        <h2>Buat akun SIMKEU</h2>
                        <p>Isi informasi berikut untuk memulai pengelolaan usaha Anda.</p>
                    </div>

                    <form class="auth-form" action="{{ route('register') }}" method="POST">
                        @csrf

                        <div class="auth-field-grid">
                            <div class="auth-field-group">
                                <label for="name">Nama lengkap</label>
                                <div class="auth-input-wrap">
                                    <i class="fas fa-user auth-input-icon" aria-hidden="true"></i>
                                    <input class="auth-input @error('name') is-invalid @enderror" id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Nama Anda" autocomplete="name" required autofocus>
                                </div>
                                @error('name')<div class="auth-field-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="auth-field-group">
                                <label for="email">Email</label>
                                <div class="auth-input-wrap">
                                    <i class="fas fa-envelope auth-input-icon" aria-hidden="true"></i>
                                    <input class="auth-input @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@bisnisanda.com" autocomplete="email" required>
                                </div>
                                @error('email')<div class="auth-field-error">{{ $message }}</div>@enderror
                                <small class="auth-field-hint">Gunakan email aktif yang belum terdaftar.</small>
                            </div>

                            <div class="auth-field-group">
                                <label for="password">Kata sandi</label>
                                <div class="auth-input-wrap">
                                    <i class="fas fa-lock auth-input-icon" aria-hidden="true"></i>
                                    <input class="auth-input @error('password') is-invalid @enderror" id="password" type="password" name="password" placeholder="Minimal 6 karakter" autocomplete="new-password" required>
                                    <button class="auth-password-toggle" type="button" onclick="togglePasswordVisibility('password', this)" aria-label="Tampilkan kata sandi" title="Tampilkan/sembunyikan kata sandi"><i class="fas fa-eye" aria-hidden="true"></i></button>
                                </div>
                                @error('password')<div class="auth-field-error">{{ $message }}</div>@enderror
                                <small class="auth-field-hint">Gunakan setidaknya 6 karakter.</small>
                            </div>

                            <div class="auth-field-group">
                                <label for="password_confirmation">Konfirmasi kata sandi</label>
                                <div class="auth-input-wrap">
                                    <i class="fas fa-shield-halved auth-input-icon" aria-hidden="true"></i>
                                    <input class="auth-input @error('password_confirmation') is-invalid @enderror" id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi kata sandi" autocomplete="new-password" required>
                                    <button class="auth-password-toggle" type="button" onclick="togglePasswordVisibility('password_confirmation', this)" aria-label="Tampilkan kata sandi" title="Tampilkan/sembunyikan kata sandi"><i class="fas fa-eye" aria-hidden="true"></i></button>
                                </div>
                                @error('password_confirmation')<div class="auth-field-error">{{ $message }}</div>@enderror
                                <small class="auth-field-hint">Pastikan sama dengan kata sandi di atas.</small>
                            </div>
                        </div>

                        <button class="auth-submit" type="submit"><span>Buat akun</span><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                    </form>

                    <div class="auth-switch"><span>Sudah memiliki akun?</span> <a href="{{ route('login') }}">Masuk di sini</a></div>
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
