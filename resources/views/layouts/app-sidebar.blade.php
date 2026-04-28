<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') - {{ $siteSettings->site_name ?? 'Homedelvalle' }}</title>
    @if($siteSettings->favicon_path ?? false)
    <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteSettings->favicon_path) }}">
    @endif
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: {{ $siteSettings->primary_color ?? '#667eea' }};
            --primary-dark: {{ $siteSettings->secondary_color ?? '#764ba2' }};
            --sidebar-bg: #1e1b4b;
            --sidebar-hover: #312e81;
            --sidebar-text: #c7d2fe;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --radius: 8px;
            --sidebar-w: 260px;
        }

        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); font-size: 14px; -webkit-font-smoothing: antialiased; }
        a { text-decoration: none; color: inherit; }

        /* ===== APP CONTAINER ===== */
        .app-container { display: flex; min-height: 100vh; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-w); background: var(--sidebar-bg); color: var(--sidebar-text);
            display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100;
            transition: transform 0.3s ease; overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex; align-items: center; gap: 0.75rem; min-height: 66px;
        }
        .sidebar-header img { max-height: 36px; max-width: 100%; object-fit: contain; }
        .sidebar-brand { display: flex; align-items: center; gap: 0.75rem; }
        .brand-icon {
            width: 34px; height: 34px; background: var(--primary); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px;
        }
        .brand-text { font-size: 1.1rem; font-weight: 700; color: #fff; letter-spacing: -0.3px; }

        /* Nav */
        .nav-section { margin-bottom: 0.5rem; }
        .nav-label {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.75rem 1.5rem 0.4rem; font-size: 0.65rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1.2px; color: rgba(199,210,254,0.4);
            cursor: pointer; user-select: none;
        }
        .nav-label:hover { color: rgba(199,210,254,0.6); }
        .nav-label .nav-chevron { transition: transform 0.2s; display: inline-flex; }
        .nav-section.collapsed .nav-chevron { transform: rotate(-90deg); }
        .nav-section.collapsed .nav-items { display: none; }
        .nav-item {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 1.5rem;
            color: var(--sidebar-text); font-size: 0.88rem; border-left: 3px solid transparent;
            transition: all 0.15s;
        }
        .nav-item:hover { background: var(--sidebar-hover); color: #fff; }
        .nav-item.active { background: rgba(255,255,255,0.08); color: #fff; border-left-color: var(--primary); font-weight: 500; }
        .nav-icon { width: 18px; height: 18px; text-align: center; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; }
        .nav-icon svg { width: 16px; height: 16px; }

        /* User Card (bottom) */
        .sidebar-footer { padding: 0.75rem; border-top: 1px solid rgba(255,255,255,0.08); margin-top: auto; }
        .user-card { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem; border-radius: var(--radius); transition: all 0.15s; }
        .user-card:hover { background: var(--sidebar-hover); }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
            background: var(--primary); display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 600; font-size: 0.85rem;
        }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-meta { flex: 1; overflow: hidden; }
        .user-name { font-size: 0.82rem; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 0.68rem; color: var(--sidebar-text); text-transform: capitalize; }
        .footer-actions { display: flex; gap: 0.35rem; margin-top: 0.4rem; }
        .footer-action-btn {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.25rem 0.55rem; border-radius: 4px; font-size: 0.68rem; font-weight: 500;
            font-family: inherit; cursor: pointer; transition: all 0.15s; border: none; text-decoration: none;
        }
        .btn-view-site {
            background: none; color: rgba(199,210,254,0.5);
        }
        .btn-view-site:hover { background: rgba(99,102,241,0.15); color: #c7d2fe; }
        .btn-logout {
            background: none; color: rgba(199,210,254,0.5);
        }
        .btn-logout:hover { background: rgba(239,68,68,0.15); color: #fca5a5; }

        /* ===== MAIN CONTENT ===== */
        .main-content { flex: 1; margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            height: 56px; background: var(--card); border-bottom: 1px solid var(--border);
            display: flex; align-items: center; padding: 0 1.5rem; gap: 1rem;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-toggle { display: none; background: none; border: none; cursor: pointer; font-size: 20px; padding: 0.4rem; color: var(--text-muted); }
        .topbar-title { font-size: 1.05rem; font-weight: 600; flex: 1; }
        .content-body { flex: 1; padding: 1.5rem; }

        /* ===== ALERTS ===== */
        .alert { display: flex; align-items: center; gap: 0.75rem; padding: 0.8rem 1.2rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.88rem; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; font-size: 18px; }

        /* ===== CARDS ===== */
        .card { background: var(--card); border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid var(--border); margin-bottom: 1.5rem; }
        .card-header { padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-header h3 { font-size: 0.95rem; font-weight: 600; }
        .card-body { padding: 1.5rem; }

        /* ===== BUTTONS ===== */
        .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1.1rem; font-size: 0.85rem; font-weight: 500; border-radius: var(--radius); border: 1px solid transparent; cursor: pointer; transition: all 0.15s; font-family: inherit; }
        .btn-primary { background: var(--primary); color: #fff; border-color: var(--primary); }
        .btn-primary:hover { opacity: 0.9; }
        .btn-outline { background: transparent; color: var(--text-muted); border-color: var(--border); }
        .btn-outline:hover { background: var(--bg); color: var(--text); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-sm { padding: 0.3rem 0.7rem; font-size: 0.78rem; }

        /* ===== FORMS ===== */
        .form-group { margin-bottom: 1.1rem; }
        .form-label { display: block; font-size: 0.82rem; font-weight: 500; margin-bottom: 0.35rem; }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 0.55rem 0.8rem; font-size: 0.88rem; font-family: inherit;
            border: 1px solid var(--border); border-radius: var(--radius); background: var(--card); color: var(--text);
            outline: none; transition: all 0.15s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .form-textarea { resize: vertical; min-height: 80px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0 1.25rem; }
        .form-grid .full-width { grid-column: 1 / -1; }
        .form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--border); margin-top: 0.5rem; }
        .form-hint { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }
        .required { color: var(--danger); }

        /* ===== TABLE ===== */
        .table-wrap { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { padding: 0.8rem 1.2rem; text-align: left; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); background: var(--bg); border-bottom: 1px solid var(--border); }
        .data-table td { padding: 0.8rem 1.2rem; border-bottom: 1px solid var(--border); font-size: 0.88rem; }
        .data-table tr:hover { background: rgba(248,250,252,0.8); }

        /* ===== BADGES ===== */
        .badge { display: inline-flex; padding: 0.15rem 0.55rem; font-size: 0.72rem; font-weight: 500; border-radius: 20px; }
        .badge-green { background: #ecfdf5; color: #065f46; }
        .badge-red { background: #fef2f2; color: #991b1b; }
        .badge-blue { background: #eef2ff; color: #3730a3; }
        .badge-yellow { background: #fffbeb; color: #92400e; }

        /* ===== STATS ===== */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 1.5rem; }
        .stat-card { background: var(--card); border-radius: 10px; padding: 1.2rem; display: flex; align-items: center; gap: 1rem; border: 1px solid var(--border); }
        .stat-icon { width: 46px; height: 46px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; }
        .stat-value { font-size: 1.4rem; font-weight: 700; line-height: 1.2; }
        .stat-label { font-size: 0.78rem; color: var(--text-muted); }
        .bg-blue { background: #3b82f6; } .bg-green { background: #10b981; } .bg-purple { background: #8b5cf6; } .bg-orange { background: #f59e0b; }

        /* ===== AVATAR PROFILE ===== */
        .profile-avatar {
            width: 110px; height: 110px; border-radius: 50%; position: relative; cursor: pointer;
            overflow: hidden; box-shadow: 0 0 0 4px var(--card), 0 0 0 6px var(--primary); margin: 0 auto 1rem;
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-avatar .avatar-placeholder {
            width: 100%; height: 100%; background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 600;
        }
        .profile-avatar .overlay {
            position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; flex-direction: column;
            align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; color: #fff;
        }
        .profile-avatar:hover .overlay { opacity: 1; }
        .overlay-text { font-size: 0.7rem; font-weight: 500; margin-top: 2px; }

        /* ===== PAGE HEADER ===== */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h2 { font-size: 1.25rem; font-weight: 600; }
        .text-muted { color: var(--text-muted); }
        .text-center { text-align: center; }
        .mb-1 { margin-bottom: 0.5rem; }

        /* ===== WELCOME BANNER ===== */
        .welcome-banner {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;
            padding: 1.25rem 1.5rem; background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px; color: #fff;
        }
        .welcome-banner h2 { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.2rem; }
        .welcome-banner p { opacity: 0.85; font-size: 0.88rem; }

        /* ===== OVERLAY ===== */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; }

        /* ===== USER LIST ITEM ===== */
        .user-cell { display: flex; align-items: center; gap: 0.75rem; }
        .user-cell .avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600; overflow: hidden; flex-shrink: 0; }
        .user-cell .avatar img { width: 100%; height: 100%; object-fit: cover; }
        .action-btns { display: flex; gap: 0.25rem; }

        /* ===== COLOR PICKER ===== */
        .color-group { display: flex; gap: 0.75rem; align-items: center; }
        .color-picker { width: 44px; height: 36px; padding: 2px; border: 1px solid var(--border); border-radius: var(--radius); cursor: pointer; background: transparent; }

        /* ===== LOGO PREVIEW ===== */
        .logo-preview {
            width: 160px; height: 100px; margin: 0 auto 1rem; border: 2px dashed var(--border);
            border-radius: var(--radius); display: flex; align-items: center; justify-content: center; overflow: hidden;
        }
        .logo-preview img { max-width: 100%; max-height: 100%; object-fit: contain; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .form-grid { grid-template-columns: 1fr; }
        }
        /* Hidden on desktop, shown on mobile */
        .mobile-bottom-nav, .mobile-fab { display: none; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; }
            .topbar-toggle { display: block; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
            .content-body { padding-bottom: 5rem; }

            /* ===== MOBILE BOTTOM NAV ===== */
            .mobile-bottom-nav {
                display: flex;
                position: fixed; bottom: 0; left: 0; right: 0; z-index: 90;
                background: var(--card); border-top: 1px solid var(--border);
                box-shadow: 0 -2px 10px rgba(0,0,0,0.06);
                height: 60px; align-items: stretch;
                padding-bottom: env(safe-area-inset-bottom, 0);
            }
            .bnav-item {
                flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
                gap: 2px; color: var(--text-muted); font-size: 0.62rem; font-weight: 500;
                text-decoration: none; position: relative; transition: color 0.15s;
                -webkit-tap-highlight-color: transparent;
            }
            .bnav-item.active { color: var(--primary); }
            .bnav-item.active .bnav-icon { color: var(--primary); }
            .bnav-icon { font-size: 20px; line-height: 1; display: inline-flex; align-items: center; justify-content: center; }
            .bnav-icon svg { width: 20px; height: 20px; }
            .bnav-label { letter-spacing: 0.2px; }
            .bnav-badge {
                position: absolute; top: 4px; right: calc(50% - 18px);
                min-width: 16px; height: 16px; background: var(--danger); color: #fff;
                font-size: 0.58rem; font-weight: 700; border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
            }

            /* FAB for new lead */
            .mobile-fab {
                position: fixed; bottom: 72px; right: 16px; z-index: 91;
                width: 52px; height: 52px; border-radius: 50%; border: none;
                background: var(--primary); color: #fff; font-size: 26px; font-weight: 300;
                box-shadow: 0 4px 14px rgba(102,126,234,0.4);
                display: flex; align-items: center; justify-content: center;
                cursor: pointer; transition: transform 0.15s, box-shadow 0.15s;
                -webkit-tap-highlight-color: transparent;
            }
            .mobile-fab:active { transform: scale(0.92); box-shadow: 0 2px 8px rgba(102,126,234,0.3); }
        }
        @media (max-width: 480px) { .stats-grid { grid-template-columns: 1fr; } .content-body { padding: 1rem; } }

        /* Image lazy loading */
        .img-loader-wrap { position: relative; overflow: hidden; background: var(--bg); border-radius: var(--radius); }
        .img-skeleton {
            position: absolute; inset: 0; background: linear-gradient(90deg, var(--bg) 25%, rgba(255,255,255,0.08) 50%, var(--bg) 75%);
            background-size: 200% 100%; animation: skeleton-pulse 1.5s infinite ease-in-out;
        }
        @keyframes skeleton-pulse { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .img-lazy { width: 100%; height: auto; display: block; opacity: 0; transition: opacity 0.4s ease; }
        .img-lazy.img-loaded { opacity: 1; }

        /* ===== TOAST NOTIFICATIONS ===== */
        #toast-container {
            position: fixed; bottom: 1.25rem; right: 1.25rem; z-index: 9999;
            display: flex; flex-direction: column; gap: 0.5rem; pointer-events: none;
        }
        .toast {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.7rem 1rem; border-radius: 8px;
            font-size: 0.85rem; font-weight: 500; max-width: 360px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.14);
            pointer-events: all; cursor: pointer;
            animation: toast-in 0.25s ease; transition: opacity 0.3s, transform 0.3s;
        }
        .toast.hiding { opacity: 0; transform: translateX(100%); }
        .toast-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .toast-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .toast-info    { background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .toast-icon { font-size: 1rem; flex-shrink: 0; }
        @keyframes toast-in { from { opacity: 0; transform: translateX(60px); } to { opacity: 1; transform: translateX(0); } }

        @yield('styles')
    </style>
</head>
<body>
    @php
        $siteSettings = $siteSettings ?? \App\Models\SiteSetting::first();
        $currentUser = auth()->user();
    @endphp

    <div class="app-container">
        {{-- ===== SIDEBAR ===== --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                @if($siteSettings?->logo_path_dark)
                    <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
                        <img src="{{ Storage::url($siteSettings->logo_path_dark) }}" alt="Logo" style="max-height:36px; max-width:85%; object-fit:contain;">
                    </div>
                @else
                    <div class="sidebar-brand">
                        <div class="brand-icon"><x-icon name="home" class="w-[18px] h-[18px]" /></div>
                        <span class="brand-text">{{ $siteSettings->site_name ?? 'Homedelvalle' }}</span>
                    </div>
                @endif
            </div>

            <nav style="flex:1; padding: 0.75rem 0; overflow-y: auto;">
                {{-- ===== PRINCIPAL ===== --}}
                <div class="nav-section" data-section="principal">
                    <span class="nav-label" onclick="toggleSection(this)">Principal <span class="nav-chevron"><x-icon name="chevron-down" class="w-3 h-3" /></span></span>
                    <div class="nav-items">
                        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="layout-dashboard" class="w-4 h-4" /></span> Dashboard
                        </a>
                        <a href="{{ route('admin.form-submissions.index') }}" class="nav-item {{ request()->routeIs('admin.form-submissions*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="clipboard-list" class="w-4 h-4" /></span> Leads
                            @php $newLeads = \App\Models\FormSubmission::where('status','new')->count(); @endphp
                            @if($newLeads > 0)
                            <span style="margin-left:auto;background:#f59e0b;color:#fff;font-size:0.65rem;font-weight:700;padding:0.1rem 0.4rem;border-radius:20px">{{ $newLeads > 99 ? '99+' : $newLeads }}</span>
                            @endif
                        </a>
                        <a href="{{ route('clients.index') }}" class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="users" class="w-4 h-4" /></span> Clientes
                        </a>
                        <a href="{{ route('properties.index') }}" class="nav-item {{ request()->routeIs('properties.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="building-2" class="w-4 h-4" /></span> Propiedades
                        </a>
                        @if(Route::has('tasks.index'))
                        <a href="{{ route('tasks.index') }}" class="nav-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="square-check" class="w-4 h-4" /></span> Tareas
                        </a>
                        @endif
                    </div>
                </div>

                {{-- ===== PROCESOS ===== --}}
                <div class="nav-section" data-section="procesos">
                    <span class="nav-label" onclick="toggleSection(this)">Procesos <span class="nav-chevron"><x-icon name="chevron-down" class="w-3 h-3" /></span></span>
                    <div class="nav-items">
                        @if(Route::has('admin.captaciones.index'))
                        <a href="{{ route('admin.captaciones.index') }}" class="nav-item {{ request()->routeIs('admin.captaciones.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="home" class="w-4 h-4" /></span> Evaluación de Propiedad
                        </a>
                        @endif
                        @if(Route::has('operations.index'))
                        <a href="{{ route('operations.index') }}" class="nav-item {{ request()->routeIs('operations.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="circle-play" class="w-4 h-4" /></span> Operaciones
                        </a>
                        @endif
                        @if(Route::has('admin.valuations.index'))
                        <a href="{{ route('admin.valuations.index') }}" class="nav-item {{ request()->routeIs('admin.valuations.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="bar-chart-3" class="w-4 h-4" /></span> Opinión de Valor
                        </a>
                        @endif
                    </div>
                </div>

                {{-- ===== HISTORICO (collapsed by default) ===== --}}
                @if(Route::has('deals.index') || Route::has('rentals.index'))
                <div class="nav-section collapsed" data-section="historico">
                    <span class="nav-label" onclick="toggleSection(this)">Historico <span class="nav-chevron"><x-icon name="chevron-down" class="w-3 h-3" /></span></span>
                    <div class="nav-items">
                        @if(Route::has('deals.index'))
                        <a href="{{ route('deals.index') }}" class="nav-item {{ request()->routeIs('deals.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="diamond" class="w-4 h-4" /></span> Deals
                        </a>
                        @endif
                        @if(Route::has('rentals.index'))
                        <a href="{{ route('rentals.index') }}" class="nav-item {{ request()->routeIs('rentals.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="home" class="w-4 h-4" /></span> Rentas
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                {{-- ===== EQUIPO ===== --}}
                @permission('users.view')
                <div class="nav-section" data-section="equipo">
                    <span class="nav-label" onclick="toggleSection(this)">Equipo <span class="nav-chevron"><x-icon name="chevron-down" class="w-3 h-3" /></span></span>
                    <div class="nav-items">
                        <a href="{{ route('brokers.index') }}" class="nav-item {{ request()->routeIs('brokers.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="star" class="w-4 h-4" /></span> Brokers Externos
                        </a>
                        <a href="{{ route('broker-companies.index') }}" class="nav-item {{ request()->routeIs('broker-companies.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="building" class="w-4 h-4" /></span> Empresas
                        </a>
                        <a href="{{ route('referrers.index') }}" class="nav-item {{ request()->routeIs('referrers.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="link" class="w-4 h-4" /></span> Comisionistas
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.users.permissions') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="user-cog" class="w-4 h-4" /></span> Usuarios
                        </a>
                        <a href="{{ route('admin.users.permissions') }}" class="nav-item {{ request()->routeIs('admin.users.permissions') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="shield-alert" class="w-4 h-4" /></span> Permisos
                        </a>
                    </div>
                </div>
                @endpermission

                {{-- ===== LEADS & MARKETING ===== --}}
                @permission('marketing.view')
                @if(Route::has('admin.submissions.index') || Route::has('admin.marketing.dashboard') || Route::has('admin.analytics'))
                <div class="nav-section" data-section="marketing">
                    <span class="nav-label" onclick="toggleSection(this)">Marketing <span class="nav-chevron"><x-icon name="chevron-down" class="w-3 h-3" /></span></span>
                    <div class="nav-items">
                        @if(Route::has('admin.analytics'))
                        <a href="{{ route('admin.analytics') }}" class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="bar-chart-3" class="w-4 h-4" /></span> Analytics
                        </a>
                        @endif
                        @if(Route::has('admin.submissions.index'))
                        <a href="{{ route('admin.submissions.index') }}" class="nav-item {{ request()->routeIs('admin.submissions.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="mail" class="w-4 h-4" /></span> Leads
                        </a>
                        @endif
                        @if(Route::has('admin.marketing.channels'))
                        <a href="{{ route('admin.marketing.channels') }}" class="nav-item {{ request()->routeIs('admin.marketing.channels*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="megaphone" class="w-4 h-4" /></span> Canales
                        </a>
                        @endif
                        @if(Route::has('admin.marketing.campaigns'))
                        <a href="{{ route('admin.marketing.campaigns') }}" class="nav-item {{ request()->routeIs('admin.marketing.campaigns*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="target" class="w-4 h-4" /></span> Campanas
                        </a>
                        @endif
                        @if(Route::has('admin.segments.index'))
                        <a href="{{ route('admin.segments.index') }}" class="nav-item {{ request()->routeIs('admin.segments.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="settings" class="w-4 h-4" /></span> Segmentos
                        </a>
                        @endif
                        @if(Route::has('admin.automations-engine.index'))
                        <a href="{{ route('admin.automations-engine.index') }}" class="nav-item {{ request()->routeIs('admin.automations-engine.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="zap" class="w-4 h-4" /></span> Automatizaciones
                        </a>
                        @endif
                        @if(Route::has('admin.scoring.index'))
                        <a href="{{ route('admin.scoring.index') }}" class="nav-item {{ request()->routeIs('admin.scoring.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="trophy" class="w-4 h-4" /></span> Lead Scoring
                        </a>
                        @endif
                        @if(Route::has('admin.messages.index'))
                        <a href="{{ route('admin.messages.index') }}" class="nav-item {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="message-circle" class="w-4 h-4" /></span> Mensajes
                        </a>
                        @endif
                        @if(Route::has('admin.newsletters.subscribers'))
                        <a href="{{ route('admin.newsletters.subscribers') }}" class="nav-item {{ request()->routeIs('admin.newsletters.subscribers*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="mail" class="w-4 h-4" /></span> Suscriptores
                        </a>
                        @endif
                        @if(Route::has('admin.newsletters.campaigns'))
                        <a href="{{ route('admin.newsletters.campaigns') }}" class="nav-item {{ request()->routeIs('admin.newsletters.campaigns*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="send" class="w-4 h-4" /></span> Newsletter
                        </a>
                        @endif
                    </div>
                </div>
                @endif
                @endpermission

                {{-- ===== FINANZAS ===== --}}
                @permission('finance.view')
                @if(Route::has('admin.finance.dashboard'))
                <div class="nav-section" data-section="finanzas">
                    <span class="nav-label" onclick="toggleSection(this)">Finanzas <span class="nav-chevron"><x-icon name="chevron-down" class="w-3 h-3" /></span></span>
                    <div class="nav-items">
                        <a href="{{ route('admin.finance.dashboard') }}" class="nav-item {{ request()->routeIs('admin.finance.dashboard') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="layout-dashboard" class="w-4 h-4" /></span> Resumen
                        </a>
                        @if(Route::has('admin.finance.transactions'))
                        <a href="{{ route('admin.finance.transactions') }}" class="nav-item {{ request()->routeIs('admin.finance.transactions*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="arrow-left-right" class="w-4 h-4" /></span> Transacciones
                        </a>
                        @endif
                        @if(Route::has('admin.finance.commissions'))
                        <a href="{{ route('admin.finance.commissions') }}" class="nav-item {{ request()->routeIs('admin.finance.commissions*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="star" class="w-4 h-4" /></span> Comisiones
                        </a>
                        @endif
                    </div>
                </div>
                @endif
                @endpermission

                {{-- ===== SITIO WEB / CMS ===== --}}
                @permission('cms.manage')
                @if(Route::has('admin.posts.index'))
                <div class="nav-section" data-section="cms">
                    <span class="nav-label" onclick="toggleSection(this)">Sitio Web <span class="nav-chevron"><x-icon name="chevron-down" class="w-3 h-3" /></span></span>
                    <div class="nav-items">
                        <a href="{{ route('admin.homepage') }}" class="nav-item {{ request()->routeIs('admin.homepage') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="home" class="w-4 h-4" /></span> Homepage
                        </a>
                        @if(Route::has('admin.servicios-page'))
                        <a href="{{ route('admin.servicios-page') }}" class="nav-item {{ request()->routeIs('admin.servicios-page') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="briefcase" class="w-4 h-4" /></span> Pag. Servicios
                        </a>
                        @endif
                        @if(Route::has('admin.nosotros-page'))
                        <a href="{{ route('admin.nosotros-page') }}" class="nav-item {{ request()->routeIs('admin.nosotros-page') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="users-round" class="w-4 h-4" /></span> Pag. Nosotros
                        </a>
                        @endif
                        @if(Route::has('admin.vender-page'))
                        <a href="{{ route('admin.vender-page') }}" class="nav-item {{ request()->routeIs('admin.vender-page') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="tag" class="w-4 h-4" /></span> Pag. Vender
                        </a>
                        @endif
                        @if(Route::has('admin.pages.index'))
                        <a href="{{ route('admin.pages.index') }}" class="nav-item {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="list" class="w-4 h-4" /></span> Paginas
                        </a>
                        @endif
                        <a href="{{ route('admin.posts.index') }}" class="nav-item {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="pen-line" class="w-4 h-4" /></span> Blog
                        </a>
                        @if(Route::has('admin.carousels.index'))
                        <a href="{{ route('admin.carousels.index') }}" class="nav-item {{ request()->routeIs('admin.carousels.index') || (request()->routeIs('admin.carousels.*') && !request()->routeIs('admin.carousels.image-test')) ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="layout-dashboard" class="w-4 h-4" /></span> Carruseles IG
                        </a>
                        <a href="{{ route('admin.carousels.image-test') }}" class="nav-item {{ request()->routeIs('admin.carousels.image-test') ? 'active' : '' }}" style="padding-left:2.75rem;font-size:.82rem;">
                            <span class="nav-icon"><x-icon name="image" class="w-3.5 h-3.5" /></span> Test imágenes IA
                        </a>
                        <a href="{{ route('admin.carousels.prompts') }}" class="nav-item {{ request()->routeIs('admin.carousels.prompts') ? 'active' : '' }}" style="padding-left:2.75rem;font-size:.82rem;">
                            <span class="nav-icon"><x-icon name="settings" class="w-3.5 h-3.5" /></span> Prompts IA
                        </a>
                        @endif
                        @if(Route::has('admin.facebook.index'))
                        <a href="{{ route('admin.facebook.index') }}" class="nav-item {{ request()->routeIs('admin.facebook.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="share-2" class="w-4 h-4" /></span> Posts Facebook
                        </a>
                        @endif
                        @if(Route::has('admin.content-calendar'))
                        <a href="{{ route('admin.content-calendar') }}" class="nav-item {{ request()->routeIs('admin.content-calendar*') ? 'active' : '' }}" style="padding-left: 2.5rem;">
                            <span class="nav-icon"><x-icon name="calendar" class="w-4 h-4" /></span> Calendario
                        </a>
                        @endif
                        @if(Route::has('admin.post-categories.index'))
                        <a href="{{ route('admin.post-categories.index') }}" class="nav-item {{ request()->routeIs('admin.post-categories.*') ? 'active' : '' }}" style="padding-left: 2.5rem;">
                            <span class="nav-icon"><x-icon name="list" class="w-4 h-4" /></span> Categorias
                        </a>
                        @endif
                        @if(Route::has('admin.tags.index'))
                        <a href="{{ route('admin.tags.index') }}" class="nav-item {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}" style="padding-left: 2.5rem;">
                            <span class="nav-icon"><x-icon name="flag" class="w-4 h-4" /></span> Etiquetas
                        </a>
                        @endif
                        @if(Route::has('admin.media.index'))
                        <a href="{{ route('admin.media.index') }}" class="nav-item {{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="camera" class="w-4 h-4" /></span> Medios
                        </a>
                        @endif
                        @if(Route::has('admin.menus.index'))
                        <a href="{{ route('admin.menus.index') }}" class="nav-item {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="menu" class="w-4 h-4" /></span> Menus
                        </a>
                        @endif
                        @if(Route::has('admin.forms.index'))
                        <a href="{{ route('admin.forms.index') }}" class="nav-item {{ request()->routeIs('admin.forms.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="clipboard-list" class="w-4 h-4" /></span> Formularios
                        </a>
                        @endif
                        @if(Route::has('admin.footer'))
                        <a href="{{ route('admin.footer') }}" class="nav-item {{ request()->routeIs('admin.footer*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="panel-bottom" class="w-4 h-4" /></span> Footer
                        </a>
                        @endif
                        @if(Route::has('admin.testimonials.index'))
                        <a href="{{ route('admin.testimonials.index') }}" class="nav-item {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="heart" class="w-4 h-4" /></span> Testimonios
                        </a>
                        @endif
                    </div>
                </div>
                @endif
                @endpermission

                {{-- ===== LEGAL ===== --}}
                @permission('system.config')
                <div class="nav-section" data-section="legal">
                    <span class="nav-label" onclick="toggleSection(this)">Legal <span class="nav-chevron"><x-icon name="chevron-down" class="w-3 h-3" /></span></span>
                    <div class="nav-items">
                        @if(Route::has('admin.legal.index'))
                        <a href="{{ route('admin.legal.index') }}" class="nav-item {{ request()->routeIs('admin.legal.index') || request()->routeIs('admin.legal.create') || request()->routeIs('admin.legal.edit') || request()->routeIs('admin.legal.show') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="scale" class="w-4 h-4" /></span> Documentos
                        </a>
                        @endif
                        @if(Route::has('admin.legal.acceptances'))
                        <a href="{{ route('admin.legal.acceptances') }}" class="nav-item {{ request()->routeIs('admin.legal.acceptances') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="square-check" class="w-4 h-4" /></span> Aceptaciones
                        </a>
                        @endif
                        @if(Route::has('admin.contract-templates.index'))
                        <a href="{{ route('admin.contract-templates.index') }}" class="nav-item {{ request()->routeIs('admin.contract-templates.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="file-text" class="w-4 h-4" /></span> Plantillas Contrato
                        </a>
                        @endif
                    </div>
                </div>
                @endpermission

                {{-- ===== CONFIGURACION ===== --}}
                @permission('system.config')
                <div class="nav-section" data-section="config">
                    <span class="nav-label" onclick="toggleSection(this)">Configuracion <span class="nav-chevron"><x-icon name="chevron-down" class="w-3 h-3" /></span></span>
                    <div class="nav-items">
                        <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="settings" class="w-4 h-4" /></span> General
                        </a>
                        <a href="{{ route('admin.ai-config') }}" class="nav-item {{ request()->routeIs('admin.ai-config*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="bot" class="w-4 h-4" /></span> Agentes IA
                        </a>
                        <a href="{{ route('admin.market.prices') }}" class="nav-item {{ request()->routeIs('admin.market.prices*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="trending-up" class="w-4 h-4" /></span> Precios Mercado
                        </a>
                        <a href="{{ route('admin.email.settings') }}" class="nav-item {{ request()->routeIs('admin.email.settings*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="mail" class="w-4 h-4" /></span> Correo SMTP
                        </a>
                        <a href="{{ route('admin.custom-templates.index') }}" class="nav-item {{ request()->routeIs('admin.custom-templates*') || request()->routeIs('admin.transactional-emails*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="mail" class="w-4 h-4" /></span> Email Templates
                        </a>
                        <a href="{{ route('admin.email.assets.index') }}" class="nav-item {{ request()->routeIs('admin.email.assets*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="image" class="w-4 h-4" /></span> Assets Email
                        </a>
                        @if(Route::has('admin.checklists.index'))
                        <a href="{{ route('admin.checklists.index') }}" class="nav-item {{ request()->routeIs('admin.checklists.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="square-check" class="w-4 h-4" /></span> Checklists
                        </a>
                        @endif
                        <a href="{{ route('admin.easybroker.settings') }}" class="nav-item {{ request()->routeIs('admin.easybroker.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="cloud" class="w-4 h-4" /></span> EasyBroker
                        </a>
                        <a href="{{ route('admin.integrations.index') }}" class="nav-item {{ request()->routeIs('admin.integrations.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="puzzle" class="w-4 h-4" /></span> Integraciones
                        </a>
                    </div>
                </div>
                @endpermission

                {{-- ===== AYUDA ===== --}}
                @if(Route::has('help.index'))
                <div class="nav-section" data-section="ayuda">
                    <div class="nav-items">
                        <a href="{{ route('help.index') }}" class="nav-item {{ request()->routeIs('help.*') ? 'active' : '' }}">
                            <span class="nav-icon"><x-icon name="circle-help" class="w-4 h-4" /></span> Centro de Ayuda
                        </a>
                    </div>
                </div>
                @endif
            </nav>

            {{-- User card at bottom --}}
            <div class="sidebar-footer">
                <a href="{{ route('profile') }}" class="user-card">
                    <div class="user-avatar">
                        @if($currentUser && $currentUser->avatar_path)
                            <img src="{{ Storage::url($currentUser->avatar_path) }}" alt="">
                        @else
                            {{ $currentUser ? strtoupper(substr($currentUser->name, 0, 1)) : '' }}
                        @endif
                    </div>
                    <div class="user-meta">
                        <div class="user-name">{{ $currentUser->name ?? '' }} {{ $currentUser->last_name ?? '' }}</div>
                        <div class="user-role">{{ $currentUser->role ?? '' }}</div>
                    </div>
                </a>
                <div class="footer-actions">
                    <a href="{{ url('/?preview') }}" target="_blank" class="footer-action-btn btn-view-site" title="Ver sitio web">
                        <x-icon name="external-link" class="w-3 h-3" /> Ver sitio
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="footer-action-btn btn-logout" title="Cerrar sesion">
                            <x-icon name="log-out" class="w-3 h-3" /> Salir
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ===== MAIN CONTENT ===== --}}
        <main class="main-content">
            <div class="topbar">
                <button class="topbar-toggle" onclick="toggleSidebar()"><x-icon name="menu" class="w-5 h-5" /></button>
                <span class="topbar-title">@yield('title', 'Panel')</span>
                {{-- Notification Bell --}}
                <div style="position:relative; margin-left:auto;" id="notifContainer">
                    <button onclick="toggleNotifications()" style="background:none; border:none; cursor:pointer; padding:0.4rem; color:var(--text-muted); position:relative; display:inline-flex; align-items:center;">
                        <x-icon name="bell" class="w-[18px] h-[18px]" />
                        <span id="notifBadge" style="display:none; position:absolute; top:2px; right:0; min-width:16px; height:16px; background:var(--danger); color:#fff; font-size:0.6rem; font-weight:700; border-radius:50%; line-height:16px; text-align:center;"></span>
                    </button>
                    <div id="notifDropdown" style="display:none; position:absolute; right:0; top:calc(100% + 6px); width:360px; max-height:420px; background:var(--card); border:1px solid var(--border); border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,0.12); z-index:200; overflow:hidden;">
                        <div style="padding:0.8rem 1rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-weight:600; font-size:0.9rem;">Notificaciones</span>
                            <a href="#" onclick="event.preventDefault(); markAllRead();" style="font-size:0.75rem; color:var(--primary);">Marcar todas como leidas</a>
                        </div>
                        <div id="notifList" style="overflow-y:auto; max-height:350px;">
                            <div style="padding:2rem; text-align:center; color:var(--text-muted); font-size:0.85rem;">Cargando...</div>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <script>document.addEventListener('DOMContentLoaded', () => window.toast(@json(session('success')), 'success'));</script>
            @endif
            @if(session('error'))
                <script>document.addEventListener('DOMContentLoaded', () => window.toast(@json(session('error')), 'error'));</script>
            @endif

            <div class="content-body">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- ===== MOBILE BOTTOM NAV ===== --}}
    <nav class="mobile-bottom-nav">
        <a href="{{ route('admin.dashboard') }}" class="bnav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="bnav-icon"><x-icon name="layout-dashboard" class="w-5 h-5" /></span>
            <span class="bnav-label">Inicio</span>
        </a>
        @if(Route::has('operations.index'))
        <a href="{{ route('operations.index') }}" class="bnav-item {{ request()->routeIs('operations.*') ? 'active' : '' }}">
            <span class="bnav-icon"><x-icon name="circle-play" class="w-5 h-5" /></span>
            <span class="bnav-label">Pipeline</span>
        </a>
        @endif
        @if(Route::has('tasks.index'))
        <a href="{{ route('tasks.index') }}" class="bnav-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
            <span class="bnav-icon"><x-icon name="square-check" class="w-5 h-5" /></span>
            <span class="bnav-label">Agenda</span>
            @if(isset($overdueCount) && $overdueCount > 0)
                <span class="bnav-badge">{{ $overdueCount > 9 ? '9+' : $overdueCount }}</span>
            @endif
        </a>
        @endif
        <button class="bnav-item" onclick="toggleSidebar()" style="background:none; border:none; cursor:pointer; font-family:inherit;">
            <span class="bnav-icon"><x-icon name="menu" class="w-5 h-5" /></span>
            <span class="bnav-label">Mas</span>
        </button>
    </nav>

    {{-- Mobile FAB - New Operation --}}
    @if(Route::has('operations.create'))
    <a href="{{ route('operations.create') }}" class="mobile-fab" title="Nueva operacion">+</a>
    @endif

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div id="toast-container"></div>

    <script>
        window.toast = function(msg, type, duration) {
            type = type || 'info';
            duration = duration || 4000;
            var icons = { success: '✓', error: '✗', info: 'ℹ' };
            var el = document.createElement('div');
            el.className = 'toast toast-' + type;
            el.innerHTML = '<span class="toast-icon">' + (icons[type] || 'ℹ') + '</span><span>' + msg + '</span>';
            el.onclick = function() { hideToast(el); };
            document.getElementById('toast-container').appendChild(el);
            setTimeout(function() { hideToast(el); }, duration);
        };
        function hideToast(el) {
            el.classList.add('hiding');
            setTimeout(function() { el.remove(); }, 300);
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
        // Collapsible sidebar sections
        function toggleSection(label) {
            var section = label.closest('.nav-section');
            section.classList.toggle('collapsed');
            saveSidebarState();
        }
        function saveSidebarState() {
            var state = {};
            document.querySelectorAll('.nav-section[data-section]').forEach(function(s) {
                state[s.dataset.section] = s.classList.contains('collapsed');
            });
            try { localStorage.setItem('sidebar_sections', JSON.stringify(state)); } catch(e) {}
        }
        function restoreSidebarState() {
            try {
                var state = JSON.parse(localStorage.getItem('sidebar_sections') || '{}');
                Object.keys(state).forEach(function(key) {
                    if (state[key]) {
                        var el = document.querySelector('.nav-section[data-section="' + key + '"]');
                        if (el) el.classList.add('collapsed');
                    }
                });
            } catch(e) {}
            // Auto-expand section containing active item
            var active = document.querySelector('.nav-item.active');
            if (active) {
                var section = active.closest('.nav-section');
                if (section) section.classList.remove('collapsed');
            }
        }
        restoreSidebarState();

        // ===== NOTIFICATIONS =====
        var notifOpen = false;
        function toggleNotifications() {
            notifOpen = !notifOpen;
            document.getElementById('notifDropdown').style.display = notifOpen ? 'block' : 'none';
            if (notifOpen) loadNotifications();
        }
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (notifOpen && !document.getElementById('notifContainer').contains(e.target)) {
                notifOpen = false;
                document.getElementById('notifDropdown').style.display = 'none';
            }
        });

        function loadNotifications() {
            fetch('{{ route("notifications.index") }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    updateBadge(data.unread_count);
                    renderNotifications(data.notifications);
                })
                .catch(function() {});
        }

        function updateBadge(count) {
            var badge = document.getElementById('notifBadge');
            if (count > 0) {
                badge.style.display = 'inline-block';
                badge.textContent = count > 9 ? '9+' : count;
            } else {
                badge.style.display = 'none';
            }
        }

        function renderNotifications(list) {
            var container = document.getElementById('notifList');
            if (!list.length) {
                container.innerHTML = '<div style="padding:2rem; text-align:center; color:var(--text-muted); font-size:0.85rem;">Sin notificaciones</div>';
                return;
            }
            var html = '';
            list.forEach(function(n) {
                var bg = n.read ? '' : 'background:rgba(102,126,234,0.04);';
                var dot = n.read ? '' : '<span style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;"></span>';
                var avatar = n.from_avatar
                    ? '<img src="' + n.from_avatar + '" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">'
                    : '<div style="width:32px;height:32px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:600;">' + n.from_initial + '</div>';
                html += '<a href="#" onclick="event.preventDefault(); clickNotification(' + n.id + ', \'' + (n.data && n.data.url ? n.data.url : '') + '\');" style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.7rem 1rem;border-bottom:1px solid var(--border);transition:background 0.15s;' + bg + '" onmouseover="this.style.background=\'var(--bg)\'" onmouseout="this.style.background=\'' + (n.read ? '' : 'rgba(102,126,234,0.04)') + '\'">'
                    + avatar
                    + '<div style="flex:1;overflow:hidden;">'
                    + '<div style="font-size:0.82rem;font-weight:' + (n.read ? '400' : '600') + ';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escapeHtml(n.title) + '</div>'
                    + '<div style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + escapeHtml(n.body || '') + '</div>'
                    + '<div style="font-size:0.68rem;color:var(--text-muted);margin-top:2px;">' + n.time_ago + '</div>'
                    + '</div>'
                    + dot
                    + '</a>';
            });
            container.innerHTML = html;
        }

        function clickNotification(id, url) {
            fetch('/notifications/' + id + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            }).then(function() {
                if (url) window.location.href = url;
                else loadNotifications();
            });
        }

        function markAllRead() {
            fetch('{{ route("notifications.read-all") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            }).then(function() { loadNotifications(); });
        }

        function escapeHtml(text) {
            var d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }

        // Poll for new notifications every 30s
        loadNotifications();
        setInterval(function() { if (!notifOpen) loadNotifications(); }, 30000);
    </script>
    @yield('scripts')
</body>
</html>
