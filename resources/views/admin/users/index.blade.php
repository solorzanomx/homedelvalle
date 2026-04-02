@extends('layouts.app-sidebar')
@section('title', 'Usuarios')

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

/* ===== Role Tabs ===== */
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

/* ===== User Grid ===== */
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
    $roleBgColors = ['admin'=>'#ef4444','editor'=>'#3b82f6','viewer'=>'#10b981','user'=>'#f59e0b','broker'=>'#f97316','client'=>'#8b5cf6'];
    $roleLabels = ['admin'=>'Admin','editor'=>'Editor','viewer'=>'Viewer','user'=>'Usuario','broker'=>'Broker','client'=>'Cliente'];
    $avatarColors = ['#667eea','#764ba2','#f093fb','#4facfe','#43e97b','#fa709a','#fee140','#a18cd1'];
@endphp

{{-- Stats --}}
<div class="u-stats">
    <div class="u-stat">
        <div class="u-stat-icon" style="background:rgba(102,126,234,0.1); color:var(--primary);">&#9823;</div>
        <div><div class="u-stat-val">{{ $stats['total'] }}</div><div class="u-stat-label">Total</div></div>
    </div>
    <div class="u-stat">
        <div class="u-stat-icon" style="background:rgba(16,185,129,0.1); color:#10b981;">&#10003;</div>
        <div><div class="u-stat-val">{{ $stats['active'] }}</div><div class="u-stat-label">Activos</div></div>
    </div>
    <div class="u-stat">
        <div class="u-stat-icon" style="background:rgba(239,68,68,0.1); color:#ef4444;">&#9733;</div>
        <div><div class="u-stat-val">{{ $stats['admins'] }}</div><div class="u-stat-label">Admins</div></div>
    </div>
    <div class="u-stat">
        <div class="u-stat-icon" style="background:rgba(249,115,22,0.1); color:#f97316;">&#128188;</div>
        <div><div class="u-stat-val">{{ $stats['brokers'] }}</div><div class="u-stat-label">Brokers</div></div>
    </div>
</div>

{{-- Toolbar --}}
<div class="u-toolbar">
    <div class="u-search">
        <span class="u-search-icon">&#128269;</span>
        <input type="text" id="userSearch" placeholder="Buscar por nombre o email..." value="{{ request('search') }}" autocomplete="off">
    </div>
    <div class="u-tabs" id="roleTabs">
        <button class="u-tab {{ !request('role') ? 'active' : '' }}" data-role="">Todos <span class="u-tab-count">{{ $stats['total'] }}</span></button>
        <button class="u-tab {{ request('role') === 'admin' ? 'active' : '' }}" data-role="admin">Admin</button>
        <button class="u-tab {{ request('role') === 'broker' ? 'active' : '' }}" data-role="broker">Broker</button>
        <button class="u-tab {{ request('role') === 'editor' ? 'active' : '' }}" data-role="editor">Director</button>
        <button class="u-tab {{ request('role') === 'viewer' ? 'active' : '' }}" data-role="viewer">Asesor</button>
        <button class="u-tab {{ request('role') === 'user' ? 'active' : '' }}" data-role="user">Usuario</button>
    </div>
    @permission('users.create')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="white-space:nowrap; padding:0.5rem 1rem;">+ Nuevo</a>
    @endpermission
</div>

{{-- User Grid --}}
<div id="userGrid">
    @include('admin.users._grid', ['users' => $users])
</div>

@permission('users.create')
<a href="{{ route('admin.users.create') }}" class="u-fab">+</a>
@endpermission
@endsection

@section('scripts')
<script>
var searchTimer;
var currentRole = '{{ request('role', '') }}';

// Tab click
document.querySelectorAll('.u-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.u-tab').forEach(function(t) { t.classList.remove('active'); });
        this.classList.add('active');
        currentRole = this.dataset.role;
        loadUsers();
    });
});

// Search
document.getElementById('userSearch').addEventListener('input', function() {
    clearTimeout(searchTimer);
    var val = this.value;
    searchTimer = setTimeout(function() { loadUsers(); }, 350);
});

function loadUsers() {
    var search = document.getElementById('userSearch').value;
    var params = new URLSearchParams();
    if (currentRole) params.set('role', currentRole);
    if (search) params.set('search', search);

    var url = '{{ route("admin.users.index") }}?' + params.toString();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.text(); })
    .then(function(html) {
        // Extract grid content from full page
        var temp = document.createElement('div');
        temp.innerHTML = html;
        var grid = temp.querySelector('#userGrid');
        if (grid) {
            document.getElementById('userGrid').innerHTML = grid.innerHTML;
        }
    });
}
</script>
@endsection
