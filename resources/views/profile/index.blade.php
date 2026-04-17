@extends('layouts.app-sidebar')
@section('title', 'Mi Perfil')

@section('styles')
<style>
    .profile-layout { display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start; }
    .profile-sidebar { position: sticky; top: 80px; }
    .bio-counter { font-size: 0.75rem; color: var(--text-muted); text-align: right; margin-top: 0.2rem; transition: color 0.2s; }
    .bio-counter.warning { color: var(--danger); font-weight: 500; }

    /* Avatar upload */
    .profile-avatar { width: 100px; height: 100px; border-radius: 50%; overflow: hidden; position: relative; cursor: pointer; margin: 0 auto; }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .avatar-placeholder { width: 100%; height: 100%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; font-weight: 600; }
    .profile-avatar .overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; color: #fff; border-radius: 50%; }
    .profile-avatar:hover .overlay { opacity: 1; }
    .overlay-text { font-size: 0.65rem; margin-top: 0.15rem; }

    /* Preview card */
    .preview-agent { text-align: center; padding: 0.5rem 0; }
    .preview-avatar-wrap { margin: 0 auto 0.75rem; }
    .preview-avatar-wrap img { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); }
    .preview-avatar-placeholder { width: 72px; height: 72px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 600; margin: 0 auto; }
    .preview-name { font-weight: 600; font-size: 1rem; color: var(--text); }
    .preview-title { font-size: 0.82rem; color: var(--text-muted); margin-top: 0.15rem; }
    .preview-bio { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.5rem; line-height: 1.5; max-width: 260px; margin-left: auto; margin-right: auto; }
    .preview-contact { margin-top: 0.75rem; display: flex; flex-direction: column; gap: 0.3rem; font-size: 0.78rem; color: var(--text-muted); }
    .preview-contact span { display: flex; align-items: center; justify-content: center; gap: 0.35rem; }
    .preview-badges { margin-top: 0.75rem; display: flex; flex-wrap: wrap; justify-content: center; gap: 0.4rem; }
    .preview-badge { font-size: 0.68rem; padding: 0.15rem 0.55rem; background: var(--bg); border-radius: 20px; color: var(--text-muted); border: 1px solid var(--border); }

    .section-divider { border: 0; border-top: 1px solid var(--border); margin: 1rem 0; }

    @media (max-width: 1024px) {
        .profile-layout { grid-template-columns: 1fr; }
        .profile-sidebar { position: static; order: -1; }
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Mi Perfil</h2>
        <p class="text-muted">Configura tu informacion personal y profesional</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    &#10003; {{ session('success') }}
    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
</div>
@endif
@if(session('error'))
<div class="alert alert-error">
    &#9888; {{ session('error') }}
    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
</div>
@endif

<div class="profile-layout">
    {{-- ========== LEFT COLUMN ========== --}}
    <div>
        {{-- AVATAR --}}
        <div class="card">
            <div class="card-body" style="text-align:center; padding: 1.5rem;">
                <div class="profile-avatar" onclick="document.getElementById('photoInput').click()">
                    @if($user->avatar_path)
                        <img src="{{ Storage::url($user->avatar_path) }}" alt="Avatar" id="avatarPreview" data-avatar-img>
                    @else
                        <div class="avatar-placeholder" id="avatarPlaceholder" data-avatar-placeholder>
                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                        </div>
                    @endif
                    <div class="overlay">
                        <span style="font-size:1.4rem;">&#128247;</span>
                        <span class="overlay-text">Cambiar foto</span>
                    </div>
                </div>
                <p style="margin-top:0.5rem; font-weight:600;">{{ $user->full_name }}</p>
                <p class="form-hint" style="margin-top:0.15rem;">{{ $user->email }}</p>
                <span class="badge badge-blue" style="margin-top:0.5rem; display:inline-block;">{{ ucfirst($user->role) }}</span>
                <input type="file" id="photoInput" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" onchange="openCropper(this.files[0])" style="display:none;">
            </div>
        </div>

        {{-- ========== MAIN FORM ========== --}}
        <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
            @csrf

            {{-- SECTION 1: Informacion Personal --}}
            <div class="card">
                <div class="card-header"><h3>Informacion Personal</h3></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre <span class="required">*</span></label>
                            <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required
                                   oninput="updatePreview()">
                            @error('name') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="last_name" class="form-input" value="{{ old('last_name', $user->last_name) }}"
                                   oninput="updatePreview()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required
                                   oninput="updatePreview()">
                            @error('email') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Celular</label>
                            <input type="tel" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}"
                                   placeholder="+52 123 456 7890" oninput="updatePreview()">
                            <p class="form-hint">Formato internacional. Se muestra en propiedades si esta activado.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">WhatsApp</label>
                            <input type="tel" name="whatsapp" class="form-input" value="{{ old('whatsapp', $user->whatsapp) }}"
                                   placeholder="+52 123 456 7890">
                            <p class="form-hint">Para boton de WhatsApp en tus propiedades.</p>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Direccion</label>
                            <textarea name="address" class="form-textarea" rows="2" placeholder="Calle, colonia, ciudad...">{{ old('address', $user->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: Perfil Profesional --}}
            <div class="card">
                <div class="card-header"><h3>Perfil Profesional</h3></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" id="bioInput" class="form-textarea" rows="3"
                                      maxlength="200" oninput="updateBioCounter(); updatePreview();"
                                      placeholder="Breve descripcion profesional que aparecera en tu tarjeta de agente...">{{ old('bio', $user->bio) }}</textarea>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.2rem;">
                                <p class="form-hint" style="margin:0;">Se muestra en tu tarjeta de agente en las propiedades.</p>
                                <span class="bio-counter" id="bioCounter"><span id="bioCount">{{ strlen(old('bio', $user->bio ?? '')) }}</span>/200</span>
                            </div>
                            @error('bio') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Titulo Profesional</label>
                            <input type="text" name="title" class="form-input" value="{{ old('title', $user->title) }}"
                                   placeholder="Ej: Directora, Asesor Senior" oninput="updatePreview()">
                            <p class="form-hint">Aparece debajo de tu nombre en la tarjeta.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sucursal</label>
                            <input type="text" name="branch" class="form-input" value="{{ old('branch', $user->branch) }}"
                                   placeholder="Ej: CDMX Centro, Monterrey" oninput="updatePreview()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Idioma</label>
                            <select name="language" class="form-select">
                                <option value="es" {{ old('language', $user->language ?? 'es') === 'es' ? 'selected' : '' }}>Espanol</option>
                                <option value="en" {{ old('language', $user->language) === 'en' ? 'selected' : '' }}>English</option>
                                <option value="fr" {{ old('language', $user->language) === 'fr' ? 'selected' : '' }}>Francais</option>
                                <option value="pt" {{ old('language', $user->language) === 'pt' ? 'selected' : '' }}>Portugues</option>
                            </select>
                            <p class="form-hint">Idioma preferido para la interfaz.</p>
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
                    </div>
                </div>
            </div>

            {{-- SECTION 3: Configuracion --}}
            <div class="card">
                <div class="card-header"><h3>Configuracion</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Firma de Email</label>
                        <textarea name="email_signature" id="signatureEditor" class="form-textarea" rows="6">{{ old('email_signature', $user->email_signature) }}</textarea>
                        <p class="form-hint">Se incluira al final de los correos enviados desde la plataforma.</p>
                    </div>
                </div>
            </div>

            {{-- SECTION 4: Opciones de Compartir --}}
            <div class="card">
                <div class="card-header"><h3>Opciones de Compartir</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="hidden" name="show_phone_on_properties" value="0">
                            <input type="checkbox" name="show_phone_on_properties" value="1"
                                   style="width:16px; height:16px; accent-color:var(--primary);"
                                   {{ old('show_phone_on_properties', $user->show_phone_on_properties ?? true) ? 'checked' : '' }}
                                   onchange="updatePreview()">
                            <span class="form-label" style="margin:0;">Mostrar telefono en propiedades</span>
                        </label>
                        <p class="form-hint" style="margin-left:1.5rem;">Si esta activo, tu numero de celular aparecera en las fichas de propiedad.</p>
                    </div>

                    <hr class="section-divider">

                    <div class="form-group">
                        <label class="form-label">Tipo de Ficha Compartida</label>
                        <select name="shared_card_type" class="form-select" onchange="updatePreview()">
                            <option value="ficha_simple" {{ old('shared_card_type', $user->shared_card_type ?? 'ficha_simple') === 'ficha_simple' ? 'selected' : '' }}>
                                Ficha Simple - Tarjeta basica con datos de contacto
                            </option>
                            <option value="micrositio" {{ old('shared_card_type', $user->shared_card_type) === 'micrositio' ? 'selected' : '' }}>
                                Micrositio - Pagina personal con tus propiedades
                            </option>
                            <option value="sitio_web" {{ old('shared_card_type', $user->shared_card_type) === 'sitio_web' ? 'selected' : '' }}>
                                Sitio Web - Enlace directo al sitio principal
                            </option>
                        </select>
                        <p class="form-hint">Define como se comparte tu perfil con clientes potenciales.</p>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>

        {{-- SECTION 5: Mi Correo de Empresa --}}
        @php $mailSetting = $user->mailSetting; @endphp
        <div class="card">
            <div class="card-header">
                <h3>Mi Correo de Empresa</h3>
                @if($mailSetting && $mailSetting->is_active && $mailSetting->isConfigured())
                    <span style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; color:var(--success);">
                        <span style="width:8px; height:8px; border-radius:50%; background:var(--success); display:inline-block;"></span>
                        {{ $mailSetting->from_email }}
                    </span>
                @elseif($mailSetting && $mailSetting->from_email)
                    <span style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; color:#f59e0b;">
                        <span style="width:8px; height:8px; border-radius:50%; background:#f59e0b; display:inline-block;"></span>
                        Desactivado
                    </span>
                @else
                    <span style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; color:var(--text-muted);">
                        <span style="width:8px; height:8px; border-radius:50%; background:var(--text-muted); display:inline-block;"></span>
                        Sin configurar
                    </span>
                @endif
            </div>
            <div class="card-body">
                <p class="form-hint" style="margin-bottom:1rem;">Configura tu correo para enviar fichas tecnicas y correos a clientes directamente desde tu cuenta. El servidor SMTP y credenciales se heredan automaticamente de la configuracion del sistema.</p>
                <form method="POST" action="{{ route('profile.mail-settings') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Correo de Empresa <span class="required">*</span></label>
                            <input type="email" name="from_email" class="form-input"
                                   value="{{ old('from_email', $mailSetting->from_email ?? '') }}"
                                   placeholder="tu.nombre@homedelvalle.mx">
                            <p class="form-hint">El correo desde el cual se enviaran los mensajes a tus clientes.</p>
                            @error('from_email') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nombre del Remitente</label>
                            <input type="text" name="from_name" class="form-input"
                                   value="{{ old('from_name', $mailSetting->from_name ?? '') }}"
                                   placeholder="{{ $user->full_name }}">
                            <p class="form-hint">Nombre que veran los clientes al recibir tu correo.</p>
                        </div>
                        <div class="form-group">
                            <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; padding:0.55rem 0;">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                       style="width:16px; height:16px; accent-color:var(--primary);"
                                       {{ old('is_active', $mailSetting->is_active ?? false) ? 'checked' : '' }}>
                                <span class="form-label" style="margin:0;">Activar mi correo</span>
                            </label>
                        </div>
                    </div>

                    {{-- Campos avanzados (colapsados por defecto) --}}
                    <details style="margin-top:0.5rem; margin-bottom:1rem;">
                        <summary style="font-size:0.8rem; color:var(--text-muted); cursor:pointer; user-select:none;">
                            &#9881; Configuracion avanzada (solo si usas un servidor SMTP diferente al del sistema)
                        </summary>
                        <div class="form-grid" style="margin-top:0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Servidor SMTP</label>
                                <input type="text" name="smtp_server" class="form-input"
                                       value="{{ old('smtp_server', $mailSetting->smtp_server ?? '') }}"
                                       placeholder="Se hereda del sistema">
                                <p class="form-hint">Dejar vacio para usar el servidor del sistema.</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Puerto</label>
                                <input type="number" name="port" class="form-input"
                                       value="{{ old('port', $mailSetting->port ?? '') }}"
                                       placeholder="Se hereda del sistema">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Usuario SMTP</label>
                                <input type="text" name="username" class="form-input"
                                       value="{{ old('username', $mailSetting->username ?? '') }}"
                                       placeholder="Se hereda del sistema">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Contrasena / API Key</label>
                                <input type="password" name="password" class="form-input"
                                       placeholder="{{ $mailSetting && $mailSetting->password ? '••••••••  (dejar vacio para no cambiar)' : 'Se hereda del sistema' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Encriptacion</label>
                                <select name="encryption" class="form-select">
                                    <option value="tls" {{ old('encryption', $mailSetting->encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ old('encryption', $mailSetting->encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="none" {{ old('encryption', $mailSetting->encryption ?? '') === 'none' ? 'selected' : '' }}>Ninguna</option>
                                </select>
                            </div>
                        </div>
                    </details>

                    <div class="form-actions" style="justify-content:space-between;">
                        <a href="#" class="btn btn-outline" onclick="event.preventDefault(); document.getElementById('testSmtpForm').submit();">&#9889; Probar Conexion</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
                <form id="testSmtpForm" method="POST" action="{{ route('profile.mail-settings.test') }}" style="display:none;">
                    @csrf
                </form>
            </div>
        </div>

        {{-- PASSWORD CHANGE --}}
        <div class="card" style="margin-top:0.5rem;">
            <div class="card-header"><h3>Cambiar Contrasena</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Contrasena Actual <span class="required">*</span></label>
                            <input type="password" name="current_password" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nueva Contrasena <span class="required">*</span></label>
                            <input type="password" name="new_password" class="form-input" required minlength="6">
                            <p class="form-hint">Minimo 6 caracteres.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmar Contrasena <span class="required">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-input" required minlength="6">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-outline">Cambiar Contrasena</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========== RIGHT COLUMN: PREVIEW ========== --}}
    <div class="profile-sidebar">
        <div class="card">
            <div class="card-header"><h3>Vista Previa</h3></div>
            <div class="card-body">
                <p class="form-hint" style="text-align:center; margin-bottom:1rem;">Asi se vera tu tarjeta en las propiedades</p>
                <div style="border:1px solid var(--border); border-radius:var(--radius); padding:1.25rem; background:var(--bg);">
                    <div class="preview-agent">
                        <div class="preview-avatar-wrap">
                            @if($user->avatar_path)
                                <img src="{{ Storage::url($user->avatar_path) }}" alt="Avatar" id="previewAvatar" data-avatar-img>
                            @else
                                <div class="preview-avatar-placeholder" id="previewAvatarPlaceholder" data-avatar-placeholder>
                                    {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="preview-name" id="previewName">{{ $user->full_name }}</div>
                        <div class="preview-title" id="previewTitle">{{ $user->title ?? '' }}</div>
                        <div class="preview-bio" id="previewBio">{{ $user->bio ?? '' }}</div>
                        <div class="preview-contact" id="previewContact">
                            <span>&#9993; {{ $user->email }}</span>
                            @if(($user->show_phone_on_properties ?? true) && $user->phone)
                                <span id="previewPhone">&#9742; {{ $user->phone }}</span>
                            @endif
                        </div>
                        <div class="preview-badges" id="previewBadges">
                            @if($user->branch)
                                <span class="preview-badge" id="previewBranch">{{ $user->branch }}</span>
                            @endif
                            <span class="preview-badge" id="previewCardType">
                                @switch($user->shared_card_type ?? 'ficha_simple')
                                    @case('micrositio') Micrositio @break
                                    @case('sitio_web') Sitio Web @break
                                    @default Ficha Simple
                                @endswitch
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<x-avatar-cropper :upload-url="route('profile.photo')" />
<script src="/vendor/tinymce/tinymce.min.js"></script>
<script>

// === Bio Counter ===
function updateBioCounter() {
    var input = document.getElementById('bioInput');
    var count = document.getElementById('bioCount');
    var wrapper = document.getElementById('bioCounter');
    var len = input.value.length;
    count.textContent = len;
    wrapper.className = len > 180 ? 'bio-counter warning' : 'bio-counter';
}

// === Live Preview ===
function updatePreview() {
    var name = document.querySelector('[name="name"]').value || '';
    var lastName = document.querySelector('[name="last_name"]').value || '';
    document.getElementById('previewName').textContent = (name + ' ' + lastName).trim();

    document.getElementById('previewTitle').textContent = document.querySelector('[name="title"]')?.value || '';

    var bioEl = document.getElementById('bioInput');
    document.getElementById('previewBio').textContent = bioEl ? bioEl.value : '';

    var email = document.querySelector('[name="email"]').value || '';
    var phone = document.querySelector('[name="phone"]').value || '';
    var showPhone = document.querySelector('[name="show_phone_on_properties"][type="checkbox"]').checked;

    var contactHtml = '<span>&#9993; ' + escapeHtml(email) + '</span>';
    if (showPhone && phone) {
        contactHtml += '<span>&#9742; ' + escapeHtml(phone) + '</span>';
    }
    document.getElementById('previewContact').innerHTML = contactHtml;

    // Branch badge
    var branch = document.querySelector('[name="branch"]').value || '';
    var branchEl = document.getElementById('previewBranch');
    if (branch) {
        if (!branchEl) {
            branchEl = document.createElement('span');
            branchEl.className = 'preview-badge';
            branchEl.id = 'previewBranch';
            document.getElementById('previewBadges').insertBefore(branchEl, document.getElementById('previewCardType'));
        }
        branchEl.textContent = branch;
        branchEl.style.display = '';
    } else if (branchEl) {
        branchEl.style.display = 'none';
    }

    // Card type badge
    var cardTypeSelect = document.querySelector('[name="shared_card_type"]');
    var cardTypeLabel = { micrositio: 'Micrositio', ficha_simple: 'Ficha Simple', sitio_web: 'Sitio Web' };
    document.getElementById('previewCardType').textContent = cardTypeLabel[cardTypeSelect.value] || 'Ficha Simple';

    // Update placeholder initials
    var placeholder = document.getElementById('previewAvatarPlaceholder');
    if (placeholder) {
        var i1 = name.charAt(0).toUpperCase();
        var i2 = lastName.charAt(0).toUpperCase();
        placeholder.textContent = i1 + i2;
    }
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// === TinyMCE (Signature) ===
tinymce.init({
    selector: '#signatureEditor',
    height: 200,
    menubar: false,
    plugins: 'lists link',
    toolbar: 'bold italic underline | forecolor | alignleft aligncenter | bullist | link | removeformat',
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 13px; padding: 8px; }',
    branding: false,
    license_key: 'gpl',
    statusbar: false,
    setup: function(editor) {
        editor.on('change', function() { editor.save(); });
    }
});

// Save TinyMCE on form submit
document.getElementById('profileForm').addEventListener('submit', function() {
    if (tinymce.get('signatureEditor')) tinymce.get('signatureEditor').save();
});

// Init bio counter on load
updateBioCounter();
</script>
@endsection
