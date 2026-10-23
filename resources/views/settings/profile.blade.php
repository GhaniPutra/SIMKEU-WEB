@extends('layouts.app')

@section('title', 'PROFILE - SIMKEU')
@section('page-title', 'PROFILE')
@section('breadcrumb', 'Akun / PROFILE')
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/settings/profile.css') }}?v=11">
@endsection

@section('content')
    <div class="account-settings-page">
        <div class="account-settings-layout">
            @include('settings._navigation')

            <div class="account-settings-content">
                @if(session('success'))
                    <div class="account-profile-alert" role="status">
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <section class="account-profile-card" aria-labelledby="account-panel-title">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="account-profile-cover">
                            <div class="account-avatar-wrap">
                                <span class="account-profile-avatar" aria-hidden="true">
                                    <img id="profile-photo-preview" src="{{ $user->avatar ?? '' }}" alt="" @if(!$user->avatar) hidden @endif>
                                    <span id="profile-photo-fallback" class="account-profile-avatar-fallback" @if($user->avatar) hidden @endif>{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                </span>
                                <label class="account-avatar-action" title="Ganti foto profil">
                                    <i class="fas fa-camera" aria-hidden="true"></i>
                                    <span class="account-sr-only">Ganti foto profil</span>
                                    <input id="profile-photo" class="account-photo-input" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-label="Pilih foto profil, JPG, PNG, atau WebP, maksimal 2 MB" data-crop-input data-preview="profile-photo-preview" data-fallback="profile-photo-fallback" data-remove="remove-profile-photo">
                                </label>
                                @if($user->avatar)
                                    <label class="account-avatar-action account-avatar-action--remove" title="Hapus foto profil">
                                        <i class="fas fa-trash-can" aria-hidden="true"></i>
                                        <span class="account-sr-only">Hapus foto profil</span>
                                        <input type="checkbox" name="remove_profile_photo" value="1" id="remove-profile-photo" aria-label="Hapus foto profil saat ini">
                                    </label>
                                @endif
                            </div>

                            <div class="account-profile-identity">
                                <span class="account-settings-eyebrow">PROFIL PENGGUNA</span>
                                <h2>{{ $user->name }}</h2>
                                <p>{{ $user->email }}</p>
                                <div class="account-profile-badges">
                                    <span class="account-role-chip"><i class="fas fa-user-tag" aria-hidden="true"></i>{{ $user->role_name }}</span>
                                    <span class="account-verification-chip {{ $user->email_verified_at ? 'is-verified' : 'is-pending' }}" title="{{ $user->email_verified_at ? 'Email terverifikasi' : 'Email belum terverifikasi' }}">
                                        <i class="fas fa-{{ $user->email_verified_at ? 'circle-check' : 'circle-info' }}" aria-hidden="true"></i>
                                        {{ $user->email_verified_at ? 'Email terverifikasi' : 'Email belum terverifikasi' }}
                                    </span>
                                </div>
                            </div>

                            <span class="account-cover-mark" aria-hidden="true"><i class="fas fa-id-card"></i></span>
                        </div>

                        <div class="account-profile-body">
                            @error('profile_photo')<div class="account-field-error account-photo-error" role="alert">{{ $message }}</div>@enderror

                            <div class="account-section-heading">
                                <span class="account-section-icon" aria-hidden="true"><i class="fas fa-address-card"></i></span>
                                <h2 id="account-panel-title">Data diri</h2>
                            </div>

                            <div class="account-form-grid">
                                <div class="account-field">
                                    <label for="user-name"><i class="fas fa-user" aria-hidden="true"></i>Nama lengkap <b>*</b></label>
                                    <input id="user-name" name="name" value="{{ old('name', $user->name) }}" maxlength="100" autocomplete="name" required>
                                    @error('name')<small class="account-field-error">{{ $message }}</small>@enderror
                                </div>
                                <div class="account-field">
                                    <label for="user-email"><i class="fas fa-envelope" aria-hidden="true"></i>Email akun <b>*</b></label>
                                    <input id="user-email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="100" autocomplete="email" required>
                                    @error('email')<small class="account-field-error">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="account-form-actions">
                                <button type="submit" class="account-save-button"><i class="fas fa-check" aria-hidden="true"></i>Simpan profil</button>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="account-security-card" aria-labelledby="security-panel-title">
                    <header class="account-section-heading account-security-heading">
                        <span class="account-section-icon account-section-icon--security" aria-hidden="true"><i class="fas fa-shield-halved"></i></span>
                        <h2 id="security-panel-title">Kata sandi</h2>
                        <i class="fas fa-lock account-security-mark" aria-hidden="true" title="Keamanan akun"></i>
                    </header>

                    <form method="POST" action="{{ route('user-password.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="account-form-grid account-password-grid">
                            <div class="account-field account-field--wide">
                                <label for="current-password"><i class="fas fa-key" aria-hidden="true"></i>Kata sandi saat ini <b>*</b></label>
                                <div class="account-password-control">
                                    <input id="current-password" name="current_password" type="password" autocomplete="current-password" required>
                                    <button type="button" class="account-password-toggle" data-password-toggle="current-password" data-password-label="kata sandi saat ini" aria-label="Tampilkan kata sandi saat ini" title="Tampilkan kata sandi"><i class="fas fa-eye" aria-hidden="true"></i></button>
                                </div>
                                @error('current_password')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="account-field">
                                <label for="new-password"><i class="fas fa-lock" aria-hidden="true"></i>Kata sandi baru <b>*</b></label>
                                <div class="account-password-control">
                                    <input id="new-password" name="password" type="password" autocomplete="new-password" required>
                                    <button type="button" class="account-password-toggle" data-password-toggle="new-password" data-password-label="kata sandi baru" aria-label="Tampilkan kata sandi baru" title="Tampilkan kata sandi"><i class="fas fa-eye" aria-hidden="true"></i></button>
                                </div>
                                @error('password')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="account-field">
                                <label for="password-confirmation"><i class="fas fa-check-double" aria-hidden="true"></i>Ulangi kata sandi baru <b>*</b></label>
                                <div class="account-password-control">
                                    <input id="password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                                    <button type="button" class="account-password-toggle" data-password-toggle="password-confirmation" data-password-label="konfirmasi kata sandi" aria-label="Tampilkan konfirmasi kata sandi" title="Tampilkan kata sandi"><i class="fas fa-eye" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="account-form-actions">
                            <button type="submit" class="account-save-button account-save-button--secondary"><i class="fas fa-key" aria-hidden="true"></i>Perbarui kata sandi</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
    @include('settings._image_cropper')
@endsection

@section('js')
    <script src="{{ asset('js/settings/image-cropper.js') }}?v=7"></script>
    <script>
        (() => {
            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const field = document.getElementById(button.dataset.passwordToggle);
                    const visible = field.type === 'password';
                    field.type = visible ? 'text' : 'password';
                    button.setAttribute('aria-label', `${visible ? 'Sembunyikan' : 'Tampilkan'} ${button.dataset.passwordLabel}`);
                    button.title = `${visible ? 'Sembunyikan' : 'Tampilkan'} kata sandi`;
                    button.querySelector('i').className = `fas fa-${visible ? 'eye-slash' : 'eye'}`;
                });
            });
        })();
    </script>
@endsection
