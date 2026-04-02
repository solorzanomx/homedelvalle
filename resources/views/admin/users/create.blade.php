@extends('layouts.app-sidebar')
@section('title', 'Nuevo Usuario')

@section('styles')
<style>
.user-form-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    max-width: 720px; overflow: hidden;
}
.user-form-header {
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
}
.user-form-header h3 { font-size: 1rem; font-weight: 600; }
.user-form-body { padding: 1.5rem; }

.section-label {
    font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.5px; margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.section-label:first-child { margin-top: 0; }

/* Role cards */
.role-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
.role-card {
    padding: 0.75rem 0.5rem; border-radius: var(--radius); border: 2px solid var(--border);
    text-align: center; cursor: pointer; transition: all 0.15s; position: relative;
}
.role-card:hover { border-color: var(--primary); }
.role-card.active { border-color: var(--primary); background: rgba(102,126,234,0.04); }
.role-card input { position: absolute; opacity: 0; pointer-events: none; }
.role-icon { font-size: 1.2rem; margin-bottom: 0.15rem; }
.role-card-label { font-size: 0.78rem; font-weight: 600; }
.role-card-desc { font-size: 0.65rem; color: var(--text-muted); margin-top: 0.1rem; }
.role-card-perms { font-size: 0.62rem; color: var(--primary); margin-top: 0.25rem; font-weight: 500; }

@media (max-width: 640px) { .role-cards { grid-template-columns: repeat(2, 1fr); } }
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.users.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Usuarios</a>
</div>

<div class="user-form-card">
    <div class="user-form-header">
        <h3>Nuevo Usuario</h3>
    </div>
    <div class="user-form-body">
        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:1rem;">
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required autofocus placeholder="Nombre">
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="last_name" class="form-input" value="{{ old('last_name') }}" placeholder="Apellido">
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" required placeholder="correo@ejemplo.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Contrasena <span class="required">*</span></label>
                    <input type="password" name="password" class="form-input" required minlength="6" placeholder="Minimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="+52 55 1234 5678">
                </div>
            </div>

            <div class="section-label">Rol</div>
            @php
                $roleIcons = [
                    'super_admin'      => '&#9733;',
                    'broker_senior'    => '&#128188;',
                    'broker_direccion' => '&#128065;',
                    'asesor'           => '&#128100;',
                    'user'             => '&#128101;',
                ];
            @endphp
            <div class="role-cards" style="margin-bottom:1rem;">
                @foreach($roles as $role)
                @if($role->is_system && $role->slug !== 'client')
                <label class="role-card {{ (int) old('rbac_role_id') === $role->id ? 'active' : '' }}" onclick="this.closest('.role-cards').querySelectorAll('.role-card').forEach(c=>c.classList.remove('active')); this.classList.add('active');">
                    <input type="radio" name="rbac_role_id" value="{{ $role->id }}" {{ (int) old('rbac_role_id') === $role->id ? 'checked' : '' }} required>
                    <div class="role-icon">{!! $roleIcons[$role->slug] ?? '&#128100;' !!}</div>
                    <div class="role-card-label">{{ $role->name }}</div>
                    <div class="role-card-desc">{{ $role->description }}</div>
                    <div class="role-card-perms">{{ $role->permissions_count ?? $role->permissions->count() }} permisos</div>
                </label>
                @endif
                @endforeach
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Usuario</button>
            </div>
        </form>
    </div>
</div>
@endsection
