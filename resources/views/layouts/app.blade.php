<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMKEU - Sistem Informasi Manajemen Keuangan UMKM')</title>

    {{-- Gaya kritis agar layout tidak berkedip sebelum seluruh CSS dimuat. --}}
    <link rel="stylesheet" href="{{ asset('css/layout/layout-critical.css') }}">

    {{-- Pulihkan status sidebar sebelum browser menggambar halaman. --}}
    <script src="{{ asset('js/layout/layout-state.js') }}"></script>

    {{-- Siapkan koneksi ke CDN agar aset eksternal lebih cepat dimuat. --}}
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Bootstrap menyediakan komponen dan gaya dasar. --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome menyediakan ikon pada navigasi dan konten. --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Poppins menjadi jenis huruf utama aplikasi. --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Prefetch halaman internal saat tautan diarahkan untuk mempercepat navigasi. --}}
    <script type="speculationrules">
    {
        "prefetch": [
            {
                "where": {
                    "and": [
                        { "href_matches": "/*" },
                        { "not": { "href_matches": "/logout" } },
                        { "not": { "href_matches": "*/delete*" } },
                        { "not": { "href_matches": "*transfer*" } }
                    ]
                },
                "eagerness": "moderate"
            }
        ]
    }
    </script>

    @php $isAuthPage = request()->routeIs('login', 'register'); @endphp
    {{-- Gaya utama kerangka aplikasi. --}}
    <link rel="stylesheet" href="{{ asset('css/layout/layout.css') }}">

    {{-- Sisipkan gaya halaman, geometri bersama, animasi, lalu penyesuaian akhir layout. --}}
    @yield('css')
    <link rel="stylesheet" href="{{ asset('css/ui-geometry.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout/layout-overrides.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout/status-colors.css') }}?v=1">
    @yield('page-css')
</head>

<body class="{{ $isAuthPage ? 'auth-page' : '' }} preload-transitions">
    {{-- Navigasi samping hanya ditampilkan di luar halaman autentikasi. --}}
    @unless ($isAuthPage)
        <aside id="sidebar">
        {{-- Identitas aplikasi dan toko. --}}
        @php
            $currentStore = Auth::user()?->store;
            $storeBrandName = $currentStore?->name ?? 'SIMKEU';
            $storeBrandLogo = $currentStore?->logo
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($currentStore->logo)
                : null;
        @endphp
        <a class="sidebar-brand" href="{{ route('home') }}" title="{{ $storeBrandName }}" aria-label="{{ $storeBrandName }}">
            <div class="brand-icon" aria-hidden="true">
                @if($storeBrandLogo)
                            <img src="{{ $storeBrandLogo }}" alt="">
                @else
                    <i class="fas fa-chart-line"></i>
                @endif
            </div>
            <span class="brand-text">{{ $storeBrandName }}</span>
        </a>

        {{-- Tautan navigasi utama aplikasi. --}}
        <nav class="sidebar-nav">
            <ul class="list-unstyled">
                @if(Auth::user()?->hasPermission('dashboard.view') || Auth::user()?->hasPermission('dashboard.mobile.view'))
                <li class="nav-item">
                    <a href="{{ route('dashboard', array_filter(['period' => request('period')])) }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-gauge-high"></i></span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>
                @endif
                @php
                    $user = Auth::user();
                    $isOwner = $user?->isOwner();
                    $isFinanceOpen = request()->routeIs('pemasukan.*', 'pengeluaran.*');
                    $isProductsOpen = request()->routeIs('products.*', 'categories.*');
                    $isDistributionOpen = request()->routeIs('employees.stock.*', 'stock-allocations.history');
                    $isEmployeesOpen = request()->routeIs('employees.*', 'roles.*') && !$isDistributionOpen;
                @endphp

                @if($user?->hasPermission('sales.view') || $user?->hasPermission('expenses.view') || $user?->hasPermission('expenses.create'))
                <li class="nav-item"><details {{ $isFinanceOpen ? 'open' : '' }}>
                    <summary class="nav-link {{ $isFinanceOpen ? 'active' : '' }}"><span class="nav-icon"><i class="fas fa-wallet"></i></span><span class="nav-label">Keuangan</span><i class="fas fa-chevron-right nav-chevron" aria-hidden="true"></i></summary>
                    <div class="nav-submenu">
                        @if($user?->hasPermission('sales.view'))<a href="{{ route('pemasukan.index', array_filter(['period' => request('period')])) }}" class="nav-link {{ request()->routeIs('pemasukan.*') ? 'active' : '' }}">Pemasukan</a>@endif
                        @if($user?->hasPermission('expenses.view'))<a href="{{ route('pengeluaran.index', array_filter(['period' => request('period')])) }}" class="nav-link {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}">Pengeluaran</a>@elseif($user?->hasPermission('expenses.create'))<a href="{{ route('pengeluaran.create') }}" class="nav-link">Pengeluaran</a>@endif
                        @if($user?->hasPermission('sales.view'))<span class="nav-link" aria-disabled="true" title="Halaman akan dikembangkan">Laporan Keuangan</span>@endif
                    </div>
                </details></li>
                @endif

                @if($user?->hasPermission('products.view') || $user?->hasPermission('categories.view') || $user?->hasPermission('categories.create'))
                <li class="nav-item"><details {{ $isProductsOpen ? 'open' : '' }}>
                    <summary class="nav-link {{ $isProductsOpen ? 'active' : '' }}"><span class="nav-icon"><i class="fas fa-box-open"></i></span><span class="nav-label">Produk dan Persediaan</span><i class="fas fa-chevron-right nav-chevron" aria-hidden="true"></i></summary>
                    <div class="nav-submenu">
                        @if($user?->hasPermission('products.view'))<a href="{{ route('products.index', ['tab' => 'produk']) }}" class="nav-link {{ request()->routeIs('products.*') && request('tab') !== 'kategori' ? 'active' : '' }}">Daftar Produk</a>@endif
                        @if($user?->hasPermission('categories.view') || $user?->hasPermission('categories.create'))<a href="{{ route('products.index', ['tab' => 'kategori']) }}" class="nav-link {{ request('tab') === 'kategori' || request()->routeIs('categories.*') ? 'active' : '' }}">Kategori Produk</a>@endif
                    </div>
                </details></li>
                @endif

                @if($user?->hasPermission('stock_allocations.view'))
                <li class="nav-item"><details {{ $isDistributionOpen ? 'open' : '' }}>
                    <summary class="nav-link {{ $isDistributionOpen ? 'active' : '' }}"><span class="nav-icon"><i class="fas fa-truck-fast"></i></span><span class="nav-label">Pergerakan Stok</span><i class="fas fa-chevron-right nav-chevron" aria-hidden="true"></i></summary>
                    <div class="nav-submenu">
                        <a href="{{ route('stock-allocations.history') }}" class="nav-link {{ request()->routeIs('stock-allocations.history') ? 'active' : '' }}" @if(request()->routeIs('stock-allocations.history')) aria-current="page" @endif>Riwayat Pergerakan Stok</a>
                    </div>
                </details></li>
                @endif

                @if(Auth::user()?->hasPermission('employees.view') || Auth::user()?->hasPermission('employees.create') || Auth::user()?->isOwner())
                <li class="nav-item">
                    <details {{ $isEmployeesOpen ? 'open' : '' }}>
                        <summary class="nav-link {{ $isEmployeesOpen ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-users"></i></span>
                            <span class="nav-label">Pegawai dan Akses</span>
                            <i class="fas fa-chevron-right nav-chevron" aria-hidden="true"></i>
                        </summary>
                        <div class="nav-submenu">
                            @if(Auth::user()?->hasPermission('employees.view') || Auth::user()?->hasPermission('employees.create'))
                                <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" @if(request()->routeIs('employees.*')) aria-current="page" @endif>
                                    Daftar Pegawai
                                </a>
                            @endif
                            @if($isOwner)<a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">Role dan Permission</a>@endif
                        </div>
                    </details>
                </li>
                @endif
            </ul>
        </nav>

        {{-- Profil pengguna dan tombol keluar. --}}
        @auth
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="user-avatar">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar }}" alt="">
                        @else
                            {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-role">{{ Auth::user()->role_name }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="user-logout" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endauth
        </aside>

        {{-- Lapisan penutup sidebar pada layar seluler. --}}
        <div id="sidebar-overlay"></div>

        {{-- Bilah atas memuat tombol sidebar, judul halaman, dan filter periode. --}}
        <header id="topbar">
        <button class="topbar-toggle" id="sidebarToggle" title="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-breadcrumb">
            <span class="page-title">@yield('page-title', 'Dashboard')</span>
            <span>@yield('breadcrumb', 'SIMKEU')</span>
        </div>

        {{-- Filter periode global dan informasi pengguna. --}}
        <div class="topbar-actions">
            {{-- Filter periode global memakai query agar semua halaman web konsisten. --}}
            <div class="period-filter-group" id="global-period-filter" data-timezone="{{ config('app.timezone') }}">
                <button
                    class="period-filter-trigger"
                    id="global-period-trigger"
                    type="button"
                    aria-label="Filter periode: {{ $periodLabel ?? 'Bulan Ini' }}"
                    aria-haspopup="dialog"
                    aria-expanded="false"
                    aria-controls="global-period-menu"
                    title="Filter periode data"
                >
                    <i class="fas fa-calendar-days" aria-hidden="true"></i>
                    <span class="period-filter-label" id="global-period-label">{{ $periodLabel ?? 'Bulan Ini' }}</span>
                    <i class="fas fa-chevron-down period-filter-chevron" aria-hidden="true"></i>
                </button>
                <div
                    class="period-filter-menu"
                    id="global-period-menu"
                    role="dialog"
                    aria-label="Filter periode data"
                    hidden
                >
                    <section class="period-filter-presets" aria-label="Preset periode">
                        <p class="period-filter-section-title">Pilih periode</p>
                        <button class="period-preset" type="button" data-period-preset="all" aria-pressed="false">
                            <span>Semua Periode</span><i class="fas fa-check" aria-hidden="true"></i>
                        </button>
                        <button class="period-preset" type="button" data-period-preset="today" aria-pressed="false">
                            <span>Hari Ini</span><i class="fas fa-check" aria-hidden="true"></i>
                        </button>
                        <button class="period-preset" type="button" data-period-preset="week" aria-pressed="false">
                            <span>Minggu Ini</span><i class="fas fa-check" aria-hidden="true"></i>
                        </button>
                        <button class="period-preset" type="button" data-period-preset="month" aria-pressed="true">
                            <span>Bulan Ini</span><i class="fas fa-check" aria-hidden="true"></i>
                        </button>
                        <div class="period-filter-divider" aria-hidden="true"></div>
                        <button
                            class="period-custom-toggle"
                            id="global-period-custom-toggle"
                            type="button"
                            aria-expanded="false"
                            aria-controls="global-period-calendar-panel"
                        >
                            <i class="fas fa-calendar-days" aria-hidden="true"></i>
                            <span>Rentang Tanggal Kustom</span>
                        </button>
                    </section>
                    <section
                        class="period-calendar-panel"
                        id="global-period-calendar-panel"
                        aria-label="Pilih rentang tanggal kustom"
                        hidden
                    >
                        <div class="period-calendar-toolbar">
                            <button class="period-calendar-nav" type="button" data-calendar-nav="previous" aria-label="Bulan sebelumnya">
                                <i class="fas fa-chevron-left" aria-hidden="true"></i>
                            </button>
                            <p class="period-calendar-title">Pilih rentang tanggal</p>
                            <button class="period-calendar-nav" type="button" data-calendar-nav="next" aria-label="Bulan berikutnya">
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="period-calendars" id="global-period-calendars"></div>
                        <div class="period-calendar-footer">
                            <p class="period-calendar-selection" id="global-period-selection" aria-live="polite">Pilih tanggal mulai</p>
                            <button class="btn btn-primary btn-sm" id="global-period-apply" type="button" disabled>Terapkan</button>
                        </div>
                    </section>
                </div>
            </div>

            @auth
                @php
                    $sessionNotifications = collect();
                    foreach (['success', 'error', 'warning', 'info'] as $notificationType) {
                        $notificationMessage = Session::get($notificationType);
                        if ($notificationMessage) {
                            $sessionNotifications->push(['type' => $notificationType, 'title' => ucfirst($notificationType), 'message' => $notificationMessage]);
                        }
                    }
                    foreach ($errors->all() as $errorMessage) {
                        $sessionNotifications->push(['type' => 'warning', 'title' => 'Validasi', 'message' => $errorMessage]);
                    }
                    $storedNotifications = Auth::user()->notifications()->latest()->limit(10)->get();
                    $storedNotifications->each(function ($notification) {
                        $notification->data = \App\Support\NotificationUrl::normalize($notification->data);
                    });
                    $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();
                @endphp
                <div class="dropdown">
                    <button
                        class="topbar-btn"
                        type="button"
                        id="notificationDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Notifikasi{{ $unreadNotificationsCount ? ', ' . $unreadNotificationsCount . ' belum dibaca' : '' }}"
                        title="Notifikasi"
                    >
                        <i class="fas fa-bell" aria-hidden="true"></i>
                        @if ($unreadNotificationsCount > 0)<span class="notification-badge" id="notification-badge" data-count="{{ $unreadNotificationsCount }}">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>@endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="notificationDropdown">
                        <div class="notification-menu-title">Notifikasi</div>
                        <div class="notification-list" id="notification-list" aria-live="polite">
                            @foreach ($sessionNotifications as $notification)
                                <div class="notification-item notification-{{ $notification['type'] }}">
                                    <strong>{{ $notification['title'] }}</strong>
                                    <span>{{ $notification['message'] }}</span>
                                </div>
                            @endforeach
                            @foreach ($storedNotifications as $notification)
                                @php($notificationData = $notification->data)
                                <div class="notification-item notification-{{ $notificationData['type'] ?? 'info' }} {{ $notification->read_at ? '' : 'notification-unread' }}" data-notification-id="{{ $notification->id }}">
                                    <strong>{{ $notificationData['title'] ?? 'Notifikasi' }}</strong>
                                    <span>{{ $notificationData['message'] ?? '' }}</span>
                                    @if (!empty($notificationData['reason']))<span class="notification-reason">Alasan: {{ $notificationData['reason'] }}</span>@endif
                                    @if (!empty($notificationData['url']))<a href="{{ $notificationData['url'] }}">Lihat alokasi stok</a>@endif
                                    <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                                </div>
                            @endforeach
                            @if ($sessionNotifications->isEmpty() && $storedNotifications->isEmpty())
                                <div class="notification-empty" id="notification-empty">
                                    <i class="fas fa-bell-slash" aria-hidden="true"></i>
                                    <span>Belum ada notifikasi</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endauth

            {{-- Pemisah visual antara filter dan info user --}}
            <div class="topbar-divider"></div>

            @auth
                {{-- Tampilkan avatar dan nama user yang sedang login --}}
                <a href="{{ route('profile.edit') }}" class="topbar-user" aria-label="Buka profil {{ Auth::user()->name }}" title="Profil & Usaha">
                    {{-- Avatar: huruf pertama nama user sebagai inisial --}}
                    <div class="avatar">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar }}" alt="">
                        @else
                            {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </div>
                    {{-- Nama user: hanya tampil di layar >= sm (≥576px) --}}
                    <span class="uname d-none d-sm-block">{{ Auth::user()->name }}</span>
                </a>
            @endauth
        </div>
    </header>
    @endunless

    {{-- Area kerja utama untuk notifikasi, isi halaman, dan footer. --}}
    <main id="main-content">
        {{-- Halaman autentikasi tidak memiliki navbar; tampilkan pesannya di halaman. --}}
        @if ($isAuthPage && ($message = Session::get('success')))
            <div class="alert alert-success status-color alert-dismissible fade show mb-4" data-status-color="success" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($isAuthPage && ($message = Session::get('error')))
            <div class="alert alert-danger status-color alert-dismissible fade show mb-4" data-status-color="danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($isAuthPage && $errors->any())
            <div class="alert alert-warning status-color alert-dismissible fade show mb-4" data-status-color="warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')

        <div class="app-footer">
            &copy; 2026 <strong>SIMKEU</strong> — Sistem Informasi Manajemen Keuangan UMKM. Versi 1.0.0
        </div>
    </main>

    {{-- Bootstrap JS mengaktifkan modal, alert, dan komponen interaktif lain. --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app-animations.js') }}"></script>

    <script src="{{ asset('js/layout/layout.js') }}"></script>
    <script>
        window.SimkeuNotifications = {
            add({ id = null, type = 'info', title = 'Notifikasi', message = '', reason = '', url = '', createdAt = '' }) {
                const list = document.getElementById('notification-list');
                if (!list) return;
                if (id && list.querySelector(`[data-notification-id="${CSS.escape(id)}"]`)) return;
                document.getElementById('notification-empty')?.remove();
                const item = document.createElement('div');
                item.className = `notification-item notification-${type}`;
                if (id) {
                    item.dataset.notificationId = id;
                    item.classList.add('notification-unread');
                }
                const heading = document.createElement('strong');
                heading.textContent = title;
                const body = document.createElement('span');
                body.textContent = message;
                item.append(heading, body);
                if (reason) {
                    const reasonText = document.createElement('span');
                    reasonText.className = 'notification-reason';
                    reasonText.textContent = `Alasan: ${reason}`;
                    item.append(reasonText);
                }
                if (url) {
                    const link = document.createElement('a');
                    link.href = url;
                    link.textContent = 'Lihat alokasi stok';
                    item.append(link);
                }
                if (createdAt) {
                    const time = document.createElement('time');
                    time.textContent = createdAt;
                    item.append(time);
                }
                list.prepend(item);
            },
            updateBadge(count) {
                const button = document.getElementById('notificationDropdown');
                let badge = document.getElementById('notification-badge');
                if (!button) return;
                const previousCount = Number(badge?.dataset.count || 0);
                if (count > 0 && !badge) {
                    badge = document.createElement('span');
                    badge.className = 'notification-badge';
                    badge.id = 'notification-badge';
                    button.append(badge);
                }
                if (badge) {
                    badge.dataset.count = String(count);
                    badge.textContent = count > 99 ? '99+' : String(count);
                    if (count > previousCount) {
                        badge.classList.remove('is-new');
                        void badge.offsetWidth;
                        badge.classList.add('is-new');
                    }
                    if (count < 1) badge.remove();
                }
                button.setAttribute('aria-label', count > 0 ? `Notifikasi, ${count} belum dibaca` : 'Notifikasi');
            }
        };
        document.getElementById('notificationDropdown')?.addEventListener('show.bs.dropdown', () => {
            const badge = document.getElementById('notification-badge');
            if (!badge) return;
            fetch(@json(route('notifications.read')), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            }).then(() => badge.remove()).catch(() => {});
        });
        const refreshNotifications = () => fetch(@json(route('notifications.unread')), {
            headers: { 'Accept': 'application/json' }
        }).then(response => response.ok ? response.json() : null).then(result => {
            if (!result) return;
            result.notifications.forEach(notification => {
                const data = notification.data || {};
                window.SimkeuNotifications.add({
                    id: notification.id,
                    type: data.type || 'info',
                    title: data.title || 'Notifikasi',
                    message: data.message || '',
                    reason: data.reason || '',
                    url: data.url || '',
                    createdAt: notification.created_at || ''
                });
            });
            window.SimkeuNotifications.updateBadge(result.unread_count);
        }).catch(() => {});
        window.setInterval(refreshNotifications, 30000);
    </script>
    {{-- Jalankan skrip tambahan milik halaman setelah interaksi layout tersedia. --}}
    {{-- Slot untuk JavaScript tambahan dari halaman turunan (via @section('js')) --}}
    @yield('js')
    {{-- Perbarui status alokasi stok pada halaman pengelolaan pegawai. --}}
    <script src="{{ asset('js/layout/employee-stock.js') }}"></script>
</body>

</html>
