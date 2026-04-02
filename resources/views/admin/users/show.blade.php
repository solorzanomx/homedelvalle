@extends('layouts.app-sidebar')
@section('title', $user->name . ' - Perfil')

@section('styles')
<style>
/* ===== Profile Header ===== */
.profile-header {
    background: var(--card); border: 1px solid var(--border); border-radius: 16px;
    padding: 0; margin-bottom: 1.25rem; overflow: hidden;
}
.profile-cover {
    height: 120px; background: linear-gradient(135deg, var(--primary), #764ba2);
    position: relative;
}
.profile-head {
    display: flex; align-items: flex-end; gap: 1.25rem; padding: 0 2rem 1.5rem;
    margin-top: -48px; position: relative; z-index: 1;
}
.profile-avatar {
    width: 96px; height: 96px; border-radius: 50%; background: var(--card);
    border: 4px solid var(--card); display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 2rem; color: #fff; overflow: hidden;
    cursor: pointer; position: relative; flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.profile-avatar-overlay {
    position: absolute; inset: 0; background: rgba(0,0,0,0.45); display: flex;
    align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;
    font-size: 1.3rem; color: #fff; border-radius: 50%;
}
.profile-avatar:hover .profile-avatar-overlay { opacity: 1; }
.profile-meta { flex: 1; padding-bottom: 0.2rem; }
.profile-name { font-size: 1.25rem; font-weight: 700; line-height: 1.3; }
.profile-subtitle { font-size: 0.82rem; color: var(--text-muted); }
.profile-badges { display: flex; gap: 0.35rem; margin-top: 0.35rem; flex-wrap: wrap; }
.profile-actions { display: flex; gap: 0.5rem; align-items: flex-end; padding-bottom: 0.3rem; }

/* ===== Tabs ===== */
.p-tabs {
    display: flex; gap: 0; border-bottom: 1px solid var(--border); margin: 0 2rem;
}
.p-tab {
    padding: 0.7rem 1.25rem; font-size: 0.82rem; font-weight: 500;
    border: none; background: none; color: var(--text-muted); cursor: pointer;
    position: relative; transition: color 0.15s;
}
.p-tab:hover { color: var(--text); }
.p-tab.active { color: var(--primary); font-weight: 600; }
.p-tab.active::after {
    content: ''; position: absolute; bottom: -1px; left: 0; right: 0;
    height: 2px; background: var(--primary); border-radius: 2px 2px 0 0;
}

/* ===== Panels ===== */
.p-panel { display: none; padding: 1.5rem 2rem; animation: panelIn 0.2s ease; }
.p-panel.active { display: block; }
@keyframes panelIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; } }

.p-section-title {
    font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;
    color: var(--text-muted); margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.p-section-title:first-child { margin-top: 0; }

/* ===== Info Rows ===== */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 2rem; }
.info-item { padding: 0.65rem 0; border-bottom: 1px solid var(--border); }
.info-item:last-child, .info-item:nth-last-child(2):nth-child(odd) + .info-item { border-bottom: none; }
.info-label { font-size: 0.72rem; color: var(--text-muted); margin-bottom: 0.15rem; }
.info-value { font-size: 0.85rem; font-weight: 500; }
.info-value a { color: var(--primary); text-decoration: none; }
.info-value a:hover { text-decoration: underline; }
.info-full { grid-column: 1 / -1; }

/* ===== Quick Actions ===== */
.quick-actions {
    display: flex; gap: 0.5rem; padding: 1rem 2rem; border-top: 1px solid var(--border);
    background: var(--bg); border-radius: 0 0 16px 16px;
}
.quick-actions .btn { flex: unset; }

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .profile-head { flex-direction: column; align-items: center; text-align: center; padding: 0 1rem 1.25rem; }
    .profile-actions { justify-content: center; }
    .p-tabs { margin: 0 1rem; overflow-x: auto; }
    .p-panel { padding: 1.25rem 1rem; }
    .info-grid { grid-template-columns: 1fr; }
    .quick-actions { padding: 0.75rem 1rem; flex-wrap: wrap; }
}
</style>
@endsection

@section('content')
@php
    $roleBgColors = ['admin'=>'#ef4444','editor'=>'#3b82f6','viewer'=>'#10b981','user'=>'#f59e0b','broker'=>'#f97316','client'=>'#8b5cf6'];
    $roleLabels = ['admin'=>'Admin','editor'=>'Director','viewer'=>'Asesor','user'=>'Usuario','broker'=>'Broker','client'=>'Cliente'];
    $avatarColors = ['#667eea','#764ba2','#f093fb','#4facfe','#43e97b','#fa709a'];
    $mailSetting = $user->mailSetting ?? null;
    $rbacRole = $user->roles->first();
@endphp

<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('admin.users.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Usuarios</a>
    <span style="color:var(--text-muted); font-size:0.72rem;">/</span>
    <span style="font-size:0.82rem; color:var(--text);">{{ $user->name }}</span>
</div>

<div class="profile-header">
    {{-- Cover + Avatar --}}
    <div class="profile-cover"></div>
    <div class="profile-head">
        <div class="profile-avatar" style="background:{{ $avatarColors[$user->id % count($avatarColors)] }};" onclick="document.getElementById('avatarInput').click()" title="Cambiar foto">
            @if($user->avatar_path)
                <img src="{{ Storage::url($user->avatar_path) }}" alt="" id="avatarPreview" data-avatar-img>
            @else
                <span id="avatarPlaceholder" data-avatar-placeholder>{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}</span>
            @endif
            <div class="profile-avatar-overlay">&#128247;</div>
        </div>
        <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" onchange="openCropper(this.files[0])" style="display:none;">
        <div class="profile-meta">
            <div class="profile-name">{{ $user->name }} {{ $user->last_name }}</div>
            <div class="profile-subtitle">{{ $user->email }}</div>
            <div class="profile-badges">
                <span class="badge" style="background:{{ $roleBgColors[$user->role] ?? '#667eea' }}15; color:{{ $roleBgColors[$user->role] ?? '#667eea' }}; font-size:0.72rem;">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span>
                @if($rbacRole && $rbacRole->slug !== 'super_admin')
                    <span class="badge" style="background:rgba(102,126,234,0.1); color:var(--primary); font-size:0.68rem;">{{ $rbacRole->name }}</span>
                @endif
                @if(($user->is_active ?? true))
                    <span class="badge badge-green" style="font-size:0.68rem;">Activo</span>
                @else
                    <span class="badge badge-red" style="font-size:0.68rem;">Inactivo</span>
                @endif
            </div>
        </div>
        <div class="profile-actions">
            @if($user->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}" target="_blank" class="btn btn-sm" style="background:#25d366; color:#fff; border:none;">WhatsApp</a>
            @endif
            @permission('users.edit')
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">Editar</a>
            @endpermission
        </div>
    </div>

    {{-- Tabs --}}
    <div class="p-tabs">
        <button type="button" class="p-tab active" onclick="showTab('info', this)">Informacion</button>
        <button type="button" class="p-tab" onclick="showTab('config', this)">Configuracion</button>
    </div>

    {{-- Tab: Info --}}
    <div class="p-panel active" id="panel-info">
        <div class="p-section-title">Informacion personal</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre completo</div>
                <div class="info-value">{{ $user->name }} {{ $user->last_name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value"><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></div>
            </div>
            <div class="info-item">
                <div class="info-label">Telefono / WhatsApp</div>
                <div class="info-value">{{ $user->phone ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Miembro desde</div>
                <div class="info-value">{{ $user->created_at->format('d M Y') }}</div>
            </div>
        </div>

        <div class="p-section-title">Perfil profesional</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Cargo en la empresa</div>
                <div class="info-value">{{ $user->title ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Rol del sistema</div>
                <div class="info-value">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}{{ $rbacRole ? ' — ' . $rbacRole->name : '' }}</div>
            </div>
            @if($user->bio)
            <div class="info-item info-full">
                <div class="info-label">Acerca de</div>
                <div class="info-value" style="font-weight:400;">{{ $user->bio }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Tab: Config --}}
    <div class="p-panel" id="panel-config">
        <div class="p-section-title">Correo corporativo</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Correo de envio</div>
                <div class="info-value">{{ $mailSetting && $mailSetting->from_email ? $mailSetting->from_email : $user->email }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Estado</div>
                <div class="info-value">
                    @if($mailSetting && $mailSetting->is_active)
                        <span class="badge badge-green" style="font-size:0.72rem;">Activo</span>
                    @else
                        <span class="badge" style="background:rgba(148,163,184,0.15); color:#94a3b8; font-size:0.72rem;">Inactivo</span>
                    @endif
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Contrasena</div>
                <div class="info-value">{{ $mailSetting && $mailSetting->password ? '••••••••' : 'Sin configurar' }}</div>
            </div>
        </div>

        <div class="p-section-title">Preferencias</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Zona horaria</div>
                <div class="info-value">{{ $user->timezone ?? 'America/Mexico_City' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Mostrar telefono en propiedades</div>
                <div class="info-value">{{ ($user->show_phone_on_properties ?? true) ? 'Si' : 'No' }}</div>
            </div>
        </div>

        <div class="p-section-title">Detalles de cuenta</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Creado</div>
                <div class="info-value">{{ $user->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Ultima actualizacion</div>
                <div class="info-value">{{ $user->updated_at->diffForHumans() }}</div>
            </div>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="quick-actions">
        @permission('users.edit')
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">Editar perfil</a>
        @endpermission
        @if($user->phone)
            <a href="tel:{{ $user->phone }}" class="btn btn-outline btn-sm">Llamar</a>
        @endif
        <a href="mailto:{{ $user->email }}" class="btn btn-outline btn-sm">Enviar email</a>
    </div>
</div>

{{-- Danger Zone --}}
@permission('users.delete')
@if($user->id !== auth()->id())
<div style="max-width: 480px; margin-top: 1.5rem;">
    <div style="background:var(--card); border:1px solid #fecaca; border-radius:12px; padding:1.25rem;">
        <div style="font-size:0.82rem; font-weight:600; color:#991b1b; margin-bottom:0.25rem;">Zona de peligro</div>
        <p style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.75rem;">Eliminar este usuario de forma permanente.</p>
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Seguro que deseas eliminar a {{ $user->name }}? Esta accion no se puede deshacer.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Eliminar usuario</button>
        </form>
    </div>
</div>
@endif
@endpermission
@endsection

@section('scripts')
<x-avatar-cropper :upload-url="route('admin.users.avatar', $user)" />
<script>
function showTab(name, btn) {
    document.querySelectorAll('.p-panel').forEach(function(p) { p.classList.toggle('active', p.id === 'panel-' + name); });
    document.querySelectorAll('.p-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');
}
</script>
@endsection
