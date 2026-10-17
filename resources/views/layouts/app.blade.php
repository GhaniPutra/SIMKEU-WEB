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
</head>

<body class="{{ $isAuthPage ? 'auth-page' : '' }} preload-transitions">
    {{-- Navigasi samping hanya ditampilkan di luar halaman autentikasi. --}}
    @unless ($isAuthPage)
        <aside id="sidebar">
        {{-- Identitas aplikasi dan toko. --}}
        <a class="sidebar-brand" href="{{ route('home') }}">
            <div class="brand-icon"><i class="fas fa-chart-line"></i></div>
            <span class="brand-text">{{ strtoupper(Auth::check() && Auth::user()->store ? Auth::user()->store->name : 'SIMKEU') }}</span>
        </a>

        {{-- Tautan navigasi utama aplikasi. --}}
        <nav class="sidebar-nav">
            <ul class="list-unstyled">
                <li class="nav-section-label">Utama</li>
                <li class="nav-item">
                    <a href="{{ route('dashboard', array_filter(['period' => request('period')])) }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-gauge-high"></i></span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>

                <li class="nav-section-label" style="margin-top:8px;">Keuangan</li>
                <li class="nav-item">
                    <a href="{{ route('pemasukan.index', array_filter(['period' => request('period')])) }}"
                       class="nav-link {{ request()->routeIs('pemasukan.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-arrow-trend-up"></i></span>
                        <span class="nav-label">Pemasukan</span>
                    </a>
                </li>
                <li class="nav-item" style="display: none;">
                    <a href="{{ route('pos.index') }}"
                       class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-cash-register"></i></span>
                        <span class="nav-label">POS</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pengeluaran.index', array_filter(['period' => request('period')])) }}"
                       class="nav-link {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-arrow-trend-down"></i></span>
                        <span class="nav-label">Pengeluaran</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <span class="nav-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                        <span class="nav-label">Laporan</span>
                    </a>
                </li>

                <li class="nav-section-label" style="margin-top:8px;">Manajemen</li>
                <li class="nav-item">
                    <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-users"></i></span>
                        <span class="nav-label">Pegawai</span>
                    </a>
                </li>
                {{-- Menu cabang belum ditampilkan pada navigasi. --}}
                {{-- Tentukan apakah bagian produk harus terbuka dan kategori sedang dipilih. --}}
                @php
                    $isProductCategoryPage = request()->routeIs('categories.*')
                        || (request()->routeIs('products.index') && request('tab') === 'kategori');
                    $isProductSectionOpen = request()->routeIs('products.*', 'categories.*');
                @endphp
                <li class="nav-item">
                    <details {{ $isProductSectionOpen ? 'open' : '' }}>
                        <summary class="nav-link {{ $isProductSectionOpen ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-box-open"></i></span>
                            <span class="nav-label">Produk</span>
                            <i class="fas fa-chevron-right nav-chevron" aria-hidden="true"></i>
                        </summary>
                        <div class="nav-submenu">
                            <a href="{{ route('products.index', ['tab' => 'produk']) }}"
                               class="nav-link {{ request()->routeIs('products.index', 'products.edit') && !$isProductCategoryPage ? 'active' : '' }}"
                               @if(request()->routeIs('products.index', 'products.edit') && !$isProductCategoryPage) aria-current="page" @endif>
                                Daftar Produk
                            </a>
                            <a href="{{ route('products.create') }}"
                               class="nav-link {{ request()->routeIs('products.create') ? 'active' : '' }}"
                               @if(request()->routeIs('products.create')) aria-current="page" @endif>
                                Tambah Produk
                            </a>
                            <a href="{{ route('categories.index') }}"
                               class="nav-link {{ $isProductCategoryPage ? 'active' : '' }}"
                               @if($isProductCategoryPage) aria-current="page" @endif>
                                Daftar Kategori
                            </a>
                        </div>
                    </details>
                </li>

            </ul>
        </nav>

        {{-- Profil pengguna dan tombol keluar. --}}
        @auth
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-role">{{ Auth::user()->role ?? 'Pengguna' }}</div>
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
            {{-- -------------------------------------------------------
                 Filter Periode Global
                 Saat nilai berubah, halaman dashboard akan di-reload
                 dengan query parameter ?period=<nilai> sehingga controller
                 dapat memfilter data sesuai periode yang dipilih.
                 Nilai yang dipilih juga disimpan di localStorage agar
                 tetap konsisten saat berpindah antar halaman.
            ------------------------------------------------------- --}}
            <select
                class="form-select form-select-sm"
                id="global-period"
                style="width:148px; border-radius:6px; font-size:.8rem; border-color:var(--input);"
                title="Filter periode data dashboard"
            >
                <option value="today">Hari Ini</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
                <option value="all">Semua Periode</option>
            </select>

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
                <div class="topbar-user">
                    {{-- Avatar: huruf pertama nama user sebagai inisial --}}
                    <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    {{-- Nama user: hanya tampil di layar >= sm (≥576px) --}}
                    <span class="uname d-none d-sm-block">{{ Auth::user()->name }}</span>
                </div>
            @endauth
        </div>
    </header>
    @endunless

    {{-- Area kerja utama untuk notifikasi, isi halaman, dan footer. --}}
    <main id="main-content">
        {{-- Halaman autentikasi tidak memiliki navbar; tampilkan pesannya di halaman. --}}
        @if ($isAuthPage && ($message = Session::get('success')))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($isAuthPage && ($message = Session::get('error')))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($isAuthPage && $errors->any())
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
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
