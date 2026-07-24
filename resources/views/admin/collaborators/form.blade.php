@extends('layouts.app-sidebar')
@section('title', $collaborator ? 'Editar colaborador' : 'Nuevo colaborador')

@section('styles')
<style>
.consent-status-box { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.9rem 1.1rem; border-radius: var(--radius); border: 1px solid var(--border); background: var(--bg); margin-bottom: 1.25rem; flex-wrap: wrap; }
.consent-status-box .label { font-size: 0.8rem; color: var(--text-muted); }
.consent-link-input { font-size: 0.78rem; color: var(--text); background: var(--card); border: 1px solid var(--border); border-radius: 6px; padding: 0.4rem 0.6rem; flex: 1; min-width: 220px; }

/* Avatar upload — mismo patrón que admin/users/create */
.avatar-upload {
    display: flex; align-items: center; gap: 1.25rem; margin-bottom: 0.5rem;
}
.avatar-circle {
    width: 80px; height: 80px; border-radius: 50%; background: var(--border);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    overflow: hidden; cursor: pointer; position: relative;
    font-size: 2rem; color: var(--text-muted); transition: border-color 0.15s;
    border: 2px dashed var(--border);
}
.avatar-circle:hover { border-color: var(--primary); }
.avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
.avatar-circle-overlay {
    position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: none;
    align-items: center; justify-content: center; color: #fff; font-size: 1.1rem;
    border-radius: 50%;
}
.avatar-circle img ~ .avatar-circle-overlay { display: flex; opacity: 0; transition: opacity 0.2s; }
.avatar-circle:hover .avatar-circle-overlay { opacity: 1; }
.avatar-upload-info { font-size: 0.75rem; color: var(--text-muted); }
.avatar-upload-info span { display: block; font-size: 0.82rem; font-weight: 600; color: var(--primary); cursor: pointer; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h2>{{ $collaborator ? 'Editar colaborador' : 'Nuevo colaborador' }}</h2>
    <a href="{{ route('admin.collaborators.index') }}" class="btn btn-outline">Volver</a>
</div>

@if($collaborator)
<div class="consent-status-box">
    <div>
        <div class="label" style="margin-bottom:0.2rem;">Estado de autorización</div>
        @if($collaborator->consent_status === 'pending')
            <strong style="color:#d97706;">Pendiente de respuesta</strong>
        @elseif($collaborator->consent_status === 'authorized')
            <strong style="color:#15803d;">Autorizado el {{ $collaborator->consent_at?->format('d/m/Y H:i') }}</strong>
        @else
            <strong style="color:#b91c1c;">No autorizó{{ $collaborator->consent_at ? ' — ' . $collaborator->consent_at->format('d/m/Y H:i') : '' }}</strong>
        @endif
    </div>
    @if($collaborator->consent_status === 'pending')
        <input type="text" class="consent-link-input" readonly value="{{ route('collaborator.consent.show', $collaborator->consent_token) }}" onclick="this.select()">
    @endif
</div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST"
              action="{{ $collaborator ? route('admin.collaborators.update', $collaborator) : route('admin.collaborators.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if($collaborator) @method('PUT') @endif

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $collaborator->name ?? '') }}" required>
                    @error('name')<div class="form-hint" style="color:var(--danger);">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Rol / especialidad <span class="required">*</span></label>
                    <input type="text" name="role" class="form-input" value="{{ old('role', $collaborator->role ?? '') }}" placeholder="Ej: Valuador certificado, Broker hipotecario" required>
                    @error('role')<div class="form-hint" style="color:var(--danger);">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Correo (para enviarle el link y su confirmación)</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $collaborator->email ?? '') }}" placeholder="correo@ejemplo.com">
                    <div class="form-hint">Si lo pones, le mandamos automáticamente una copia de lo que autorizó.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Orden</label>
                    <input type="number" name="sort_order" class="form-input" value="{{ old('sort_order', $collaborator->sort_order ?? 0) }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Link a su sitio / despacho / empresa</label>
                    <input type="url" name="link_url" class="form-input" value="{{ old('link_url', $collaborator->link_url ?? '') }}" placeholder="https://...">
                    <div class="form-hint">Opcional — no todos tienen sitio propio.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Texto del link</label>
                    <input type="text" name="link_label" class="form-input" value="{{ old('link_label', $collaborator->link_label ?? '') }}" placeholder="Ej: Ver despacho">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Descripción breve</label>
                <textarea name="bio" class="form-textarea" rows="3" maxlength="600" placeholder="Una línea de qué hace y cómo apoya a los clientes de Home del Valle.">{{ old('bio', $collaborator->bio ?? '') }}</textarea>
            </div>

            <div class="avatar-upload">
                <div class="avatar-circle" onclick="document.getElementById('photoInput').click()">
                    @if($collaborator && $collaborator->photo_path)
                        <img src="{{ Storage::url($collaborator->photo_path) }}" alt="">
                    @else
                        <span id="photoPlaceholder">&#128100;</span>
                    @endif
                    <div class="avatar-circle-overlay">&#128247;</div>
                </div>
                <input type="file" id="photoInput" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp" style="display:none;" onchange="previewAvatar(this)">
                <div class="avatar-upload-info">
                    <span onclick="document.getElementById('photoInput').click()">Subir foto de perfil</span>
                    JPG, PNG o WebP. Max 2MB.
                </div>
            </div>
            @error('photo')<div class="form-hint" style="color:var(--danger); margin-bottom:1rem;">{{ $message }}</div>@enderror

            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $collaborator->is_active ?? true) ? 'checked' : '' }}>
                    Activo (se muestra en /nosotros una vez autorizado)
                </label>
            </div>

            @if($collaborator)
            <div class="form-hint" style="margin-bottom:1rem;">
                Si cambias nombre, rol, descripción, foto o link después de que ya haya autorizado, su autorización se reinicia automáticamente — porque solo autorizó lo que vio, no ediciones futuras. Vas a tener que mandarle el link de nuevo.
            </div>
            @endif

            <div class="form-actions">
                <a href="{{ route('admin.collaborators.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $collaborator ? 'Guardar cambios' : 'Crear colaborador' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var circle = document.querySelector('.avatar-circle');
        var existing = circle.querySelector('img');
        if (existing) {
            existing.src = e.target.result;
        } else {
            var placeholder = document.getElementById('photoPlaceholder');
            if (placeholder) placeholder.style.display = 'none';
            var img = document.createElement('img');
            img.src = e.target.result;
            circle.insertBefore(img, circle.firstChild);
            circle.querySelector('.avatar-circle-overlay').style.display = 'flex';
        }
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endsection
