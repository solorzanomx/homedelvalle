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

.consent-link-actions { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.btn-whatsapp { display: inline-flex; align-items: center; gap: 0.35rem; background: #25D366; color: #fff; border: none; border-radius: 6px; padding: 0.45rem 0.8rem; font-size: 0.78rem; font-weight: 600; cursor: pointer; text-decoration: none; }
.btn-whatsapp:hover { opacity: 0.9; }
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
        <div class="consent-link-actions">
            <input type="text" class="consent-link-input" id="consentLinkInput" readonly value="{{ route('collaborator.consent.show', $collaborator->consent_token) }}" onclick="this.select()">
            <button type="button" class="btn btn-outline btn-sm" onclick="copyConsentLinkInput()">Copiar</button>
            <button type="button" class="btn-whatsapp" onclick="sendConsentLinkByWhatsApp('{{ addslashes($collaborator->name) }}')">Enviar por WhatsApp</button>
        </div>
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
                <input type="file" id="photoInput" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp" style="display:none;" onchange="handlePhotoSelect(this)">
                <div class="avatar-upload-info">
                    <span onclick="document.getElementById('photoInput').click()">Subir foto de perfil</span>
                    JPG, PNG o WebP. Puedes ajustar zoom y posición al subirla.
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

{{-- Modal de recorte — mismo cropper.js que usa la edición de usuarios --}}
<div id="cropperModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; max-width:460px; width:92%; padding:1.2rem; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.8rem;">
            <h3 style="margin:0; font-size:1rem; font-weight:600;">Ajustar foto de perfil</h3>
            <button type="button" onclick="closeCropperModal()" style="background:none; border:none; font-size:1.4rem; cursor:pointer; color:#666;">&times;</button>
        </div>
        <div style="width:100%; max-height:360px; overflow:hidden; border-radius:8px; background:#f0f0f0;">
            <img id="cropperImage" src="" style="display:block; max-width:100%;">
        </div>
        <div style="display:flex; align-items:center; gap:0.6rem; margin-top:0.8rem;">
            <button type="button" onclick="cropperInstance?.zoom(-0.1)" style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:6px; padding:0.4rem 0.7rem; cursor:pointer; font-size:0.9rem;" title="Alejar">&#8722;</button>
            <button type="button" onclick="cropperInstance?.zoom(0.1)" style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:6px; padding:0.4rem 0.7rem; cursor:pointer; font-size:0.9rem;" title="Acercar">&#43;</button>
            <button type="button" onclick="cropperInstance?.rotate(-90)" style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:6px; padding:0.4rem 0.7rem; cursor:pointer; font-size:0.9rem;" title="Rotar">&#8635;</button>
            <div style="flex:1;"></div>
            <button type="button" onclick="closeCropperModal()" style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:8px; padding:0.5rem 1rem; cursor:pointer; font-size:0.85rem;">Cancelar</button>
            <button type="button" onclick="applyCropToForm()" style="background:var(--primary, #667eea); color:#fff; border:none; border-radius:8px; padding:0.5rem 1.2rem; cursor:pointer; font-size:0.85rem; font-weight:600;">Guardar</button>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
@endsection

@section('scripts')
<script>
let cropperInstance = null;

function handlePhotoSelect(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const allowed = ['image/jpeg','image/png','image/jpg','image/webp'];
    if (!allowed.includes(file.type)) { alert('Solo imágenes JPG, PNG o WebP.'); input.value = ''; return; }
    if (file.size > 8 * 1024 * 1024) { alert('Máximo 8MB.'); input.value = ''; return; }

    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('cropperImage');
        img.src = e.target.result;
        document.getElementById('cropperModal').style.display = 'flex';

        if (cropperInstance) cropperInstance.destroy();

        setTimeout(() => {
            cropperInstance = new Cropper(img, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                cropBoxResizable: false,
                cropBoxMovable: false,
                toggleDragModeOnDblclick: false,
                background: true,
                autoCropArea: 1,
                responsive: true,
                ready() {
                    const cropBox = this.cropper.querySelector('.cropper-view-box');
                    const face = this.cropper.querySelector('.cropper-face');
                    if (cropBox) cropBox.style.borderRadius = '50%';
                    if (face) face.style.borderRadius = '50%';
                }
            });
        }, 100);
    };
    reader.readAsDataURL(file);
}

function closeCropperModal() {
    document.getElementById('cropperModal').style.display = 'none';
    if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
    document.getElementById('photoInput').value = '';
}

function applyCropToForm() {
    if (!cropperInstance) return;

    const canvas = cropperInstance.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob(function (blob) {
        // La foto recortada se convierte en el archivo que se envía con el
        // formulario normal (crear/guardar) — no se sube por separado.
        const file = new File([blob], 'foto-perfil.jpg', { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('photoInput').files = dt.files;

        const circle = document.querySelector('.avatar-circle');
        let img = circle.querySelector('img');
        const placeholder = document.getElementById('photoPlaceholder');
        if (placeholder) placeholder.style.display = 'none';
        if (!img) {
            img = document.createElement('img');
            circle.insertBefore(img, circle.firstChild);
        }
        img.src = canvas.toDataURL('image/jpeg', 0.9);
        circle.querySelector('.avatar-circle-overlay').style.display = 'flex';

        document.getElementById('cropperModal').style.display = 'none';
        cropperInstance.destroy();
        cropperInstance = null;
    }, 'image/jpeg', 0.9);
}

document.getElementById('cropperModal')?.addEventListener('click', function (e) {
    if (e.target === this) closeCropperModal();
});

function copyConsentLinkInput() {
    const input = document.getElementById('consentLinkInput');
    input.select();
    navigator.clipboard.writeText(input.value);
}

function sendConsentLinkByWhatsApp(name) {
    const link = document.getElementById('consentLinkInput').value;
    const text = 'Hola ' + name + ', te comparto este link para revisar y autorizar tu perfil en homedelvalle.mx: ' + link;
    window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
}
</script>
@endsection
