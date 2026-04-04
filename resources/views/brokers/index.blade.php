@extends('layouts.app-sidebar')
@section('title', 'Brokers Externos')

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
.u-search {
    flex: 1; min-width: 200px; position: relative;
}
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

/* ===== Filter Tabs ===== */
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
.u-tab.active .u-tab-count { background: rgba(102,126,234,0.12); color: var(--primary); }

/* ===== Broker Grid ===== */
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
    position: relative;
}
.u-avatar img { width: 100%; height: 100%; object-fit: cover; }
.u-status-dot {
    position: absolute; bottom: 0; right: 0; width: 12px; height: 12px; border-radius: 50%;
    border: 2px solid var(--card);
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

/* ===== Empty ===== */
.u-empty {
    text-align: center; padding: 4rem 2rem; color: var(--text-muted);
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
}
.u-empty-icon { font-size: 3rem; opacity: 0.2; margin-bottom: 0.75rem; }

/* ===== FAB ===== */
.u-fab {
    display: none; position: fixed; bottom: 80px; right: 16px; z-index: 91;
    width: 52px; height: 52px; border-radius: 50%; border: none;
    background: var(--primary); color: #fff; font-size: 26px; font-weight: 300;
    box-shadow: 0 4px 14px rgba(102,126,234,0.4);
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
    $avatarColors = ['#667eea','#764ba2','#f093fb','#4facfe','#43e97b','#fa709a','#fee140','#a18cd1'];
@endphp

{{-- Stats --}}
<div class="u-stats" style="display:flex; flex-direction:row; flex-wrap:nowrap; gap:0.75rem; margin-bottom:1.5rem;">
    <div class="u-stat" style="flex:1;">
        <div class="u-stat-icon" style="background:rgba(102,126,234,0.1); color:var(--primary);">&#9734;</div>
        <div><div class="u-stat-val">{{ $stats['total'] }}</div><div class="u-stat-label">Total</div></div>
    </div>
    <div class="u-stat" style="flex:1;">
        <div class="u-stat-icon" style="background:rgba(16,185,129,0.1); color:#10b981;">&#10003;</div>
        <div><div class="u-stat-val">{{ $stats['active'] }}</div><div class="u-stat-label">Activos</div></div>
    </div>
    <div class="u-stat" style="flex:1;">
        <div class="u-stat-icon" style="background:rgba(168,85,247,0.1); color:#a855f7;">&#128188;</div>
        <div><div class="u-stat-val">{{ $stats['operations'] }}</div><div class="u-stat-label">Operaciones</div></div>
    </div>
    <div class="u-stat" style="flex:1;">
        <div class="u-stat-icon" style="background:rgba(234,179,8,0.1); color:#ca8a04;">&#128176;</div>
        <div><div class="u-stat-val">${{ number_format($stats['commission'], 0) }}</div><div class="u-stat-label">Comision Pagada</div></div>
    </div>
</div>

{{-- Toolbar --}}
<div class="u-toolbar">
    <div class="u-search">
        <span class="u-search-icon">&#128269;</span>
        <input type="text" id="brokerSearch" placeholder="Buscar por nombre, email, empresa..." value="{{ request('search') }}" autocomplete="off">
    </div>
    <div class="u-tabs" id="statusTabs">
        <button class="u-tab {{ !request('status') && !request('company') ? 'active' : '' }}" data-status="">Todos <span class="u-tab-count">{{ $stats['total'] }}</span></button>
        <button class="u-tab {{ request('status') === 'active' ? 'active' : '' }}" data-status="active">Activos</button>
        <button class="u-tab {{ request('status') === 'inactive' ? 'active' : '' }}" data-status="inactive">Inactivos</button>
        @foreach($companies as $company)
            <button class="u-tab {{ request('company') == $company->id ? 'active' : '' }}" data-company="{{ $company->id }}">{{ $company->name }}</button>
        @endforeach
    </div>
    <a href="{{ route('brokers.create') }}" class="btn btn-primary" style="white-space:nowrap; padding:0.5rem 1rem;">+ Nuevo</a>
</div>

{{-- Broker Grid --}}
<div id="brokerGrid">
    @if($brokers->count())
    <div class="u-grid">
        @foreach($brokers as $broker)
        <div class="u-card" onclick="window.location='{{ route('brokers.show', $broker) }}'">
            <div class="u-card-top">
                <div class="u-avatar" style="background:{{ $avatarColors[$broker->id % count($avatarColors)] }};">
                    @if($broker->photo)
                        <img src="{{ asset('storage/' . $broker->photo) }}" alt="">
                    @else
                        {{ strtoupper(substr($broker->name, 0, 1)) }}
                    @endif
                    <div class="u-status-dot" style="background:{{ $broker->status === 'active' ? '#10b981' : '#ef4444' }};"></div>
                </div>
                <div style="min-width:0;">
                    <div class="u-name"><a href="{{ route('brokers.show', $broker) }}">{{ $broker->name }}</a></div>
                    <div class="u-email">{{ $broker->email }}</div>
                </div>
            </div>
            <div class="u-card-badges">
                @if($broker->company)
                    <span class="u-role-badge" style="background:rgba(102,126,234,0.1); color:var(--primary);">{{ $broker->company->name }}</span>
                @elseif($broker->company_name)
                    <span class="u-role-badge" style="background:rgba(148,163,184,0.1); color:#94a3b8;">{{ $broker->company_name }}</span>
                @endif
                @if($broker->commission_rate)
                    <span class="u-role-badge" style="background:rgba(234,179,8,0.1); color:#ca8a04;">{{ $broker->commission_rate }}%</span>
                @endif
                @if($broker->status === 'active')
                    <span class="u-role-badge" style="background:rgba(16,185,129,0.1); color:#10b981;">Activo</span>
                @else
                    <span class="u-role-badge" style="background:rgba(239,68,68,0.1); color:#ef4444;">Inactivo</span>
                @endif
            </div>
            <div class="u-card-meta">
                @if($broker->phone)
                    <div class="meta-row"><span>Telefono</span><span>{{ $broker->phone }}</span></div>
                @endif
                <div class="meta-row"><span>Operaciones</span><span>{{ $broker->operations_count ?? 0 }}</span></div>
                <div class="meta-row"><span>Clientes</span><span>{{ $broker->clients_count ?? 0 }}</span></div>
            </div>
            <div class="u-card-footer" onclick="event.stopPropagation();">
                <a href="{{ route('brokers.show', $broker) }}" class="btn btn-sm btn-outline">Ver</a>
                <a href="{{ route('brokers.edit', $broker) }}" class="btn btn-sm btn-outline">Editar</a>
            </div>
        </div>
        @endforeach
    </div>
    @if($brokers->hasPages())
        <div style="margin-top:1.25rem; text-align:center;">{{ $brokers->links() }}</div>
    @endif
    @else
    <div class="u-empty">
        <div class="u-empty-icon">&#9734;</div>
        <p style="font-weight:600; margin-bottom:0.25rem;">No hay brokers externos</p>
        <p style="font-size:0.82rem;">Agrega un broker externo para comenzar a colaborar.</p>
        <a href="{{ route('brokers.create') }}" class="btn btn-primary" style="margin-top:1rem;">+ Nuevo Broker</a>
    </div>
    @endif
</div>

<a href="{{ route('brokers.create') }}" class="u-fab">+</a>
@endsection

@section('scripts')
<script>
var searchTimer;
var currentStatus = '{{ request('status', '') }}';
var currentCompany = '{{ request('company', '') }}';

document.querySelectorAll('.u-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.u-tab').forEach(function(t) { t.classList.remove('active'); });
        this.classList.add('active');
        if (this.dataset.company !== undefined) {
            currentCompany = this.dataset.company;
            currentStatus = '';
        } else {
            currentStatus = this.dataset.status;
            currentCompany = '';
        }
        loadBrokers();
    });
});

document.getElementById('brokerSearch').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() { loadBrokers(); }, 350);
});

function loadBrokers() {
    var search = document.getElementById('brokerSearch').value;
    var params = new URLSearchParams();
    if (currentStatus) params.set('status', currentStatus);
    if (currentCompany) params.set('company', currentCompany);
    if (search) params.set('search', search);

    var url = '{{ route("brokers.index") }}?' + params.toString();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.text(); })
    .then(function(html) {
        var temp = document.createElement('div');
        temp.innerHTML = html;
        var grid = temp.querySelector('#brokerGrid');
        if (grid) {
            document.getElementById('brokerGrid').innerHTML = grid.innerHTML;
        }
    });
}
</script>
@endsection
