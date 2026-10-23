@php($employee = $employee ?? null)
@php($isEditing = $employee !== null)
@php($roleOptions = $isEditing ? $roles : ($assignableRoles ?? $roles))

@if($errors->any())
    <div class="employee-form-errors" role="alert" tabindex="-1" aria-labelledby="employee-error-title">
        <h2 id="employee-error-title"><i class="fas fa-circle-exclamation" aria-hidden="true"></i> Periksa kembali isian berikut</h2>
        <ul>
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="employee-form-layout">
    <section class="employee-form-card employee-profile-card" aria-labelledby="employee-profile-title">
        <div class="employee-form-section-heading">
            <span class="employee-form-icon" aria-hidden="true"><i class="fas fa-id-card"></i></span>
            <div>
                <h2 id="employee-profile-title">Profil pegawai</h2>
                <p>Informasi yang digunakan untuk mengenali akun.</p>
            </div>
        </div>

        <div class="employee-photo-row">
            <div class="employee-photo-wrap">
                <span class="employee-photo" aria-hidden="true">
                    <img id="employee-photo-preview" src="{{ $employee->avatar ?? '' }}" alt="" @if(!$employee?->avatar) hidden @endif>
                    <span id="employee-photo-fallback" @if($employee?->avatar) hidden @endif>{{ mb_strtoupper(mb_substr(old('name', $employee->name ?? 'P'), 0, 1)) }}</span>
                </span>
                <label class="employee-photo-action" title="Pilih foto profil">
                    <i class="fas fa-camera" aria-hidden="true"></i>
                    <span class="employee-sr-only">Pilih foto profil</span>
                    <input id="employee-photo-input" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-label="Pilih foto profil, JPG, PNG, atau WebP, maksimal 2 MB" aria-describedby="employee-photo-help" data-crop-input data-preview="employee-photo-preview" data-fallback="employee-photo-fallback" data-remove="remove-employee-photo">
                </label>
                @if($employee?->avatar)
                    <label class="employee-photo-action employee-photo-remove" title="Hapus foto profil">
                        <i class="fas fa-trash-can" aria-hidden="true"></i>
                        <span class="employee-sr-only">Hapus foto profil</span>
                        <input type="checkbox" name="remove_profile_photo" value="1" id="remove-employee-photo" aria-label="Hapus foto profil saat ini">
                    </label>
                @endif
            </div>
            <div class="employee-photo-copy">
                <strong>Foto profil</strong>
                <p id="employee-photo-help">JPG, PNG, atau WebP. Maksimal 2 MB. Anda dapat mengatur potongan sebelum menyimpan.</p>
                @error('profile_photo')<span class="employee-field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="employee-form-fields">
            <div class="employee-field">
                <label for="employee-name">Nama lengkap <b aria-hidden="true">*</b></label>
                <div class="employee-input-wrap"><i class="fas fa-user" aria-hidden="true"></i><input id="employee-name" type="text" name="name" value="{{ old('name', $employee->name ?? '') }}" maxlength="100" autocomplete="name" placeholder="Nama pegawai" required @class(['is-invalid' => $errors->has('name')]) aria-describedby="employee-name-error"></div>
                @error('name')<span class="employee-field-error" id="employee-name-error">{{ $message }}</span>@enderror
            </div>

            <div class="employee-field">
                <label for="employee-email">Alamat email <b aria-hidden="true">*</b></label>
                <div class="employee-input-wrap"><i class="fas fa-envelope" aria-hidden="true"></i><input id="employee-email" type="email" name="email" value="{{ old('email', $employee->email ?? '') }}" autocomplete="email" placeholder="nama@contoh.com" required @class(['is-invalid' => $errors->has('email')]) aria-describedby="employee-email-error"></div>
                @error('email')<span class="employee-field-error" id="employee-email-error">{{ $message }}</span>@enderror
            </div>

            <div class="employee-field employee-field-wide">
                <label for="employee-role">Role akses <b aria-hidden="true">*</b></label>
                <div class="employee-input-wrap"><i class="fas fa-user-shield" aria-hidden="true"></i><select id="employee-role" name="role_id" required @class(['is-invalid' => $errors->has('role_id')]) aria-describedby="employee-role-help employee-role-error"><option value="">Pilih role untuk pegawai</option>@foreach($roleOptions as $role)<option value="{{ $role->id }}" @selected((string) old('role_id', $employee->role_id ?? '') === (string) $role->id)>{{ $role->name }}</option>@endforeach</select></div>
                <small id="employee-role-help">Role menentukan menu dan tindakan yang dapat diakses pegawai.</small>
                @error('role_id')<span class="employee-field-error" id="employee-role-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </section>

    <section class="employee-form-card employee-security-card" aria-labelledby="employee-security-title">
        <div class="employee-form-section-heading">
            <span class="employee-form-icon employee-form-icon-security" aria-hidden="true"><i class="fas fa-shield-halved"></i></span>
            <div>
                <h2 id="employee-security-title">Keamanan akun</h2>
                <p>{{ $isEditing ? 'Ganti kata sandi hanya jika diperlukan.' : 'Buat kata sandi awal untuk akun pegawai.' }}</p>
            </div>
        </div>

        <div class="employee-form-fields employee-security-fields">
            <div class="employee-field">
                <label for="employee-password">{{ $isEditing ? 'Kata sandi baru' : 'Kata sandi' }} @unless($isEditing)<b aria-hidden="true">*</b>@endunless</label>
                <div class="employee-input-wrap employee-password-wrap"><i class="fas fa-key" aria-hidden="true"></i><input id="employee-password" type="password" name="password" minlength="6" autocomplete="new-password" placeholder="{{ $isEditing ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' }}" @required(!$isEditing) @class(['is-invalid' => $errors->has('password')]) aria-describedby="employee-password-help employee-password-error"><button type="button" class="employee-password-toggle" data-password-toggle="employee-password" aria-label="Tampilkan kata sandi" aria-pressed="false"><i class="fas fa-eye" aria-hidden="true"></i></button></div>
                <small id="employee-password-help">{{ $isEditing ? 'Biarkan kosong untuk mempertahankan kata sandi saat ini.' : 'Gunakan minimal 6 karakter.' }}</small>
                @error('password')<span class="employee-field-error" id="employee-password-error">{{ $message }}</span>@enderror
            </div>

            <div class="employee-field">
                <label for="employee-password-confirmation">Konfirmasi kata sandi @unless($isEditing)<b aria-hidden="true">*</b>@endunless</label>
                <div class="employee-input-wrap employee-password-wrap"><i class="fas fa-lock" aria-hidden="true"></i><input id="employee-password-confirmation" type="password" name="password_confirmation" minlength="6" autocomplete="new-password" placeholder="Ulangi kata sandi" @required(!$isEditing) aria-describedby="employee-password-confirmation-error"><button type="button" class="employee-password-toggle" data-password-toggle="employee-password-confirmation" aria-label="Tampilkan konfirmasi kata sandi" aria-pressed="false"><i class="fas fa-eye" aria-hidden="true"></i></button></div>
                @error('password_confirmation')<span class="employee-field-error" id="employee-password-confirmation-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="employee-security-note"><i class="fas fa-circle-info" aria-hidden="true"></i><p>Foto dan informasi pegawai tersimpan pada usaha ini. Pegawai dapat memperbarui foto profilnya sendiri setelah masuk.</p></div>
    </section>
</div>
