@php
    $roleBgColors = ['admin'=>'#ef4444','editor'=>'#3b82f6','viewer'=>'#10b981','user'=>'#f59e0b','broker'=>'#f97316','client'=>'#8b5cf6'];
    $roleLabels = ['admin'=>'Admin','editor'=>'Director','viewer'=>'Asesor','user'=>'Usuario','broker'=>'Broker','client'=>'Cliente'];
    $avatarColors = ['#667eea','#764ba2','#f093fb','#4facfe','#43e97b','#fa709a','#fee140','#a18cd1'];
@endphp

@if($users->count())
<div class="u-grid">
    @foreach($users as $user)
    <div class="u-card" onclick="window.location='{{ route('admin.users.show', $user) }}'">
        <div class="u-card-top">
            <div class="u-avatar" style="background:{{ $avatarColors[$user->id % count($avatarColors)] }};">
                @if($user->avatar_path)
                    <img src="{{ Storage::url($user->avatar_path) }}" alt="">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                @endif
                <span class="u-status-dot" style="background:{{ ($user->is_active ?? true) ? '#10b981' : '#94a3b8' }};"></span>
            </div>
            <div style="flex:1; min-width:0;">
                <div class="u-name"><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }} {{ $user->last_name }}</a></div>
                <div class="u-email">{{ $user->email }}</div>
                @if($user->title)
                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:0.1rem;">{{ $user->title }}</div>
                @endif
            </div>
        </div>
        <div class="u-card-badges">
            <span class="u-role-badge" style="background:{{ $roleBgColors[$user->role] ?? '#667eea' }}10; color:{{ $roleBgColors[$user->role] ?? '#667eea' }};">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span>
            @php $rbacRole = $user->roles->first(); @endphp
            @if($rbacRole && $rbacRole->slug !== 'super_admin')
                <span style="font-size:0.65rem; color:var(--text-muted);">{{ $rbacRole->name }}</span>
            @endif
        </div>
        <div class="u-card-footer" onclick="event.stopPropagation();">
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline">Ver</a>
            @permission('users.edit')
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">Editar</a>
            @endpermission
        </div>
    </div>
    @endforeach
</div>

@if($users->hasPages())
<div style="margin-top:1.25rem; text-align:center;">{{ $users->withQueryString()->links() }}</div>
@endif

@else
<div class="u-empty">
    <div class="u-empty-icon">&#9823;</div>
    <p style="font-size:0.9rem; font-weight:500; margin-bottom:0.25rem;">No se encontraron usuarios</p>
    <p style="font-size:0.78rem;">{{ request('search') ? 'Intenta con otro termino de busqueda' : 'No hay usuarios con ese filtro' }}</p>
</div>
@endif
