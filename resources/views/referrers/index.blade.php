@extends('layouts.app-sidebar')
@section('title', 'Comisionistas')

@section('styles')
<style>
/* ===== Stats ===== */
.u-stats { display: flex !important; flex-direction: row !important; flex-wrap: nowrap; gap: 0.75rem; margin-bottom: 1.5rem; }
.u-stat {
    flex: 1; min-width: 0; background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;
}
.u-stat-icon {
    width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center;
    justify-content: center; font-size: 1.1rem; flex-shrink: 0;
}
.u-stat-val { font-size: 1.4rem; font-weight: 700; line-height: 1; }
.u-stat-label { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.1rem; }

/* ===== Toolbar ===== */
.u-toolbar {
    display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap;
}
.u-search { flex: 1; min-width: 200px; position: relative; }
.u-search input {
    width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.2rem; border: 1px solid var(--border);
    border-radius: 8px; font-size: 0.82rem; background: var(--card); color: var(--text);
    outline: none; transition: border-color 0.15s;
}
.u-search input:focus { border-color: var(--primary); }
.u-search-icon {
    position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%);
    color: var(--text-muted); font-size: 0.9rem; pointer-events: none;
}

/* ===== Tabs ===== */
.u-tabs {
    display: flex; gap: 2px; background: var(--bg); border-radius: 8px; padding: 3px;
    border: 1px solid var(--border); overflow-x: auto;
}
.u-tab {
    padding: 0.4rem 0.85rem; border-radius: 6px; font-size: 0.78rem; font-weight: 500;
    border: none; background: transparent; color: var(--text-muted); cursor: pointer;
    white-space: nowrap; transition: all 0.15s;
}
.u-tab:hover { color: var(--text); }
.u-tab.active { background: var(--card); color: var(--primary); font-weight: 600; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.u-tab .u-tab-count {
    font-size: 0.65rem; background: var(--bg); padding: 0 5px; border-radius: 8px;
    margin-left: 3px; font-weight: 600; color: var(--text-muted);
}
.u-tab.active .u-tab-count { background: rgba(59,130,196,0.12); color: var(--primary); }

/* ===== Grid ===== */
.u-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
.u-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
    padding: 1.25rem; transition: all 0.2s; position: relative; cursor: pointer;
}
.u-card:hover { border-color: var(--primary); box-shadow: 0 4px 20px rgba(0,0,0,0.05); transform: translateY(-1px); }
.u-card-top { display: flex; gap: 0.75rem; align-items: center; margin-bottom: 0.85rem; }
.u-avatar {
    width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 0.95rem; color: #fff; overflow: hidden;
}
.u-name { font-weight: 600; font-size: 0.9rem; line-height: 1.3; }
.u-name a { color: var(--text); text-decoration: none; }
.u-email { font-size: 0.75rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.u-card-badges { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 0.75rem; }
.u-role-badge {
    font-size: 0.68rem; font-weight: 600; padding: 0.15rem 0.5rem; border-radius: 4px;
    letter-spacing: 0.02em;
}
.u-card-meta { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem; }
.u-card-meta .meta-row { display: flex; justify-content: space-between; margin-bottom: 0.2rem; }
.u-card-footer { display: flex; gap: 0.4rem; }
.u-card-footer .btn { flex: 1; text-align: center; font-size: 0.72rem; padding: 0.35rem 0.5rem; }

.u-empty {
    text-align: center; padding: 4rem 2rem; color: var(--text-muted);
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
}
.u-empty-icon { font-size: 3rem; opacity: 0.2; margin-bottom: 0.75rem; }

.type-portero { background: rgba(59,130,246,0.1); color: #3b82f6; }
.type-vecino { background: rgba(34,197,94,0.1); color: #22c55e; }
.type-broker_hipotecario { background: rgba(168,85,247,0.1); color: #a855f7; }
.type-cliente_pasado { background: rgba(249,115,22,0.1); color: #f97316; }
.type-comisionista { background: rgba(234,179,8,0.1); color: #ca8a04; }
.type-otro { background: rgba(107,114,128,0.1); color: #6b7280; }

.u-fab {
    display: none; position: fixed; bottom: 80px; right: 16px; z-index: 91;
    width: 52px; height: 52px; border-radius: 50%; border: none;
    background: var(--primary); color: #fff; font-size: 26px; font-weight: 300;
    box-shadow: 0 4px 14px rgba(59,130,196,0.4);
    align-items: center; justify-content: center; cursor: pointer; text-decoration: none;
}

@media (max-width: 1024px) { .u-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) {
    .u-grid { grid-template-columns: 1fr; }
    .u-stats { flex-wrap: wrap; }
    .u-stat { flex: 1 1 calc(50% - 0.75rem); }
    .u-fab { display: flex; }
    .u-toolbar { flex-direction: column; align-items: stretch; }
}
</style>
@endsection

@section('content')
@php
    $avatarColors = ['#3B82C4','#1E3A5F','#f093fb','#4facfe','#43e97b','#fa709a','#fee140','#a18cd1'];
    $typeColors = [
        'portero' => '#3b82f6', 'vecino' => '#22c55e', 'broker_hipotecario' => '#a855f7',
        'cliente_pasado' => '#f97316', 'comisionista' => '#ca8a04', 'otro' => '#6b7280',
    ];
@endphp

{{-- Stats --}}
<div class="u-stats" style="display:flex; flex-direction:row; flex-wrap:nowrap; gap:0.75rem; margin-bottom:1.5rem;">
    <div class="u-stat" style="flex:1;">
        <div class="u-stat-icon" style="background:rgba(59,130,196,0.1); color:var(--primary);">&#128101;</div>
        <div><div class="u-stat-val">{{ $stats['total'] }}</div><div class="u-stat-label">Total</div></div>
    </div>
    <div class="u-stat" style="flex:1;">
        <div class="u-stat-icon" style="background:rgba(16,185,129,0.1); color:#10b981;">&#10003;</div>
        <div><div class="u-stat-val">{{ $stats['active'] }}</div><div class="u-stat-label">Activos</div></div>
    </div>
    <div class="u-stat" style="flex:1;">
        <div class="u-stat-icon" style="background:rgba(168,85,247,0.1); color:#a855f7;">&#128279;</div>
        <div><div class="u-stat-val">{{ $stats['referrals'] }}</div><div class="u-stat-label">Referidos</div></div>
    </div>
    <div class="u-stat" style="flex:1;">
        <div class="u-stat-icon" style="background:rgba(234,179,8,0.1); color:#ca8a04;">&#128176;</div>
        <div><div class="u-stat-val">${{ number_format($stats['paid'], 0) }}</div><div class="u-stat-label">Total Pagado</div></div>
    </div>
</div>

{{-- Toolbar --}}
<div class="u-toolbar">
    <div class="u-search">
        <span class="u-search-icon">&#128269;</span>
        <input type="text" id="refSearch" placeholder="Buscar por nombre, telefono, email..." value="{{ request('search') }}" autocomplete="off">
    </div>
    <div class="u-tabs" id="typeTabs">
        <button class="u-tab {{ !request('type') && !request('status') ? 'active' : '' }}" data-type="" data-status="">Todos <span class="u-tab-count">{{ $stats['total'] }}</span></button>
        @foreach(\App\Models\Referrer::TYPES as $val => $label)
            <button class="u-tab {{ request('type') === $val ? 'active' : '' }}" data-type="{{ $val }}">{{ $label }}</button>
        @endforeach
    </div>
    <a href="{{ route('referrers.create') }}" class="btn btn-primary" style="white-space:nowrap; padding:0.5rem 1rem;">+ Nuevo</a>
</div>

{{-- Grid --}}
<div id="refGrid">
    @if($referrers->count())
    <div class="u-grid">
        @foreach($referrers as $referrer)
        <div class="u-card" onclick="window.location='{{ route('referrers.show', $referrer) }}'">
            <div class="u-card-top">
                <div class="u-avatar" style="background:{{ $avatarColors[$referrer->id % count($avatarColors)] }};">
                    {{ strtoupper(substr($referrer->name, 0, 1)) }}
                </div>
                <div style="min-width:0;">
                    <div class="u-name"><a href="{{ route('referrers.show', $referrer) }}">{{ $referrer->name }}</a></div>
                    <div class="u-email">{{ $referrer->phone ?: $referrer->email ?: '—' }}</div>
                </div>
            </div>
            <div class="u-card-badges">
                <span class="u-role-badge type-{{ $referrer->type }}">
                    {{ \App\Models\Referrer::TYPES[$referrer->type] ?? $referrer->type }}
                </span>
                @if($referrer->status === 'active')
                    <span class="u-role-badge" style="background:rgba(16,185,129,0.1); color:#10b981;">Activo</span>
                @else
                    <span class="u-role-badge" style="background:rgba(239,68,68,0.1); color:#ef4444;">Inactivo</span>
                @endif
            </div>
            <div class="u-card-meta">
                <div class="meta-row"><span>Referidos</span><span>{{ $referrer->referrals_count }}</span></div>
                <div class="meta-row"><span>Ganado</span><span>${{ number_format($referrer->total_earned, 0) }}</span></div>
            </div>
            <div class="u-card-footer" onclick="event.stopPropagation();">
                <a href="{{ route('referrers.show', $referrer) }}" class="btn btn-sm btn-outline">Ver</a>
                <a href="{{ route('referrers.edit', $referrer) }}" class="btn btn-sm btn-outline">Editar</a>
            </div>
        </div>
        @endforeach
    </div>
    @if($referrers->hasPages())
        <div style="margin-top:1.25rem; text-align:center;">{{ $referrers->links() }}</div>
    @endif
    @else
    <div class="u-empty">
        <div class="u-empty-icon">&#128279;</div>
        <p style="font-weight:600; margin-bottom:0.25rem;">No hay comisionistas</p>
        <p style="font-size:0.82rem;">Registra porteros, vecinos y contactos que te refieran propietarios o clientes.</p>
        <a href="{{ route('referrers.create') }}" class="btn btn-primary" style="margin-top:1rem;">+ Nuevo Comisionista</a>
    </div>
    @endif
</div>

<a href="{{ route('referrers.create') }}" class="u-fab">+</a>
@endsection

@section('scripts')
<script>
var searchTimer;
var currentType = '{{ request('type', '') }}';

document.querySelectorAll('.u-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.u-tab').forEach(function(t) { t.classList.remove('active'); });
        this.classList.add('active');
        currentType = this.dataset.type || '';
        loadReferrers();
    });
});

document.getElementById('refSearch').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() { loadReferrers(); }, 350);
});

function loadReferrers() {
    var search = document.getElementById('refSearch').value;
    var params = new URLSearchParams();
    if (currentType) params.set('type', currentType);
    if (search) params.set('search', search);

    var url = '{{ route("referrers.index") }}?' + params.toString();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.text(); })
    .then(function(html) {
        var temp = document.createElement('div');
        temp.innerHTML = html;
        var grid = temp.querySelector('#refGrid');
        if (grid) document.getElementById('refGrid').innerHTML = grid.innerHTML;
    });
}
</script>
@endsection
