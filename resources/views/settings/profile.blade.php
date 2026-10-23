@extends('layouts.app')

@section('title', 'Profil - SIMKEU')
@section('page-title', 'Profil')
@section('breadcrumb', 'Akun / Profil')
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/settings/profile.css') }}?v=14">
@endsection

@section('content')
    <div class="account-settings-page account-profile-redesign" data-account-settings>
        <div class="account-settings-content">
            @if(session('success'))
                <div class="account-profile-alert" role="status"><i class="fas fa-circle-check" aria-hidden="true"></i><span>{{ session('success') }}</span></div>
            @endif

            <section id="account-settings-panel" class="account-profile-card">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="account-photo-row">
                        <div class="account-avatar-wrap">
                            <span class="account-profile-avatar" aria-hidden="true">
                                <img id="profile-photo-preview" src="{{ $user->avatar ?? '' }}" alt="" @if(!$user->avatar) hidden @endif>
                                <span id="profile-photo-fallback" class="account-profile-avatar-fallback" @if($user->avatar) hidden @endif>{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                            </span>
                            <label class="account-avatar-action" title="Ganti foto profil">
                                <i class="fas fa-camera" aria-hidden="true"></i><span class="account-sr-only">Ganti foto</span>
                                <input id="profile-photo" class="account-photo-input" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-label="Pilih foto profil, JPG, PNG, atau WebP, maksimal 2 MB" data-crop-input data-preview="profile-photo-preview" data-fallback="profile-photo-fallback" data-remove="remove-profile-photo">
                            </label>
                            @if($user->avatar)
                                <label class="account-avatar-action account-avatar-action--remove" title="Hapus foto profil">
                                    <i class="fas fa-trash-can" aria-hidden="true"></i><span class="account-sr-only">Hapus foto</span>
                                    <input type="checkbox" name="remove_profile_photo" value="1" id="remove-profile-photo" aria-label="Hapus foto profil saat ini">
                                </label>
                            @endif
                        </div>
                        <div class="account-photo-copy">
                            <strong>{{ $user->name }}</strong>
                            <span>{{ $user->role_name }}</span>
                            @error('profile_photo')<small class="account-field-error" role="alert">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <div class="account-profile-body account-profile-body--compact">
                        <div class="account-form-grid">
                            <div class="account-field">
                                <label for="user-name"><i class="far fa-user" aria-hidden="true"></i>Nama lengkap <b>*</b></label>
                                <input id="user-name" name="name" value="{{ old('name', $user->name) }}" maxlength="100" autocomplete="name" required>
                                @error('name')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="account-field">
                                <label for="user-email"><i class="far fa-envelope" aria-hidden="true"></i>Email <b>*</b></label>
                                <input id="user-email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="100" autocomplete="email" required>
                                @error('email')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="account-field">
                                <label for="new-password"><i class="fas fa-lock" aria-hidden="true"></i>Kata sandi baru</label>
                                <div class="account-password-control">
                                    <input id="new-password" name="password" type="password" autocomplete="new-password" placeholder="Kosongkan jika tidak diubah">
                                    <button type="button" class="account-password-toggle" data-password-toggle="new-password" data-password-label="kata sandi baru" aria-label="Tampilkan kata sandi baru" title="Tampilkan kata sandi"><i class="fas fa-eye" aria-hidden="true"></i></button>
                                </div>
                                @error('password')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="account-field">
                                <label for="password-confirmation"><i class="fas fa-check-double" aria-hidden="true"></i>Ulangi kata sandi</label>
                                <div class="account-password-control">
                                    <input id="password-confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                                    <button type="button" class="account-password-toggle" data-password-toggle="password-confirmation" data-password-label="ulangan kata sandi" aria-label="Tampilkan ulangan kata sandi" title="Tampilkan kata sandi"><i class="fas fa-eye" aria-hidden="true"></i></button>
                                </div>
                                @error('password_confirmation')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="account-form-actions">
                            <button type="submit" class="account-save-button"><i class="fas fa-check" aria-hidden="true"></i>Simpan</button>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </div>
    @include('settings._image_cropper')
@endsection

@section('js')
    <script src="{{ asset('js/settings/image-cropper.js') }}?v=7"></script>
    <script>
        (() => {
            const root = document.querySelector('[data-account-settings]');
            if (!root) return;

            const newPassword = root.querySelector('#new-password');
            const passwordConfirmation = root.querySelector('#password-confirmation');
            newPassword?.addEventListener('input', () => {
                newPassword.required = newPassword.value.length > 0;
                if (passwordConfirmation) passwordConfirmation.required = newPassword.value.length > 0;
            });

            root.querySelectorAll('[data-password-toggle]').forEach((button) => {
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
