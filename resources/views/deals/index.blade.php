@extends('layouts.app-sidebar')
@section('title', 'Deals')

@section('styles')
<style>
.view-toggle {
    display: flex;
    gap: 0.25rem;
    background: var(--bg);
    border-radius: var(--radius);
    padding: 3px;
}
.view-toggle .btn { justify-content: center; min-width: 38px; }

/* ===== KANBAN ===== */
.kanban-board {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding-bottom: 1rem;
    -webkit-overflow-scrolling: touch;
}
.kanban-col {
    min-width: 260px;
    max-width: 300px;
    background: var(--bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 260px);
}
.kanban-col-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.82rem;
    font-weight: 600;
    background: var(--card);
    border-radius: var(--radius) var(--radius) 0 0;
}
.kanban-col-header .count {
    font-size: 0.7rem;
    font-weight: 500;
    padding: 0.1rem 0.45rem;
    border-radius: 10px;
    background: var(--border);
    color: var(--text-muted);
}
.kanban-col-body {
    padding: 0.5rem;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.kanban-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 0.75rem;
    transition: box-shadow 0.15s;
}
.kanban-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.kanban-card-title {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kanban-card-sub {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.35rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kanban-card-amount {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 0.35rem;
}
.kanban-card-broker {
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}
.kanban-card-actions {
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
}
.kanban-card-actions form { display: inline; }
.kanban-card-actions .btn { padding: 0.2rem 0.5rem; font-size: 0.7rem; }
.kanban-empty {
    text-align: center;
    color: var(--text-muted);
    font-size: 0.78rem;
    padding: 1.5rem 0.5rem;
}

/* Stage color bars */
.stage-bar-lead { border-top: 3px solid #3b82f6; }
.stage-bar-contact { border-top: 3px solid #3b82f6; }
.stage-bar-visit { border-top: 3px solid #f59e0b; }
.stage-bar-negotiation { border-top: 3px solid #f59e0b; }
.stage-bar-offer { border-top: 3px solid #8b5cf6; }
.stage-bar-closing { border-top: 3px solid #8b5cf6; }
.stage-bar-won { border-top: 3px solid #10b981; }
.stage-bar-lost { border-top: 3px solid #ef4444; }

/* Badge extras */
.badge-purple { background: #EBF5FF; color: #2563A0; }

@media (max-width: 768px) {
    .kanban-col { min-width: 220px; }
}
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Deals</h2>
        <p class="text-muted">Pipeline de ventas</p>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <div class="view-toggle">
            <button type="button" class="btn btn-sm" id="btnKanban" onclick="setDealView('kanban')" title="Kanban">&#9638;</button>
            <button type="button" class="btn btn-sm" id="btnTable" onclick="setDealView('table')" title="Lista">&#9776;</button>
        </div>
        <a href="{{ route('deals.create') }}" class="btn btn-primary">+ Nuevo Deal</a>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">&#9830;</div>
        <div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Deals</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-orange">&#9654;</div>
        <div>
            <div class="stat-value">{{ $stats['active'] }}</div>
            <div class="stat-label">Activos</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">&#10003;</div>
        <div>
            <div class="stat-value">{{ $stats['won'] }}</div>
            <div class="stat-label">Ganados</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-purple">&#36;</div>
        <div>
            <div class="stat-value">${{ number_format($stats['value'], 0) }}</div>
            <div class="stat-label">Valor Pipeline</div>
        </div>
    </div>
</div>

@php
    $stageColors = [
        'lead' => 'blue', 'contact' => 'blue',
        'visit' => 'yellow', 'negotiation' => 'yellow',
        'offer' => 'purple', 'closing' => 'purple',
        'won' => 'green', 'lost' => 'red',
    ];
    $stageList = array_keys($stages);
@endphp

{{-- Kanban View --}}
<div id="viewKanban" style="display:none;">
    <div class="kanban-board">
        @foreach($stages as $stageKey => $stageLabel)
        <div class="kanban-col stage-bar-{{ $stageKey }}">
            <div class="kanban-col-header">
                <span>{{ $stageLabel }}</span>
                <span class="count">{{ $dealsByStage[$stageKey]->count() }}</span>
            </div>
            <div class="kanban-col-body">
                @forelse($dealsByStage[$stageKey] as $deal)
                <div class="kanban-card">
                    <div class="kanban-card-title">{{ $deal->client->name ?? 'Sin cliente' }}</div>
                    <div class="kanban-card-sub" title="{{ $deal->property->title ?? '' }}">{{ Str::limit($deal->property->title ?? 'Sin propiedad', 30) }}</div>
                    <div class="kanban-card-amount">${{ number_format($deal->amount, 0) }}</div>
                    @if($deal->broker)
                        <div class="kanban-card-broker">&#9734; {{ $deal->broker->name }}</div>
                    @endif
                    <div class="kanban-card-actions">
                        @php
                            $idx = array_search($stageKey, $stageList);
                        @endphp
                        @if($idx > 0)
                        <form method="POST" action="{{ route('deals.update-stage', $deal->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="stage" value="{{ $stageList[$idx - 1] }}">
                            <button type="submit" class="btn btn-sm btn-outline" title="Mover a {{ $stages[$stageList[$idx - 1]] }}">&#9664;</button>
                        </form>
                        @endif
                        <a href="{{ route('deals.edit', $deal->id) }}" class="btn btn-sm btn-outline">Editar</a>
                        @if($idx < count($stageList) - 1)
                        <form method="POST" action="{{ route('deals.update-stage', $deal->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="stage" value="{{ $stageList[$idx + 1] }}">
                            <button type="submit" class="btn btn-sm btn-outline" title="Mover a {{ $stages[$stageList[$idx + 1]] }}">&#9654;</button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="kanban-empty">Sin deals</div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Table View --}}
<div class="card" id="viewTable" style="display:none;">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Propiedad</th>
                        <th>Cliente</th>
                        <th>Broker</th>
                        <th>Monto</th>
                        <th>Etapa</th>
                        <th>Fecha Esperada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deals as $deal)
                    <tr>
                        <td style="font-weight:500; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $deal->property->title ?? '—' }}</td>
                        <td>{{ $deal->client->name ?? '—' }}</td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $deal->broker->name ?? '—' }}</td>
                        <td style="font-weight:600; white-space:nowrap;">${{ number_format($deal->amount, 0) }}</td>
                        <td>
                            <span class="badge badge-{{ $stageColors[$deal->stage] ?? 'blue' }}">{{ $stages[$deal->stage] ?? $deal->stage }}</span>
                        </td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $deal->expected_close_date ? $deal->expected_close_date->format('d/m/Y') : '—' }}</td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('deals.edit', $deal) }}" class="btn btn-sm btn-outline">Editar</a>
                                <form method="POST" action="{{ route('deals.destroy', $deal) }}" style="display:inline" onsubmit="return confirm('Eliminar este deal?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted" style="padding:2rem;">No hay deals registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deals->hasPages())
        <div style="padding:1rem 1.5rem; border-top:1px solid var(--border);">{{ $deals->links() }}</div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function setDealView(mode) {
    var kanban = document.getElementById('viewKanban');
    var table = document.getElementById('viewTable');
    var btnK = document.getElementById('btnKanban');
    var btnT = document.getElementById('btnTable');
    if (mode === 'kanban') {
        kanban.style.display = '';
        table.style.display = 'none';
        btnK.className = 'btn btn-sm btn-primary';
        btnT.className = 'btn btn-sm btn-outline';
    } else {
        kanban.style.display = 'none';
        table.style.display = '';
        btnK.className = 'btn btn-sm btn-outline';
        btnT.className = 'btn btn-sm btn-primary';
    }
    try { localStorage.setItem('deals_view', mode); } catch(e) {}
}
(function() {
    var s = 'kanban';
    try { s = localStorage.getItem('deals_view') || 'kanban'; } catch(e) {}
    setDealView(s);
})();
</script>
@endsection
