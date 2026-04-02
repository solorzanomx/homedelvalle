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
    {{-- Logo Section --}}
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
                {{-- Enviar config existente para no perderla --}}
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
                        <div style="display:flex; gap:0.5rem; justify-content:center;">
                            <input type="file" id="logoInput" name="logo" accept="image/*" style="display:none" onchange="previewLogo(this)">
                            <label for="logoInput" class="btn btn-outline" style="cursor:pointer;">Seleccionar imagen</label>
                            <button type="submit" class="btn btn-primary" id="saveLogoBtn">Guardar</button>
                        </div>
                        <p class="form-hint" style="margin-top:0.5rem;">JPG, PNG, SVG, WebP (max 2MB)</p>
                    </div>
                </div>
            </form>
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
</script>
@endsection
