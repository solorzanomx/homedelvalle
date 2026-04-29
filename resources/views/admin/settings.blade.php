@extends('layouts.app-sidebar')
@section('title', 'Configuracion del Sitio')

@section('content')
<div class="page-header">
    <div>
        <h2>Configuracion del Sitio</h2>
        <p class="text-muted">Personaliza la apariencia y datos de la plataforma</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
    {{-- Logo + Favicon Section --}}
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        {{-- Logo --}}
        <div class="card">
            <div class="card-header"><h3>Logotipo</h3></div>
            <div class="card-body">
                {{-- Toggle: Texto o Imagen --}}
                <div style="display:flex; gap:0.5rem; margin-bottom:1.25rem; background:var(--bg); border-radius:var(--radius); padding:4px;">
                    <button type="button" class="btn btn-sm {{ (!$settings || !$settings->logo_type || $settings->logo_type === 'text') ? 'btn-primary' : 'btn-outline' }}"
                            style="flex:1; justify-content:center;" onclick="setLogoType('text')" id="btnLogoText">
                        Texto
                    </button>
                    <button type="button" class="btn btn-sm {{ ($settings && $settings->logo_type === 'image') ? 'btn-primary' : 'btn-outline' }}"
                            style="flex:1; justify-content:center;" onclick="setLogoType('image')" id="btnLogoImage">
                        Imagen
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="logoForm">
                    @csrf
                    <input type="hidden" name="logo_type" id="logoTypeInput" value="{{ $settings->logo_type ?? 'text' }}">
                    <input type="hidden" name="site_name" value="{{ $settings->site_name ?? 'Homedelvalle' }}">
                    <input type="hidden" name="site_tagline" value="{{ $settings->site_tagline ?? '' }}">
                    <input type="hidden" name="primary_color" value="{{ $settings->primary_color ?? '#667eea' }}">
                    <input type="hidden" name="secondary_color" value="{{ $settings->secondary_color ?? '#764ba2' }}">
                    <input type="hidden" name="home_welcome_text" value="{{ $settings->home_welcome_text ?? '' }}">

                    {{-- Modo Texto --}}
                    <div id="logoTextSection" style="{{ ($settings && $settings->logo_type === 'image') ? 'display:none' : '' }}">
                        <div class="text-center" style="padding:1.5rem 0;">
                            <div style="display:inline-flex; align-items:center; gap:0.75rem; background:var(--sidebar-bg); padding:0.75rem 1.5rem; border-radius:var(--radius); color:#fff;">
                                <div style="width:30px; height:30px; background:var(--primary); border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:16px;">&#9830;</div>
                                <span style="font-size:1rem; font-weight:700;">{{ $settings->site_name ?? 'Homedelvalle' }}</span>
                            </div>
                            <p class="form-hint" style="margin-top:0.75rem;">El nombre del sitio se mostrara como logo en el sidebar.</p>
                        </div>
                        <div class="form-actions" style="border:none; padding-top:0;">
                            <button type="submit" class="btn btn-primary">Usar Nombre como Logo</button>
                        </div>
                    </div>

                    {{-- Modo Imagen --}}
                    <div id="logoImageSection" style="{{ (!$settings || $settings->logo_type !== 'image') ? 'display:none' : '' }}">
                        <div class="text-center">
                            <div class="logo-preview">
                                @if($settings && $settings->logo_path)
                                    <img src="{{ Storage::url($settings->logo_path) }}" alt="Logo" id="logoPreview">
                                @else
                                    <span class="text-muted" id="logoPlaceholder" style="font-size:0.85rem;">Sin imagen</span>
                                @endif
                            </div>
                            <p class="form-hint" style="margin-bottom:0.5rem; color: var(--primary); font-weight:500;">Logo para fondo claro</p>
                            <div style="display:flex; gap:0.5rem; justify-content:center;">
                                <input type="file" id="logoInput" name="logo" accept="image/*" style="display:none" onchange="previewLogo(this)">
                                <label for="logoInput" class="btn btn-outline" style="cursor:pointer;">Seleccionar imagen</label>
                                <button type="submit" class="btn btn-primary" id="saveLogoBtn">Guardar</button>
                            </div>
                            <p class="form-hint" style="margin-top:0.5rem;">JPG, PNG, SVG, WebP (max 2MB)</p>
                        </div>
                        <hr style="border:none; border-top:1px solid var(--border); margin:1.5rem 0;">
                        {{-- Logo Oscuro --}}
                        <div class="text-center">
                            <h4 style="font-size:0.9rem; font-weight:600; margin-bottom:1rem;">Logo para Fondo Oscuro</h4>
                            <div class="logo-dark-preview">
                                @if($settings && $settings->logo_path_dark)
                                    <img src="{{ Storage::url($settings->logo_path_dark) }}" alt="Logo Oscuro" id="logoDarkPreview" style="max-height:80px; max-width:100%; display:block;">
                                @else
                                    <span class="text-muted" id="logoDarkPlaceholder" style="font-size:0.85rem; display:block; padding:1rem;">Sin imagen</span>
                                @endif
                            </div>
                            <div style="display:flex; gap:0.5rem; justify-content:center; margin-top:1rem;">
                                <input type="file" id="logoDarkInput" name="logo_dark" accept="image/*" style="display:none" onchange="previewLogoDark(this)">
                                <label for="logoDarkInput" class="btn btn-outline" style="cursor:pointer;">Seleccionar imagen</label>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                            <p class="form-hint" style="margin-top:0.5rem;">Se mostrara en el footer y areas con fondo oscuro. JPG, PNG, SVG, WebP (max 2MB)</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Favicon --}}
        <div class="card">
            <div class="card-header"><h3>Favicon</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="site_name" value="{{ $settings->site_name ?? 'Homedelvalle' }}">
                    <input type="hidden" name="primary_color" value="{{ $settings->primary_color ?? '#667eea' }}">
                    <input type="hidden" name="secondary_color" value="{{ $settings->secondary_color ?? '#764ba2' }}">
                    <input type="hidden" name="logo_type" value="{{ $settings->logo_type ?? 'text' }}">

                    <div class="text-center">
                        <div style="display:inline-block; width:80px; height:80px; border-radius:50%; overflow:hidden; border:3px solid var(--border); margin-bottom:1rem; background:var(--bg);">
                            @if($settings && $settings->favicon_path)
                                <img src="{{ Storage::url($settings->favicon_path) }}" alt="Favicon" id="faviconPreview" style="width:100%; height:100%; object-fit:cover; display:block;">
                            @else
                                <div id="faviconPlaceholder" style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:0.75rem;">Sin icono</div>
                            @endif
                        </div>
                        <div style="display:flex; gap:0.5rem; justify-content:center;">
                            <input type="file" id="faviconInput" name="favicon" accept="image/png,image/jpeg,image/webp" style="display:none" onchange="previewFavicon(this)">
                            <label for="faviconInput" class="btn btn-outline" style="cursor:pointer;">Seleccionar imagen</label>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                        <p class="form-hint" style="margin-top:0.75rem;">Sube una imagen cuadrada. Se recortara en circulo automaticamente y se usara como icono en la pestana del navegador.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Informacion General --}}
    <div class="card">
        <div class="card-header"><h3>Informacion General</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                <input type="hidden" name="logo_type" value="{{ $settings->logo_type ?? 'text' }}">
                <div class="form-group">
                    <label class="form-label">Nombre del Sitio</label>
                    <input type="text" name="site_name" class="form-input" value="{{ old('site_name', $settings->site_name ?? 'Homedelvalle') }}" required>
                    <p class="form-hint">Se muestra en el sidebar y la pestana del navegador.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Eslogan</label>
                    <input type="text" name="site_tagline" class="form-input" value="{{ old('site_tagline', $settings->site_tagline ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Color Primario</label>
                    <div class="color-group">
                        <input type="color" name="primary_color" class="color-picker" id="primaryColor" value="{{ old('primary_color', $settings->primary_color ?? '#667eea') }}">
                        <input type="text" class="form-input" style="max-width:120px" id="primaryText" value="{{ old('primary_color', $settings->primary_color ?? '#667eea') }}" onchange="document.getElementById('primaryColor').value=this.value">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Color Secundario</label>
                    <div class="color-group">
                        <input type="color" name="secondary_color" class="color-picker" id="secondaryColor" value="{{ old('secondary_color', $settings->secondary_color ?? '#764ba2') }}">
                        <input type="text" class="form-input" style="max-width:120px" id="secondaryText" value="{{ old('secondary_color', $settings->secondary_color ?? '#764ba2') }}" onchange="document.getElementById('secondaryColor').value=this.value">
                    </div>
                </div>
                {{-- Preserve whatsapp_number so Form 1 doesn't clear it --}}
                <input type="hidden" name="whatsapp_number" value="{{ $settings->whatsapp_number ?? '' }}">
                <div class="form-group">
                    <label class="form-label">Texto de Bienvenida</label>
                    <textarea name="home_welcome_text" class="form-textarea">{{ old('home_welcome_text', $settings->home_welcome_text ?? '') }}</textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Configuracion</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Contacto & Redes --}}
<div class="card" style="margin-top:1.5rem;">
    <div class="card-header"><h3>Contacto, Redes y Ubicacion</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            {{-- Preserve required fields --}}
            <input type="hidden" name="site_name" value="{{ $settings->site_name ?? 'Homedelvalle' }}">
            <input type="hidden" name="primary_color" value="{{ $settings->primary_color ?? '#667eea' }}">
            <input type="hidden" name="secondary_color" value="{{ $settings->secondary_color ?? '#764ba2' }}">
            <input type="hidden" name="logo_type" value="{{ $settings->logo_type ?? 'text' }}">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="text" name="contact_phone" class="form-input" value="{{ old('contact_phone', $settings->contact_phone ?? '') }}" placeholder="55 1345 0978">
                </div>
                <div class="form-group">
                    <label class="form-label">Email de contacto</label>
                    <input type="email" name="contact_email" class="form-input" value="{{ old('contact_email', $settings->contact_email ?? '') }}" placeholder="contacto@homedelvalle.mx">
                </div>
                <div class="form-group">
                    <label class="form-label">WhatsApp <span style="color:var(--primary);font-size:0.75rem;font-weight:600">★ Activa el botón flotante</span></label>
                    <input type="text" name="whatsapp_number" class="form-input" value="{{ old('whatsapp_number', $settings->whatsapp_number ?? '') }}" placeholder="5571944188">
                    <p class="form-hint">Solo dígitos, sin espacios ni +. Ejemplo: 5571944188. Este número activa el botón verde de WhatsApp en el sitio público.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Direccion</label>
                    <input type="text" name="address" class="form-input" value="{{ old('address', $settings->address ?? '') }}" placeholder="Heriberto Frías 903 C, Col. del Valle, CDMX">
                </div>
            </div>

            <div style="border-top:1px solid var(--border); margin:1rem 0 1.25rem; padding-top:1.25rem;">
                <label class="form-label" style="margin-bottom:0.75rem; font-size:0.85rem; font-weight:600;">Redes sociales</label>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Facebook</label>
                    <input type="url" name="facebook_url" class="form-input" value="{{ old('facebook_url', $settings->facebook_url ?? '') }}" placeholder="https://facebook.com/homedelvalle">
                </div>
                <div class="form-group">
                    <label class="form-label">Instagram</label>
                    <input type="url" name="instagram_url" class="form-input" value="{{ old('instagram_url', $settings->instagram_url ?? '') }}" placeholder="https://instagram.com/homedelvalle">
                </div>
                <div class="form-group">
                    <label class="form-label">TikTok</label>
                    <input type="url" name="tiktok_url" class="form-input" value="{{ old('tiktok_url', $settings->tiktok_url ?? '') }}" placeholder="https://tiktok.com/@homedelvalle">
                </div>
            </div>

            <div style="border-top:1px solid var(--border); margin:1rem 0 1.25rem; padding-top:1.25rem;">
                <label class="form-label" style="margin-bottom:0.75rem; font-size:0.85rem; font-weight:600;">Google Maps</label>
            </div>
            <div class="form-group">
                <label class="form-label">Codigo embed de Google Maps</label>
                <textarea name="google_maps_embed" class="form-textarea" rows="3" placeholder='<iframe src="https://www.google.com/maps/embed?pb=..." ...></iframe>'>{{ old('google_maps_embed', $settings->google_maps_embed ?? '') }}</textarea>
                <p class="form-hint">Ve a Google Maps &gt; Compartir &gt; Incorporar mapa &gt; Copiar HTML. Pega el codigo completo del iframe aqui.</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Contacto</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('primaryColor').addEventListener('input', function() { document.getElementById('primaryText').value = this.value; });
document.getElementById('secondaryColor').addEventListener('input', function() { document.getElementById('secondaryText').value = this.value; });

function setLogoType(type) {
    document.getElementById('logoTypeInput').value = type;
    document.getElementById('logoTextSection').style.display = type === 'text' ? '' : 'none';
    document.getElementById('logoImageSection').style.display = type === 'image' ? '' : 'none';

    var btnText = document.getElementById('btnLogoText');
    var btnImage = document.getElementById('btnLogoImage');
    if (type === 'text') {
        btnText.className = 'btn btn-sm btn-primary'; btnText.style.flex = '1'; btnText.style.justifyContent = 'center';
        btnImage.className = 'btn btn-sm btn-outline'; btnImage.style.flex = '1'; btnImage.style.justifyContent = 'center';
    } else {
        btnImage.className = 'btn btn-sm btn-primary'; btnImage.style.flex = '1'; btnImage.style.justifyContent = 'center';
        btnText.className = 'btn btn-sm btn-outline'; btnText.style.flex = '1'; btnText.style.justifyContent = 'center';
    }
}

function previewLogo(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var p = document.getElementById('logoPlaceholder');
            if (p) p.style.display = 'none';
            var img = document.getElementById('logoPreview');
            if (img) { img.src = e.target.result; }
            else {
                img = document.createElement('img'); img.src = e.target.result; img.id = 'logoPreview';
                document.querySelector('.logo-preview').appendChild(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewFavicon(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var p = document.getElementById('faviconPlaceholder');
            if (p) p.style.display = 'none';
            var img = document.getElementById('faviconPreview');
            if (img) { img.src = e.target.result; }
            else {
                img = document.createElement('img'); img.src = e.target.result; img.id = 'faviconPreview'; img.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
                p.parentNode.appendChild(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewLogoDark(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var p = document.getElementById('logoDarkPlaceholder');
            if (p) p.style.display = 'none';
            var img = document.getElementById('logoDarkPreview');
            if (img) { img.src = e.target.result; }
            else {
                img = document.createElement('img'); img.src = e.target.result; img.id = 'logoDarkPreview'; img.style.cssText = 'max-height:80px;max-width:100%;display:block;';
                document.querySelector('.logo-dark-preview').appendChild(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
