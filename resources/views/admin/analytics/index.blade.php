@extends('layouts.app-sidebar')
@section('title', 'Analytics')

@section('styles')
<style>
    /* ===== Chart Bars ===== */
    .chart-bar {
        height: 22px;
        border-radius: 4px;
        min-width: 2px;
        transition: width 0.3s;
        display: inline-block;
    }
    .chart-bar-income { background: #10b981; }
    .chart-bar-expense { background: #ef4444; }
    .chart-bar-primary { background: var(--primary); }
    .chart-bar-blue { background: #3b82f6; }
    .chart-bar-purple { background: #8b5cf6; }

    /* ===== Finance Chart ===== */
    .finance-chart { display: flex; flex-direction: column; gap: 0.85rem; }
    .finance-row { display: grid; grid-template-columns: 50px 1fr; align-items: center; gap: 0.75rem; }
    .finance-label { font-size: 0.82rem; font-weight: 500; color: var(--text-muted); text-align: right; }
    .finance-bars { display: flex; flex-direction: column; gap: 4px; }
    .finance-bar-row { display: flex; align-items: center; gap: 0.5rem; }
    .finance-bar-row .chart-bar { flex-shrink: 0; }
    .finance-amount { font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; }

    /* ===== Stage Chart ===== */
    .stage-chart { display: flex; flex-direction: column; gap: 0.6rem; }
    .stage-row { display: grid; grid-template-columns: 100px 1fr 40px; align-items: center; gap: 0.75rem; }
    .stage-label { font-size: 0.82rem; font-weight: 500; text-align: right; }
    .stage-bar-wrap { position: relative; height: 24px; background: var(--bg); border-radius: 4px; overflow: hidden; }
    .stage-bar-fill { height: 100%; border-radius: 4px; transition: width 0.3s; }
    .stage-count { font-size: 0.82rem; font-weight: 600; }

    /* ===== Clients Chart ===== */
    .clients-chart { display: flex; align-items: flex-end; gap: 0.75rem; height: 140px; padding-top: 0.5rem; }
    .clients-bar-group { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.3rem; height: 100%; justify-content: flex-end; }
    .clients-bar { width: 100%; max-width: 48px; border-radius: 4px 4px 0 0; background: var(--primary); transition: height 0.3s; min-height: 2px; }
    .clients-bar-label { font-size: 0.72rem; color: var(--text-muted); font-weight: 500; }
    .clients-bar-value { font-size: 0.72rem; font-weight: 600; }

    /* ===== Distribution Bars ===== */
    .dist-chart { display: flex; flex-direction: column; gap: 0.7rem; }
    .dist-row { display: flex; flex-direction: column; gap: 0.25rem; }
    .dist-header { display: flex; justify-content: space-between; align-items: center; }
    .dist-type { font-size: 0.82rem; font-weight: 500; text-transform: capitalize; }
    .dist-pct { font-size: 0.75rem; color: var(--text-muted); }
    .dist-bar-bg { height: 10px; background: var(--bg); border-radius: 6px; overflow: hidden; }
    .dist-bar-fill { height: 100%; border-radius: 6px; transition: width 0.3s; }

    /* ===== Task Mini Stats ===== */
    .task-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
    .task-mini {
        text-align: center; padding: 1rem 0.75rem; border-radius: var(--radius);
        border: 1px solid var(--border); background: var(--card);
    }
    .task-mini-value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.2rem; }
    .task-mini-label { font-size: 0.75rem; color: var(--text-muted); }
    .task-mini-danger { border-color: #fecaca; background: #fef2f2; }
    .task-mini-danger .task-mini-value { color: #dc2626; }

    /* ===== Two Column Grid ===== */
    .two-col-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }

    /* ===== Legend ===== */
    .chart-legend { display: flex; gap: 1.25rem; margin-top: 0.75rem; }
    .legend-item { display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; color: var(--text-muted); }
    .legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

    /* ===== Stage Colors ===== */
    .stage-lead { background: #93c5fd; }
    .stage-contact { background: #6ee7b7; }
    .stage-visit { background: #fde68a; }
    .stage-negotiation { background: #fdba74; }
    .stage-offer { background: #c4b5fd; }
    .stage-closing { background: #a5b4fc; }
    .stage-won { background: #10b981; }
    .stage-lost { background: #ef4444; }

    /* ===== Distribution Colors ===== */
    .dist-color-0 { background: #3b82f6; }
    .dist-color-1 { background: #8b5cf6; }
    .dist-color-2 { background: #10b981; }
    .dist-color-3 { background: #f59e0b; }
    .dist-color-4 { background: #ef4444; }
    .dist-color-5 { background: #06b6d4; }
    .dist-color-6 { background: #ec4899; }

    /* ===== Responsive ===== */
    @media (max-width: 1024px) {
        .two-col-grid { grid-template-columns: 1fr; }
        .task-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .task-grid { grid-template-columns: 1fr 1fr; }
        .stage-row { grid-template-columns: 80px 1fr 30px; }
    }
</style>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <h2>&#128202; Analytics</h2>
    <span class="text-muted">{{ now()->translatedFormat('l, d F Y') }}</span>
</div>

{{-- ===== KPI Stats Grid ===== --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">&#8962;</div>
        <div>
            <div class="stat-value">{{ number_format($kpis['properties_active']) }}</div>
            <div class="stat-label">Propiedades Activas</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-purple">&#9830;</div>
        <div>
            <div class="stat-value">{{ number_format($kpis['deals_pipeline']) }}</div>
            <div class="stat-label">Deals en Pipeline</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">&#36;</div>
        <div>
            <div class="stat-value">${{ number_format($kpis['revenue_month'], 2) }}</div>
            <div class="stat-label">Ingresos del Mes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-orange">&#9998;</div>
        <div>
            <div class="stat-value">{{ $kpis['conversion_rate'] }}%</div>
            <div class="stat-label">Tasa de Conversion</div>
        </div>
    </div>
</div>

{{-- ===== Ingresos vs Egresos (6 meses) ===== --}}
@php
    $financeMax = max(1, max(array_column($monthlyFinance, 'income') ?: [0]), max(array_column($monthlyFinance, 'expense') ?: [0]));
@endphp
<div class="card">
    <div class="card-header">
        <h3>Ingresos vs Egresos (6 meses)</h3>
    </div>
    <div class="card-body">
        <div class="finance-chart">
            @foreach($monthlyFinance as $month)
                <div class="finance-row">
                    <div class="finance-label">{{ $month['label'] }}</div>
                    <div class="finance-bars">
                        <div class="finance-bar-row">
                            <div class="chart-bar chart-bar-income" style="width: {{ ($month['income'] / $financeMax) * 100 }}%;"></div>
                            <span class="finance-amount">${{ number_format($month['income'], 0) }}</span>
                        </div>
                        <div class="finance-bar-row">
                            <div class="chart-bar chart-bar-expense" style="width: {{ ($month['expense'] / $financeMax) * 100 }}%;"></div>
                            <span class="finance-amount">${{ number_format($month['expense'], 0) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="chart-legend">
            <div class="legend-item"><div class="legend-dot" style="background:#10b981;"></div> Ingresos</div>
            <div class="legend-item"><div class="legend-dot" style="background:#ef4444;"></div> Egresos</div>
        </div>
    </div>
</div>

{{-- ===== Deals por Etapa ===== --}}
@php
    $stageMax = max(1, max(array_column($dealsByStage, 'count') ?: [0]));
    $stageClasses = ['stage-lead', 'stage-contact', 'stage-visit', 'stage-negotiation', 'stage-offer', 'stage-closing', 'stage-won', 'stage-lost'];
@endphp
<div class="card">
    <div class="card-header">
        <h3>Deals por Etapa</h3>
    </div>
    <div class="card-body">
        <div class="stage-chart">
            @foreach($dealsByStage as $i => $item)
                <div class="stage-row">
                    <div class="stage-label">{{ $item['stage'] }}</div>
                    <div class="stage-bar-wrap">
                        <div class="stage-bar-fill {{ $stageClasses[$i] ?? 'stage-lead' }}" style="width: {{ ($item['count'] / $stageMax) * 100 }}%;"></div>
                    </div>
                    <div class="stage-count">{{ $item['count'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ===== Top 5 Brokers & Top 5 Propiedades ===== --}}
<div class="two-col-grid">
    {{-- Top 5 Brokers --}}
    <div class="card">
        <div class="card-header">
            <h3>Top 5 Brokers</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Comisiones Totales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topBrokers as $idx => $broker)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $broker->name }} {{ $broker->last_name ?? '' }}</td>
                            <td style="font-weight:600;">${{ number_format($broker->total_commissions, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted text-center" style="padding:2rem;">Sin datos de brokers</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top 5 Propiedades --}}
    <div class="card">
        <div class="card-header">
            <h3>Top 5 Propiedades</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titulo</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProperties as $idx => $property)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ Str::limit($property->title ?? $property->name ?? '-', 35) }}</td>
                            <td style="font-weight:600;">${{ number_format($property->price, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted text-center" style="padding:2rem;">Sin propiedades</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ===== Clientes Nuevos por Mes ===== --}}
@php
    $clientMax = max(1, max(array_column($clientsMonthly, 'count') ?: [0]));
@endphp
<div class="card">
    <div class="card-header">
        <h3>Clientes Nuevos por Mes</h3>
    </div>
    <div class="card-body">
        <div class="clients-chart">
            @foreach($clientsMonthly as $cm)
                <div class="clients-bar-group">
                    <div class="clients-bar-value">{{ $cm['count'] }}</div>
                    <div class="clients-bar" style="height: {{ ($cm['count'] / $clientMax) * 100 }}%;"></div>
                    <div class="clients-bar-label">{{ $cm['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ===== Propiedades por Tipo ===== --}}
@php
    $typeTotal = $propertyTypes->sum('count') ?: 1;
@endphp
<div class="card">
    <div class="card-header">
        <h3>Propiedades por Tipo</h3>
    </div>
    <div class="card-body">
        @if($propertyTypes->isEmpty())
            <p class="text-muted text-center" style="padding:1rem 0;">Sin datos de tipos de propiedad</p>
        @else
            <div class="dist-chart">
                @foreach($propertyTypes as $idx => $pt)
                    @php
                        $pct = round(($pt->count / $typeTotal) * 100, 1);
                    @endphp
                    <div class="dist-row">
                        <div class="dist-header">
                            <span class="dist-type">{{ $pt->property_type }}</span>
                            <span class="dist-pct">{{ $pt->count }} ({{ $pct }}%)</span>
                        </div>
                        <div class="dist-bar-bg">
                            <div class="dist-bar-fill dist-color-{{ $idx % 7 }}" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ===== Resumen de Tareas ===== --}}
<div class="card">
    <div class="card-header">
        <h3>Resumen de Tareas</h3>
    </div>
    <div class="card-body">
        <div class="task-grid">
            <div class="task-mini">
                <div class="task-mini-value" style="color:#f59e0b;">{{ $taskStats['pending'] }}</div>
                <div class="task-mini-label">Pendientes</div>
            </div>
            <div class="task-mini">
                <div class="task-mini-value" style="color:#3b82f6;">{{ $taskStats['in_progress'] }}</div>
                <div class="task-mini-label">En Progreso</div>
            </div>
            <div class="task-mini task-mini-danger">
                <div class="task-mini-value">{{ $taskStats['overdue'] }}</div>
                <div class="task-mini-label">Vencidas</div>
            </div>
            <div class="task-mini">
                <div class="task-mini-value" style="color:#10b981;">{{ $taskStats['completed_week'] }}</div>
                <div class="task-mini-label">Completadas esta semana</div>
            </div>
        </div>
    </div>
</div>

@endsection
