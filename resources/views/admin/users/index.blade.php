@extends('layouts.app-sidebar')
@section('title', 'Usuarios')

@section('styles')
<style>
/* Role pills */
.role-pills { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 2px; }
.role-pill {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.9rem; border-radius: 20px;
    font-size: 0.78rem; font-weight: 500; border: 1px solid var(--border); background: var(--card);
    color: var(--text-muted); text-decoration: none; white-space: nowrap; transition: all 0.15s;
}
.role-pill:hover { border-color: var(--primary); color: var(--text); }
.role-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.pill-count { font-size: 0.7rem; background: rgba(0,0,0,0.08); padding: 1px 6px; border-radius: 10px; }
.role-pill.active .pill-count { background: rgba(255,255,255,0.25); }

/* User cards */
.usr-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.usr-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 1.25rem; transition: all 0.15s; position: relative;
}
.usr-card:hover { border-color: var(--primary); box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
.usr-card-top { display: flex; gap: 0.75rem; align-items: flex-start; margin-bottom: 0.75rem; }
.usr-card-avatar {
    width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 1rem; color: #fff; background: var(--primary);
    overflow: hidden;
}
.usr-card-avatar img { width: 100%; height: 100%; object-fit: cover; }
.usr-card-info { flex: 1; min-width: 0; }
.usr-card-name { font-weight: 600; font-size: 0.92rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.usr-card-name a { color: var(--text); text-decoration: none; }
.usr-card-email { font-size: 0.78rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.usr-card-meta { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-bottom: 0.75rem; }
.usr-card-actions { display: flex; gap: 0.4rem; }
.usr-card-actions .btn { flex: 1; text-align: center; font-size: 0.75rem; padding: 0.35rem 0.5rem; }
.user-status-dot {
    position: absolute; top: 1rem; right: 1rem; width: 8px; height: 8px; border-radius: 50%;
}
.status-active { background: var(--success); }
.status-inactive { background: #94a3b8; }

/* View toggle */
.view-toggle { display: flex; gap: 0.25rem; }
.view-toggle button {
    background: var(--card); border: 1px solid var(--border); padding: 0.35rem 0.55rem;
    border-radius: var(--radius); cursor: pointer; color: var(--text-muted); font-size: 0.82rem;
    transition: all 0.15s;
}
.view-toggle button.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* Table */
.user-table-avatar {
    width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 0.75rem; color: #fff; background: var(--primary);
    overflow: hidden;
}
.user-table-avatar img { width: 100%; height: 100%; object-fit: cover; }

/* Empty state */
.users-empty { text-align: center; padding: 3rem; color: var(--text-muted); }

/* FAB */
.user-fab {
    display: none; position: fixed; bottom: 80px; right: 16px; z-index: 91;
    width: 52px; height: 52px; border-radius: 50%; border: none;
    background: var(--primary); color: #fff; font-size: 26px; font-weight: 300;
    box-shadow: 0 4px 14px rgba(102,126,234,0.4);
    align-items: center; justify-content: center; cursor: pointer; text-decoration: none;
}

@media (max-width: 1024px) { .usr-cards { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) {
    .usr-cards { grid-template-columns: 1fr; }
    .user-fab { display: flex; }
}
</style>
@endsection

@section('content')
@php
    $roleBadges = [
        'admin' => 'badge-red',
        'editor' => 'badge-blue',
        'viewer' => 'badge-green',
        'user' => 'badge-yellow',
        'broker' => 'badge-orange',
        'client' => 'badge-purple',
    ];
    $roleLabels = [
        'admin' => 'Admin',
        'editor' => 'Editor',
        'viewer' => 'Viewer',
        'user' => 'Usuario',
        'broker' => 'Broker',
        'client' => 'Cliente',
    ];
@endphp

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">&#9776;</div>
        <div><div class="stat-value">{{ $users->total() }}</div><div class="stat-label">Total</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">&#10003;</div>
        <div><div class="stat-value">{{ $users->where('is_active', true)->count() }}</div><div class="stat-label">Activos</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ef4444;">&#9733;</div>
        <div><div class="stat-value">{{ $users->where('role', 'admin')->count() }}</div><div class="stat-label">Admins</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-orange">&#128188;</div>
        <div><div class="stat-value">{{ $users->where('role', 'broker')->count() }}</div><div class="stat-label">Brokers</div></div>
    </div>
</div>

{{-- Header --}}
<div class="page-header">
    <div>
        <h2>Usuarios</h2>
        <p class="text-muted">{{ $users->total() }} usuario{{ $users->total() !== 1 ? 's' : '' }} registrados</p>
    </div>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        <div class="view-toggle">
            <button onclick="setView('cards')" id="btn-cards" title="Tarjetas">&#9871;</button>
            <button onclick="setView('table')" id="btn-table" title="Tabla">&#9776;</button>
        </div>
        @permission('users.create')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="white-space:nowrap;">+ Nuevo</a>
        @endpermission
    </div>
</div>

{{-- Role pills --}}
<div class="role-pills">
    <a href="{{ route('admin.users.index') }}" class="role-pill {{ !request('role') ? 'active' : '' }}">Todos <span class="pill-count">{{ $users->total() }}</span></a>
    <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="role-pill {{ request('role') === 'admin' ? 'active' : '' }}">Admin</a>
    <a href="{{ route('admin.users.index', ['role' => 'editor']) }}" class="role-pill {{ request('role') === 'editor' ? 'active' : '' }}">Editor</a>
    <a href="{{ route('admin.users.index', ['role' => 'broker']) }}" class="role-pill {{ request('role') === 'broker' ? 'active' : '' }}">Broker</a>
    <a href="{{ route('admin.users.index', ['role' => 'viewer']) }}" class="role-pill {{ request('role') === 'viewer' ? 'active' : '' }}">Viewer</a>
    <a href="{{ route('admin.users.index', ['role' => 'user']) }}" class="role-pill {{ request('role') === 'user' ? 'active' : '' }}">Usuario</a>
</div>

{{-- Card View --}}
<div id="view-cards">
    @if($users->count())
    <div class="usr-cards">
        @foreach($users as $user)
        <div class="usr-card">
            <span class="user-status-dot {{ ($user->is_active ?? true) ? 'status-active' : 'status-inactive' }}"></span>
            <div class="usr-card-top">
                <div class="usr-card-avatar">
                    @if($user->avatar_path)
                        <img src="{{ Storage::url($user->avatar_path) }}" alt="">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                    @endif
                </div>
                <div class="usr-card-info">
                    <div class="usr-card-name"><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }} {{ $user->last_name }}</a></div>
                    <div class="usr-card-email">{{ $user->email }}</div>
                    @if($user->phone)
                        <div class="usr-card-email">{{ $user->phone }}</div>
                    @endif
                </div>
            </div>
            <div class="usr-card-meta">
                <span class="badge {{ $roleBadges[$user->role] ?? 'badge-blue' }}">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span>
                @if($user->can_read) <span class="badge badge-green" style="font-size:0.68rem;">Leer</span> @endif
                @if($user->can_edit) <span class="badge badge-blue" style="font-size:0.68rem;">Editar</span> @endif
                @if($user->can_delete) <span class="badge badge-red" style="font-size:0.68rem;">Eliminar</span> @endif
            </div>
            <div class="usr-card-actions">
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline">Ver</a>
                @permission('users.edit')
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline">Editar</a>
                @endpermission
                @permission('users.delete')
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline" onsubmit="return confirm('Eliminar a {{ $user->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                    @endif
                @endpermission
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="users-empty">
        No hay usuarios {{ request('role') ? 'con ese rol' : '' }}.<br>
        @permission('users.create')
            <a href="{{ route('admin.users.create') }}" style="color:var(--primary); font-weight:500;">+ Crear primer usuario</a>
        @endpermission
    </div>
    @endif
</div>

{{-- Table View --}}
<div id="view-table" style="display:none;">
    <div class="card" style="overflow:hidden;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Permisos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr onclick="window.location='{{ route('admin.users.show', $user) }}'" style="cursor:pointer;">
                        <td>
                            <div style="display:flex; align-items:center; gap:0.6rem;">
                                <div class="user-table-avatar">
                                    @if($user->avatar_path)
                                        <img src="{{ Storage::url($user->avatar_path) }}" alt="">
                                    @else
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight:500;">{{ $user->name }} {{ $user->last_name }}</div>
                                    @if($user->phone)<div class="text-muted" style="font-size:0.75rem;">{{ $user->phone }}</div>@endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge {{ $roleBadges[$user->role] ?? 'badge-blue' }}">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span></td>
                        <td>
                            <div style="display:flex; gap:0.25rem; flex-wrap:wrap;">
                                @if($user->can_read) <span class="badge badge-green" style="font-size:0.68rem;">Leer</span> @endif
                                @if($user->can_edit) <span class="badge badge-blue" style="font-size:0.68rem;">Editar</span> @endif
                                @if($user->can_delete) <span class="badge badge-red" style="font-size:0.68rem;">Eliminar</span> @endif
                            </div>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div class="action-btns">
                                @permission('users.edit')
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline">Editar</a>
                                @endpermission
                                @permission('users.delete')
                                    @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline" onsubmit="return confirm('Eliminar a {{ $user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                    @endif
                                @endpermission
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted" style="padding:2rem;">No hay usuarios registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($users->hasPages())
<div style="margin-top:1rem; text-align:center;">{{ $users->links() }}</div>
@endif

@permission('users.create')
<a href="{{ route('admin.users.create') }}" class="user-fab">+</a>
@endpermission
@endsection

@section('scripts')
<script>
function setView(view) {
    document.getElementById('view-cards').style.display = view === 'cards' ? '' : 'none';
    document.getElementById('view-table').style.display = view === 'table' ? '' : 'none';
    document.getElementById('btn-cards').classList.toggle('active', view === 'cards');
    document.getElementById('btn-table').classList.toggle('active', view === 'table');
    localStorage.setItem('users_view', view);
}
(function() {
    var v = localStorage.getItem('users_view') || 'cards';
    setView(v);
})();
</script>
@endsection
