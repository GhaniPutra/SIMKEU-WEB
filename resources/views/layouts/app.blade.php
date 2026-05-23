<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMKEU - Sistem Informasi Manajemen Keuangan UMKM')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    @php $isAuthPage = request()->routeIs('login', 'register'); @endphp
    <style>
        /* ============================================================
           GLOBAL RESET & BASE
        ============================================================ */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --sidebar-w: 260px;
            --sidebar-collapsed-w: 72px;
            --topbar-h: 64px;
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255, 255, 255, 0.07);
            --sidebar-active: rgba(79, 70, 229, 0.25);
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #e2e8f0;
            --body-bg: #f1f5f9;
            --topbar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-primary: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, .10);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, .14);
            --radius: 12px;
            --transition: 0.25s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--body-bg);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ============================================================
           SIDEBAR
        ============================================================ */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: width var(--transition);
            overflow: hidden;
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-w);
        }

        /* Brand */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px;
            height: var(--topbar-h);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            text-decoration: none;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-text {
            font-weight: 800;
            font-size: 1.1rem;
            color: #f1f5f9;
            letter-spacing: 0.5px;
            white-space: nowrap;
            transition: opacity var(--transition);
        }

        #sidebar.collapsed .brand-text {
            opacity: 0;
            pointer-events: none;
        }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 16px 0;
            scrollbar-width: thin;
            scrollbar-color: #334155 transparent;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }

        .nav-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #475569;
            padding: 12px 20px 6px;
            white-space: nowrap;
            transition: opacity var(--transition);
        }

        #sidebar.collapsed .nav-section-label {
            opacity: 0;
        }

        .sidebar-nav .nav-item {
            list-style: none;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 11px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 0;
            transition: all var(--transition);
            position: relative;
            white-space: nowrap;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .sidebar-nav .nav-link .nav-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.95rem;
            flex-shrink: 0;
            transition: background var(--transition);
        }

        .sidebar-nav .nav-link .nav-label {
            transition: opacity var(--transition);
            flex: 1;
        }

        #sidebar.collapsed .nav-link .nav-label {
            opacity: 0;
        }

        .sidebar-nav .nav-link:hover {
            color: var(--sidebar-text-active);
            background: var(--sidebar-hover);
        }

        .sidebar-nav .nav-link:hover .nav-icon {
            background: rgba(255, 255, 255, 0.08);
        }

        .sidebar-nav .nav-link.active {
            color: #fff;
            background: var(--sidebar-active);
        }

        .sidebar-nav .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 4px;
            bottom: 4px;
            width: 3px;
            background: var(--primary-light);
            border-radius: 0 3px 3px 0;
        }

        .sidebar-nav .nav-link.active .nav-icon {
            background: var(--primary);
            color: white;
        }

        /* Badge */
        .nav-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            background: var(--primary);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            border-radius: 10px;
            flex-shrink: 0;
            transition: opacity var(--transition);
        }

        #sidebar.collapsed .nav-badge {
            opacity: 0;
        }

        /* Sidebar footer - user profile */
        .sidebar-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 14px 16px;
            flex-shrink: 0;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-user .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: white;
            flex-shrink: 0;
        }

        .sidebar-user .user-info {
            flex: 1;
            min-width: 0;
            transition: opacity var(--transition);
        }

        #sidebar.collapsed .user-info {
            opacity: 0;
        }

        .sidebar-user .user-name {
            font-size: 0.825rem;
            font-weight: 600;
            color: #e2e8f0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user .user-role {
            font-size: 0.7rem;
            color: #64748b;
        }

        .sidebar-user .user-logout {
            color: #475569;
            font-size: 0.85rem;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            transition: color var(--transition);
            flex-shrink: 0;
        }

        .sidebar-user .user-logout:hover {
            color: var(--danger);
        }

        /* ============================================================
           TOPBAR
        ============================================================ */
        #topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-w);
            height: var(--topbar-h);
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            z-index: 1030;
            transition: left var(--transition);
            box-shadow: var(--shadow-sm);
        }

        #topbar.sidebar-collapsed {
            left: var(--sidebar-collapsed-w);
        }

        .topbar-toggle {
            background: none;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: all var(--transition);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .topbar-toggle:hover {
            background: var(--border);
            color: var(--text-primary);
        }

        .topbar-breadcrumb {
            flex: 1;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .topbar-breadcrumb .page-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-primary);
            display: block;
            line-height: 1.2;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .topbar-btn {
            background: none;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 1rem;
            cursor: pointer;
            position: relative;
            transition: all var(--transition);
        }

        .topbar-btn:hover {
            background: var(--border);
            color: var(--text-primary);
        }

        .topbar-btn .dot {
            position: absolute;
            top: 7px;
            right: 7px;
            width: 7px;
            height: 7px;
            background: var(--danger);
            border-radius: 50%;
            border: 1.5px solid white;
        }

        .topbar-divider {
            width: 1px;
            height: 28px;
            background: var(--border);
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 10px 4px 4px;
            border-radius: 10px;
            cursor: pointer;
            transition: background var(--transition);
            text-decoration: none;
            color: inherit;
        }

        .topbar-user:hover {
            background: var(--border);
        }

        .topbar-user .avatar {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            color: white;
        }

        .topbar-user .uname {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* ============================================================
           MAIN CONTENT
        ============================================================ */
        #main-content {
            margin-left: var(--sidebar-w);
            margin-top: var(--topbar-h);
            min-height: calc(100vh - var(--topbar-h));
            padding: 28px 28px 40px;
            transition: margin-left var(--transition);
        }

        #main-content.sidebar-collapsed {
            margin-left: var(--sidebar-collapsed-w);
        }

        .auth-page #sidebar,
        .auth-page #topbar,
        .auth-page #sidebar-overlay {
            display: none;
        }

        .auth-page #main-content {
            margin-left: 0;
            margin-top: 0;
            min-height: 100vh;
        }

        /* ============================================================
           ALERTS
        ============================================================ */
        .alert {
            border-radius: var(--radius);
            border: none;
            font-size: 0.875rem;
            font-weight: 500;
            animation: slideInDown 0.3s ease;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-12px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* ============================================================
           FOOTER
        ============================================================ */
        .app-footer {
            text-align: center;
            font-size: 0.78rem;
            color: var(--text-muted);
            padding: 16px;
            border-top: 1px solid var(--border);
            margin-top: 32px;
        }

        /* ============================================================
           OVERLAY (mobile)
        ============================================================ */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
            backdrop-filter: blur(2px);
        }

        #sidebar-overlay.active {
            display: block;
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 991px) {
            #sidebar {
                left: calc(-1 * var(--sidebar-w));
                width: var(--sidebar-w) !important;
            }

            #sidebar.mobile-open {
                left: 0;
            }

            #topbar {
                left: 0 !important;
            }

            #main-content {
                margin-left: 0 !important;
            }
        }
    </style>

    @yield('css')
</head>

<body class="{{ $isAuthPage ? 'auth-page' : '' }}">
    <!-- ============================================================
         SIDEBAR
    ============================================================ -->
    @unless ($isAuthPage)
        <aside id="sidebar">
        <!-- Brand -->
        <a class="sidebar-brand" href="{{ route('home') }}">
            <div class="brand-icon"><i class="fas fa-chart-line"></i></div>
            <span class="brand-text">SIMKEU</span>
        </a>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <ul class="list-unstyled">
                <li class="nav-section-label">Utama</li>
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-gauge-high"></i></span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>

                <li class="nav-section-label" style="margin-top:8px;">Keuangan</li>
                <li class="nav-item">
                    <a href="{{ route('pemasukan.index') }}"
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
                    <a href="{{ route('pengeluaran.index') }}"
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
                <!-- Menu "Cabang" dihapus -->
                <li class="nav-item">
                    {{-- Link menu Produk / Stok --}}
                    {{-- Aktif jika route saat ini adalah products.* --}}
                    <a href="{{ route('products.index') }}"
                       class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-box-open"></i></span>
                        <span class="nav-label">Produk / Stok</span>
                    </a>
                </li>
                <li class="nav-item">
                    {{-- Link menu Kategori Produk --}}
                    {{-- Aktif jika route saat ini adalah categories.* --}}
                    <a href="{{ route('categories.index') }}"
                       class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-tags"></i></span>
                        <span class="nav-label">Kategori</span>
                    </a>
                </li>

            </ul>
        </nav>

        <!-- User footer -->
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

        <!-- Overlay for mobile -->
        <div id="sidebar-overlay"></div>

        <!-- ============================================================
             TOPBAR
        ============================================================ -->
        <header id="topbar">
        <button class="topbar-toggle" id="sidebarToggle" title="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-breadcrumb">
            <span class="page-title">@yield('page-title', 'Dashboard')</span>
            <span>@yield('breadcrumb', 'SIMKEU')</span>
        </div>

        {{-- ============================================================
             TOPBAR ACTIONS: Filter periode dan info user
        ============================================================ --}}
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
                style="width:148px; border-radius:8px; font-size:.8rem; border-color:var(--border);"
                title="Filter periode data dashboard"
            >
                <option value="today">Hari Ini</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
            </select>

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

    <!-- ============================================================
         MAIN CONTENT
    ============================================================ -->
    <main id="main-content">
        <!-- Session alerts -->
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
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

    <!-- Bootstrap JS: komponen seperti modal, alert, dll membutuhkan ini -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ============================================================
        // SIDEBAR TOGGLE LOGIC
        // Mengelola buka/tutup sidebar baik di desktop maupun mobile
        // ============================================================

        // Referensi elemen-elemen utama layout
        const sidebar = document.getElementById('sidebar');          // Panel sidebar kiri
        const topbar  = document.getElementById('topbar');           // Bar navigasi atas
        const main    = document.getElementById('main-content');     // Area konten utama
        const overlay = document.getElementById('sidebar-overlay'); // Overlay gelap untuk mobile
        const toggle  = document.getElementById('sidebarToggle');   // Tombol toggle sidebar

        /**
         * Cek apakah layar dalam mode mobile (lebar < 992px / breakpoint lg Bootstrap)
         * @returns {boolean} true jika mobile, false jika desktop
         */
        const isMobile = () => window.innerWidth < 992;

        /**
         * Buka sidebar di mode mobile:
         * - Tambahkan class 'mobile-open' agar sidebar bergeser masuk dari kiri
         * - Tampilkan overlay gelap di belakang sidebar
         */
        function openMobileSidebar() {
            sidebar.classList.add('mobile-open');  // Geser sidebar ke dalam layar
            overlay.classList.add('active');        // Tampilkan overlay latar belakang
        }

        /**
         * Tutup sidebar di mode mobile:
         * - Hapus class 'mobile-open' sehingga sidebar tersembunyi kembali
         * - Sembunyikan overlay
         */
        function closeMobileSidebar() {
            sidebar.classList.remove('mobile-open'); // Kembalikan sidebar ke luar layar
            overlay.classList.remove('active');       // Sembunyikan overlay
        }

        /**
         * Toggle sidebar di mode desktop:
         * - Jika sidebar terbuka, collapse menjadi hanya ikon (72px)
         * - Jika sidebar collapsed, kembalikan ke lebar penuh (260px)
         * - Simpan status ke localStorage agar konsisten saat refresh halaman
         */
        function toggleDesktopSidebar() {
            const collapsed = sidebar.classList.toggle('collapsed'); // Toggle class dan simpan status
            topbar.classList.toggle('sidebar-collapsed', collapsed); // Sesuaikan posisi topbar
            main.classList.toggle('sidebar-collapsed', collapsed);   // Sesuaikan margin konten
            localStorage.setItem('sidebarCollapsed', collapsed);     // Simpan status ke localStorage
        }

        // Event listener: klik tombol hamburger / toggle
        toggle.addEventListener('click', () => {
            if (isMobile()) {
                // Di mobile: buka atau tutup sidebar overlay
                sidebar.classList.contains('mobile-open') ? closeMobileSidebar() : openMobileSidebar();
            } else {
                // Di desktop: toggle collapsed state
                toggleDesktopSidebar();
            }
        });

        // Klik overlay di mobile akan menutup sidebar
        overlay.addEventListener('click', closeMobileSidebar);

        // Pulihkan status collapse sidebar dari localStorage saat halaman dimuat (desktop saja)
        if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');          // Sidebar dalam kondisi collapsed
            topbar.classList.add('sidebar-collapsed');   // Topbar menyesuaikan
            main.classList.add('sidebar-collapsed');     // Konten menyesuaikan
        }

        // Saat layar di-resize dari mobile ke desktop, tutup sidebar mobile jika terbuka
        window.addEventListener('resize', () => {
            if (!isMobile()) closeMobileSidebar();
        });

        // ============================================================
        // PERIOD FILTER LOGIC
        // Mengelola filter periode global di topbar
        //
        // Cara kerja:
        //   1. Saat halaman dimuat, select diisi dari parameter URL (?period=...).
        //      Jika tidak ada di URL, gunakan nilai dari localStorage (default: 'month').
        //   2. Saat user mengubah nilai select:
        //      a. Simpan nilai ke localStorage agar persisten.
        //      b. Jika halaman saat ini adalah '/dashboard', reload dengan ?period=<nilai>.
        //      c. Jika bukan halaman dashboard, cukup simpan ke localStorage saja.
        // ============================================================

        (function initPeriodFilter() {
            // Referensi elemen select filter periode
            const periodSelect = document.getElementById('global-period');
            if (!periodSelect) return; // Amankan jika elemen tidak ditemukan

            // -------------------------------------------------------
            // Ambil nilai periode aktif:
            // Prioritas 1: parameter ?period dari URL saat ini
            // Prioritas 2: nilai tersimpan di localStorage
            // Prioritas 3: default 'month'
            // -------------------------------------------------------
            const urlParams    = new URLSearchParams(window.location.search); // Parse query string URL
            const periodFromUrl = urlParams.get('period');                     // Coba ambil dari URL
            const savedPeriod  = localStorage.getItem('dashboardPeriod') || 'month'; // Fallback localStorage
            const activePeriod = periodFromUrl || savedPeriod;                 // Gunakan URL jika ada

            // Setel nilai awal select sesuai periode aktif
            periodSelect.value = activePeriod;

            // Simpan ke localStorage agar konsisten di semua halaman
            if (periodFromUrl) {
                localStorage.setItem('dashboardPeriod', periodFromUrl);
            }

            // -------------------------------------------------------
            // Event listener: saat user mengubah pilihan periode
            // -------------------------------------------------------
            periodSelect.addEventListener('change', function () {
                const selectedPeriod = this.value; // Nilai periode yang baru dipilih

                // Simpan nilai yang dipilih ke localStorage untuk persistensi
                localStorage.setItem('dashboardPeriod', selectedPeriod);

                // Periksa apakah halaman saat ini adalah halaman dashboard
                // (path '/dashboard' atau root '/')
                const currentPath = window.location.pathname;
                const isDashboard = currentPath === '/dashboard' || currentPath === '/';
                const isPemasukanIndex = currentPath === '/pemasukan';
                const isPengeluaranIndex = currentPath === '/pengeluaran';

                if (isDashboard) {
                    // Jika berada di halaman dashboard, reload dengan query param ?period=<nilai>
                    // Ini akan memicu DashboardController untuk memfilter data berdasarkan periode
                    window.location.href = '/dashboard?period=' + encodeURIComponent(selectedPeriod);
                } else if (isPemasukanIndex || isPengeluaranIndex) {
                    // Jika berada di halaman index pemasukan atau pengeluaran, perbarui URL
                    const url = new URL(window.location.href);
                    url.searchParams.set('period', selectedPeriod);
                    url.searchParams.delete('page'); // Reset halaman ke 1
                    window.location.href = url.toString();
                }
                // Jika bukan halaman dashboard/pemasukan/pengeluaran, filter disimpan di localStorage saja
                // dan akan diterapkan otomatis saat user berpindah ke sana
            });
        })();
    </script>

    {{-- Slot untuk JavaScript tambahan dari halaman turunan (via @section('js')) --}}
    @yield('js')
</body>

</html>