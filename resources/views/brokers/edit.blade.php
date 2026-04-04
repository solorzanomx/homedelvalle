@extends('layouts.app-sidebar')
@section('title', 'Editar: ' . $broker->name)

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
    $avatarColors = ['#3B82C4','#1E3A5F','#f093fb','#4facfe','#43e97b','#fa709a'];
@endphp

<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('brokers.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Brokers Externos</a>
    <span style="color:var(--text-muted); font-size:0.72rem;">/</span>
    <span style="font-size:0.82rem; color:var(--text);">{{ $broker->name }}</span>
</div>

@if($errors->any())
    <div class="alert alert-error" style="margin-bottom:1rem;">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<form method="POST" action="{{ route('brokers.update', $broker) }}" enctype="multipart/form-data" id="editForm">
@csrf @method('PUT')

<div class="profile-header">
    {{-- Cover + Avatar --}}
    <div class="profile-cover"></div>
    <div class="profile-head">
        <div class="profile-avatar" style="background:{{ $avatarColors[$broker->id % count($avatarColors)] }};" onclick="document.getElementById('photoInput').click()" title="Cambiar foto">
            @if($broker->photo)
                <img src="{{ asset('storage/' . $broker->photo) }}" alt="" id="avatarPreview">
            @else
                <span id="avatarPlaceholder">{{ strtoupper(substr($broker->name, 0, 1)) }}</span>
            @endif
            <div class="profile-avatar-overlay">&#128247;</div>
        </div>
        <input type="file" id="photoInput" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif" style="display:none;" onchange="previewPhoto(this)">
        <div class="profile-meta">
            <div class="profile-name">{{ $broker->name }}</div>
            <div class="profile-subtitle">{{ $broker->email }}</div>
            <div class="profile-badges">
                @if($broker->company)
                    <span class="badge" style="background:rgba(59,130,196,0.1); color:var(--primary); font-size:0.72rem;">{{ $broker->company->name }}</span>
                @endif
                @if($broker->status === 'active')
                    <span class="badge badge-green" style="font-size:0.68rem;">Activo</span>
                @else
                    <span class="badge badge-red" style="font-size:0.68rem;">Inactivo</span>
                @endif
            </div>
        </div>
        <div class="profile-actions">
            <a href="{{ route('brokers.show', $broker) }}" class="btn btn-sm btn-outline">Ver perfil</a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="p-tabs">
        <button type="button" class="p-tab active" onclick="showTab('general', this)">General</button>
        <button type="button" class="p-tab" onclick="showTab('professional', this)">Profesional</button>
    </div>

    {{-- Tab: General --}}
    <div class="p-panel active" id="panel-general">
        <div class="p-section-title">Informacion personal</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nombre <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $broker->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email <span class="required">*</span></label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $broker->email) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Telefono</label>
                <input type="tel" name="phone" class="form-input" value="{{ old('phone', $broker->phone) }}" placeholder="+52 55 1234 5678">
            </div>
            <div class="form-group">
                <label class="form-label">Empresa</label>
                <select name="broker_company_id" class="form-select">
                    <option value="">Sin empresa</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('broker_company_id', $broker->broker_company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
                <p class="form-hint"><a href="{{ route('broker-companies.create') }}" target="_blank" style="color:var(--primary);">+ Crear nueva empresa</a></p>
            </div>
        </div>

        <div class="p-section-title">Estado</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="active" {{ old('status', $broker->status) === 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ old('status', $broker->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tab: Professional --}}
    <div class="p-panel" id="panel-professional">
        <div class="p-section-title">Datos profesionales</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Numero de Licencia</label>
                <input type="text" name="license_number" class="form-input" value="{{ old('license_number', $broker->license_number) }}" placeholder="Ej: AMPI-12345">
            </div>
            <div class="form-group">
                <label class="form-label">Comision (%)</label>
                <input type="number" name="commission_rate" class="form-input" value="{{ old('commission_rate', $broker->commission_rate) }}" step="0.01" min="0" max="100">
            </div>
            <div class="form-group">
                <label class="form-label">Especialidad</label>
                <input type="text" name="specialty" class="form-input" value="{{ old('specialty', $broker->specialty) }}" placeholder="Residencial, comercial, terrenos...">
            </div>
            <div class="form-group">
                <label class="form-label">Como lo conocimos</label>
                <input type="text" name="referral_source" class="form-input" value="{{ old('referral_source', $broker->referral_source) }}" placeholder="Referido, evento, portal...">
            </div>
        </div>

        <div class="p-section-title">Bio</div>
        <div class="form-group full-width">
            <textarea name="bio" class="form-textarea" rows="3" maxlength="500" placeholder="Descripcion profesional del broker...">{{ old('bio', $broker->bio) }}</textarea>
            <p class="form-hint">Maximo 500 caracteres</p>
        </div>
    </div>

    {{-- Save Bar --}}
    <div class="p-save">
        <div class="p-save-meta">Creado {{ $broker->created_at->format('d/m/Y') }} &middot; Actualizado {{ $broker->updated_at->diffForHumans() }}</div>
        <div class="p-save-actions">
            <a href="{{ route('brokers.show', $broker) }}" class="btn btn-outline btn-sm">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-sm">Guardar cambios</button>
        </div>
    </div>
</div>
</form>

{{-- Danger Zone --}}
<div style="max-width: 480px; margin-top: 1.5rem;">
    <div style="background:var(--card); border:1px solid #fecaca; border-radius:12px; padding:1.25rem;">
        <div style="font-size:0.82rem; font-weight:600; color:#991b1b; margin-bottom:0.25rem;">Zona de peligro</div>
        <p style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.75rem;">Eliminar este broker de forma permanente.</p>
        <form method="POST" action="{{ route('brokers.destroy', $broker) }}" onsubmit="return confirm('Seguro que deseas eliminar a {{ $broker->name }}? Esta accion no se puede deshacer.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Eliminar broker</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showTab(name, btn) {
    document.querySelectorAll('.p-panel').forEach(function(p) { p.classList.toggle('active', p.id === 'panel-' + name); });
    document.querySelectorAll('.p-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');
}

function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var avatar = document.querySelector('.profile-avatar');
        var existing = avatar.querySelector('img');
        if (existing) {
            existing.src = e.target.result;
        } else {
            var placeholder = document.getElementById('avatarPlaceholder');
            if (placeholder) placeholder.style.display = 'none';
            var img = document.createElement('img');
            img.src = e.target.result;
            avatar.insertBefore(img, avatar.firstChild);
        }
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endsection
