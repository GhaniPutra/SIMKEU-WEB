@php
    $currentPermissions = old('permissions', \App\Support\RolePermissions::canonicalize($role?->permissions ?? []));
    $currentProfile = old('permission_profile', $selectedProfile ?? 'custom');
    $profileIcons = [
        'manager' => 'fa-user-tie',
        'finance' => 'fa-calculator',
        'warehouse' => 'fa-boxes-stacked',
        'viewer' => 'fa-eye',
        'mobile_cashier' => 'fa-mobile-screen-button',
    ];
    $profileIcons['custom'] = 'fa-sliders';
@endphp

<div class="role-editor">
    <header class="role-editor-heading">
        <a href="{{ route('roles.index') }}" class="role-back-link"><i class="fas fa-arrow-left"></i> Kembali ke daftar role</a>
        <span class="roles-eyebrow"><i class="fas fa-shield-halved" aria-hidden="true"></i> Pengaturan akses</span>
        <h1>{{ $role ? 'Ubah role' : 'Buat role baru' }}</h1>
    </header>

    @if($errors->any())
        <div class="role-validation-message" role="alert">
            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
            <div><strong>Periksa isian berikut.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" id="role-form" class="role-editor-form">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <div class="role-editor-layout">
            <div class="role-editor-main">
                <section class="role-editor-panel role-identity-panel">
                    <div class="role-panel-heading">
                        <span class="role-panel-step">1</span>
                        <div><h2>Identitas role</h2></div>
                    </div>
                    <label for="role-name" class="role-field-label">Nama role <span>*</span></label>
                    <input id="role-name" class="role-name-input @error('name') is-invalid @enderror" name="name"
                           value="{{ old('name', $role?->name) }}" maxlength="80" required placeholder="Contoh: Manajer operasional">
                    @error('name')<span class="role-field-error">{{ $message }}</span>@enderror
                </section>

                <section class="role-editor-panel role-profile-panel">
                    <div class="role-panel-heading">
                        <span class="role-panel-step">2</span>
                        <div><h2>Pola akses</h2></div>
                    </div>

                    <fieldset class="role-profile-grid">
                        <legend class="visually-hidden">Pola akses role</legend>
                        @foreach($profiles as $key => $profile)
                            <label class="role-profile-card" for="profile-{{ $key }}">
                                <input class="role-profile" type="radio" name="permission_profile" id="profile-{{ $key }}"
                                       value="{{ $key }}" data-permissions='@json($profile['permissions'])' @checked($currentProfile === $key)>
                                <span class="role-profile-icon"><i class="fas {{ $profileIcons[$key] ?? 'fa-shield-halved' }}"></i></span>
                                <span class="role-profile-copy">
                                    <strong>{{ $profile['name'] }}</strong>
                                    <span>{{ $profile['description'] }}</span>
                                </span>
                                <span class="role-profile-check"><i class="fas fa-check"></i></span>
                            </label>
                        @endforeach
                    </fieldset>
                </section>

                <section class="role-editor-panel role-permissions-panel">
                    <div class="role-panel-heading role-permissions-heading">
                        <span class="role-panel-step">3</span>
                        <div><h2>Izin</h2></div>
                    </div>

                    <div class="role-permission-groups">
                        @foreach($groups as $groupIndex => $groupData)
                            <section class="role-permission-group" data-permission-group>
                                <div class="role-permission-group-heading">
                                    <div class="role-permission-group-title">
                                        <span class="role-group-icon"><i class="fas {{ ['fa-chart-pie', 'fa-cash-register', 'fa-wallet', 'fa-boxes-stacked', 'fa-tags', 'fa-users', 'fa-truck-fast'][$loop->index % 7] }}"></i></span>
                                        <h3>{{ $groupIndex }}</h3>
                                        <span class="role-group-count" data-group-count>0/{{ count($groupData) }}</span>
                                    </div>
                                    <button type="button" class="role-group-toggle" data-group-toggle>Pilih semua</button>
                                </div>
                                <div class="role-permission-options">
                                    @foreach($groupData as $key => $label)
                                        <label class="role-permission-option">
                                            <input class="role-permission" type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $currentPermissions, true))>
                                            <span class="role-permission-check"><i class="fas fa-check"></i></span>
                                            <span class="role-permission-label">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="role-editor-aside">
                <section class="role-summary-card">
                    <div class="role-summary-top">
                        <span class="role-summary-icon"><i class="fas fa-user-shield"></i></span>
                        <span class="role-summary-label">Pratinjau role</span>
                    </div>
                    <h2 data-role-preview-name>{{ old('name', $role?->name) ?: 'Nama role' }}</h2>
                    <div class="role-summary-stat">
                        <span><i class="fas fa-key" aria-hidden="true"></i> Izin dipilih</span>
                        <strong><span data-total-permissions>0</span></strong>
                    </div>
                    <div class="role-summary-stat">
                        <span><i class="fas fa-layer-group" aria-hidden="true"></i> Pola akses</span>
                        <strong class="role-summary-profile" data-selected-profile>{{ $profiles[$currentProfile]['name'] ?? 'Atur sendiri' }}</strong>
                    </div>

                    <div class="role-summary-note">
                        <i class="fas fa-circle-info" aria-hidden="true"></i>
                        <span>Pemilik tetap memiliki akses penuh.</span>
                    </div>

                    <button class="role-save-button" type="submit"><i class="fas fa-check"></i> {{ $role ? 'Simpan perubahan' : 'Simpan role' }}</button>
                    <a href="{{ route('roles.index') }}" class="role-cancel-button">Batal</a>
                </section>
            </aside>
        </div>
    </form>
</div>
