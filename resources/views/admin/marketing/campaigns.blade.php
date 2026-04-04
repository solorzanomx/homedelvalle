@extends('layouts.app-sidebar')
@section('title', 'Campanas de Marketing')

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

/* Status pills */
.status-pills { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; }
.status-pill {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.9rem; border-radius: 20px;
    font-size: 0.78rem; font-weight: 500; border: 1px solid var(--border); background: var(--card);
    color: var(--text-muted); text-decoration: none; white-space: nowrap; transition: all 0.15s;
}
.status-pill:hover { border-color: var(--primary); color: var(--text); }
.status-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* Filter */
.filter-toggle {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.75rem;
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    font-size: 0.82rem; font-weight: 500; cursor: pointer; margin-bottom: 0.75rem;
    color: var(--text-muted); transition: all 0.15s; width: fit-content;
}
.filter-toggle:hover { border-color: var(--primary); color: var(--text); }
.filter-toggle .chevron { transition: transform 0.2s; font-size: 0.7rem; }
.filter-toggle.open .chevron { transform: rotate(180deg); }
.filter-body {
    display: none; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;
    padding: 1rem 1.25rem; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    margin-bottom: 1.25rem;
}
.filter-body.open { display: flex; }
.filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
.filter-group label { font-size: 0.72rem; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.filter-group select { padding: 0.45rem 0.7rem; font-size: 0.85rem; border: 1px solid var(--border); border-radius: var(--radius); background: var(--bg); font-family: inherit; }

/* Campaign cards */
.camp-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.camp-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 1.25rem; transition: all 0.15s; position: relative; overflow: hidden;
}
.camp-card:hover { border-color: var(--primary); box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
.camp-card-bar { position: absolute; top: 0; left: 0; right: 0; height: 3px; }
.camp-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; }
.camp-card-name { font-weight: 600; font-size: 0.92rem; }
.camp-card-channel { font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.35rem; margin-top: 0.15rem; }
.camp-card-dot { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
.camp-card-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 0.75rem; }
.camp-stat { text-align: center; }
.camp-stat-val { font-weight: 600; font-size: 0.92rem; }
.camp-stat-label { font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; }
.camp-card-pacing { margin-bottom: 0.75rem; }
.camp-pacing-header { display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-muted); margin-bottom: 0.25rem; }
.camp-pacing-track { height: 6px; background: var(--bg); border-radius: 3px; overflow: hidden; }
.camp-pacing-fill { height: 100%; border-radius: 3px; }
.camp-card-footer { display: flex; justify-content: space-between; align-items: center; }
.camp-card-period { font-size: 0.72rem; color: var(--text-muted); }
.camp-card-actions { display: flex; gap: 0.3rem; }

/* Empty */
.camp-empty { text-align: center; padding: 3rem; color: var(--text-muted); font-size: 0.88rem; }

/* FAB */
.camp-fab {
    display: none; position: fixed; bottom: 80px; right: 16px; z-index: 91;
    width: 52px; height: 52px; border-radius: 50%; border: none;
    background: var(--primary); color: #fff; font-size: 26px; font-weight: 300;
    box-shadow: 0 4px 14px rgba(59,130,196,0.4);
    align-items: center; justify-content: center; cursor: pointer; text-decoration: none;
}

@media (max-width: 1024px) { .camp-cards { grid-template-columns: 1fr; } }
@media (max-width: 768px) { .camp-fab { display: flex; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Campanas</h2>
        <p class="text-muted">{{ $campaigns->total() }} campana{{ $campaigns->total() !== 1 ? 's' : '' }}</p>
    </div>
    <a href="{{ route('admin.marketing.campaigns.create') }}" class="btn btn-primary" style="white-space:nowrap;">+ Nueva</a>
</div>

{{-- Nav pills --}}
<div class="mkt-pills">
    <a href="{{ route('admin.marketing.dashboard') }}" class="mkt-pill">Resumen</a>
    <a href="{{ route('admin.marketing.campaigns') }}" class="mkt-pill active">Campanas</a>
    <a href="{{ route('admin.marketing.channels') }}" class="mkt-pill">Canales</a>
</div>

{{-- Status pills --}}
<div class="status-pills">
    <a href="{{ route('admin.marketing.campaigns', request()->except('status')) }}" class="status-pill {{ !request('status') ? 'active' : '' }}">Todas</a>
    <a href="{{ route('admin.marketing.campaigns', array_merge(request()->query(), ['status' => 'active'])) }}" class="status-pill {{ request('status') === 'active' ? 'active' : '' }}" style="{{ request('status') !== 'active' ? 'border-color:#86efac; color:#10b981;' : '' }}">Activas</a>
    <a href="{{ route('admin.marketing.campaigns', array_merge(request()->query(), ['status' => 'paused'])) }}" class="status-pill {{ request('status') === 'paused' ? 'active' : '' }}" style="{{ request('status') !== 'paused' ? 'border-color:#fde68a; color:#f59e0b;' : '' }}">Pausadas</a>
    <a href="{{ route('admin.marketing.campaigns', array_merge(request()->query(), ['status' => 'completed'])) }}" class="status-pill {{ request('status') === 'completed' ? 'active' : '' }}" style="{{ request('status') !== 'completed' ? 'border-color:#93c5fd; color:#3b82f6;' : '' }}">Completadas</a>
</div>

{{-- Channel filter --}}
@if(request('channel_id'))
<div style="margin-bottom:1rem;">
    <span class="badge badge-blue" style="font-size:0.78rem;">
        Canal: {{ $channels->firstWhere('id', request('channel_id'))->name ?? '—' }}
        <a href="{{ route('admin.marketing.campaigns', request()->except('channel_id')) }}" style="color:inherit; margin-left:0.3rem;">&#10005;</a>
    </span>
</div>
@else
<div class="filter-toggle" onclick="this.classList.toggle('open'); document.getElementById('filterBody').classList.toggle('open');">
    &#9881; Filtrar por canal <span class="chevron">&#9660;</span>
</div>
<form action="{{ route('admin.marketing.campaigns') }}" method="GET" id="filterBody" class="filter-body">
    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <div class="filter-group">
        <label>Canal</label>
        <select name="channel_id">
            <option value="">Todos</option>
            @foreach($channels as $channel)
                <option value="{{ $channel->id }}">{{ $channel->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
</form>
@endif

{{-- Campaign cards --}}
@if($campaigns->count())
<div class="camp-cards">
    @foreach($campaigns as $campaign)
    <div class="camp-card">
        <div class="camp-card-bar" style="background:{{ $campaign->channel->color ?? 'var(--primary)' }};"></div>
        <div class="camp-card-top">
            <div>
                <div class="camp-card-name">{{ $campaign->name }}</div>
                <div class="camp-card-channel">
                    <span class="camp-card-dot" style="background:{{ $campaign->channel->color ?? '#ccc' }};"></span>
                    {{ $campaign->channel->name ?? '—' }}
                </div>
            </div>
            @if($campaign->status === 'active')
                <span class="badge badge-green">Activa</span>
            @elseif($campaign->status === 'paused')
                <span class="badge badge-yellow">Pausada</span>
            @else
                <span class="badge badge-blue">Completada</span>
            @endif
        </div>
        <div class="camp-card-stats">
            <div class="camp-stat">
                <div class="camp-stat-val">${{ number_format($campaign->budget, 0) }}</div>
                <div class="camp-stat-label">Presupuesto</div>
            </div>
            <div class="camp-stat">
                <div class="camp-stat-val">${{ number_format($campaign->spent, 0) }}</div>
                <div class="camp-stat-label">Gastado</div>
            </div>
            <div class="camp-stat">
                <div class="camp-stat-val">{{ $campaign->clients_count }}</div>
                <div class="camp-stat-label">Leads</div>
            </div>
        </div>
        <div class="camp-card-pacing">
            <div class="camp-pacing-header">
                <span>Pacing</span>
                <span>{{ $campaign->budget_pacing }}%</span>
            </div>
            <div class="camp-pacing-track">
                <div class="camp-pacing-fill" style="width:{{ min(100, $campaign->budget_pacing) }}%; background:{{ $campaign->budget_pacing > 90 ? '#ef4444' : ($campaign->budget_pacing > 70 ? '#f59e0b' : 'var(--primary)') }};"></div>
            </div>
        </div>
        <div class="camp-card-footer">
            <div class="camp-card-period">
                @if($campaign->start_date)
                    {{ $campaign->start_date->format('d/m/Y') }}
                    @if($campaign->end_date) - {{ $campaign->end_date->format('d/m/Y') }} @endif
                @else
                    Sin fechas
                @endif
            </div>
            <div class="camp-card-actions">
                <a href="{{ route('admin.marketing.campaigns.edit', $campaign) }}" class="btn btn-sm btn-outline" style="padding:0.2rem 0.5rem; font-size:0.72rem;">&#9998;</a>
                <form method="POST" action="{{ route('admin.marketing.campaigns.destroy', $campaign) }}" style="display:inline" onsubmit="return confirm('Eliminar esta campana?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" style="padding:0.2rem 0.5rem; font-size:0.72rem;">&#10005;</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="camp-empty">
    No hay campanas {{ request('status') ? 'con ese estado' : '' }}.<br>
    <a href="{{ route('admin.marketing.campaigns.create') }}" style="color:var(--primary); font-weight:500;">+ Crear primera campana</a>
</div>
@endif

@if($campaigns->hasPages())
<div style="margin-top:1rem; text-align:center;">{{ $campaigns->links() }}</div>
@endif

<a href="{{ route('admin.marketing.campaigns.create') }}" class="camp-fab">+</a>
@endsection
