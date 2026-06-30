<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mi Portal') - {{ $siteSettings->site_name ?? 'Homedelvalle' }}</title>
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary:       {{ $siteSettings->primary_color ?? '#667eea' }};
            --primary-dark:  {{ $siteSettings->secondary_color ?? '#764ba2' }};
            --sidebar-bg:    #1e1b4b;
            --sidebar-hover: #312e81;
            --sidebar-text:  #c7d2fe;
            --bg:            #f8fafc;
            --card:          #ffffff;
            --text:          #1e293b;
            --text-muted:    #64748b;
            --border:        #e2e8f0;
            --success:       #10b981;
            --danger:        #ef4444;
            --radius:        8px;
            --sidebar-w:     260px;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; color: inherit; }

        /* ─── Layout shell ─── */
        .portal-shell {
            display: flex;
            min-height: 100vh;
        }

        /* ─── Sidebar ─── */
        .portal-sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 200;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform .25s ease;
        }

        /* ─── Sidebar: logo zone ─── */
        .sb-logo {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            min-height: 66px;
            display: flex;
            align-items: center;
        }
        .sb-logo a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            width: 100%;
        }
        .sb-logo img { max-height: 36px; max-width: 85%; object-fit: contain; }
        .sb-logo-mark {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 18px; font-weight: 700;
            flex-shrink: 0;
        }
        .sb-logo-text {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.3px;
        }
        .sb-logo-sub {
            font-size: .65rem;
            color: var(--sidebar-text);
            opacity: .5;
            font-weight: 400;
            display: block;
        }

        /* ─── Sidebar: property strip ─── */
        .sb-property {
            padding: .75rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sb-property-label {
            font-size: .65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(199,210,254,.4);
            margin-bottom: .3rem;
        }
        .sb-property-addr {
            font-size: .82rem;
            font-weight: 600;
            color: #fff;
            line-height: 1.35;
        }
        .sb-property-colonia {
            font-size: .72rem;
            color: var(--sidebar-text);
            opacity: .6;
            margin-top: .15rem;
        }

        /* ─── Sidebar: nav sections ─── */
        .sb-section { margin-bottom: .5rem; }
        .sb-section-label {
            display: flex; align-items: center;
            padding: .75rem 1.5rem .4rem;
            font-size: .65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(199,210,254,.4);
        }

        /* ─── Sidebar: nav items — match CRM exactly ─── */
        .sb-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem 1.5rem;
            font-size: .88rem;
            font-weight: 400;
            color: var(--sidebar-text);
            border-left: 3px solid transparent;
            transition: background .15s, color .15s;
            position: relative;
            cursor: pointer;
            border-radius: 0;
            margin: 0;
        }
        .sb-item:hover { background: var(--sidebar-hover); color: #fff; }
        .sb-item.active {
            background: rgba(255,255,255,.08);
            color: #fff;
            font-weight: 500;
            border-left-color: var(--primary);
        }
        .sb-item.done { color: var(--sidebar-text); opacity: .8; }
        .sb-item.locked {
            opacity: .35;
            cursor: default;
            pointer-events: none;
        }

        /* ─── Stage number badge ─── */
        .sb-stage-num {
            width: 20px; height: 20px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .65rem;
            font-weight: 700;
            flex-shrink: 0;
            background: rgba(255,255,255,.08);
            color: rgba(199,210,254,.5);
        }
        .sb-item.done .sb-stage-num {
            background: rgba(16,185,129,.2);
            color: #10b981;
        }
        .sb-item.active .sb-stage-num {
            background: var(--primary);
            color: #fff;
        }
        .sb-stage-check { font-size: .7rem; }

        /* ─── Sidebar: divider ─── */
        .sb-divider {
            height: 1px;
            background: rgba(255,255,255,.08);
            margin: .5rem 0;
        }

        /* ─── Sidebar: user card (matches CRM) ─── */
        .sb-user {
            padding: .75rem;
            border-top: 1px solid rgba(255,255,255,.08);
            margin-top: auto;
        }
        .sb-user-card {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .5rem;
            border-radius: var(--radius);
            transition: background .15s;
        }
        .sb-user-card:hover { background: var(--sidebar-hover); }
        .sb-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 600; font-size: .85rem;
            flex-shrink: 0;
        }
        .sb-user-meta { flex: 1; min-width: 0; }
        .sb-user-name {
            font-size: .82rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sb-user-role {
            font-size: .68rem;
            color: var(--sidebar-text);
        }
        .sb-footer-actions {
            display: flex; gap: .35rem; margin-top: .4rem;
        }
        .sb-footer-btn {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .25rem .55rem; border-radius: 4px;
            font-size: .68rem; font-weight: 500; font-family: inherit;
            cursor: pointer; transition: all .15s; border: none;
            background: none; color: rgba(199,210,254,.45);
        }
        .sb-footer-btn:hover { background: rgba(239,68,68,.15); color: #fca5a5; }

        /* ─── Mobile top bar ─── */
        .portal-topbar {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 52px;
            background: var(--sidebar-bg);
            z-index: 150;
            align-items: center;
            padding: 0 1rem;
            gap: .75rem;
        }
        .topbar-hamburger {
            background: none; border: none; cursor: pointer;
            color: rgba(255,255,255,.8); font-size: 1.25rem;
            padding: .25rem; flex-shrink: 0;
        }
        .topbar-logo {
            display: flex; align-items: center; gap: .5rem;
        }
        .topbar-logo-mark {
            width: 28px; height: 28px;
            border-radius: 7px;
            background: var(--hdv-blue);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .85rem; font-weight: 800;
        }
        .topbar-logo-text {
            font-size: .82rem; font-weight: 700;
            color: rgba(255,255,255,.9);
        }
        .topbar-page {
            margin-left: auto;
            font-size: .75rem;
            color: rgba(255,255,255,.45);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
        }

        /* ─── Sidebar overlay (mobile) ─── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 190;
        }

        /* ─── Main content area ─── */
        .portal-content {
            margin-left: var(--sidebar-w);
            flex: 1;
            min-width: 0;
        }
        .portal-main {
            max-width: 960px;
            margin: 0 auto;
            padding: 1.75rem 2rem;
        }

        /* ─── Cards ─── */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-header h3 { font-size: .92rem; font-weight: 600; }
        .card-body { padding: 1.25rem; }

        /* ─── Page header ─── */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
        .page-header h2 { font-size: 1.35rem; font-weight: 700; }
        .text-muted { color: var(--text-muted); }

        /* ─── Stats ─── */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem; display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .stat-value { font-size: 1.5rem; font-weight: 700; }
        .stat-label { font-size: .78rem; color: var(--text-muted); }

        /* ─── Badges ─── */
        .badge { display: inline-block; padding: .2rem .55rem; border-radius: 20px; font-size: .72rem; font-weight: 600; }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }

        /* ─── Buttons ─── */
        .btn { display: inline-flex; align-items: center; gap: .35rem; padding: .55rem 1rem; border-radius: var(--radius); font-size: .85rem; font-weight: 500; border: none; cursor: pointer; transition: all .15s; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { filter: brightness(1.1); }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-outline:hover { background: var(--bg); }
        .btn-sm { padding: .35rem .65rem; font-size: .78rem; }
        .btn-danger { background: var(--danger); color: #fff; }

        /* ─── Tables ─── */
        .table-wrap { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { padding: .6rem 1rem; font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted); background: var(--bg); text-align: left; border-bottom: 1px solid var(--border); }
        .data-table td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); font-size: .85rem; }

        /* ─── Forms ─── */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem 1rem; }
        .form-group { display: flex; flex-direction: column; gap: .3rem; }
        .form-label { font-size: .78rem; font-weight: 600; color: var(--text-muted); }
        .form-input, .form-select, .form-textarea, .form-control {
            padding: .55rem .75rem; border: 1px solid var(--border); border-radius: var(--radius);
            font-size: .85rem; font-family: inherit; background: var(--card);
            transition: border-color .15s; width: 100%;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus, .form-control:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102,126,234,.08);
        }
        .full-width { grid-column: 1 / -1; }

        /* ─── Alerts ─── */
        .alert { padding: .75rem 1rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: .85rem; display: flex; align-items: center; gap: .5rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-info    { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

        /* ─── Empty state ─── */
        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
        .empty-state-icon { font-size: 2.5rem; margin-bottom: .5rem; }

        /* ─── Detail rows ─── */
        .detail-row { display: flex; justify-content: space-between; padding: .5rem 0; font-size: .85rem; }
        .detail-row .label { color: var(--text-muted); }
        .detail-row .value { font-weight: 500; }

        /* ─── Stage progress bar ─── */
        .stage-bar { display: flex; gap: 3px; margin-bottom: .5rem; }
        .stage-seg { flex: 1; height: 6px; border-radius: 3px; background: var(--border); }
        .stage-seg.done { background: var(--primary); }
        .stage-seg.now  { background: var(--primary); opacity: .5; }

        /* ─── Mobile ─── */
        @media (max-width: 768px) {
            .portal-topbar { display: flex; }
            .portal-sidebar { transform: translateX(-100%); }
            .portal-sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .portal-content { margin-left: 0; padding-top: 52px; }
            .portal-main { padding: 1rem; }
            .form-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }

        @yield('styles')
    </style>
    {{-- Portal Tailwind 4 + Livewire --}}
    @vite(['resources/css/portal.css', 'resources/js/portal.js'])
    @livewireStyles
</head>
<body>
@php
    $interests   = $portalClient->interest_types ?? [];
    $isVenta     = !empty(array_intersect(['venta','venta_propietario'], $interests));
    $isRenta     = !empty(array_intersect(['renta_propietario', 'renta_inquilino'], $interests));
    $etapa       = $portalCaptacion->portal_etapa ?? 0;
    $etapa4Done  = $portalCaptacion ? $portalCaptacion->isEtapa4Complete() : false;

    // Rental processes for sidebar
    $portalRentals = collect();
    if ($isRenta && $portalClient) {
        $portalRentals = \App\Models\RentalProcess::with('property')
            ->where(function($q) use ($portalClient) {
                $q->where('owner_client_id', $portalClient->id)
                  ->orWhere('tenant_client_id', $portalClient->id);
            })
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get();
    }
    $activeRental = $portalRentals->first();

    // Stage route groups
    $onCaptacion  = request()->routeIs('portal.captacion*') || request()->routeIs('portal.captacion');
    $onValuacion  = request()->routeIs('portal.valuacion*');
    $onDocs       = request()->routeIs('portal.documents.*');
    $onAccount    = request()->routeIs('portal.account');
    $onDashboard  = request()->routeIs('portal.dashboard');
    $onRental     = request()->routeIs('portal.rentals.*');
    $onMiInmueble = request()->routeIs('portal.mi-inmueble');

    // Stage status helper
    // 0=locked, 1=done, 2=active
    $st = function(int $stageEtapa) use ($etapa) {
        if ($etapa > $stageEtapa) return 1;   // done
        if ($etapa === $stageEtapa) return 2;  // active
        return 0;                              // locked
    };
@endphp

    {{-- ── Mobile top bar ── --}}
    <div class="portal-topbar">
        <button class="topbar-hamburger" onclick="toggleSidebar()" aria-label="Menú">&#9776;</button>
        <a href="{{ route('portal.dashboard') }}" class="topbar-logo">
            @if($siteSettings->logo_path ?? false)
                <img src="{{ asset('storage/' . $siteSettings->logo_path) }}" alt="" style="max-height:26px;">
            @else
                <div class="topbar-logo-mark">H</div>
            @endif
            <span class="topbar-logo-text">Mi Portal</span>
        </a>
        <span class="topbar-page">@yield('title', 'Inicio')</span>
    </div>

    {{-- ── Sidebar overlay (mobile) ── --}}
    <div class="sidebar-overlay" id="sb-overlay" onclick="toggleSidebar()"></div>

    {{-- ── Sidebar ── --}}
    <aside class="portal-sidebar" id="portal-sidebar">

        {{-- Logo --}}
        <div class="sb-logo">
            <a href="{{ route('portal.dashboard') }}">
                @if($siteSettings->logo_path_dark ?? false)
                    <img src="{{ Storage::url($siteSettings->logo_path_dark) }}" alt="{{ $siteSettings->site_name ?? 'Home del Valle' }}">
                @elseif($siteSettings->logo_path ?? false)
                    <img src="{{ asset('storage/' . $siteSettings->logo_path) }}" alt="{{ $siteSettings->site_name ?? 'Home del Valle' }}" style="max-height:36px;">
                @else
                    <div class="sb-logo-mark">H</div>
                    <div>
                        <span class="sb-logo-text">{{ $siteSettings->site_name ?? 'Home del Valle' }}</span>
                        <span class="sb-logo-sub">Portal del Propietario</span>
                    </div>
                @endif
            </a>
        </div>

        {{-- Property info --}}
        @if($portalCaptacion)
        <div class="sb-property">
            <div class="sb-property-label">Tu inmueble</div>
            <div class="sb-property-addr">{{ $portalCaptacion->property_address ?? 'Inmueble en proceso' }}</div>
        </div>
        @elseif($activeRental?->property)
        <div class="sb-property">
            <div class="sb-property-label">Tu inmueble en renta</div>
            <div class="sb-property-addr">{{ $activeRental->property->address ?? 'Inmueble en proceso' }}</div>
            @if($activeRental->property->colony)
            <div class="sb-property-colonia">{{ $activeRental->property->colony }}</div>
            @endif
        </div>
        @endif

        {{-- ── Navigation ── --}}

        {{-- Dashboard --}}
        <div class="sb-section">
            <div class="sb-section-label">General</div>
            <a href="{{ route('portal.dashboard') }}"
               class="sb-item {{ $onDashboard ? 'active' : '' }}">
                <span>&#8962;</span>
                Mi Inicio
            </a>
        </div>

        {{-- Venta funnel stages --}}
        @if($isVenta && $portalCaptacion)
        <div class="sb-section">
            <div class="sb-section-label">Mi proceso de venta</div>

            {{-- Stage 1: Documentación --}}
            @php $s1 = ($etapa === 0) ? 2 : $st(1); @endphp
            <a href="{{ route('portal.captacion') }}"
               class="sb-item {{ $s1 === 2 || ($onCaptacion && $etapa <= 1) ? 'active' : ($s1 === 1 ? 'done' : '') }}">
                <span class="sb-stage-num">
                    @if($s1 === 1) <span class="sb-stage-check">&#10003;</span>
                    @else 1
                    @endif
                </span>
                Documentación
            </a>

            {{-- Stage 2: Valuación --}}
            @php $s2 = $st(2); @endphp
            <a href="{{ $s2 > 0 ? route('portal.valuacion') : '#' }}"
               class="sb-item {{ $onValuacion ? 'active' : ($s2 === 1 ? 'done' : ($s2 === 0 ? 'locked' : '')) }}">
                <span class="sb-stage-num">
                    @if($s2 === 1) <span class="sb-stage-check">&#10003;</span>
                    @else 2
                    @endif
                </span>
                Valuación
            </a>

            {{-- Stage 3: Precio de Salida --}}
            @php $s3 = $st(3); @endphp
            <a href="{{ $s3 > 0 ? route('portal.captacion') : '#' }}"
               class="sb-item {{ ($onCaptacion && $etapa === 3) ? 'active' : ($s3 === 1 ? 'done' : ($s3 === 0 ? 'locked' : '')) }}">
                <span class="sb-stage-num">
                    @if($s3 === 1) <span class="sb-stage-check">&#10003;</span>
                    @else 3
                    @endif
                </span>
                Precio de Salida
            </a>

            {{-- Stage 4: Firma de Exclusiva --}}
            @php $s4 = $etapa4Done ? 1 : $st(4); @endphp
            <a href="{{ $s4 > 0 ? route('portal.captacion') : '#' }}"
               class="sb-item {{ ($onCaptacion && $etapa === 4) ? 'active' : ($s4 === 1 ? 'done' : ($s4 === 0 ? 'locked' : '')) }}">
                <span class="sb-stage-num">
                    @if($s4 === 1) <span class="sb-stage-check">&#10003;</span>
                    @else 4
                    @endif
                </span>
                Firma de Exclusiva
            </a>
        </div>

        {{-- Post-exclusiva stages — locked until etapa4 is done --}}
        <div class="sb-section">
            <div class="sb-section-label">En el mercado</div>

            <span class="sb-item locked">
                <span class="sb-stage-num">5</span>
                Preparación
            </span>
            <span class="sb-item locked">
                <span class="sb-stage-num">6</span>
                Promoción
            </span>
            <span class="sb-item locked">
                <span class="sb-stage-num">7</span>
                Visitas
            </span>
            <span class="sb-item locked">
                <span class="sb-stage-num">8</span>
                Negociación
            </span>
            <span class="sb-item locked">
                <span class="sb-stage-num">9</span>
                Cierre
            </span>
        </div>

        @elseif($isVenta && !$portalCaptacion)
        <div class="sb-section">
            <div class="sb-section-label">Mi proceso de venta</div>
            <a href="{{ route('portal.captacion') }}"
               class="sb-item {{ $onCaptacion ? 'active' : '' }}">
                <span>&#128196;</span>
                Mi Proceso
            </a>
        </div>
        @endif

        {{-- ── Secciones de renta ── --}}
        @if($isRenta)
        <div class="sb-section">
            <div class="sb-section-label">Mi proceso de renta</div>

            @if($activeRental)
            @php
                $rentalStages = \App\Models\RentalProcess::STAGES;
                $stageKeys    = array_keys($rentalStages);
                $currentStageIdx = array_search($activeRental->stage, $stageKeys);
            @endphp
            @foreach($rentalStages as $sk => $sl)
            @php
                $sIdx   = array_search($sk, $stageKeys);
                $isDone = $sIdx < $currentStageIdx;
                $isAct  = $sk === $activeRental->stage;
            @endphp
            <a href="{{ route('portal.rentals.show', $activeRental->id) }}"
               class="sb-item {{ $isAct ? 'active' : ($isDone ? 'done' : '') }}">
                <span class="sb-stage-num">
                    @if($isDone)<span class="sb-stage-check">&#10003;</span>
                    @else {{ $sIdx + 1 }}
                    @endif
                </span>
                {{ $sl }}
            </a>
            @endforeach

            @if($activeRental->investigation?->visible_to_owner && $activeRental->investigation?->owner_decision === 'pending')
            <a href="{{ route('portal.rentals.investigacion', $activeRental->id) }}"
               class="sb-item {{ request()->routeIs('portal.rentals.investigacion') ? 'active' : '' }}"
               style="border-left-color:#f59e0b;background:rgba(245,158,11,.06);">
                <span>&#128100;</span>
                <span>Candidato pendiente</span>
                <span style="margin-left:auto;width:8px;height:8px;border-radius:50%;background:#f59e0b;flex-shrink:0;"></span>
            </a>
            @endif

            @else
            <a href="{{ route('portal.rentals.index') }}"
               class="sb-item {{ $onRental ? 'active' : '' }}">
                <span>&#127968;</span>
                Mis Rentas
            </a>
            @endif
        </div>
        @endif

        {{-- Divider --}}
        <div class="sb-divider" style="margin-top:.5rem;"></div>

        {{-- Bottom links --}}
        <div style="padding:.5rem 0;">
            @if($isVenta || $isRenta)
            <a href="{{ route('portal.mi-inmueble') }}"
               class="sb-item {{ $onMiInmueble ? 'active' : '' }}">
                <span>📊</span>
                Mi Inmueble
            </a>
            @endif
            <a href="{{ route('portal.documents.index') }}"
               class="sb-item {{ $onDocs ? 'active' : '' }}">
                <span>&#128196;</span>
                Mis Documentos
            </a>
            <a href="{{ route('portal.account') }}"
               class="sb-item {{ $onAccount ? 'active' : '' }}">
                <span>&#9881;&#65039;</span>
                Mi Cuenta
            </a>
            @if($portalClient && ($portalClient->advisor_whatsapp ?? false))
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $portalClient->advisor_whatsapp) }}"
               target="_blank" class="sb-item">
                <span>&#128242;</span>
                Contactar Asesor
            </a>
            @endif
        </div>

        {{-- User strip + logout --}}
        <div class="sb-user">
            <div class="sb-user-card">
                <div class="sb-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="sb-user-meta">
                    <div class="sb-user-name">{{ Auth::user()->name }}</div>
                    <div class="sb-user-role">Portal del Propietario</div>
                </div>
            </div>
            <div class="sb-footer-actions">
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="sb-footer-btn">&#10005; Cerrar sesión</button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ── Content area ── --}}
    <div class="portal-content">
        <main class="portal-main">
            @if(session('success'))
            <div class="alert alert-success">&#10003; {{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="alert alert-error">&#10007; {{ session('error') }}</div>
            @endif
            @if(session('info'))
            <div class="alert alert-info">&#8505; {{ session('info') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('scripts')

    <script>
    function toggleSidebar() {
        var sb  = document.getElementById('portal-sidebar');
        var ov  = document.getElementById('sb-overlay');
        var open = sb.classList.toggle('open');
        ov.classList.toggle('open', open);
        document.body.style.overflow = open ? 'hidden' : '';
    }
    </script>

    @if($showLegalModal ?? false)
    <div id="legal-modal" style="position:fixed;inset:0;background:rgba(15,23,42,.72);z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;">
        <div style="background:#fff;border-radius:16px;max-width:680px;width:100%;height:82vh;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,.3);overflow:hidden;">
            <div style="padding:1.1rem 1.5rem;border-bottom:1px solid #e5e7eb;flex-shrink:0;display:flex;align-items:center;gap:.75rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#667eea" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <div>
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;">Antes de continuar</div>
                    <div style="font-size:1rem;font-weight:700;color:#1e293b;line-height:1.2;">Aviso de Privacidad</div>
                </div>
            </div>
            <iframe
                src="{{ url('/legal/aviso-de-privacidad?embed=1') }}"
                style="flex:1;border:none;width:100%;"
                title="Aviso de Privacidad"
                loading="eager"
            ></iframe>
            <div style="padding:1rem 1.5rem;border-top:1px solid #e5e7eb;flex-shrink:0;background:#f8fafc;">
                <form method="POST" action="{{ route('portal.terminos.aceptar') }}">
                    @csrf
                    <button type="submit" style="width:100%;padding:.8rem 1.5rem;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:700;cursor:pointer;letter-spacing:.2px;">
                        He leído y acepto el Aviso de Privacidad &mdash; Continuar &rarr;
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script>document.body.style.overflow='hidden';</script>
    @endif
    @livewireScripts
    @stack('scripts')
</body>
</html>
