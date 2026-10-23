<aside class="account-settings-nav" aria-label="Navigasi profil dan usaha">
    <span class="account-nav-label">PENGATURAN AKUN</span>
    <a href="{{ route('profile.edit') }}" class="account-settings-nav-link {{ request()->routeIs('profile.edit', 'profile.update', 'user-password.edit', 'user-password.update') ? 'is-active' : '' }}" @if(request()->routeIs('profile.edit', 'profile.update', 'user-password.edit', 'user-password.update')) aria-current="page" @endif>
        <i class="fas fa-user-gear" aria-hidden="true"></i><span>PROFILE</span><i class="fas fa-chevron-right account-nav-chevron" aria-hidden="true"></i>
    </a>
    @if(auth()->user()?->isOwner())
        <a href="{{ route('profile.store.edit') }}" class="account-settings-nav-link {{ request()->routeIs('profile.store.*') ? 'is-active' : '' }}" @if(request()->routeIs('profile.store.*')) aria-current="page" @endif>
            <i class="fas fa-store" aria-hidden="true"></i><span>Data usaha</span><i class="fas fa-chevron-right account-nav-chevron" aria-hidden="true"></i>
        </a>
    @endif
    <div class="account-nav-divider" aria-hidden="true"></div>
    <span class="account-nav-note" title="Foto, data diri, dan kata sandi dikelola melalui PROFILE.">
        <i class="fas fa-circle-info" aria-hidden="true"></i>
        <span class="account-sr-only">Foto, data diri, dan kata sandi dikelola melalui PROFILE.</span>
    </span>
</aside>
