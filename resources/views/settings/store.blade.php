@extends('layouts.app')

@section('title', 'Data Usaha - SIMKEU')
@section('page-title', 'Data usaha')
@section('breadcrumb', 'Akun / Data usaha')
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/settings/profile.css') }}?v=4">
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
                        <span class="account-store-avatar" aria-hidden="true"><i class="fas fa-shop"></i></span>
                        <div class="account-profile-identity">
                            <span class="account-settings-eyebrow">IDENTITAS TOKO</span>
                            <h2 id="store-panel-title">{{ $store->name }}</h2>
                            <span class="account-role-chip"><i class="fas fa-crown" aria-hidden="true"></i>Pemilik utama</span>
                        </div>
                        <span class="account-cover-mark" aria-hidden="true"><i class="fas fa-building"></i></span>
                    </header>

                    <form method="POST" action="{{ route('profile.store.update') }}" class="account-profile-body">
                        @csrf
                        @method('PATCH')

                        <div class="account-section-heading">
                            <span class="account-section-icon account-section-icon--store" aria-hidden="true"><i class="fas fa-shop"></i></span>
                            <h2>Data usaha</h2>
                        </div>

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
@endsection
