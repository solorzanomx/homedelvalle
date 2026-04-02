@extends('layouts.app-sidebar')
@section('title', $user->name . ' - Usuario')

@section('styles')
<style>
.user-hero {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 2rem; text-align: center; margin-bottom: 1.25rem; position: relative;
}
.user-hero-avatar {
    width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 0.75rem;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 1.5rem; color: #fff; background: var(--primary);
    overflow: hidden; cursor: pointer; position: relative;
}
.user-hero-avatar img { width: 100%; height: 100%; object-fit: cover; }
.user-hero-avatar .avatar-overlay {
    position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: flex;
    align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;
    font-size: 1.2rem; color: #fff;
}
.user-hero-avatar:hover .avatar-overlay { opacity: 1; }
.user-hero-name { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.15rem; }
.user-hero-title { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; }
.user-hero-badges { display: flex; justify-content: center; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 1rem; }
.user-hero-actions { display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap; }

/* Two column layout */
.user-layout { display: grid; grid-template-columns: 1fr 320px; gap: 1.25rem; align-items: start; }
.user-main { min-width: 0; }
.user-sidebar { position: sticky; top: 1rem; }

/* Info sections */
.info-section {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    margin-bottom: 1rem; overflow: hidden;
}
.info-section-header {
    padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border);
    font-weight: 600; font-size: 0.85rem;
}
.info-section-body { padding: 1rem 1.25rem; }
.info-row {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 0.5rem 0; border-bottom: 1px solid var(--border);
    font-size: 0.85rem;
}
.info-row:last-child { border-bottom: none; }
.info-row-label { color: var(--text-muted); font-size: 0.78rem; flex-shrink: 0; }
.info-row-value { text-align: right; font-weight: 500; }

/* Delete zone */
.danger-zone {
    background: var(--card); border: 1px solid #fecaca; border-radius: 10px; padding: 1rem 1.25rem;
}
.danger-zone h4 { font-size: 0.82rem; color: #991b1b; margin-bottom: 0.35rem; }
.danger-zone p { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem; }

@media (max-width: 1024px) {
    .user-layout { grid-template-columns: 1fr; }
    .user-sidebar { position: static; }
}
</style>
@endsection

@section('content')
@php
    $roleBadges = ['admin'=>'badge-red','editor'=>'badge-blue','viewer'=>'badge-green','user'=>'badge-yellow','broker'=>'badge-orange','client'=>'badge-purple'];
    $roleLabels = ['admin'=>'Admin','editor'=>'Editor','viewer'=>'Viewer','user'=>'Usuario','broker'=>'Broker','client'=>'Cliente'];
@endphp

<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.users.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Usuarios</a>
</div>

{{-- Hero Card --}}
<div class="user-hero">
    <div class="user-hero-avatar"
         @if(in_array(auth()->user()->role, ['admin', 'editor']))
         onclick="document.getElementById('avatarInput').click()" title="Cambiar foto"
         @endif>
        @if($user->avatar_path)
            <img src="{{ Storage::url($user->avatar_path) }}" alt="Avatar" id="avatarPreview" data-avatar-img>
        @else
            <span id="avatarPlaceholder" data-avatar-placeholder>{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}</span>
        @endif
        @if(in_array(auth()->user()->role, ['admin', 'editor']))
        <div class="avatar-overlay">&#128247;</div>
        @endif
    </div>
    <div class="user-hero-name">{{ $user->name }} {{ $user->last_name }}</div>
    @if($user->title)
        <div class="user-hero-title">{{ $user->title }}</div>
    @endif
    <div class="user-hero-badges">
        <span class="badge {{ $roleBadges[$user->role] ?? 'badge-blue' }}">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span>
        @if($user->is_active ?? true)
            <span class="badge badge-green">Activo</span>
        @else
            <span class="badge badge-red">Inactivo</span>
        @endif
        @if($user->can_read) <span class="badge badge-green" style="font-size:0.7rem;">Lectura</span> @endif
        @if($user->can_edit) <span class="badge badge-blue" style="font-size:0.7rem;">Escritura</span> @endif
        @if($user->can_delete) <span class="badge badge-red" style="font-size:0.7rem;">Eliminacion</span> @endif
    </div>
    <div class="user-hero-actions">
        @if($user->phone)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->whatsapp ?? $user->phone) }}" target="_blank" class="btn btn-sm" style="background:#25d366; color:#fff; border:none;">WhatsApp</a>
            <a href="tel:{{ $user->phone }}" class="btn btn-sm btn-outline">Llamar</a>
        @endif
        @if(in_array(auth()->user()->role, ['admin', 'editor']))
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">Editar</a>
        @endif
    </div>

    @if(in_array(auth()->user()->role, ['admin', 'editor']))
    <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" onchange="openCropper(this.files[0])" style="display:none;">
    @endif
</div>

<div class="user-layout">
    <div class="user-main">
        {{-- Contact Info --}}
        <div class="info-section">
            <div class="info-section-header">Informacion de Contacto</div>
            <div class="info-section-body">
                <div class="info-row">
                    <span class="info-row-label">Email</span>
                    <span class="info-row-value"><a href="mailto:{{ $user->email }}" style="color:var(--primary);">{{ $user->email }}</a></span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Telefono</span>
                    <span class="info-row-value">{{ $user->phone ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">WhatsApp</span>
                    <span class="info-row-value">{{ $user->whatsapp ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Direccion</span>
                    <span class="info-row-value">{{ $user->address ?: '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Professional Profile --}}
        <div class="info-section">
            <div class="info-section-header">Perfil Profesional</div>
            <div class="info-section-body">
                @if($user->bio)
                <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.75rem; padding-bottom:0.75rem; border-bottom:1px solid var(--border);">
                    {{ $user->bio }}
                </div>
                @endif
                <div class="info-row">
                    <span class="info-row-label">Titulo</span>
                    <span class="info-row-value">{{ $user->title ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Sucursal</span>
                    <span class="info-row-value">{{ $user->branch ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Idioma</span>
                    <span class="info-row-value">{{ ['es'=>'Espanol','en'=>'English','fr'=>'Francais','pt'=>'Portugues'][$user->language ?? 'es'] ?? $user->language }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Zona Horaria</span>
                    <span class="info-row-value">{{ $user->timezone ?? 'America/Mexico_City' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="user-sidebar">
        {{-- Quick Meta --}}
        <div class="info-section">
            <div class="info-section-header">Detalles</div>
            <div class="info-section-body">
                <div class="info-row">
                    <span class="info-row-label">Creado</span>
                    <span class="info-row-value">{{ $user->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Ultima Actualizacion</span>
                    <span class="info-row-value">{{ $user->updated_at->diffForHumans() }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Ficha Compartida</span>
                    <span class="info-row-value">{{ ['ficha_simple'=>'Simple','micrositio'=>'Micrositio','sitio_web'=>'Sitio Web'][$user->shared_card_type ?? 'ficha_simple'] ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Tel. en Propiedades</span>
                    <span class="info-row-value">{{ ($user->show_phone_on_properties ?? true) ? 'Si' : 'No' }}</span>
                </div>
            </div>
        </div>

        {{-- Mail Settings --}}
        @php $mailSetting = $user->mailSetting ?? null; @endphp
        <div class="info-section">
            <div class="info-section-header">Correo Empresa</div>
            <div class="info-section-body">
                @if($mailSetting && $mailSetting->from_email)
                    <div class="info-row">
                        <span class="info-row-label">Correo</span>
                        <span class="info-row-value" style="font-size:0.78rem;">{{ $mailSetting->from_email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-label">Estado</span>
                        <span class="info-row-value">
                            @if($mailSetting->is_active)
                                <span class="badge badge-green">Activo</span>
                            @else
                                <span class="badge badge-red">Inactivo</span>
                            @endif
                        </span>
                    </div>
                @else
                    <p class="text-muted" style="font-size:0.82rem; text-align:center; padding:0.5rem 0;">Sin configurar</p>
                @endif
            </div>
        </div>

        {{-- Danger Zone --}}
        @if(auth()->user()->role === 'admin' && $user->id !== auth()->id())
        <div class="danger-zone">
            <h4>Zona de Peligro</h4>
            <p>Eliminar este usuario de forma permanente.</p>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Seguro que desea eliminar este usuario? Esta accion no se puede deshacer.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">Eliminar Usuario</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<x-avatar-cropper :upload-url="route('admin.users.avatar', $user)" />
@endsection
