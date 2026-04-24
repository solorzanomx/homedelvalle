@extends('layouts.app-sidebar')
@section('title', 'Evaluación de Propiedad')

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
    transition: all 0.15s; overflow: hidden;
}
.k-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
.k-card-body   { padding: 0.6rem 0.7rem 0.5rem; }
.k-card-top    { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.3rem; }
.k-card-name   { font-weight: 700; color: var(--text); font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; min-width: 0; }
.k-card-name a { color: inherit; text-decoration: none; }
.k-card-name a:hover { color: var(--primary); }
.k-card-prop   { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.k-card-amount { font-size: 0.92rem; font-weight: 800; color: var(--text); margin-bottom: 0.35rem; }

/* Doc progress bar */
.k-progress { height: 3px; background: var(--border); border-radius: 2px; overflow: hidden; margin-bottom: 0.4rem; }
.k-progress-bar { height: 100%; border-radius: 2px; transition: width 0.3s; }

.k-card-meta {
    display: flex; align-items: center; justify-content: space-between;
    gap: 0.3rem; margin-bottom: 0.35rem;
}
.k-card-time { font-size: 0.7rem; color: var(--text-muted); }
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
.kanban-empty { text-align: center; color: var(--text-muted); font-size: 0.75rem; padding: 1.5rem 0.5rem; opacity: 0.7; }

/* Doc status dots */
.doc-dots { display: flex; gap: 3px; flex-wrap: wrap; margin-bottom: 0.35rem; }
.doc-dot  { width: 7px; height: 7px; border-radius: 50%; }

/* ===== TABLE VIEW ===== */
.client-cell   { display: flex; align-items: center; gap: 0.5rem; }
.client-avatar { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; color: #fff; flex-shrink: 0; }
.client-cell-name  { font-weight: 600; }
.client-cell-phone { font-size: 0.72rem; color: var(--text-muted); }
.table-actions-inline { display: flex; align-items: center; gap: 0.25rem; }
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
    .kanban-col { min-width: 250px; max-width: 250px; }
    .stats-strip { display: none; }
}
</style>
@endsection

@section('content')
@php
$etapaList   = [1, 2, 3, 4];
@endphp

<div class="page-header">
    <div>
        <h2>Evaluación de Propiedad</h2>
        <p style="color:var(--text-muted);font-size:.82rem;">Pipeline de captación de inmuebles</p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
        <div class="view-toggle">
            <button type="button" class="btn btn-sm" id="btnKanban" onclick="setView('kanban')" title="Kanban">&#9638;</button>
            <button type="button" class="btn btn-sm" id="btnTable"  onclick="setView('table')"  title="Lista">&#9776;</button>
        </div>
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
    @if($stats['pipeline_value'] > 0)
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(16,185,129,.1);color:#10b981;">$</div>
        <div>
            <div class="mini-stat-val">${{ number_format($stats['pipeline_value'] / 1000000, 1) }}M</div>
            <div class="mini-stat-label">Valor acordado</div>
        </div>
    </div>
    @endif
    @if($stats['docs_pending'] > 0)
    <div class="mini-stat" style="border-color:rgba(245,158,11,.3);">
        <div class="mini-stat-icon" style="background:rgba(245,158,11,.1);color:#f59e0b;">&#128196;</div>
        <div>
            <div class="mini-stat-val" style="color:#f59e0b;">{{ $stats['docs_pending'] }}</div>
            <div class="mini-stat-label">Docs pendientes</div>
        </div>
    </div>
    @endif
    @foreach($etapaLabels as $n => $label)
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:{{ $etapaColors[$n] }}1a;color:{{ $etapaColors[$n] }};font-size:.7rem;font-weight:800;">{{ $n }}</div>
        <div>
            <div class="mini-stat-val">{{ ($byEtapa[$n] ?? collect())->count() }}</div>
            <div class="mini-stat-label">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- ===== KANBAN VIEW ===== --}}
<div id="viewKanban" style="display:none;">
    <div class="kanban-wrap">
        @foreach($etapaList as $etapa)
        @php
            $color = $etapaColors[$etapa];
            $label = $etapaLabels[$etapa];
            $caps  = $byEtapa[$etapa] ?? collect();
        @endphp
        <div class="kanban-col">
            <div class="kanban-col-header">
                <span class="kanban-col-title">{{ $label }}</span>
                <span class="kanban-col-count">{{ $caps->count() }}</span>
                <style>.kanban-col:nth-child({{ $etapa }}) .kanban-col-header::before { background: {{ $color }}; }</style>
            </div>
            <div class="kanban-col-body">
                @forelse($caps as $cap)
                @php
                    $client   = $cap->client;
                    $phone    = $client->whatsapp ?? $client->phone ?? '';
                    $waLink   = $phone ? 'https://wa.me/52' . preg_replace('/\D/', '', $phone) : '';
                    $approved = $cap->documents->where('captacion_status','aprobado')->count();
                    $total    = $cap->documents->count();
                    $pct      = $total > 0 ? round(($approved / $total) * 100) : 0;
                    $hoursSince = $cap->updated_at->diffInHours(now());
                    $isStale  = $hoursSince > 72;
                    $timeLabel = $hoursSince < 1 ? 'ahora' : ($hoursSince < 24 ? 'hace '.$hoursSince.'h' : 'hace '.floor($hoursSince/24).'d');
                    $initials  = collect(explode(' ', $client->name ?? '?'))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('');
                @endphp
                <div class="k-card">
                    <div style="height:3px;background:{{ $color }};"></div>
                    <div class="k-card-body">
                        <div class="k-card-top">
                            <div class="k-card-name">
                                <a href="{{ route('admin.captaciones.show', $cap) }}">{{ $client->name ?? '—' }}</a>
                            </div>
                        </div>
                        @if($client->email)
                        <div class="k-card-prop">{{ $client->email }}</div>
                        @endif
                        @if($cap->precio_acordado)
                        <div class="k-card-amount">${{ number_format($cap->precio_acordado, 0) }}</div>
                        @endif
                        @if($total > 0)
                        <div class="doc-dots">
                            @foreach($cap->documents as $doc)
                            <span class="doc-dot" style="background:{{ $doc->captacion_status === 'aprobado' ? '#10b981' : ($doc->captacion_status === 'rechazado' ? '#ef4444' : '#d1d5db') }};" title="{{ $doc->category }}: {{ $doc->captacion_status }}"></span>
                            @endforeach
                        </div>
                        <div class="k-progress" title="{{ $approved }}/{{ $total }} docs aprobados">
                            <div class="k-progress-bar" style="width:{{ $pct }}%;background:{{ $color }};"></div>
                        </div>
                        @endif
                        <div class="k-card-meta">
                            <span class="k-card-time {{ $isStale ? 'stale' : '' }}">{{ $timeLabel }}</span>
                            <span style="font-size:.68rem;color:var(--text-muted);">{{ $approved }}/{{ $total }} docs</span>
                        </div>
                    </div>
                    <div class="k-card-actions">
                        @if($waLink)
                        <a href="{{ $waLink }}" target="_blank" class="k-action wa" title="WhatsApp">&#128172;</a>
                        @endif
                        <a href="{{ route('admin.captaciones.show', $cap) }}" class="k-action" title="Ver detalle">&#128065; Ver</a>
                    </div>
                </div>
                @empty
                <div class="kanban-empty">Sin captaciones</div>
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
                        <th>Etapa</th>
                        <th>Documentos</th>
                        <th>Precio acordado</th>
                        <th>Actualizado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($captaciones as $cap)
                    @php
                        $client   = $cap->client;
                        $phone    = $client->whatsapp ?? $client->phone ?? '';
                        $waLink   = $phone ? 'https://wa.me/52' . preg_replace('/\D/', '', $phone) : '';
                        $approved = $cap->documents->where('captacion_status','aprobado')->count();
                        $total    = $cap->documents->count();
                        $color    = $etapaColors[$cap->portal_etapa] ?? '#94a3b8';
                        $label    = $etapaLabels[$cap->portal_etapa] ?? 'Etapa '.$cap->portal_etapa;
                        $initials = collect(explode(' ', $client->name ?? '?'))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('');
                        $hoursSince = $cap->updated_at->diffInHours(now());
                        $isStale  = $hoursSince > 72;
                        $timeLabel = $hoursSince < 1 ? 'ahora' : ($hoursSince < 24 ? 'hace '.$hoursSince.'h' : 'hace '.floor($hoursSince/24).'d');
                    @endphp
                    <tr>
                        <td>
                            <div class="client-cell">
                                <div class="client-avatar" style="background:{{ $color }};">{{ $initials }}</div>
                                <div>
                                    <div class="client-cell-name">{{ $client->name ?? '—' }}</div>
                                    <div class="client-cell-phone">{{ $client->email ?? $client->phone ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background:{{ $color }}20;color:{{ $color }};">{{ $label }}</span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                <div style="width:60px;height:4px;background:var(--border);border-radius:2px;overflow:hidden;">
                                    <div style="height:100%;width:{{ $total > 0 ? round($approved/$total*100) : 0 }}%;background:{{ $color }};border-radius:2px;"></div>
                                </div>
                                <span style="font-size:.78rem;color:var(--text-muted);">{{ $approved }}/{{ $total }}</span>
                            </div>
                        </td>
                        <td style="font-weight:700;">
                            {{ $cap->precio_acordado ? '$'.number_format($cap->precio_acordado,0) : '—' }}
                        </td>
                        <td>
                            <span style="font-size:.82rem;{{ $isStale ? 'color:#ef4444;font-weight:600;' : 'color:var(--text-muted);' }}">{{ $timeLabel }}</span>
                        </td>
                        <td>
                            <div class="table-actions-inline" style="justify-content:flex-end;">
                                @if($waLink)
                                <a href="{{ $waLink }}" target="_blank" class="t-action wa" title="WhatsApp">&#128172;</a>
                                @endif
                                <a href="{{ route('admin.captaciones.show', $cap) }}" class="t-action" title="Ver">&#128065;</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:2.5rem;color:var(--text-muted);">No hay captaciones activas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($captaciones->hasPages())
        <div style="padding:.75rem 1.25rem;border-top:1px solid var(--border);">{{ $captaciones->withQueryString()->links() }}</div>
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
    try { localStorage.setItem('capView', mode); } catch(e) {}
}
(function() {
    var s = 'kanban';
    try { s = localStorage.getItem('capView') || 'kanban'; } catch(e) {}
    setView(s);
})();
</script>
@endsection
