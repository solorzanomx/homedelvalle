@extends('layouts.app-sidebar')
@section('title', 'Editar: ' . $user->name)

@section('styles')
<style>
.edit-layout { display: grid; grid-template-columns: 1fr 300px; gap: 1.25rem; align-items: start; max-width: 960px; }
.edit-main { min-width: 0; }
.edit-sidebar { position: sticky; top: 1rem; }

.edit-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    margin-bottom: 1rem; overflow: hidden;
}
.edit-card-header {
    padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border);
    font-weight: 600; font-size: 0.85rem;
}
.edit-card-body { padding: 1.25rem; }

/* Tab pills */
.tab-pills { display: flex; gap: 0.4rem; margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 2px; }
.tab-pill {
    padding: 0.45rem 0.9rem; border-radius: 20px; font-size: 0.78rem; font-weight: 500;
    border: 1px solid var(--border); background: var(--card); color: var(--text-muted);
    cursor: pointer; white-space: nowrap; transition: all 0.15s;
}
.tab-pill:hover { border-color: var(--primary); color: var(--text); }
.tab-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* Avatar upload */
.edit-avatar {
    width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 0.5rem;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 1.5rem; color: #fff; background: var(--primary);
    overflow: hidden; cursor: pointer; position: relative;
}
.edit-avatar img { width: 100%; height: 100%; object-fit: cover; }
.edit-avatar-overlay {
    position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: flex;
    align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;
    font-size: 1.2rem; color: #fff;
}
.edit-avatar:hover .edit-avatar-overlay { opacity: 1; }

/* Save bar */
.save-bar {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 1rem 1.25rem; text-align: center;
}
.save-bar .btn { width: 100%; margin-bottom: 0.5rem; }
.save-bar .btn:last-child { margin-bottom: 0; }
.save-bar-meta { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.5rem; }

@media (max-width: 1024px) {
    .edit-layout { grid-template-columns: 1fr; max-width: 720px; }
    .edit-sidebar { position: static; }
}
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('admin.users.index') }}" style="font-size:0.82rem; color:var(--text-muted);">Usuarios</a>
    <span style="color:var(--text-muted); font-size:0.75rem;">/</span>
    <a href="{{ route('admin.users.show', $user) }}" style="font-size:0.82rem; color:var(--text-muted);">{{ $user->name }}</a>
    <span style="color:var(--text-muted); font-size:0.75rem;">/</span>
    <span style="font-size:0.82rem; color:var(--text);">Editar</span>
</div>

{{-- Tab pills --}}
<div class="tab-pills">
    <button class="tab-pill active" onclick="showTab('general')">General</button>
    <button class="tab-pill" onclick="showTab('profesional')">Profesional</button>
    <button class="tab-pill" onclick="showTab('correo')">Correo Empresa</button>
</div>

@if($errors->any())
    <div class="alert alert-error" style="margin-bottom:1rem;">
        <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    </div>
@endif

<form method="POST" action="{{ route('admin.users.update', $user) }}">
@csrf @method('PUT')

<div class="edit-layout">
    <div class="edit-main">
        {{-- Tab: General --}}
        <div id="tab-general">
            <div class="edit-card">
                <div class="edit-card-header">Informacion Basica</div>
                <div class="edit-card-body">
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
                            <label class="form-label">Telefono</label>
                            <input type="tel" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">WhatsApp</label>
                            <input type="tel" name="whatsapp" class="form-input" value="{{ old('whatsapp', $user->whatsapp) }}">
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Direccion</label>
                            <textarea name="address" class="form-textarea" rows="2">{{ old('address', $user->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab: Profesional --}}
        <div id="tab-profesional" style="display:none;">
            <div class="edit-card">
                <div class="edit-card-header">Perfil Profesional</div>
                <div class="edit-card-body">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-textarea" rows="3" maxlength="200" placeholder="Breve descripcion profesional...">{{ old('bio', $user->bio) }}</textarea>
                            <p class="form-hint">Maximo 200 caracteres.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Titulo Profesional</label>
                            <input type="text" name="title" class="form-input" value="{{ old('title', $user->title) }}" placeholder="Ej: Directora, Asesor Senior">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sucursal</label>
                            <input type="text" name="branch" class="form-input" value="{{ old('branch', $user->branch) }}" placeholder="Ej: CDMX Centro">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Idioma</label>
                            <select name="language" class="form-select">
                                <option value="es" {{ old('language', $user->language ?? 'es') === 'es' ? 'selected' : '' }}>Espanol</option>
                                <option value="en" {{ old('language', $user->language) === 'en' ? 'selected' : '' }}>English</option>
                                <option value="fr" {{ old('language', $user->language) === 'fr' ? 'selected' : '' }}>Francais</option>
                                <option value="pt" {{ old('language', $user->language) === 'pt' ? 'selected' : '' }}>Portugues</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Zona Horaria</label>
                            <select name="timezone" class="form-select">
                                @foreach([
                                    'America/Mexico_City' => 'Ciudad de Mexico (GMT-6)',
                                    'America/Cancun' => 'Cancun (GMT-5)',
                                    'America/Monterrey' => 'Monterrey (GMT-6)',
                                    'America/Tijuana' => 'Tijuana (GMT-8)',
                                    'America/Los_Angeles' => 'Los Angeles (GMT-8)',
                                    'America/New_York' => 'New York (GMT-5)',
                                    'America/Bogota' => 'Bogota (GMT-5)',
                                    'America/Argentina/Buenos_Aires' => 'Buenos Aires (GMT-3)',
                                    'Europe/Madrid' => 'Madrid (GMT+1)',
                                ] as $tz => $label)
                                    <option value="{{ $tz }}" {{ old('timezone', $user->timezone ?? 'America/Mexico_City') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo de Ficha Compartida</label>
                            <select name="shared_card_type" class="form-select">
                                <option value="ficha_simple" {{ old('shared_card_type', $user->shared_card_type ?? 'ficha_simple') === 'ficha_simple' ? 'selected' : '' }}>Ficha Simple</option>
                                <option value="micrositio" {{ old('shared_card_type', $user->shared_card_type) === 'micrositio' ? 'selected' : '' }}>Micrositio</option>
                                <option value="sitio_web" {{ old('shared_card_type', $user->shared_card_type) === 'sitio_web' ? 'selected' : '' }}>Sitio Web</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                                <input type="hidden" name="show_phone_on_properties" value="0">
                                <input type="checkbox" name="show_phone_on_properties" value="1"
                                       style="width:16px; height:16px; accent-color:var(--primary);"
                                       {{ old('show_phone_on_properties', $user->show_phone_on_properties ?? true) ? 'checked' : '' }}>
                                <span class="form-label" style="margin:0;">Mostrar telefono en propiedades</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab: Correo Empresa --}}
        <div id="tab-correo" style="display:none;">
            <div class="edit-card">
                <div class="edit-card-header">Correo de Empresa</div>
                <div class="edit-card-body">
                    <p class="form-hint" style="margin-bottom:1rem;">Configura el correo @homedelvalle.mx para que este usuario pueda enviar fichas y correos a clientes.</p>
                    @php $mailSetting = $user->mailSetting; @endphp
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Correo de Empresa</label>
                            <input type="email" name="mail_from_email" class="form-input"
                                   value="{{ old('mail_from_email', $mailSetting->from_email ?? '') }}"
                                   placeholder="nombre@homedelvalle.mx">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contrasena del Correo</label>
                            <input type="password" name="mail_password" class="form-input"
                                   placeholder="{{ $mailSetting && $mailSetting->password ? '••••••••  (dejar vacio para no cambiar)' : 'Contrasena del correo' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nombre Remitente</label>
                            <input type="text" name="mail_from_name" class="form-input"
                                   value="{{ old('mail_from_name', $mailSetting->from_name ?? '') }}"
                                   placeholder="{{ $user->name }} {{ $user->last_name }}">
                        </div>
                        <div class="form-group">
                            <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; padding:0.55rem 0;">
                                <input type="hidden" name="mail_is_active" value="0">
                                <input type="checkbox" name="mail_is_active" value="1"
                                       style="width:16px; height:16px; accent-color:var(--primary);"
                                       {{ old('mail_is_active', $mailSetting->is_active ?? false) ? 'checked' : '' }}>
                                <span class="form-label" style="margin:0;">Correo activo</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="edit-sidebar">
        {{-- Avatar --}}
        <div class="edit-card" style="text-align:center;">
            <div class="edit-card-body">
                <div class="edit-avatar" onclick="document.getElementById('avatarInput').click()" title="Cambiar foto">
                    @if($user->avatar_path)
                        <img src="{{ Storage::url($user->avatar_path) }}" alt="Avatar" id="avatarPreview">
                    @else
                        <span id="avatarPlaceholder">{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}</span>
                    @endif
                    <div class="edit-avatar-overlay">&#128247;</div>
                </div>
                <p class="form-hint">Clic para cambiar foto</p>
                <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="uploadAvatar(this)" style="display:none;">
            </div>
        </div>

        {{-- Save --}}
        <div class="save-bar">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline">Cancelar</a>
            <div class="save-bar-meta">Actualizado {{ $user->updated_at->diffForHumans() }}</div>
        </div>
    </div>
</div>
</form>
@endsection

@section('scripts')
<script>
function showTab(tab) {
    ['general','profesional','correo'].forEach(function(t) {
        var el = document.getElementById('tab-' + t);
        if (el) el.style.display = t === tab ? '' : 'none';
    });
    document.querySelectorAll('.tab-pills .tab-pill').forEach(function(pill) {
        pill.classList.remove('active');
    });
    event.target.classList.add('active');
}

function uploadAvatar(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 2 * 1024 * 1024) { alert('La imagen no puede pesar mas de 2MB'); return; }
    var reader = new FileReader();
    reader.onload = function(e) {
        var container = document.querySelector('.edit-avatar');
        var placeholder = document.getElementById('avatarPlaceholder');
        if (placeholder) placeholder.style.display = 'none';
        var img = document.getElementById('avatarPreview');
        if (img) { img.src = e.target.result; }
        else {
            img = document.createElement('img');
            img.src = e.target.result; img.id = 'avatarPreview'; img.alt = 'Avatar';
            container.insertBefore(img, container.firstChild);
        }
    };
    reader.readAsDataURL(file);
    var formData = new FormData();
    formData.append('avatar', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    fetch('{{ route("admin.users.avatar", $user) }}', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.json(); })
    .then(function(data) { if (data.success) document.getElementById('avatarPreview').src = data.avatar_url; })
    .catch(function() { alert('Error al subir la imagen'); });
}
</script>
@endsection
