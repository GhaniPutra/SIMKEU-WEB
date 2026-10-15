<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMKEU - Sistem Informasi Manajemen Keuangan UMKM')</title>

    <!-- Critical style to prevent white screen flicker & layout jump -->
    <style>
        :root {
            --sidebar-w: 260px;
            --sidebar-collapsed-w: 72px;
            --topbar-h: 64px;
            --body-bg: #FFFCF8;
            --sidebar-bg: #ffffff;
            --sidebar-indicator: #B27D55;
            --topbar-bg: #FFFFFF;
            --text-primary: #192b52;
        }

        html, body {
            background-color: var(--body-bg);
            color: var(--text-primary);
        }

        /* Prevent sidebar collapse flash/jump on initial render */
        html.sidebar-is-collapsed #sidebar {
            width: var(--sidebar-collapsed-w) !important;
        }
        html.sidebar-is-collapsed #topbar {
            left: var(--sidebar-collapsed-w) !important;
        }
        html.sidebar-is-collapsed #main-content {
            margin-left: var(--sidebar-collapsed-w) !important;
        }
        html.sidebar-is-collapsed .sidebar-brand .brand-text,
        html.sidebar-is-collapsed .nav-section-label,
        html.sidebar-is-collapsed .sidebar-nav .nav-link .nav-label,
        html.sidebar-is-collapsed .nav-badge,
        html.sidebar-is-collapsed .sidebar-user .user-info {
            opacity: 0 !important;
            pointer-events: none !important;
        }
        html.sidebar-is-collapsed #sidebar .nav-submenu {
            display: none;
        }

        /* Disable transitions during initial page load to prevent layout flicker */
        body.preload-transitions,
        body.preload-transitions * {
            transition: none !important;
        }
    </style>

    <!-- Immediate script to set sidebar state before DOM paint -->
    <script>
        (function() {
            try {
                if (window.innerWidth >= 992 && localStorage.getItem('sidebarCollapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-is-collapsed');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Preconnect to CDNs for fast resource loading -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Speculation Rules: Prefetch navigation pages on hover for instant smooth page transitions -->
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
            --blue-50: #f0f7fe;
            --blue-100: #dcedfd;
            --blue-200: #c1e0fc;
            --blue-300: #90caf9;
            --blue-400: #65b1f5;
            --blue-500: #4192f0;
            --blue-600: #2c74e4;
            --blue-700: #235fd2;
            --blue-800: #234eaa;
            --blue-900: #224486;
            --blue-950: #192b52;
            --red-50: #fff0f2;
            --red-100: #ffdee2;
            --red-200: #ffc3cb;
            --red-300: #ff99a7;
            --red-400: #ff5e73;
            --red-500: #ff2c48;
            --red-600: #f62440;
            --red-700: #cf0621;
            --red-800: #aa0a1f;
            --red-900: #8c1021;
            --red-950: #4d020c;
            --yellow-50: #fffee7;
            --yellow-100: #fffdc3;
            --yellow-200: #fffa8d;
            --yellow-300: #fff24a;
            --yellow-400: #fbe417;
            --yellow-500: #e8cc06;
            --yellow-600: #c5a102;
            --yellow-700: #9b7705;
            --yellow-800: #7f5d0c;
            --yellow-900: #6b4e11;
            --yellow-950: #3f2a04;
            --green-50: #f5fcf1;
            --green-100: #e6f9df;
            --green-200: #cef1c1;
            --green-300: #a7e590;
            --green-400: #88d66c;
            --green-500: #54b631;
            --green-600: #419623;
            --green-700: #35761f;
            --green-800: #2d5e1d;
            --green-900: #264d1a;
            --green-950: #102a09;
            --primary: var(--blue-700);
            --primary-dark: var(--blue-800);
            --primary-light: var(--blue-500);
            --accent: var(--yellow-600);
            --success: var(--green-700);
            --success-bg: var(--green-50);
            --warning: var(--yellow-800);
            --warning-bg: var(--yellow-50);
            --danger: var(--red-700);
            --danger-bg: var(--red-50);
            --primary-rgb: 35 95 210;
            --accent-rgb: 197 161 2;
            --accent-soft-rgb: 255 250 141;
            --success-rgb: 53 118 31;
            --warning-rgb: 127 93 12;
            --danger-rgb: 207 6 33;
            --surface-light: var(--blue-50);
            --surface-secondary: var(--blue-100);
            --surface-alt: var(--blue-200);
            --border-accent: var(--blue-300);
            --surface-hover: var(--blue-50);
            --surface-selected: var(--blue-100);
            --text-secondary: var(--blue-900);
            --sidebar-bg: #ffffff;
            --sidebar-hover: #e6e6e6;
            --sidebar-active: #dcedfd;
            --sidebar-active-text: #382719;
            --sidebar-indicator: #B27D55;
            --sidebar-border: #d3d3d3;
            --sidebar-badge-bg: #F3E2D0;
            --sidebar-text: #633719;
            --sidebar-text-active: #382719;
            --topbar-bg: #FFFFFF;
            --card-bg: var(--blue-50);
            --text-primary: var(--blue-950);
            --text-muted: var(--blue-800);
            --border: var(--blue-200);
            --input: var(--blue-300);
            --topbar-primary: #633719;
            --topbar-accent: #B27D55;
            --topbar-danger: #C65C4B;
            --topbar-border: #d3d3d3;
            --topbar-hover: #F8E9D8;
            --topbar-focus: #633719;
            --topbar-focus-rgb: 99 55 25;
            --bs-primary: var(--primary);
            --bs-primary-rgb: var(--primary-rgb);
            --bs-success: var(--success);
            --bs-success-rgb: var(--success-rgb);
            --bs-warning: var(--warning);
            --bs-warning-rgb: var(--warning-rgb);
            --bs-danger: var(--danger);
            --bs-danger-rgb: var(--danger-rgb);
            --bs-body-bg: var(--card-bg);
            --bs-body-color: var(--text-primary);
            --bs-border-color: var(--border);
            --bs-link-color: var(--primary);
            --bs-link-hover-color: var(--primary-dark);
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, .10);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, .14);
            --radius: 12px;
            --transition: 0.25s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
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
            border-right: 1px solid var(--sidebar-border);
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
            text-decoration: none;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-icon {
            width: 36px;
            height: 36px;
            background: var(--sidebar-active);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--sidebar-active-text);
            flex-shrink: 0;
        }

        .sidebar-brand .brand-text {
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--sidebar-text);
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
            scrollbar-color: var(--sidebar-border) transparent;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: var(--sidebar-border);
            border-radius: 4px;
        }

        .nav-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--sidebar-text);
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

        .sidebar-nav details > summary {
            list-style: none;
            cursor: pointer;
        }

        .sidebar-nav details > summary::-webkit-details-marker {
            display: none;
        }

        .sidebar-nav .nav-chevron {
            font-size: 0.7rem;
            transition: transform 220ms ease, opacity var(--transition);
        }

        .sidebar-nav details[open] > summary .nav-chevron {
            transform: rotate(90deg);
        }

        .sidebar-nav .nav-submenu {
            height: 0;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-6px);
            transition: height 220ms ease, opacity 220ms ease, transform 220ms ease;
        }

        .sidebar-nav details[open] > .nav-submenu {
            height: auto;
            opacity: 1;
            transform: translateY(0);
        }

        .sidebar-nav .nav-submenu .nav-link {
            padding: 9px 20px 9px 66px;
            font-size: 0.8rem;
        }

        #sidebar.collapsed .nav-submenu {
            display: none;
        }

        #sidebar.collapsed .nav-chevron {
            opacity: 0;
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
            background: var(--sidebar-hover);
        }

        .sidebar-nav .nav-link.active {
            color: var(--sidebar-active-text);
            background: var(--sidebar-active);
        }

        .sidebar-nav .nav-link.active .nav-icon {
            background: var(--sidebar-active);
            color: var(--sidebar-active-text);
        }

        /* Badge */
        .nav-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            background: var(--sidebar-badge-bg);
            color: var(--sidebar-text);
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
            border-top: 1px solid var(--sidebar-border);
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
            background: var(--sidebar-active);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--sidebar-active-text);
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
            color: var(--sidebar-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user .user-role {
            font-size: 0.7rem;
            color: var(--sidebar-text);
        }

        .sidebar-user .user-logout {
            color: var(--sidebar-text);
            font-size: 0.85rem;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            transition: color var(--transition);
            flex-shrink: 0;
        }

        .sidebar-user .user-logout:hover {
            color: var(--sidebar-text);
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
            border-bottom: 1px solid var(--topbar-border);
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
            background: var(--topbar-hover);
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
            background: var(--topbar-hover);
            color: var(--text-primary);
        }

        .topbar-btn .dot {
            position: absolute;
            top: 7px;
            right: 7px;
            width: 7px;
            height: 7px;
            background: var(--topbar-danger);
            border-radius: 50%;
            border: 1.5px solid #FFFCF8;
        }

        .topbar-divider {
            width: 1px;
            height: 28px;
            background: var(--topbar-border);
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
            background: var(--topbar-hover);
        }

        .topbar-user .avatar {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, var(--topbar-primary), var(--topbar-accent));
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            color: #FFFCF8;
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
            background-color: #fafafa;
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

        .alert-success,
        .alert-banner.alert-success {
            color: var(--success);
            background: var(--success-bg);
        }

        .alert-warning,
        .alert-banner.alert-warning {
            color: var(--warning);
            background: var(--warning-bg);
        }

        .alert-danger,
        .alert-banner.alert-danger {
            color: var(--danger);
            background: var(--danger-bg);
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
    <link rel="stylesheet" href="{{ asset('css/ui-geometry.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-animations.css') }}">
    <style>
        #main-content input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not(:disabled):not(.is-invalid),
        #main-content select:not(:disabled):not(.is-invalid),
        #main-content textarea:not(:disabled):not(.is-invalid),
        .modal input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not(:disabled):not(.is-invalid),
        .modal select:not(:disabled):not(.is-invalid),
        .modal textarea:not(:disabled):not(.is-invalid) {
            border-color: var(--input);
            background-color: var(--card-bg);
        }

        #main-content input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not(:disabled):not(.is-invalid):focus,
        #main-content select:not(:disabled):not(.is-invalid):focus,
        #main-content textarea:not(:disabled):not(.is-invalid):focus,
        .modal input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not(:disabled):not(.is-invalid):focus,
        .modal select:not(:disabled):not(.is-invalid):focus,
        .modal textarea:not(:disabled):not(.is-invalid):focus,
        #topbar .form-select:focus {
            border-color: var(--topbar-focus);
            outline: none;
            box-shadow: 0 0 0 3px rgba(var(--topbar-focus-rgb), 0.12);
        }

        #topbar .form-select:not(:disabled) {
            border-color: var(--topbar-border);
            background-color: var(--topbar-bg);
        }

        #main-content .btn-cancel:hover,
        #main-content .btn-back:hover,
        #main-content .btn-modal-cancel:hover,
        #main-content .btn-qa-outline:hover,
        .modal .btn-cancel:hover,
        .modal .btn-modal-cancel:hover,
        .modal .btn-qa-outline:hover {
            border-color: var(--border);
            background: var(--surface-hover);
            color: var(--text-primary);
        }

        #main-content .data-table tbody tr:hover,
        #main-content .items-table tbody tr:hover,
        #main-content .pos-table tbody tr:hover {
            background-color: var(--surface-hover);
        }

        *,
        *::before,
        *::after {
            color: var(--text-primary) !important;
            border-color: var(--border) !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        #main-content .content-card,
        #main-content .kpi-card {
            background-color: #fff !important;
        }

        ::placeholder {
            color: var(--text-muted) !important;
        }

        #sidebar,
        #sidebar *,
        #sidebar *::before,
        #sidebar *::after,
        #topbar,
        #topbar *,
        #topbar *::before,
        #topbar *::after {
            color: #1a1a1a !important;
            border-color: #d3d3d3 !important;
        }

        #topbar ::placeholder {
            color: #1a1a1a !important;
        }

        #sidebar .sidebar-nav .nav-link:not(.active) .nav-icon,
        #sidebar .sidebar-nav .nav-link:not(.active) .nav-icon i,
        #sidebar .sidebar-nav .nav-link:not(.active) .nav-icon i::before {
            color: #404040 !important;
        }

        #sidebar .sidebar-nav .nav-link.active,
        #sidebar .sidebar-nav .nav-link.active * {
            color: #235fd2 !important;
        }

        #main-content .alert-success,
        #main-content .alert-success *,
        #main-content .text-success,
        #main-content .badge-success,
        #main-content .stock-ok {
            color: var(--success) !important;
        }

        #main-content .alert-warning,
        #main-content .alert-warning *,
        #main-content .text-warning,
        #main-content .badge-warning,
        #main-content .stock-low {
            color: var(--warning) !important;
        }

        #main-content .alert-danger,
        #main-content .alert-danger *,
        #main-content .text-danger,
        #main-content .badge-danger,
        #main-content .stock-empty {
            color: var(--danger) !important;
        }

        #main-content .btn-primary,
        #main-content .btn-qa-primary,
        #main-content .btn-qa-success,
        #main-content .btn-add,
        #main-content .btn-submit,
        #main-content .btn-setup,
        #main-content .btn-login,
        #main-content .btn-register,
        #main-content .btn-detail:hover,
        #main-content .btn-back:hover,
        #main-content .btn-stock:hover,
        #main-content .btn-edit:hover,
        #main-content .btn-delete:hover,
        #main-content .btn-action:hover,
        #main-content .btn-give,
        #main-content .btn-pull:hover:not(:disabled),
        #main-content .page-item.active .page-link {
            color: var(--blue-50) !important;
        }

        #main-content .amount-highlight,
        #main-content .amount-highlight *,
        #main-content .setup-icon,
        #main-content .employee-avatar,
        #main-content .badge-kategori {
            color: var(--blue-50) !important;
        }

        #main-content .badge-kategori[style*="#c5a102"] {
            color: var(--yellow-950) !important;
        }

        #main-content .kpi-card.primary .kpi-icon {
            color: var(--blue-700) !important;
        }

        #main-content .kpi-card.success .kpi-icon {
            color: var(--green-700) !important;
        }

        #main-content .kpi-card.warning .kpi-icon,
        #main-content .kpi-card.accent .kpi-icon {
            color: var(--yellow-800) !important;
        }

        #main-content .kpi-card.danger .kpi-icon {
            color: var(--red-700) !important;
        }

        #main-content :focus-visible {
            outline-color: var(--primary) !important;
        }

        .sidebar-nav .nav-link:not(.active) .nav-icon,
        .sidebar-nav .nav-link:not(.active) .nav-icon i,
        .sidebar-nav .nav-link:not(.active) .nav-icon i::before {
            color: #404040 !important;
        }

        .sidebar-nav .nav-link.active,
        .sidebar-nav .nav-link.active * {
            color: #235fd2 !important;
        }

        .sidebar-nav .nav-link.active .nav-icon {
            color: #235fd2 !important;
        }

        .sidebar-nav .nav-link.active .nav-icon i,
        .sidebar-nav .nav-link.active .nav-icon i::before {
            color: #235fd2 !important;
        }

        .border-transparent,
        .btn-qa:not(.btn-qa-outline),
        .btn-qa-primary,
        .btn-qa-success,
        .role-pegawai,
        .btn-pull:hover:not(:disabled) {
            border-color: transparent !important;
        }

        .employee-manage .spin,
        .submit-spinner {
            border-right-color: transparent !important;
        }

        :focus-visible {
            outline: 2px solid #0b0b0b !important;
            outline-offset: 2px;
        }
    </style>
</head>

<body class="{{ $isAuthPage ? 'auth-page' : '' }} preload-transitions">
    <!-- ============================================================
         SIDEBAR
    ============================================================ -->
    @unless ($isAuthPage)
        <aside id="sidebar">
        <!-- Brand -->
        <a class="sidebar-brand" href="{{ route('home') }}">
            <div class="brand-icon"><i class="fas fa-chart-line"></i></div>
            <span class="brand-text">{{ strtoupper(Auth::check() && Auth::user()->store ? Auth::user()->store->name : 'SIMKEU') }}</span>
        </a>

        <!-- Navigation -->
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
                <!-- Menu "Cabang" dihapus -->
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
                style="width:148px; border-radius:8px; font-size:.8rem; border-color:var(--input);"
                title="Filter periode data dashboard"
            >
                <option value="today">Hari Ini</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
                <option value="all">Semua Periode</option>
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
    <script src="{{ asset('js/app-animations.js') }}"></script>

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
            document.documentElement.classList.toggle('sidebar-is-collapsed', collapsed);
            localStorage.setItem('sidebarCollapsed', collapsed ? 'true' : 'false');     // Simpan status ke localStorage
        }

        // Hapus class preload-transitions setelah frame pertama selesai di-paint agar animasi layout stabil
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                document.body.classList.remove('preload-transitions');
            });
        });

        // Event listener: klik tombol hamburger / toggle
        toggle?.addEventListener('click', () => {
            if (isMobile()) {
                // Di mobile: buka atau tutup sidebar overlay
                sidebar.classList.contains('mobile-open') ? closeMobileSidebar() : openMobileSidebar();
            } else {
                // Di desktop: toggle collapsed state
                toggleDesktopSidebar();
            }
        });

        // Klik overlay di mobile akan menutup sidebar
        overlay?.addEventListener('click', closeMobileSidebar);

        // Pulihkan status collapse sidebar dari localStorage saat halaman dimuat (desktop saja)
        if (sidebar && topbar && main && !isMobile() && (document.documentElement.classList.contains('sidebar-is-collapsed') || localStorage.getItem('sidebarCollapsed') === 'true')) {
            sidebar.classList.add('collapsed');          // Sidebar dalam kondisi collapsed
            topbar.classList.add('sidebar-collapsed');   // Topbar menyesuaikan
            main.classList.add('sidebar-collapsed');     // Konten menyesuaikan
            document.documentElement.classList.add('sidebar-is-collapsed');
        }

        // Saat layar di-resize dari mobile ke desktop, tutup sidebar mobile jika terbuka
        window.addEventListener('resize', () => {
            if (sidebar && overlay && !isMobile()) closeMobileSidebar();
        });

        // ============================================================
        // PERIOD FILTER LOGIC
        // Mengelola filter periode global di topbar
        //
        // Cara kerja:
        //   1. Periode URL menjadi state aktif; localStorage & cookie dipakai saat
        //      halaman relevan dibuka sehingga server & client selalu sinkron.
        //   2. Menggunakan history.replaceState (BUKAN location.replace) agar
        //      tidak terjadi double-reload yang menyebabkan layar berkedip/flicker.
        //   3. Href navigasi otomatis disinkronkan ke periode aktif.
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
            const supportedPeriods = ['all', 'today', 'week', 'month'];
            const periodFromUrl = urlParams.get('period');
            const savedPeriod = localStorage.getItem('dashboardPeriod') || 'month';
            let activePeriod = supportedPeriods.includes(periodFromUrl)
                ? periodFromUrl
                : supportedPeriods.includes(savedPeriod) ? savedPeriod : 'month';

            // Setel nilai awal select sesuai periode aktif
            periodSelect.value = activePeriod;

            // Simpan ke localStorage & cookie agar konsisten di semua halaman
            localStorage.setItem('dashboardPeriod', activePeriod);
            document.cookie = `dashboardPeriod=${activePeriod}; path=/; max-age=31536000; SameSite=Lax`;

            function supportsPeriod(path) {
                return path === '/' || path === '/dashboard'
                    || path === '/pemasukan' || path.startsWith('/pemasukan/')
                    || path === '/pengeluaran' || path.startsWith('/pengeluaran/')
                    || /^\/employees\/[^/]+\/(manage|sales-dashboard)$/.test(path);
            }

            // Jika query param belum ada di URL, sinkronkan query string menggunakan replaceState
            // TANPA me-reload halaman agar tidak ada flicker/kedipan
            if (supportsPeriod(window.location.pathname) && !supportedPeriods.includes(periodFromUrl)) {
                urlParams.set('period', activePeriod);
                window.history.replaceState({}, '', `${window.location.pathname}?${urlParams.toString()}`);
            }

            function syncPeriodLinks() {
                document.querySelectorAll('a[href]').forEach((link) => {
                    if (link.target && link.target.toLowerCase() !== '_self') return;
                    if (link.hasAttribute('download')) return;
                    try {
                        const destination = new URL(link.href, window.location.href);
                        if (destination.origin === window.location.origin && supportsPeriod(destination.pathname)) {
                            destination.searchParams.set('period', activePeriod);
                            link.href = destination.toString();
                        }
                    } catch (e) {}
                });
            }

            syncPeriodLinks();

            document.addEventListener('click', (event) => {
                if (event.defaultPrevented || event.button !== 0
                    || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

                const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
                if (!link || link.target && link.target.toLowerCase() !== '_self'
                    || link.hasAttribute('download')) return;

                const destination = new URL(link.href, window.location.href);
                if (destination.origin === window.location.origin && supportsPeriod(destination.pathname)) {
                    destination.searchParams.set('period', activePeriod);
                    link.href = destination.toString();
                }
            }, true);

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'get') return;

                const destination = new URL(form.action, window.location.href);
                if (!supportsPeriod(destination.pathname)) return;

                let periodInput = form.querySelector('input[name="period"]');
                if (!periodInput) {
                    periodInput = document.createElement('input');
                    periodInput.type = 'hidden';
                    periodInput.name = 'period';
                    form.appendChild(periodInput);
                }
                periodInput.value = activePeriod;
            }, true);

            // -------------------------------------------------------
            // Event listener: saat user mengubah pilihan periode
            // -------------------------------------------------------
            periodSelect.addEventListener('change', function () {
                const selectedPeriod = this.value; // Nilai periode yang baru dipilih
                activePeriod = selectedPeriod;

                // Simpan nilai yang dipilih ke localStorage & cookie untuk persistensi
                localStorage.setItem('dashboardPeriod', selectedPeriod);
                document.cookie = `dashboardPeriod=${selectedPeriod}; path=/; max-age=31536000; SameSite=Lax`;
                syncPeriodLinks();

                // Periksa apakah halaman saat ini adalah halaman dashboard/keuangan
                if (supportsPeriod(window.location.pathname)) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('period', selectedPeriod);
                    ['page', 'sales_page', 'products_page'].forEach((page) => url.searchParams.delete(page));
                    window.location.href = url.toString();
                }
            });
        })();
    </script>

    <script>
        document.querySelectorAll('.sidebar-nav details > summary').forEach((summary) => {
            const details = summary.parentElement;
            const submenu = details.querySelector(':scope > .nav-submenu');
            if (!submenu) return;

            summary.addEventListener('click', (event) => {
                event.preventDefault();

                const isClosing = details.dataset.closing === 'true';
                const shouldOpen = isClosing || !details.open;
                const currentHeight = submenu.getBoundingClientRect().height;

                submenu.style.height = `${currentHeight}px`;
                submenu.style.opacity = getComputedStyle(submenu).opacity;
                submenu.style.transform = getComputedStyle(submenu).transform;
                submenu.offsetHeight;

                if (shouldOpen) {
                    details.open = true;
                    delete details.dataset.closing;
                    requestAnimationFrame(() => {
                        submenu.style.height = `${submenu.scrollHeight}px`;
                        submenu.style.opacity = '1';
                        submenu.style.transform = 'translateY(0)';
                    });
                    return;
                }

                details.dataset.closing = 'true';
                requestAnimationFrame(() => {
                    submenu.style.height = '0px';
                    submenu.style.opacity = '0';
                    submenu.style.transform = 'translateY(-6px)';
                });
            });

            submenu.addEventListener('transitionend', (event) => {
                if (event.propertyName !== 'height') return;

                if (details.dataset.closing === 'true') {
                    details.open = false;
                    delete details.dataset.closing;
                }
                if (details.open) {
                    submenu.style.removeProperty('height');
                    submenu.style.removeProperty('opacity');
                    submenu.style.removeProperty('transform');
                }
            });
        });
    </script>

    {{-- Slot untuk JavaScript tambahan dari halaman turunan (via @section('js')) --}}
    @yield('js')
    <script>
        (() => {
            const page = document.querySelector('.employee-manage');
            if (!page) return;

            window.refreshPendingStockAllocations = async () => {
                const response = await fetch(@json(route('employees.stock.allocations.pending-status')), {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });
                const result = await response.json();
                if (!response.ok || result.success !== true || !Array.isArray(result.data)) {
                    throw new Error(result.message || 'Gagal memperbarui status alokasi stok.');
                }

                const employeeId = page?.dataset.employeeId;
                const pendingProductIds = new Set(result.data
                    .filter((allocation) => String(allocation.employee_id) === String(employeeId))
                    .map((allocation) => String(allocation.product_id)));

                document.querySelectorAll('[data-pending-allocation-icon]').forEach((icon) => {
                    icon.hidden = !pendingProductIds.has(icon.dataset.productId);
                });
            };

            const refresh = () => window.refreshPendingStockAllocations()
                .catch((error) => console.error(error));
            refresh();
            window.setInterval(refresh, 10000);
        })();
    </script>
</body>

</html>