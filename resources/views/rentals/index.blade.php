@extends('layouts.app-sidebar')
@section('title', 'Rentas')

@section('styles')
<style>
/* ===== VIEW TOGGLE ===== */
.view-toggle {
    display: flex; gap: 0.2rem; background: var(--bg);
    border-radius: var(--radius); padding: 3px;
}
.view-toggle .btn { justify-content: center; min-width: 36px; }

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
.mini-stat-val   { font-size: 1.05rem; font-weight: 700; color: var(--text); line-height: 1; }
.mini-stat-label { font-size: 0.68rem; color: var(--text-muted); }

/* ===== KANBAN ===== */
.kanban-wrap {
    display: flex; gap: 0.6rem; overflow-x: auto;
    padding-bottom: 1rem; -webkit-overflow-scrolling: touch;
}
.kanban-col {
    min-width: 260px; max-width: 260px; flex-shrink: 0;
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
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
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
    transition: all 0.15s; overflow: hidden;
}
.k-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
.k-card-body   { padding: 0.6rem 0.7rem 0.5rem; }
.k-card-top    { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem; }
.k-card-name   { font-weight: 700; color: var(--text); font-size: 0.84rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0; }
.k-card-name a { color: inherit; text-decoration: none; }
.k-card-name a:hover { color: var(--primary); }
.k-card-sub    { font-size: 0.74rem; color: var(--text-muted); margin-bottom: 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.k-card-amount { font-size: 0.92rem; font-weight: 800; color: var(--text); margin-bottom: 0.35rem; }
.k-card-meta   { display: flex; align-items: center; justify-content: space-between; gap: 0.3rem; margin-top: 0.35rem; }
.k-card-time   { font-size: 0.7rem; color: var(--text-muted); }
.k-card-time.stale { color: #ef4444; font-weight: 600; }

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
.k-action.danger:hover { border-color: #ef4444; color: #ef4444; }
.kanban-empty { text-align: center; color: var(--text-muted); font-size: 0.75rem; padding: 1.5rem 0.5rem; opacity: 0.7; }

/* Expiry badge */
.expiry-badge {
    font-size: 0.65rem; font-weight: 700; padding: 0.1rem 0.45rem;
    border-radius: 999px; white-space: nowrap;
}
.expiry-ok     { background: #f0fdf4; color: #16a34a; }
.expiry-warn   { background: #fffbeb; color: #d97706; }
.expiry-danger { background: #fef2f2; color: #dc2626; }

/* Freq badge */
.freq-anual {
    font-size: 0.65rem; font-weight: 700; padding: 0.1rem 0.45rem;
    border-radius: 999px; background: #eff6ff; color: #1d4ed8;
}

/* ===== TABLE VIEW ===== */
.client-cell   { display: flex; align-items: center; gap: 0.5rem; }
.client-avatar { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700; color: #fff; flex-shrink: 0; }
.client-cell-name  { font-weight: 600; font-size: 0.84rem; }
.client-cell-sub   { font-size: 0.72rem; color: var(--text-muted); }
.table-actions-inline { display: flex; align-items: center; gap: 0.25rem; justify-content: flex-end; }
.t-action {
    width: 28px; height: 28px; border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--border); background: var(--card);
    color: var(--text-muted); cursor: pointer; transition: all 0.15s;
    text-decoration: none; font-size: 0.82rem;
}
.t-action:hover { border-color: var(--primary); color: var(--primary); }
.t-action.wa:hover { border-color: #25d366; color: #25d366; }

@media (max-width: 768px) {
    .kanban-col { min-width: 240px; max-width: 240px; }
    .stats-strip { display: none; }
}
</style>
@endsection

@section('content')
@php
$stageColorMap = \App\Models\RentalProcess::STAGE_COLORS;
@endphp

<div class="page-header">
    <div>
        <h2>Rentas</h2>
        <p style="color:var(--text-muted);font-size:.82rem;">Pipeline de arrendamiento</p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
        <div class="view-toggle">
            <button type="button" class="btn btn-sm" id="btnKanban" onclick="setView('kanban')" title="Kanban">&#9638;</button>
            <button type="button" class="btn btn-sm" id="btnTable"  onclick="setView('table')"  title="Lista">&#9776;</button>
        </div>
        <a href="{{ route('rentals.create') }}" class="btn btn-primary">+ Nueva Renta</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="margin-bottom:1rem;">{{ session('error') }}</div>
@endif

{{-- Stats Strip --}}
<div class="stats-strip">
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(59,130,246,.1);color:#3b82f6;">&#127968;</div>
        <div>
            <div class="mini-stat-val">{{ $stats['total'] }}</div>
            <div class="mini-stat-label">En proceso</div>
        </div>
    </div>
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(34,197,94,.1);color:#22c55e;">&#10003;</div>
        <div>
            <div class="mini-stat-val">{{ $stats['activo'] }}</div>
            <div class="mini-stat-label">Activas</div>
        </div>
    </div>
    @if($stats['valor_mensual'] > 0)
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(139,92,246,.1);color:#8b5cf6;">$</div>
        <div>
            <div class="mini-stat-val">${{ number_format($stats['valor_mensual'] / 1000, 0) }}k</div>
            <div class="mini-stat-label">Valor mensual</div>
        </div>
    </div>
    @endif
    @if($stats['por_vencer'] > 0)
    <div class="mini-stat" style="border-color:rgba(239,68,68,.3);">
        <div class="mini-stat-icon" style="background:rgba(239,68,68,.1);color:#ef4444;">&#9888;</div>
        <div>
            <div class="mini-stat-val" style="color:#ef4444;">{{ $stats['por_vencer'] }}</div>
            <div class="mini-stat-label">Por vencer (30d)</div>
        </div>
    </div>
    @endif
    @foreach($stages as $stageKey => $stageLabel)
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:{{ $stageColorMap[$stageKey] ?? '#94a3b8' }}1a;color:{{ $stageColorMap[$stageKey] ?? '#94a3b8' }};font-size:.65rem;font-weight:800;letter-spacing:-.02em;">
            {{ $rentalsByStage[$stageKey]->count() }}
        </div>
        <div>
            <div class="mini-stat-val">{{ $rentalsByStage[$stageKey]->count() }}</div>
            <div class="mini-stat-label">{{ $stageLabel }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- ===== KANBAN VIEW ===== --}}
<div id="viewKanban" style="display:none;">
    <div class="kanban-wrap">
        @php $stageList = array_keys($stages); @endphp
        @foreach($stages as $stageKey => $stageLabel)
        @php
            $color   = $stageColorMap[$stageKey] ?? '#94a3b8';
            $cards   = $rentalsByStage[$stageKey];
            $idx     = array_search($stageKey, $stageList);
        @endphp
        <div class="kanban-col">
            <div class="kanban-col-header" style="">
                <style>.kanban-col:nth-child({{ $idx + 1 }}) .kanban-col-header::before { background: {{ $color }}; }</style>
                <span class="kanban-col-title">{{ $stageLabel }}</span>
                <span class="kanban-col-count">{{ $cards->count() }}</span>
            </div>
            <div class="kanban-col-body">
                @forelse($cards as $rental)
                @php
                    $tenant     = $rental->tenantClient;
                    $owner      = $rental->ownerClient;
                    $phone      = $tenant->whatsapp ?? $tenant->phone ?? $owner->whatsapp ?? $owner->phone ?? '';
                    $waLink     = $phone ? 'https://wa.me/52' . preg_replace('/\D/', '', $phone) : '';
                    $daysLeft   = $rental->days_until_expiration;
                    $initials   = $tenant
                        ? collect(explode(' ', $tenant->name))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('')
                        : '?';
                    $hoursSince = $rental->updated_at->diffInHours(now());
                    $timeLabel  = $hoursSince < 1 ? 'ahora' : ($hoursSince < 24 ? 'hace '.$hoursSince.'h' : 'hace '.floor($hoursSince/24).'d');
                    $isStale    = $hoursSince > 72;
                    $freqAnual  = ($rental->payment_frequency ?? '') === 'anual';
                @endphp
                <div class="k-card">
                    <div style="height:3px;background:{{ $color }};"></div>
                    <div class="k-card-body">
                        <div class="k-card-top">
                            <div class="k-card-name">
                                <a href="{{ route('rentals.show', $rental->id) }}">{{ $rental->property->title ?? 'Sin propiedad' }}</a>
                            </div>
                            @if($freqAnual)
                            <span class="freq-anual">Anual</span>
                            @endif
                        </div>
                        @if($owner)
                        <div class="k-card-sub">&#127968; {{ $owner->name }}</div>
                        @endif
                        @if($tenant)
                        <div class="k-card-sub">&#128100; {{ $tenant->name }}</div>
                        @endif
                        @if($rental->monthly_rent)
                        <div class="k-card-amount">${{ number_format($rental->monthly_rent, 0) }} <span style="font-size:.72rem;font-weight:500;color:var(--text-muted);">{{ $rental->currency ?? 'MXN' }}/mes</span></div>
                        @endif
                        <div class="k-card-meta">
                            <span class="k-card-time {{ $isStale ? 'stale' : '' }}">{{ $timeLabel }}</span>
                            @if($daysLeft !== null && $stageKey === 'activo')
                                @if($daysLeft < 0)
                                    <span class="expiry-badge expiry-danger">Vencida</span>
                                @elseif($daysLeft <= 30)
                                    <span class="expiry-badge expiry-warn">{{ $daysLeft }}d</span>
                                @else
                                    <span class="expiry-badge expiry-ok">{{ $daysLeft }}d</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="k-card-actions">
                        @if($waLink)
                        <a href="{{ $waLink }}" target="_blank" class="k-action wa" title="WhatsApp">&#128172;</a>
                        @endif
                        @if($idx > 0)
                        <form method="POST" action="{{ route('rentals.update-stage', $rental->id) }}" style="flex:1;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="stage" value="{{ $stageList[$idx - 1] }}">
                            <button type="submit" class="k-action" style="width:100%;" title="{{ $stages[$stageList[$idx - 1]] }}">&#9664;</button>
                        </form>
                        @endif
                        <a href="{{ route('rentals.show', $rental->id) }}" class="k-action" title="Ver detalle">&#128065; Ver</a>
                        @if($idx < count($stageList) - 1)
                        <form method="POST" action="{{ route('rentals.update-stage', $rental->id) }}" style="flex:1;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="stage" value="{{ $stageList[$idx + 1] }}">
                            <button type="submit" class="k-action" style="width:100%;" title="{{ $stages[$stageList[$idx + 1]] }}">&#9654;</button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="kanban-empty">Sin procesos</div>
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
                        <th>Arrendatario</th>
                        <th>Propiedad</th>
                        <th>Propietario</th>
                        <th>Renta</th>
                        <th>Etapa</th>
                        <th>Vencimiento</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rentals as $rental)
                    @php
                        $tenant    = $rental->tenantClient;
                        $owner     = $rental->ownerClient;
                        $color     = $stageColorMap[$rental->stage] ?? '#94a3b8';
                        $phone     = $tenant->whatsapp ?? $tenant->phone ?? $owner->whatsapp ?? $owner->phone ?? '';
                        $waLink    = $phone ? 'https://wa.me/52' . preg_replace('/\D/', '', $phone) : '';
                        $initials  = $tenant
                            ? collect(explode(' ', $tenant->name))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('')
                            : ($owner ? collect(explode(' ', $owner->name))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('') : '?');
                        $daysLeft  = $rental->days_until_expiration;
                        $freqAnual = ($rental->payment_frequency ?? '') === 'anual';
                    @endphp
                    <tr>
                        <td>
                            <div class="client-cell">
                                <div class="client-avatar" style="background:{{ $color }};">{{ $initials }}</div>
                                <div>
                                    <div class="client-cell-name">{{ $tenant->name ?? '—' }}</div>
                                    <div class="client-cell-sub">{{ $tenant->phone ?? $tenant->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.84rem;font-weight:500;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $rental->property->title ?? '—' }}
                        </td>
                        <td style="font-size:.82rem;color:var(--text-muted);">{{ $owner->name ?? '—' }}</td>
                        <td style="font-weight:700;white-space:nowrap;">
                            @if($rental->monthly_rent)
                                ${{ number_format($rental->monthly_rent, 0) }}
                                @if($freqAnual) <span class="freq-anual">Anual</span> @endif
                            @else —
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background:{{ $color }}20;color:{{ $color }};">{{ $stages[$rental->stage] ?? $rental->stage }}</span>
                        </td>
                        <td>
                            @if($rental->lease_end_date)
                                @if($daysLeft < 0)
                                    <span class="expiry-badge expiry-danger">Vencida</span>
                                @elseif($daysLeft <= 30)
                                    <span class="expiry-badge expiry-warn">{{ $daysLeft }}d</span>
                                @else
                                    <span style="font-size:.78rem;color:var(--text-muted);">{{ $rental->lease_end_date->format('d/m/Y') }}</span>
                                @endif
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions-inline">
                                @if($waLink)
                                <a href="{{ $waLink }}" target="_blank" class="t-action wa" title="WhatsApp">&#128172;</a>
                                @endif
                                <a href="{{ route('rentals.show', $rental) }}" class="t-action" title="Ver">&#128065;</a>
                                <a href="{{ route('rentals.edit', $rental) }}" class="t-action" title="Editar">&#9998;</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:var(--text-muted);">No hay procesos de renta registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rentals->hasPages())
        <div style="padding:.75rem 1.25rem;border-top:1px solid var(--border);">{{ $rentals->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function setView(mode) {
    var k  = document.getElementById('viewKanban');
    var t  = document.getElementById('viewTable');
    var bk = document.getElementById('btnKanban');
    var bt = document.getElementById('btnTable');
    if (mode === 'kanban') {
        k.style.display = ''; t.style.display = 'none';
        bk.className = 'btn btn-sm btn-primary'; bt.className = 'btn btn-sm btn-outline';
    } else {
        k.style.display = 'none'; t.style.display = '';
        bk.className = 'btn btn-sm btn-outline'; bt.className = 'btn btn-sm btn-primary';
    }
    try { localStorage.setItem('rentalsView', mode); } catch(e) {}
}
(function() {
    var s = 'kanban';
    try { s = localStorage.getItem('rentalsView') || 'kanban'; } catch(e) {}
    setView(s);
})();
</script>
@endsection
