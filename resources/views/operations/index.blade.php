@extends('layouts.app-sidebar')
@section('title', 'Pipeline')

@section('styles')
<style>
/* ===== VIEW TOGGLE ===== */
.view-toggle {
    display: flex; gap: 0.2rem; background: var(--bg);
    border-radius: var(--radius); padding: 3px;
}
.view-toggle .btn { justify-content: center; min-width: 36px; }

/* ===== FILTER BAR ===== */
.filter-bar {
    display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;
    margin-bottom: 1.25rem; padding: 0.65rem 0.85rem;
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
}
.filter-bar .form-input,
.filter-bar .form-select {
    width: auto; min-width: 140px; padding: 0.35rem 0.6rem; font-size: 0.8rem;
}
.filter-bar .form-input { min-width: 170px; }

/* ===== TYPE TABS ===== */
.type-tabs {
    display: flex; gap: 0.25rem; margin-bottom: 1rem;
}
.type-tab {
    padding: 0.45rem 1rem; border-radius: 20px; font-size: 0.82rem; font-weight: 600;
    border: 1.5px solid var(--border); background: var(--card); color: var(--text-muted);
    cursor: pointer; transition: all 0.15s; text-decoration: none;
    display: inline-flex; align-items: center; gap: 0.35rem;
}
.type-tab:hover { border-color: var(--primary); color: var(--text); }
.type-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.type-tab .tab-count {
    font-size: 0.7rem; background: rgba(255,255,255,0.2); padding: 0.05rem 0.4rem;
    border-radius: 10px; font-weight: 700;
}
.type-tab.active .tab-count { background: rgba(255,255,255,0.3); }

/* ===== STATS STRIP ===== */
.stats-strip {
    display: flex; gap: 0.65rem; margin-bottom: 1.25rem; overflow-x: auto;
    padding-bottom: 0.25rem;
}
.mini-stat {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.55rem 0.85rem; background: var(--card); border: 1px solid var(--border);
    border-radius: var(--radius); white-space: nowrap; min-width: max-content;
}
.mini-stat-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; flex-shrink: 0;
}
.mini-stat-val { font-size: 1.05rem; font-weight: 700; color: var(--text); line-height: 1; }
.mini-stat-label { font-size: 0.68rem; color: var(--text-muted); }

/* ===== KANBAN ===== */
.kanban-wrap {
    display: flex; gap: 0.6rem; overflow-x: auto;
    padding-bottom: 1rem; -webkit-overflow-scrolling: touch;
}
.kanban-col {
    min-width: 280px; max-width: 280px; flex-shrink: 0;
    display: flex; flex-direction: column;
    max-height: calc(100vh - 260px);
}
.kanban-col-header {
    padding: 0.55rem 0.75rem;
    display: flex; justify-content: space-between; align-items: center;
    background: var(--card); border: 1px solid var(--border);
    border-bottom: none; border-radius: var(--radius) var(--radius) 0 0;
    position: relative;
}
.kanban-col-header::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    border-radius: var(--radius) var(--radius) 0 0;
}
.kanban-col-title { font-size: 0.78rem; font-weight: 700; color: var(--text); text-transform: uppercase; letter-spacing: 0.03em; }
.kanban-col-count {
    font-size: 0.68rem; font-weight: 600; padding: 0.1rem 0.45rem;
    border-radius: 10px; background: var(--bg); color: var(--text-muted);
}
.kanban-col-body {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 0 0 var(--radius) var(--radius);
    padding: 0.45rem; min-height: 80px; overflow-y: auto;
    flex: 1; display: flex; flex-direction: column; gap: 0.45rem;
}

/* ===== KANBAN CARD ===== */
.k-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 0; font-size: 0.82rem;
    transition: all 0.15s; cursor: grab; overflow: hidden;
}
.k-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
.k-card.dragging { opacity: 0.5; box-shadow: 0 8px 25px rgba(0,0,0,0.15); }

.k-card-temp {
    height: 3px; width: 100%;
}
.k-card-temp.hot { background: #ef4444; }
.k-card-temp.warm { background: #f59e0b; }
.k-card-temp.cold { background: #3b82f6; }
.k-card-temp.none { background: var(--border); }

.k-card-body { padding: 0.6rem 0.7rem 0.5rem; }

.k-card-top {
    display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.3rem;
}
.k-card-name {
    font-weight: 700; color: var(--text); font-size: 0.85rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    flex: 1; min-width: 0;
}
.k-card-name a { color: inherit; text-decoration: none; }
.k-card-name a:hover { color: var(--primary); }

.k-card-type {
    font-size: 0.62rem; font-weight: 700; padding: 0.08rem 0.35rem;
    border-radius: 3px; text-transform: uppercase; letter-spacing: 0.04em;
    flex-shrink: 0; margin-left: 0.35rem;
}
.k-card-type.venta { background: #dbeafe; color: #1e40af; }
.k-card-type.renta { background: #EBF5FF; color: #2563A0; }
.k-card-type.captacion { background: #ccfbf1; color: #0f766e; }

.k-card-prop {
    font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.k-card-amount {
    font-size: 0.92rem; font-weight: 800; color: var(--text); margin-bottom: 0.35rem;
}

/* Progress bar */
.k-progress {
    height: 3px; background: var(--border); border-radius: 2px;
    overflow: hidden; margin-bottom: 0.4rem;
}
.k-progress-bar { height: 100%; border-radius: 2px; transition: width 0.3s; }

/* Card meta row */
.k-card-meta {
    display: flex; align-items: center; justify-content: space-between;
    gap: 0.3rem; margin-bottom: 0.35rem;
}
.k-card-time {
    font-size: 0.7rem; color: var(--text-muted);
    display: flex; align-items: center; gap: 0.2rem;
}
.k-card-time.stale { color: #ef4444; font-weight: 600; }
.k-card-time.stale::before {
    content: '';
    width: 6px; height: 6px; border-radius: 50%;
    background: #ef4444; display: inline-block;
    animation: stalePulse 2s ease infinite;
}
@keyframes stalePulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

.k-card-user {
    font-size: 0.68rem; color: var(--text-muted);
    background: var(--bg); padding: 0.1rem 0.35rem; border-radius: 3px;
    max-width: 80px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* Quick actions */
.k-card-actions {
    display: flex; gap: 0.2rem; padding: 0.4rem 0.7rem;
    border-top: 1px solid var(--border); background: var(--bg);
}
.k-action {
    flex: 1; display: flex; align-items: center; justify-content: center;
    gap: 0.2rem; padding: 0.3rem; border-radius: 5px;
    font-size: 0.72rem; font-weight: 600; color: var(--text-muted);
    background: var(--card); border: 1px solid var(--border);
    cursor: pointer; transition: all 0.15s; text-decoration: none;
}
.k-action:hover { border-color: var(--primary); color: var(--primary); }
.k-action.wa:hover { border-color: #25d366; color: #25d366; }
.k-action.phone:hover { border-color: #3b82f6; color: #3b82f6; }
.k-action-icon { font-size: 0.85rem; }

/* Stage advance button */
.k-advance {
    display: flex; align-items: center; justify-content: center;
    gap: 0.2rem; padding: 0.3rem; border-radius: 5px;
    font-size: 0.7rem; font-weight: 700; color: #fff;
    background: var(--primary); border: none; cursor: pointer;
    transition: all 0.15s; flex: 1;
}
.k-advance:hover { opacity: 0.9; }

/* Kanban empty */
.kanban-empty {
    text-align: center; color: var(--text-muted); font-size: 0.75rem;
    padding: 1.5rem 0.5rem; opacity: 0.7;
}

/* ===== TABLE VIEW ===== */
.client-cell {
    display: flex; align-items: center; gap: 0.5rem;
}
.client-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.client-cell-name { font-weight: 600; }
.client-cell-phone { font-size: 0.72rem; color: var(--text-muted); }
.table-actions-inline {
    display: flex; align-items: center; gap: 0.25rem;
}
.t-action {
    width: 28px; height: 28px; border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--border); background: var(--card);
    color: var(--text-muted); cursor: pointer; transition: all 0.15s;
    text-decoration: none; font-size: 0.82rem;
}
.t-action:hover { border-color: var(--primary); color: var(--primary); }
.t-action.wa:hover { border-color: #25d366; color: #25d366; }

/* ===== FAB ===== */
.fab {
    position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 100;
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark, #1E3A5F));
    color: #fff; border: none; font-size: 1.5rem; cursor: pointer;
    box-shadow: 0 4px 15px rgba(59,130,196,0.4);
    transition: transform 0.15s, box-shadow 0.15s;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none;
}
.fab:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(59,130,196,0.5); }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .stats-strip { gap: 0.4rem; }
    .mini-stat { padding: 0.4rem 0.65rem; }
}
@media (max-width: 768px) {
    .kanban-col { min-width: 250px; max-width: 250px; }
    .filter-bar { flex-direction: column; align-items: stretch; }
    .filter-bar .form-input, .filter-bar .form-select { min-width: 100%; }
    .type-tabs { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 0.25rem; }
    .stats-strip { display: none; }
}
</style>
@endsection

@section('content')
@php
    $stageLabels = \App\Models\Operation::STAGES;
    $stageColors = \App\Models\Operation::STAGE_COLORS;
    $stageList = $stages;
    $typeFilter = request('type', '');
@endphp

<div class="page-header">
    <div>
        <h2>Pipeline</h2>
    </div>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        <div class="view-toggle">
            <button type="button" class="btn btn-sm" id="btnKanban" onclick="setView('kanban')" title="Kanban">&#9638;</button>
            <button type="button" class="btn btn-sm" id="btnTable" onclick="setView('table')" title="Lista">&#9776;</button>
        </div>
    </div>
</div>

{{-- Type Tabs --}}
<div class="type-tabs">
    <a href="{{ route('operations.index') }}" class="type-tab {{ !$typeFilter ? 'active' : '' }}">
        Todas <span class="tab-count">{{ $stats['total'] }}</span>
    </a>
    <a href="{{ route('operations.index', ['type' => 'captacion']) }}" class="type-tab {{ $typeFilter === 'captacion' ? 'active' : '' }}">
        Captacion <span class="tab-count">{{ $stats['captaciones'] }}</span>
    </a>
    <a href="{{ route('operations.index', ['type' => 'venta']) }}" class="type-tab {{ $typeFilter === 'venta' ? 'active' : '' }}">
        Venta <span class="tab-count">{{ $stats['ventas'] }}</span>
    </a>
    <a href="{{ route('operations.index', ['type' => 'renta']) }}" class="type-tab {{ $typeFilter === 'renta' ? 'active' : '' }}">
        Renta <span class="tab-count">{{ $stats['rentas'] }}</span>
    </a>
</div>

{{-- Stats Strip --}}
<div class="stats-strip">
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(59,130,246,0.1); color:#3b82f6;">&#9830;</div>
        <div>
            <div class="mini-stat-val">{{ $stats['total'] }}</div>
            <div class="mini-stat-label">Activas</div>
        </div>
    </div>
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(16,185,129,0.1); color:#10b981;">$</div>
        <div>
            <div class="mini-stat-val">${{ number_format($stats['pipeline_value'] / 1000000, 1) }}M</div>
            <div class="mini-stat-label">Pipeline</div>
        </div>
    </div>
    @php
        $staleCount = 0;
        foreach ($operationsByStage as $ops) {
            foreach ($ops as $op) {
                if ($op->updated_at->diffInHours(now()) > 48) $staleCount++;
            }
        }
    @endphp
    @if($staleCount > 0)
    <div class="mini-stat" style="border-color: rgba(239,68,68,0.3);">
        <div class="mini-stat-icon" style="background:rgba(239,68,68,0.1); color:#ef4444;">!</div>
        <div>
            <div class="mini-stat-val" style="color:#ef4444;">{{ $staleCount }}</div>
            <div class="mini-stat-label">Sin contacto &gt;48h</div>
        </div>
    </div>
    @endif
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('operations.index') }}" class="filter-bar" id="filterBar" style="display:none;">
    <input type="hidden" name="type" value="{{ $typeFilter }}">
    <input type="text" name="search" class="form-input" placeholder="Buscar cliente, propiedad..." value="{{ request('search') }}">
    <select name="stage" class="form-select">
        <option value="">Todas las etapas</option>
        @foreach($stageList as $sk)
            <option value="{{ $sk }}" {{ request('stage') === $sk ? 'selected' : '' }}>{{ $stageLabels[$sk] ?? $sk }}</option>
        @endforeach
    </select>
    <select name="user_id" class="form-select">
        <option value="">Todos</option>
        @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
    @if(request()->hasAny(['stage', 'search', 'user_id']))
        <a href="{{ route('operations.index', ['type' => $typeFilter]) }}" class="btn btn-outline btn-sm">Limpiar</a>
    @endif
</form>

<div style="margin-bottom:0.75rem;">
    <button type="button" class="btn btn-sm btn-outline" onclick="toggleFilters()" id="filterToggle" style="font-size:0.78rem;">
        &#128269; Filtros @if(request()->hasAny(['stage','search','user_id'])) <span style="color:var(--primary);">(activos)</span> @endif
    </button>
</div>

{{-- ===== KANBAN VIEW ===== --}}
<div id="viewKanban" style="display:none;">
    <div class="kanban-wrap" id="kanbanWrap">
        @foreach($stageList as $stageKey)
        @php
            $color = $stageColors[$stageKey] ?? '#64748b';
            $ops = $operationsByStage[$stageKey] ?? collect();
        @endphp
        <div class="kanban-col" data-stage="{{ $stageKey }}">
            <div class="kanban-col-header">
                <span class="kanban-col-title">{{ $stageLabels[$stageKey] ?? $stageKey }}</span>
                <span class="kanban-col-count">{{ $ops->count() }}</span>
                <style>.kanban-col[data-stage="{{ $stageKey }}"] .kanban-col-header::before { background: {{ $color }}; }</style>
            </div>
            <div class="kanban-col-body" data-stage="{{ $stageKey }}"
                 ondragover="event.preventDefault(); this.style.background='rgba(59,130,196,0.06)'"
                 ondragleave="this.style.background=''"
                 ondrop="handleDrop(event, '{{ $stageKey }}')">

                @forelse($ops as $op)
                @php
                    // Checklist progress
                    $clTotal = $op->checklistItems->count();
                    $clDone = $op->checklistItems->where('completed', true)->count();
                    $pct = $clTotal > 0 ? round(($clDone / $clTotal) * 100) : 0;

                    // Stage navigation
                    $currentIdx = array_search($stageKey, $stageList);
                    $nextStage = ($currentIdx !== false && $currentIdx < count($stageList) - 1) ? $stageList[$currentIdx + 1] : null;

                    // Time since last update
                    $hoursSince = $op->updated_at->diffInHours(now());
                    $isStale = $hoursSince > 48;
                    $timeLabel = $hoursSince < 1 ? 'ahora' : ($hoursSince < 24 ? 'hace ' . $hoursSince . 'h' : 'hace ' . floor($hoursSince/24) . 'd');

                    // Client data
                    $client = $op->client;
                    $temp = $client->lead_temperature ?? '';
                    $phone = $client->whatsapp ?: $client->phone ?? '';
                    $waLink = $phone ? 'https://wa.me/52' . preg_replace('/\D/', '', $phone) : '';
                    $telLink = $phone ? 'tel:' . $phone : '';
                @endphp

                <div class="k-card" draggable="true"
                     data-op-id="{{ $op->id }}"
                     ondragstart="event.dataTransfer.setData('text/plain', '{{ $op->id }}'); this.classList.add('dragging')"
                     ondragend="this.classList.remove('dragging')">
                    <div class="k-card-temp {{ $temp === 'hot' ? 'hot' : ($temp === 'warm' ? 'warm' : ($temp === 'cold' ? 'cold' : 'none')) }}"></div>
                    <div class="k-card-body">
                        <div class="k-card-top">
                            <div class="k-card-name"><a href="{{ route('operations.show', $op) }}">{{ $client->name ?? 'Sin cliente' }}</a></div>
                            <span class="k-card-type {{ $op->type }}">{{ $op->type_label }}</span>
                        </div>
                        <div class="k-card-prop" title="{{ $op->property->title ?? '' }}">
                            {{ Str::limit($op->property->title ?? 'Sin propiedad', 30) }}
                        </div>
                        <div class="k-card-amount">
                            @if($op->type === 'renta')
                                ${{ number_format($op->monthly_rent ?? 0, 0) }}<span style="font-size:0.7rem; font-weight:500; color:var(--text-muted);">/mes</span>
                            @else
                                ${{ number_format($op->amount ?? 0, 0) }}
                            @endif
                        </div>
                        @if($clTotal > 0)
                        <div class="k-progress" title="{{ $clDone }}/{{ $clTotal }}">
                            <div class="k-progress-bar" style="width:{{ $pct }}%; background:{{ $color }};"></div>
                        </div>
                        @endif
                        <div class="k-card-meta">
                            <span class="k-card-time {{ $isStale ? 'stale' : '' }}">{{ $timeLabel }}</span>
                            @if($op->user)
                            <span class="k-card-user">{{ Str::limit($op->user->name, 10) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="k-card-actions">
                        @if($waLink)
                        <a href="{{ $waLink }}" target="_blank" class="k-action wa" title="WhatsApp" onclick="event.stopPropagation()">
                            <span class="k-action-icon">&#128172;</span>
                        </a>
                        @endif
                        @if($telLink)
                        <a href="{{ $telLink }}" class="k-action phone" title="Llamar" onclick="event.stopPropagation()">
                            <span class="k-action-icon">&#128222;</span>
                        </a>
                        @endif
                        <a href="{{ route('operations.show', $op) }}" class="k-action" title="Ver detalle" onclick="event.stopPropagation()">
                            <span class="k-action-icon">&#128065;</span>
                        </a>
                        @if($nextStage)
                        <form method="POST" action="{{ route('operations.update-stage', $op) }}" style="flex:1; display:flex;" onclick="event.stopPropagation()">
                            @csrf @method('PATCH')
                            <input type="hidden" name="stage" value="{{ $nextStage }}">
                            <button type="submit" class="k-advance" title="Avanzar a {{ $stageLabels[$nextStage] ?? '' }}">
                                {{ Str::limit($stageLabels[$nextStage] ?? '', 8) }} &#8594;
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="kanban-empty">Sin operaciones</div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ===== TABLE VIEW ===== --}}
<div class="card" id="viewTable" style="display:none;">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Propiedad</th>
                        <th>Monto</th>
                        <th>Etapa</th>
                        <th>Ultimo contacto</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operations as $op)
                    @php
                        $client = $op->client;
                        $phone = $client->whatsapp ?? $client->phone ?? '';
                        $waLink = $phone ? 'https://wa.me/52' . preg_replace('/\D/', '', $phone) : '';
                        $initials = collect(explode(' ', $client->name ?? '?'))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
                        $temp = $client->lead_temperature ?? '';
                        $avatarBg = match($temp) { 'hot' => '#ef4444', 'warm' => '#f59e0b', 'cold' => '#3b82f6', default => '#94a3b8' };
                        $sColor = $stageColors[$op->stage] ?? '#64748b';
                        $hoursSince = $op->updated_at->diffInHours(now());
                        $isStale = $hoursSince > 48;
                        $timeLabel = $hoursSince < 1 ? 'ahora' : ($hoursSince < 24 ? 'hace ' . $hoursSince . 'h' : 'hace ' . floor($hoursSince/24) . 'd');
                    @endphp
                    <tr>
                        <td>
                            <div class="client-cell">
                                <div class="client-avatar" style="background:{{ $avatarBg }}">{{ $initials }}</div>
                                <div>
                                    <div class="client-cell-name">{{ $client->name ?? '—' }}</div>
                                    <div class="client-cell-phone">
                                        <span class="k-card-type {{ $op->type }}" style="font-size:0.6rem;">{{ $op->type_label }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.85rem;">
                            {{ Str::limit($op->property->title ?? '—', 25) }}
                        </td>
                        <td style="font-weight:700; white-space:nowrap;">
                            @if($op->type === 'renta')
                                ${{ number_format($op->monthly_rent ?? 0, 0) }}/mes
                            @else
                                ${{ number_format($op->amount ?? 0, 0) }}
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background:{{ $sColor }}20; color:{{ $sColor }};">{{ $stageLabels[$op->stage] ?? $op->stage }}</span>
                        </td>
                        <td>
                            <span style="font-size:0.82rem; {{ $isStale ? 'color:#ef4444; font-weight:600;' : 'color:var(--text-muted);' }}">
                                {{ $timeLabel }}
                            </span>
                        </td>
                        <td>
                            <div class="table-actions-inline" style="justify-content:flex-end;">
                                @if($waLink)
                                <a href="{{ $waLink }}" target="_blank" class="t-action wa" title="WhatsApp">&#128172;</a>
                                @endif
                                <a href="{{ route('operations.show', $op) }}" class="t-action" title="Ver">&#128065;</a>
                                <a href="{{ route('operations.edit', $op) }}" class="t-action" title="Editar">&#9998;</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:2.5rem;">No hay operaciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($operations->hasPages())
        <div style="padding:0.75rem 1.25rem; border-top:1px solid var(--border);">{{ $operations->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

{{-- FAB --}}
<a href="{{ route('operations.create') }}" class="fab" title="Nueva Operacion">+</a>

@endsection

@section('scripts')
<script>
/* View toggle */
function setView(mode) {
    var k = document.getElementById('viewKanban');
    var t = document.getElementById('viewTable');
    var bk = document.getElementById('btnKanban');
    var bt = document.getElementById('btnTable');
    if (mode === 'kanban') {
        k.style.display = ''; t.style.display = 'none';
        bk.className = 'btn btn-sm btn-primary'; bt.className = 'btn btn-sm btn-outline';
    } else {
        k.style.display = 'none'; t.style.display = '';
        bk.className = 'btn btn-sm btn-outline'; bt.className = 'btn btn-sm btn-primary';
    }
    try { localStorage.setItem('opView', mode); } catch(e) {}
}
(function() {
    var s = 'kanban';
    try { s = localStorage.getItem('opView') || 'kanban'; } catch(e) {}
    setView(s);
})();

/* Filter toggle */
function toggleFilters() {
    var fb = document.getElementById('filterBar');
    fb.style.display = fb.style.display === 'none' ? 'flex' : 'none';
}
@if(request()->hasAny(['stage','search','user_id']))
document.getElementById('filterBar').style.display = 'flex';
@endif

/* Drag & Drop stage change */
var csrfToken = '{{ csrf_token() }}';

function handleDrop(e, targetStage) {
    e.preventDefault();
    e.currentTarget.style.background = '';
    var opId = e.dataTransfer.getData('text/plain');
    if (!opId) return;

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/operations/' + opId + '/stage';
    form.style.display = 'none';

    var tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = '_token';
    tokenInput.value = csrfToken;
    form.appendChild(tokenInput);

    var methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PATCH';
    form.appendChild(methodInput);

    var stageInput = document.createElement('input');
    stageInput.type = 'hidden';
    stageInput.name = 'stage';
    stageInput.value = targetStage;
    form.appendChild(stageInput);

    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
