@extends('layouts.app-sidebar')
@section('title', 'Permisos de Usuarios')

@section('styles')
<style>
/* Nav pills */
.perm-pills { display: flex; gap: 0.4rem; margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 2px; }
.perm-pill {
    padding: 0.45rem 0.9rem; border-radius: 20px; font-size: 0.78rem; font-weight: 500;
    border: 1px solid var(--border); background: var(--card); color: var(--text-muted);
    text-decoration: none; white-space: nowrap; transition: all 0.15s;
}
.perm-pill:hover { border-color: var(--primary); color: var(--text); }
.perm-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* User list */
.user-list { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.user-row {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem;
    border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.1s;
}
.user-row:last-child { border-bottom: none; }
.user-row:hover { background: rgba(248,250,252,0.8); }
.user-row.active { background: var(--bg); border-left: 3px solid var(--primary); }
.user-row-avatar {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 0.78rem; color: #fff; background: var(--primary); overflow: hidden;
}
.user-row-avatar img { width: 100%; height: 100%; object-fit: cover; }
.user-row-info { flex: 1; min-width: 0; }
.user-row-name { font-weight: 500; font-size: 0.88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-row-email { font-size: 0.72rem; color: var(--text-muted); }
.user-row-role { flex-shrink: 0; }

/* Two col layout */
.perm-layout { display: grid; grid-template-columns: 280px 1fr; gap: 1.25rem; align-items: start; }

/* Permission panel */
.perm-panel { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; position: sticky; top: 1rem; }
.perm-panel-header {
    padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
}
.perm-panel-header h3 { font-size: 0.92rem; font-weight: 600; }
.perm-panel-body { padding: 1.25rem; max-height: calc(100vh - 200px); overflow-y: auto; }

/* Role selector */
.role-selector { margin-bottom: 1.25rem; }
.role-selector label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.35rem; display: block; }
.role-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.4rem; }
.role-card {
    padding: 0.5rem; border-radius: var(--radius); border: 2px solid var(--border);
    text-align: center; cursor: pointer; transition: all 0.15s; position: relative;
}
.role-card:hover { border-color: var(--primary); }
.role-card.selected { border-color: var(--primary); background: rgba(59,130,196,0.06); }
.role-card input { position: absolute; opacity: 0; pointer-events: none; }
.role-card-name { font-size: 0.75rem; font-weight: 600; }
.role-card-count { font-size: 0.65rem; color: var(--text-muted); }

/* Permission module */
.perm-module { margin-bottom: 1rem; }
.perm-module:last-child { margin-bottom: 0; }
.perm-module-header {
    font-size: 0.72rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.5px; padding-bottom: 0.35rem; border-bottom: 1px solid var(--border);
    margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;
}
.perm-module-toggle { font-size: 0.68rem; color: var(--primary); cursor: pointer; text-transform: none; letter-spacing: 0; font-weight: 500; }

/* Permission row */
.perm-row {
    display: flex; align-items: center; justify-content: space-between; padding: 0.35rem 0;
}
.perm-row-info { flex: 1; min-width: 0; }
.perm-row-slug { font-size: 0.78rem; font-weight: 500; font-family: monospace; }
.perm-row-name { font-size: 0.7rem; color: var(--text-muted); }

/* Toggle switch */
.toggle-switch { position: relative; width: 38px; height: 22px; flex-shrink: 0; }
.toggle-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute; inset: 0; background: #cbd5e1; border-radius: 22px; cursor: pointer;
    transition: all 0.2s;
}
.toggle-slider::before {
    content: ''; position: absolute; left: 2px; top: 2px; width: 18px; height: 18px;
    background: #fff; border-radius: 50%; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.toggle-switch input:checked + .toggle-slider { background: var(--primary); }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(16px); }

/* No selection */
.perm-empty { text-align: center; padding: 3rem 1.5rem; color: var(--text-muted); font-size: 0.88rem; }
.perm-self-msg { text-align: center; padding: 2rem 1.5rem; color: var(--text-muted); font-size: 0.85rem; font-style: italic; }

/* Module labels */
.module-label { text-transform: capitalize; }
.module-label-leads { color: #f59e0b; }
.module-label-pipeline { color: #8b5cf6; }
.module-label-dashboard { color: #3b82f6; }
.module-label-users { color: #10b981; }
.module-label-system { color: #ef4444; }
.module-label-finance { color: #06b6d4; }
.module-label-cms { color: #ec4899; }
.module-label-marketing { color: #f97316; }

@media (max-width: 1024px) {
    .perm-layout { grid-template-columns: 1fr; }
    .perm-panel { position: static; }
}
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Permisos y Roles</h2>
        <p class="text-muted">Gestiona los permisos de cada usuario</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">&#8592; Usuarios</a>
</div>

<div class="perm-layout">
    {{-- Left: User list --}}
    <div class="user-list">
        @foreach($users as $user)
        <div class="user-row {{ ($loop->first && !request('user_id')) || request('user_id') == $user->id ? 'active' : '' }}"
             onclick="selectUser({{ $user->id }})" id="row-{{ $user->id }}">
            <div class="user-row-avatar">
                @if($user->avatar_path)
                    <img src="{{ Storage::url($user->avatar_path) }}" alt="">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div class="user-row-info">
                <div class="user-row-name">{{ $user->full_name }}</div>
                <div class="user-row-email">{{ $user->email }}</div>
            </div>
            <div class="user-row-role">
                @php
                    $rbacRole = $user->roles->first();
                    $roleColors = ['super_admin'=>'#ef4444','broker_senior'=>'#f59e0b','broker_direccion'=>'#3b82f6','asesor'=>'#10b981','user'=>'#64748b','client'=>'#8b5cf6'];
                @endphp
                @if($rbacRole)
                    <span class="badge" style="background:{{ $roleColors[$rbacRole->slug] ?? 'var(--primary)' }}; color:#fff; font-size:0.65rem;">{{ $rbacRole->name }}</span>
                @else
                    <span class="badge" style="font-size:0.65rem;">Sin rol</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Right: Permission panel --}}
    <div class="perm-panel">
        @foreach($users as $user)
        <div class="user-panel" id="panel-{{ $user->id }}" style="{{ (($loop->first && !request('user_id')) || request('user_id') == $user->id) ? '' : 'display:none;' }}">
            <div class="perm-panel-header">
                <h3>{{ $user->full_name }}</h3>
                @if($user->id === auth()->id())
                    <span class="badge badge-green" style="font-size:0.68rem;">Tu cuenta</span>
                @endif
            </div>
            <div class="perm-panel-body">
                @if($user->id === auth()->id())
                    <div class="perm-self-msg">No puedes modificar tus propios permisos</div>
                @else
                <form method="POST" action="{{ route('admin.users.updatePermissions', $user) }}" id="form-{{ $user->id }}">
                    @csrf

                    {{-- Role selector --}}
                    <div class="role-selector">
                        <label>Rol RBAC</label>
                        <div class="role-cards">
                            @php
                                $userRole = $user->roles->first();
                                $userRoleId = $userRole ? $userRole->id : null;
                                // Check if user has a custom role
                                $isCustom = $userRole && !$userRole->is_system;
                            @endphp
                            @foreach($roles as $role)
                            @if($role->is_system && $role->slug !== 'client')
                            <label class="role-card {{ $userRoleId == $role->id || ($isCustom && !$loop->first) ? '' : '' }}{{ (!$isCustom && $userRoleId == $role->id) ? ' selected' : '' }}"
                                   onclick="applyRolePreset({{ $user->id }}, {{ $role->id }}, {{ json_encode($role->permissions->pluck('id')) }})">
                                <input type="radio" name="rbac_role_id" value="{{ $role->id }}" {{ (!$isCustom && $userRoleId == $role->id) ? 'checked' : '' }}>
                                <div class="role-card-name">{{ $role->name }}</div>
                                <div class="role-card-count">{{ $role->permissions->count() }} permisos</div>
                            </label>
                            @endif
                            @endforeach
                            @if($isCustom)
                            <label class="role-card selected">
                                <input type="radio" name="rbac_role_id" value="{{ $userRoleId }}" checked>
                                <div class="role-card-name">Custom</div>
                                <div class="role-card-count">Personalizado</div>
                            </label>
                            @endif
                        </div>
                    </div>

                    {{-- Permission toggles by module --}}
                    @php
                        $userPermSlugs = $user->roles->pluck('permissions')->flatten()->pluck('id')->toArray();
                    @endphp
                    @foreach($permissionsByModule as $module => $perms)
                    <div class="perm-module">
                        <div class="perm-module-header">
                            <span class="module-label module-label-{{ $module }}">{{ $module }}</span>
                            <span class="perm-module-toggle" onclick="toggleModule({{ $user->id }}, '{{ $module }}')">Todos</span>
                        </div>
                        @foreach($perms as $perm)
                        <div class="perm-row">
                            <div class="perm-row-info">
                                <div class="perm-row-slug">{{ $perm->slug }}</div>
                                <div class="perm-row-name">{{ $perm->name }}</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                       data-user="{{ $user->id }}" data-module="{{ $module }}"
                                       {{ in_array($perm->id, $userPermSlugs) ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @endforeach

                    {{-- Save --}}
                    <div style="margin-top:1.25rem; padding-top:1rem; border-top:1px solid var(--border); display:flex; justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary">Guardar permisos</button>
                    </div>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

@if($users->hasPages())
<div style="margin-top:1rem; text-align:center;">{{ $users->links() }}</div>
@endif
@endsection

@section('scripts')
<script>
function selectUser(id) {
    document.querySelectorAll('.user-row').forEach(r => r.classList.remove('active'));
    document.querySelectorAll('.user-panel').forEach(p => p.style.display = 'none');
    document.getElementById('row-' + id).classList.add('active');
    document.getElementById('panel-' + id).style.display = '';
}

function applyRolePreset(userId, roleId, permissionIds) {
    var form = document.getElementById('form-' + userId);
    if (!form) return;
    // Select the radio
    var radios = form.querySelectorAll('input[name="rbac_role_id"]');
    radios.forEach(function(r) {
        r.checked = (parseInt(r.value) === roleId);
        r.closest('.role-card').classList.toggle('selected', r.checked);
    });
    // Toggle checkboxes
    var checkboxes = form.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(function(cb) {
        cb.checked = permissionIds.includes(parseInt(cb.value));
    });
}

function toggleModule(userId, module) {
    var form = document.getElementById('form-' + userId);
    if (!form) return;
    var cbs = form.querySelectorAll('input[data-module="' + module + '"]');
    var allChecked = Array.from(cbs).every(function(cb) { return cb.checked; });
    cbs.forEach(function(cb) { cb.checked = !allChecked; });
}
</script>
@endsection
