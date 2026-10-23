@extends('layouts.app')

@section('title', 'Data Usaha - SIMKEU')
@section('page-title', 'Data usaha')
@section('breadcrumb', 'Akun / Data usaha')
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/settings/profile.css') }}?v=15">
@endsection

@section('content')
    <div class="account-settings-page account-profile-redesign account-store-redesign">
        <div class="account-settings-content">
            @if(session('success'))
                <div class="account-profile-alert" role="status"><i class="fas fa-circle-check" aria-hidden="true"></i><span>{{ session('success') }}</span></div>
            @endif

            <section class="account-profile-card account-store-card" aria-labelledby="store-panel-title">
                <div class="account-photo-row account-store-photo-row">
                    <div class="account-avatar-wrap account-store-logo-wrap">
                        <span class="account-profile-avatar account-store-avatar" aria-hidden="true">
                            <img id="store-logo-preview" src="{{ $store->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($store->logo) : '' }}" alt="" @if(!$store->logo) hidden @endif>
                            <i class="fas fa-shop" id="store-logo-fallback" @if($store->logo) hidden @endif></i>
                        </span>
                        <label class="account-avatar-action" title="Ganti logo usaha">
                            <i class="fas fa-camera" aria-hidden="true"></i><span class="account-sr-only">Ganti logo usaha</span>
                            <input id="store-logo" class="account-photo-input" type="file" name="store_logo" form="store-update-form" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-label="Pilih logo usaha, JPG, PNG, atau WebP, maksimal 2 MB" aria-describedby="store-logo-help" data-crop-input data-preview="store-logo-preview" data-fallback="store-logo-fallback" data-remove="remove-store-logo">
                        </label>
                        @if($store->logo)
                            <label class="account-avatar-action account-avatar-action--remove" title="Hapus logo usaha">
                                <i class="fas fa-trash-can" aria-hidden="true"></i><span class="account-sr-only">Hapus logo usaha</span>
                                <input type="checkbox" name="remove_store_logo" value="1" id="remove-store-logo" form="store-update-form" aria-label="Hapus logo usaha saat ini">
                            </label>
                        @endif
                    </div>
                    <div class="account-photo-copy">
                        <strong id="store-panel-title">{{ $store->name }}</strong>
                        <span>Data usaha</span>
                        <button class="account-photo-picker" type="button" data-store-logo-trigger><i class="fas fa-arrow-up-from-bracket" aria-hidden="true"></i> Ganti logo</button>
                        <small>JPG, PNG, WebP · maks. 2 MB</small>
                        <span id="store-logo-help" class="account-sr-only">Format JPG, PNG, atau WebP, maksimal 2 MB.</span>
                        @error('store_logo')<small class="account-field-error" role="alert">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="account-profile-body account-profile-body--compact">
                    <form id="store-update-form" method="POST" action="{{ route('profile.store.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <div class="account-form-grid">
                            <div class="account-field">
                                <label for="store-name"><i class="fas fa-signature" aria-hidden="true"></i>Nama usaha <b>*</b></label>
                                <input id="store-name" name="store_name" value="{{ old('store_name', $store->name) }}" maxlength="100" autocomplete="organization" required>
                                @error('store_name')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="account-field">
                                <label for="store-phone"><i class="fas fa-phone" aria-hidden="true"></i>Telepon</label>
                                <input id="store-phone" name="store_phone" type="tel" value="{{ old('store_phone', $store->phone) }}" maxlength="20" autocomplete="tel">
                                @error('store_phone')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="account-field account-field--wide">
                                <label for="store-address"><i class="fas fa-location-dot" aria-hidden="true"></i>Alamat</label>
                                <textarea id="store-address" name="store_address" rows="3" maxlength="2000" autocomplete="street-address">{{ old('store_address', $store->address) }}</textarea>
                                @error('store_address')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="account-field">
                                <label for="stock-closing-time"><i class="far fa-clock" aria-hidden="true"></i>Jam penutupan</label>
                                <input id="stock-closing-time" type="time" name="stock_closing_time" value="{{ old('stock_closing_time', substr($store->stock_closing_time ?? '23:59:00', 0, 5)) }}" required>
                                @error('stock_closing_time')<small class="account-field-error" role="alert">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="account-form-actions"><button type="submit" class="account-save-button"><i class="fas fa-check" aria-hidden="true"></i>Simpan</button></div>
                    </form>
                </div>
            </section>
        </div>
    </div>
    @include('settings._image_cropper')
    <script src="{{ asset('js/settings/image-cropper.js') }}?v=7"></script>
    <script>
        document.querySelector('[data-store-logo-trigger]')?.addEventListener('click', () => document.getElementById('store-logo')?.click());
    </script>
@endsection
