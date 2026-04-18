@extends('layouts.app-sidebar')
@section('title', 'Editar: ' . $user->name)

@section('styles')
<style>
/* ===== Profile Header ===== */
.profile-header {
    background: var(--card); border: 1px solid var(--border); border-radius: 16px;
    padding: 0; margin-bottom: 1.25rem; overflow: hidden;
}
.profile-cover {
    height: 48px; position: relative;
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

/* ===== Form Panels ===== */
.p-panel { display: none; padding: 1.5rem 2rem; animation: panelIn 0.2s ease; }
.p-panel.active { display: block; }
@keyframes panelIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; } }

.p-section-title {
    font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;
    color: var(--text-muted); margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.p-section-title:first-child { margin-top: 0; }

/* ===== Save Bar ===== */
.p-save {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 2rem; border-top: 1px solid var(--border); background: var(--bg);
    border-radius: 0 0 16px 16px;
}
.p-save-meta { font-size: 0.72rem; color: var(--text-muted); }
.p-save-actions { display: flex; gap: 0.5rem; }

/* ===== Toggle Switch ===== */
.toggle-wrap { display: flex; align-items: center; gap: 0.6rem; }
.toggle-switch {
    position: relative; width: 40px; height: 22px; flex-shrink: 0;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute; inset: 0; background: var(--border); border-radius: 11px;
    cursor: pointer; transition: background 0.2s;
}
.toggle-slider::before {
    content: ''; position: absolute; width: 16px; height: 16px; border-radius: 50%;
    background: #fff; left: 3px; top: 3px; transition: transform 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.toggle-switch input:checked + .toggle-slider { background: var(--primary); }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(18px); }

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .profile-head { flex-direction: column; align-items: center; text-align: center; padding: 0 1rem 1.25rem; }
    .profile-actions { justify-content: center; }
    .p-tabs { margin: 0 1rem; overflow-x: auto; }
    .p-panel { padding: 1.25rem 1rem; }
    .p-save { padding: 0.75rem 1rem; flex-direction: column; gap: 0.5rem; }
}
</style>
@endsection

@section('content')
@php
    $roleBgColors = ['admin'=>'#ef4444','editor'=>'#3b82f6','viewer'=>'#10b981','user'=>'#f59e0b','broker'=>'#f97316'];
    $roleLabels = ['admin'=>'Admin','editor'=>'Director','viewer'=>'Asesor','user'=>'Usuario','broker'=>'Broker'];
    $avatarColors = ['#667eea','#764ba2','#f093fb','#4facfe','#43e97b','#fa709a'];
@endphp

<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('admin.users.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Usuarios</a>
    <span style="color:var(--text-muted); font-size:0.72rem;">/</span>
    <span style="font-size:0.82rem; color:var(--text);">{{ $user->name }}</span>
</div>

@if($errors->any())
    <div class="alert alert-error" style="margin-bottom:1rem;">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<form method="POST" action="{{ route('admin.users.update', $user) }}" id="editForm">
@csrf @method('PUT')

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
                @if(($user->is_active ?? true))
                    <span class="badge badge-green" style="font-size:0.68rem;">Activo</span>
                @else
                    <span class="badge badge-red" style="font-size:0.68rem;">Inactivo</span>
                @endif
            </div>
        </div>
        <div class="profile-actions">
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline">Ver perfil</a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="p-tabs">
        <button type="button" class="p-tab active" onclick="showTab('general', this)">General</button>
        <button type="button" class="p-tab" onclick="showTab('config', this)">Configuracion</button>
    </div>

    {{-- Tab: General --}}
    <div class="p-panel active" id="panel-general">
        <div class="p-section-title">Informacion personal</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nombre <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Apellido</label>
                <input type="text" name="last_name" class="form-input" value="{{ old('last_name', $user->last_name) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Email <span class="required">*</span></label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Telefono / WhatsApp</label>
                <input type="tel" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}" placeholder="+52 33 1234 5678">
            </div>
        </div>

        <div class="p-section-title">Perfil profesional</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Cargo en la empresa</label>
                <input type="text" name="title" class="form-input" value="{{ old('title', $user->title) }}" placeholder="Ej: Director Comercial, Asesor Senior">
            </div>
            <div class="form-group full-width">
                <label class="form-label">Acerca de</label>
                <textarea name="bio" class="form-textarea" rows="2" maxlength="200" placeholder="Breve descripcion profesional...">{{ old('bio', $user->bio) }}</textarea>
                <p class="form-hint">Maximo 200 caracteres</p>
            </div>
        </div>
    </div>

    {{-- Tab: Config --}}
    <div class="p-panel" id="panel-config">
        @php $mailSetting = $user->mailSetting; @endphp

        <div class="p-section-title">Correo corporativo</div>
        <p style="font-size:0.78rem; color:var(--text-muted); margin-bottom:1rem;">El correo del perfil se utiliza como remitente. Las credenciales SMTP se heredan de la configuracion global del sistema.</p>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Correo de envio</label>
                <input type="email" name="mail_from_email" class="form-input"
                       value="{{ old('mail_from_email', $mailSetting->from_email ?? $user->email) }}"
                       placeholder="{{ $user->email }}">
                <p class="form-hint">Correo desde el cual se envian mensajes a clientes. Por defecto usa el email del perfil.</p>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre del remitente</label>
                <input type="text" name="mail_from_name" class="form-input"
                       value="{{ old('mail_from_name', $mailSetting->from_name ?? '') }}"
                       placeholder="{{ $user->name }} {{ $user->last_name }}">
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end; padding-bottom:0.3rem;">
                <div class="toggle-wrap">
                    <label class="toggle-switch">
                        <input type="hidden" name="mail_is_active" value="0">
                        <input type="checkbox" name="mail_is_active" value="1" {{ old('mail_is_active', $mailSetting->is_active ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="form-label" style="margin:0;">Correo activo</span>
                </div>
            </div>
        </div>

        <div class="p-section-title">Preferencias</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Zona horaria</label>
                <select name="timezone" class="form-select">
                    @foreach([
                        'America/Mexico_City' => 'Ciudad de Mexico (GMT-6)',
                        'America/Cancun' => 'Cancun (GMT-5)',
                        'America/Monterrey' => 'Monterrey (GMT-6)',
                        'America/Tijuana' => 'Tijuana (GMT-8)',
                        'America/Los_Angeles' => 'Los Angeles (GMT-8)',
                        'America/New_York' => 'New York (GMT-5)',
                    ] as $tz => $label)
                        <option value="{{ $tz }}" {{ old('timezone', $user->timezone ?? 'America/Mexico_City') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end; padding-bottom:0.3rem;">
                <div class="toggle-wrap">
                    <label class="toggle-switch">
                        <input type="hidden" name="show_phone_on_properties" value="0">
                        <input type="checkbox" name="show_phone_on_properties" value="1" {{ old('show_phone_on_properties', $user->show_phone_on_properties ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="form-label" style="margin:0;">Mostrar telefono en propiedades</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Save Bar --}}
    <div class="p-save">
        <div class="p-save-meta">Actualizado {{ $user->updated_at->diffForHumans() }}</div>
        <div class="p-save-actions">
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline btn-sm">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-sm">Guardar cambios</button>
        </div>
    </div>
</div>
</form>

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
