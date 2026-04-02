<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mi Portal') - {{ $siteSettings->site_name ?? 'Homedelvalle' }}</title>
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: {{ $siteSettings->primary_color ?? '#667eea' }};
            --primary-dark: {{ $siteSettings->secondary_color ?? '#764ba2' }};
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --radius: 8px;
        }

        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); font-size: 14px; -webkit-font-smoothing: antialiased; }
        a { text-decoration: none; color: inherit; }

        /* Navbar */
        .portal-navbar {
            background: var(--card); border-bottom: 1px solid var(--border);
            padding: 0 2rem; display: flex; align-items: center; height: 60px;
            position: sticky; top: 0; z-index: 100;
        }
        .portal-logo { display: flex; align-items: center; gap: 0.5rem; }
        .portal-logo-icon {
            width: 32px; height: 32px; background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 16px; font-weight: 700;
        }
        .portal-logo-text { font-size: 1rem; font-weight: 700; color: var(--text); }

        .portal-nav { display: flex; gap: 0.25rem; margin-left: 2rem; }
        .portal-nav a {
            padding: 0.5rem 0.85rem; border-radius: var(--radius); font-size: 0.85rem; font-weight: 500;
            color: var(--text-muted); transition: all 0.15s;
        }
        .portal-nav a:hover { background: var(--bg); color: var(--text); }
        .portal-nav a.active { background: rgba(102,126,234,0.08); color: var(--primary); }

        .portal-user {
            margin-left: auto; display: flex; align-items: center; gap: 0.75rem;
            font-size: 0.85rem; color: var(--text-muted);
        }
        .portal-user .avatar {
            width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 0.75rem;
        }

        /* Main */
        .portal-main { max-width: 1100px; margin: 0 auto; padding: 1.5rem 2rem; }

        /* Cards */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-header h3 { font-size: 0.92rem; font-weight: 600; }
        .card-body { padding: 1.25rem; }

        /* Page header */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
        .page-header h2 { font-size: 1.35rem; font-weight: 700; }
        .text-muted { color: var(--text-muted); }

        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card {
            background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
            padding: 1.25rem; display: flex; align-items: center; gap: 1rem;
        }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; flex-shrink: 0;
        }
        .stat-value { font-size: 1.5rem; font-weight: 700; }
        .stat-label { font-size: 0.78rem; color: var(--text-muted); }

        /* Badges */
        .badge { display: inline-block; padding: 0.2rem 0.55rem; border-radius: 20px; font-size: 0.72rem; font-weight: 600; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.55rem 1rem; border-radius: var(--radius); font-size: 0.85rem; font-weight: 500;
            border: none; cursor: pointer; transition: all 0.15s;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { filter: brightness(1.1); }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-outline:hover { background: var(--bg); }
        .btn-sm { padding: 0.35rem 0.65rem; font-size: 0.78rem; }
        .btn-danger { background: var(--danger); color: #fff; }

        /* Tables */
        .table-wrap { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { padding: 0.6rem 1rem; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); background: var(--bg); text-align: left; border-bottom: 1px solid var(--border); }
        .data-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); font-size: 0.85rem; }

        /* Forms */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem 1rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.3rem; }
        .form-label { font-size: 0.78rem; font-weight: 600; color: var(--text-muted); }
        .form-input, .form-select, .form-textarea { padding: 0.55rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 0.85rem; font-family: inherit; background: var(--card); transition: border-color 0.15s; width: 100%; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(102,126,234,0.08); }
        .full-width { grid-column: 1 / -1; }

        /* Alerts */
        .alert { padding: 0.75rem 1rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Empty state */
        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
        .empty-state-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }

        /* Detail rows */
        .detail-row { display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.85rem; }
        .detail-row .label { color: var(--text-muted); }
        .detail-row .value { font-weight: 500; }

        /* Stage progress */
        .stage-bar { display: flex; gap: 3px; margin-bottom: 0.5rem; }
        .stage-seg { flex: 1; height: 6px; border-radius: 3px; background: var(--border); }
        .stage-seg.done { background: var(--primary); }
        .stage-seg.now { background: var(--primary); opacity: 0.5; }

        @media (max-width: 768px) {
            .portal-navbar { padding: 0 1rem; }
            .portal-nav { margin-left: 1rem; gap: 0; }
            .portal-main { padding: 1rem; }
            .form-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }

        @yield('styles')
    </style>
</head>
<body>
    {{-- Navbar --}}
    <nav class="portal-navbar">
        <a href="{{ route('portal.dashboard') }}" class="portal-logo">
            @if($siteSettings->logo_path ?? false)
                <img src="{{ asset('storage/' . $siteSettings->logo_path) }}" alt="" style="max-height:32px;">
            @else
                <div class="portal-logo-icon">H</div>
            @endif
            <span class="portal-logo-text">Mi Portal</span>
        </a>

        <div class="portal-nav">
            <a href="{{ route('portal.dashboard') }}" class="{{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">Inicio</a>
            <a href="{{ route('portal.rentals.index') }}" class="{{ request()->routeIs('portal.rentals.*') ? 'active' : '' }}">Mis Rentas</a>
            <a href="{{ route('portal.documents.index') }}" class="{{ request()->routeIs('portal.documents.*') ? 'active' : '' }}">Documentos</a>
            <a href="{{ route('portal.account') }}" class="{{ request()->routeIs('portal.account') ? 'active' : '' }}">Mi Cuenta</a>
        </div>

        <div class="portal-user">
            <span>{{ Auth::user()->name }}</span>
            <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline" style="margin-left:0.25rem;">Salir</button>
            </form>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="portal-main">
        @if(session('success'))
        <div class="alert alert-success">&#10003; {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-error">&#10007; {{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
