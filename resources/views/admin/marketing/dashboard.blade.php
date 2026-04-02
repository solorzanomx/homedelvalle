@extends('layouts.app-sidebar')
@section('title', 'Marketing')

@section('styles')
<style>
/* Nav pills */
.mkt-pills { display: flex; gap: 0.4rem; margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 2px; }
.mkt-pill {
    padding: 0.45rem 0.9rem; border-radius: 20px; font-size: 0.78rem; font-weight: 500;
    border: 1px solid var(--border); background: var(--card); color: var(--text-muted);
    text-decoration: none; white-space: nowrap; transition: all 0.15s;
}
.mkt-pill:hover { border-color: var(--primary); color: var(--text); }
.mkt-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* Two column grid */
.mkt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; }

/* Cards */
.mkt-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.mkt-card-header {
    padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
    font-weight: 600; font-size: 0.85rem;
}
.mkt-card-body { padding: 1.25rem; }

/* Horizontal bar chart */
.h-chart { display: flex; flex-direction: column; gap: 0.65rem; }
.h-chart-row { display: grid; grid-template-columns: 110px 1fr 55px; align-items: center; gap: 0.75rem; }
.h-chart-label { font-size: 0.78rem; font-weight: 500; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.h-chart-track { height: 22px; background: var(--bg); border-radius: 4px; overflow: hidden; }
.h-chart-fill { height: 100%; border-radius: 4px; transition: width 0.4s; min-width: 3px; display: flex; align-items: center; padding-left: 6px; font-size: 0.68rem; color: #fff; font-weight: 500; }
.h-chart-val { font-size: 0.82rem; font-weight: 600; text-align: right; }
.roi-pos { color: #10b981; }
.roi-neg { color: #ef4444; }

/* Funnel */
.funnel { display: flex; flex-direction: column; gap: 0.75rem; }
.funnel-row-wrap { }
.funnel-row-header { display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 500; margin-bottom: 0.25rem; }
.funnel-bars { display: flex; gap: 3px; height: 20px; }
.funnel-bar { height: 100%; border-radius: 3px; transition: width 0.3s; min-width: 3px; }
.funnel-legend { display: flex; gap: 1rem; margin-top: 0.75rem; flex-wrap: wrap; padding-top: 0.75rem; border-top: 1px solid var(--border); }
.funnel-legend-item { display: flex; align-items: center; gap: 0.35rem; font-size: 0.72rem; color: var(--text-muted); }
.funnel-legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

/* Monthly bar chart */
.monthly-chart { display: flex; align-items: flex-end; gap: 0.75rem; height: 140px; padding-top: 0.5rem; }
.monthly-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.25rem; height: 100%; justify-content: flex-end; }
.monthly-bar { width: 100%; max-width: 48px; border-radius: 4px 4px 0 0; background: linear-gradient(180deg, var(--primary), #a78bfa); transition: height 0.3s; min-height: 3px; }
.monthly-val { font-size: 0.72rem; font-weight: 600; }
.monthly-label { font-size: 0.72rem; color: var(--text-muted); }

/* Recommendation items */
.rec-list { display: flex; flex-direction: column; gap: 0.5rem; }
.rec-item {
    display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem;
    border-radius: var(--radius); border: 1px solid var(--border); background: var(--bg);
}
.rec-badge { padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.72rem; font-weight: 600; white-space: nowrap; flex-shrink: 0; }
.rec-increase { background: #dcfce7; color: #16a34a; }
.rec-pause { background: #fee2e2; color: #dc2626; }
.rec-optimize { background: #fef3c7; color: #d97706; }
.rec-channel { font-weight: 600; font-size: 0.85rem; }
.rec-msg { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.1rem; }

/* Campaign list */
.camp-list { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.camp-list-header {
    padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
    font-weight: 600; font-size: 0.85rem;
}
.camp-item {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.7rem 1.25rem;
    border-bottom: 1px solid var(--border); transition: background 0.1s;
}
.camp-item:last-child { border-bottom: none; }
.camp-item:hover { background: rgba(248,250,252,0.8); }
.camp-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.camp-info { flex: 1; min-width: 0; }
.camp-name { font-size: 0.85rem; font-weight: 500; }
.camp-sub { font-size: 0.72rem; color: var(--text-muted); display: flex; gap: 0.5rem; flex-wrap: wrap; }
.camp-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }
.camp-budget { font-weight: 600; font-size: 0.85rem; }
.camp-pacing { display: flex; align-items: center; gap: 0.35rem; }
.camp-pacing-bar { width: 50px; height: 5px; background: var(--bg); border-radius: 3px; overflow: hidden; }
.camp-pacing-fill { height: 100%; border-radius: 3px; }
.camp-pacing-val { font-size: 0.72rem; color: var(--text-muted); }

/* Empty */
.mkt-empty { text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.85rem; }

@media (max-width: 1024px) { .mkt-grid { grid-template-columns: 1fr; } }
@media (max-width: 768px) { .h-chart-row { grid-template-columns: 80px 1fr 45px; } }
</style>
@endsection

@section('content')
{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--primary);">&#128101;</div>
        <div><div class="stat-value">{{ $leadsThisMonth }}</div><div class="stat-label">Leads este mes</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-orange">&#128176;</div>
        <div><div class="stat-value">${{ number_format($avgCostPerLead, 0) }}</div><div class="stat-label">Costo prom. / lead</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">&#128200;</div>
        <div><div class="stat-value">{{ $conversionRate }}%</div><div class="stat-label">Tasa de conversion</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ef4444;">&#128184;</div>
        <div><div class="stat-value">${{ number_format($marketingSpendMonth, 0) }}</div><div class="stat-label">Gasto marketing mes</div></div>
    </div>
</div>

{{-- Header --}}
<div class="page-header">
    <h2>Marketing</h2>
    <a href="{{ route('admin.marketing.campaigns.create') }}" class="btn btn-primary" style="white-space:nowrap;">+ Nueva Campana</a>
</div>

{{-- Nav pills --}}
<div class="mkt-pills">
    <a href="{{ route('admin.marketing.dashboard') }}" class="mkt-pill active">Resumen</a>
    <a href="{{ route('admin.marketing.campaigns') }}" class="mkt-pill">Campanas</a>
    <a href="{{ route('admin.marketing.channels') }}" class="mkt-pill">Canales</a>
</div>

{{-- ROI + CPL --}}
<div class="mkt-grid">
    <div class="mkt-card">
        <div class="mkt-card-header">ROI por Canal</div>
        <div class="mkt-card-body">
            @if(count($channelStats) > 0)
            <div class="h-chart">
                @foreach($channelStats as $stat)
                @if($stat['leads'] > 0)
                <div class="h-chart-row">
                    <span class="h-chart-label" title="{{ $stat['channel']->name }}">{{ $stat['channel']->name }}</span>
                    <div class="h-chart-track">
                        @php $roiW = $maxRoi > 0 ? min(100, abs($stat['roi']) / $maxRoi * 100) : 0; @endphp
                        <div class="h-chart-fill" style="width:{{ $roiW }}%; background:{{ $stat['roi'] >= 0 ? '#10b981' : '#ef4444' }};"></div>
                    </div>
                    <span class="h-chart-val {{ $stat['roi'] >= 0 ? 'roi-pos' : 'roi-neg' }}">{{ $stat['roi'] }}%</span>
                </div>
                @endif
                @endforeach
            </div>
            @else
            <div class="mkt-empty">Sin datos. Asigna canales a tus clientes.</div>
            @endif
        </div>
    </div>

    <div class="mkt-card">
        <div class="mkt-card-header">Costo por Lead</div>
        <div class="mkt-card-body">
            @if(count($channelStats) > 0)
            <div class="h-chart">
                @foreach($channelStats as $stat)
                @if($stat['leads'] > 0)
                <div class="h-chart-row">
                    <span class="h-chart-label" title="{{ $stat['channel']->name }}">{{ $stat['channel']->name }}</span>
                    <div class="h-chart-track">
                        @php $cplW = $maxCpl > 0 ? min(100, $stat['cpl'] / $maxCpl * 100) : 0; @endphp
                        <div class="h-chart-fill" style="width:{{ $cplW }}%; background:{{ $stat['channel']->color }};"></div>
                    </div>
                    <span class="h-chart-val">${{ number_format($stat['cpl'], 0) }}</span>
                </div>
                @endif
                @endforeach
            </div>
            @else
            <div class="mkt-empty">Sin datos.</div>
            @endif
        </div>
    </div>
</div>

{{-- Funnel --}}
<div class="mkt-card" style="margin-bottom:1.25rem;">
    <div class="mkt-card-header">Embudo de Conversion por Canal</div>
    <div class="mkt-card-body">
        @if(count($channelStats) > 0)
        <div class="funnel">
            @foreach($channelStats as $stat)
            @if($stat['leads'] > 0)
            <div class="funnel-row-wrap">
                <div class="funnel-row-header">
                    <span>{{ $stat['channel']->name }}</span>
                    <span style="color:var(--text-muted);">{{ $stat['conversion_rate'] }}% conversion</span>
                </div>
                <div class="funnel-bars">
                    @php $maxF = max($stat['leads'], 1); @endphp
                    <div class="funnel-bar" style="width:{{ ($stat['leads'] / $maxF) * 100 }}%; background:#93c5fd;" title="Leads: {{ $stat['leads'] }}"></div>
                    <div class="funnel-bar" style="width:{{ ($stat['contacted'] / $maxF) * 100 }}%; background:#6ee7b7;" title="Contactados: {{ $stat['contacted'] }}"></div>
                    <div class="funnel-bar" style="width:{{ ($stat['visited'] / $maxF) * 100 }}%; background:#fde68a;" title="Visitados: {{ $stat['visited'] }}"></div>
                    <div class="funnel-bar" style="width:{{ ($stat['won'] / $maxF) * 100 }}%; background:#10b981;" title="Ganados: {{ $stat['won'] }}"></div>
                </div>
            </div>
            @endif
            @endforeach
            <div class="funnel-legend">
                <div class="funnel-legend-item"><div class="funnel-legend-dot" style="background:#93c5fd;"></div> Leads</div>
                <div class="funnel-legend-item"><div class="funnel-legend-dot" style="background:#6ee7b7;"></div> Contactados</div>
                <div class="funnel-legend-item"><div class="funnel-legend-dot" style="background:#fde68a;"></div> Visitados</div>
                <div class="funnel-legend-item"><div class="funnel-legend-dot" style="background:#10b981;"></div> Ganados</div>
            </div>
        </div>
        @else
        <div class="mkt-empty">Sin datos.</div>
        @endif
    </div>
</div>

{{-- Monthly + Recommendations --}}
<div class="mkt-grid">
    <div class="mkt-card">
        <div class="mkt-card-header">Leads por Mes</div>
        <div class="mkt-card-body">
            <div class="monthly-chart">
                @foreach($monthlyLeads as $month)
                <div class="monthly-col">
                    <span class="monthly-val">{{ $month['count'] }}</span>
                    <div class="monthly-bar" style="height:{{ $maxMonthLeads > 0 ? max(4, ($month['count'] / $maxMonthLeads) * 100) : 4 }}%;"></div>
                    <span class="monthly-label">{{ $month['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mkt-card">
        <div class="mkt-card-header">Recomendaciones</div>
        <div class="mkt-card-body">
            @if(count($recommendations) > 0)
            <div class="rec-list">
                @foreach($recommendations as $rec)
                <div class="rec-item">
                    @if($rec['type'] === 'increase')
                        <span class="rec-badge rec-increase">Aumentar</span>
                    @elseif($rec['type'] === 'pause')
                        <span class="rec-badge rec-pause">Pausar</span>
                    @else
                        <span class="rec-badge rec-optimize">Optimizar</span>
                    @endif
                    <div>
                        <div class="rec-channel">{{ $rec['channel'] }}</div>
                        <div class="rec-msg">{{ $rec['message'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="mkt-empty">Agrega datos de marketing para ver recomendaciones.</div>
            @endif
        </div>
    </div>
</div>

{{-- Campaign Performance --}}
<div class="camp-list" style="margin-top:1.25rem;">
    <div class="camp-list-header">
        <span>Rendimiento de Campanas</span>
        <a href="{{ route('admin.marketing.campaigns') }}" class="btn btn-sm btn-outline">Ver todas</a>
    </div>
    @forelse($campaigns as $c)
    <div class="camp-item">
        <div class="camp-dot" style="background:{{ $c['campaign']->channel->color ?? '#ccc' }};"></div>
        <div class="camp-info">
            <div class="camp-name">{{ $c['campaign']->name }}</div>
            <div class="camp-sub">
                <span>{{ $c['campaign']->channel->name ?? '—' }}</span>
                <span>&middot; {{ $c['leads'] }} leads</span>
                <span>&middot; {{ $c['won'] }} ganados</span>
                <span>&middot; CPL ${{ number_format($c['cpl'], 0) }}</span>
            </div>
        </div>
        <div class="camp-right">
            <div class="camp-pacing">
                <div class="camp-pacing-bar">
                    <div class="camp-pacing-fill" style="width:{{ min(100, $c['campaign']->budget_pacing) }}%; background:{{ $c['campaign']->budget_pacing > 90 ? '#ef4444' : 'var(--primary)' }};"></div>
                </div>
                <span class="camp-pacing-val">{{ $c['campaign']->budget_pacing }}%</span>
            </div>
            <div class="camp-budget">${{ number_format($c['campaign']->budget, 0) }}</div>
            <span class="h-chart-val {{ $c['roi'] >= 0 ? 'roi-pos' : 'roi-neg' }}" style="font-size:0.82rem;">{{ $c['roi'] }}%</span>
        </div>
    </div>
    @empty
    <div class="mkt-empty">No hay campanas registradas.</div>
    @endforelse
</div>
@endsection
