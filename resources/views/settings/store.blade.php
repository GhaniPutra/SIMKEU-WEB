@extends('layouts.app')

@section('title', 'Data Usaha - SIMKEU')
@section('page-title', 'Data usaha')
@section('breadcrumb', 'Akun / Data usaha')
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/settings/profile.css') }}?v=13">
@endsection

@section('content')
    <div class="account-settings-page">
        <header class="account-settings-heading">
            <span class="account-settings-heading-icon account-settings-heading-icon--store" aria-hidden="true"><i class="fas fa-store"></i></span>
            <div>
                <span class="account-settings-eyebrow">PENGATURAN</span>
                <h1 class="account-settings-title">DATA USAHA</h1>
            </div>
        </header>

        <div class="account-settings-layout">
            @include('settings._navigation')

            <div class="account-settings-content">
                @if(session('success'))
                    <div class="account-profile-alert" role="status">
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <section class="account-profile-card account-store-card" aria-labelledby="store-panel-title">
                    <header class="account-store-cover">
                        <div class="account-store-logo-wrap">
                            <span class="account-store-avatar" aria-hidden="true">
                                <img id="store-logo-preview" src="{{ $store->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($store->logo) : '' }}" alt="Logo {{ $store->name }}" @if(!$store->logo) hidden @endif>
                                <i class="fas fa-shop" id="store-logo-fallback" @if($store->logo) hidden @endif></i>
                            </span>
                            <label class="account-avatar-action" title="Ganti logo toko">
                                <i class="fas fa-camera" aria-hidden="true"></i>
                                <span class="account-sr-only">Pilih logo perusahaan atau toko</span>
                                <input id="store-logo" class="account-photo-input" type="file" name="store_logo" form="store-update-form" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-label="Pilih logo, format JPG, PNG, atau WebP, maksimal 2 MB" aria-describedby="store-logo-help" data-crop-input data-preview="store-logo-preview" data-fallback="store-logo-fallback" data-remove="remove-store-logo">
                            </label>
                            @if($store->logo)
                                <label class="account-avatar-action account-avatar-action--remove" title="Hapus logo toko">
                                    <i class="fas fa-trash-can" aria-hidden="true"></i>
                                    <span class="account-sr-only">Hapus logo toko</span>
                                    <input type="checkbox" name="remove_store_logo" value="1" id="remove-store-logo" aria-label="Hapus logo toko saat ini">
                                </label>
                            @endif
                        </div>
                        <div class="account-profile-identity">
                            <span class="account-settings-eyebrow">IDENTITAS TOKO</span>
                            <h2 id="store-panel-title">{{ $store->name }}</h2>
                            <span class="account-role-chip"><i class="fas fa-crown" aria-hidden="true"></i>Pemilik utama</span>
                        </div>
                        <span class="account-cover-mark" aria-hidden="true"><i class="fas fa-building"></i></span>
                    </header>

                    <form id="store-update-form" method="POST" action="{{ route('profile.store.update') }}" class="account-profile-body" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="account-section-heading">
                            <span class="account-section-icon account-section-icon--store" aria-hidden="true"><i class="fas fa-shop"></i></span>
                            <h2>Data usaha</h2>
                        </div>

                        <span id="store-logo-help" class="account-sr-only">Format JPG, PNG, atau WebP, maksimal 2 MB.</span>
                        @error('store_logo')<div class="account-field-error account-photo-error" role="alert">{{ $message }}</div>@enderror

                        <div class="account-form-grid">
                            <div class="account-field">
                                <label for="store-name"><i class="fas fa-signature" aria-hidden="true"></i>Nama usaha <b>*</b></label>
                                <input id="store-name" name="store_name" value="{{ old('store_name', $store->name) }}" maxlength="100" autocomplete="organization" required>
                                @error('store_name')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="account-field">
                                <label for="store-phone"><i class="fas fa-phone" aria-hidden="true"></i>Nomor telepon</label>
                                <input id="store-phone" name="store_phone" type="tel" value="{{ old('store_phone', $store->phone) }}" maxlength="20" autocomplete="tel">
                                @error('store_phone')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                            <div class="account-field account-field--wide">
                                <label for="store-address"><i class="fas fa-location-dot" aria-hidden="true"></i>Alamat usaha</label>
                                <textarea id="store-address" name="store_address" rows="4" maxlength="2000" autocomplete="street-address">{{ old('store_address', $store->address) }}</textarea>
                                @error('store_address')<small class="account-field-error">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="account-form-actions">
                            <button type="submit" class="account-save-button"><i class="fas fa-check" aria-hidden="true"></i>Simpan data usaha</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
    @include('settings._image_cropper')
    <script src="{{ asset('js/settings/image-cropper.js') }}?v=7"></script>
@endsection
