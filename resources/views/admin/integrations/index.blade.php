@extends('layouts.app-sidebar')
@section('title', 'Integraciones')

@section('styles')
.int-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start; }
.int-full { grid-column: 1 / -1; }
.int-toggle { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.int-toggle-label { font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem; }
.int-status { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.int-status.on { background: #22c55e; }
.int-status.off { background: #d1d5db; }
.toggle-switch { position: relative; width: 44px; height: 24px; cursor: pointer; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-track { position: absolute; inset: 0; background: #d1d5db; border-radius: 12px; transition: background 0.2s; }
.toggle-switch input:checked + .toggle-track { background: var(--primary, #3B82C4); }
.toggle-track::after { content: ''; position: absolute; width: 18px; height: 18px; background: #fff; border-radius: 50%; top: 3px; left: 3px; transition: transform 0.2s; }
.toggle-switch input:checked + .toggle-track::after { transform: translateX(20px); }
.int-fields { padding: 0.75rem 0 0; }
.int-warning { background: #fef3c7; border: 1px solid #fde68a; border-radius: var(--radius, 8px); padding: 0.75rem 1rem; font-size: 0.82rem; color: #92400e; margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 0.5rem; }
@media (max-width: 768px) { .int-grid { grid-template-columns: 1fr; } }
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Integraciones</h2>
        <p class="text-muted">Configura codigos de seguimiento y conexiones con servicios externos</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.integrations.update') }}">
    @csrf

    <div class="int-grid">
        {{-- Google Tag Manager --}}
        <div class="card">
            <div class="card-header"><h3>Google Tag Manager</h3></div>
            <div class="card-body">
                <div class="int-toggle">
                    <div class="int-toggle-label">
                        <span class="int-status {{ ($settings && $settings->gtm_enabled && $settings->gtm_id) ? 'on' : 'off' }}"></span>
                        {{ ($settings && $settings->gtm_enabled && $settings->gtm_id) ? 'Activo' : 'Inactivo' }}
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="gtm_enabled" value="0">
                        <input type="checkbox" name="gtm_enabled" value="1" {{ ($settings && $settings->gtm_enabled) ? 'checked' : '' }} onchange="toggleFields(this, 'gtmFields')">
                        <span class="toggle-track"></span>
                    </label>
                </div>
                <div id="gtmFields" class="int-fields" style="{{ ($settings && $settings->gtm_enabled) ? '' : 'display:none' }}">
                    <div class="form-group">
                        <label class="form-label">GTM ID</label>
                        <input type="text" name="gtm_id" class="form-input" value="{{ old('gtm_id', $settings->gtm_id ?? '') }}" placeholder="GTM-XXXXXXXX">
                        <p class="form-hint">Se inyecta automaticamente en todas las paginas publicas.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Google Analytics --}}
        <div class="card">
            <div class="card-header"><h3>Google Analytics</h3></div>
            <div class="card-body">
                <div class="int-toggle">
                    <div class="int-toggle-label">
                        <span class="int-status {{ ($settings && $settings->ga_enabled && $settings->google_analytics_id) ? 'on' : 'off' }}"></span>
                        {{ ($settings && $settings->ga_enabled && $settings->google_analytics_id) ? 'Activo' : 'Inactivo' }}
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="ga_enabled" value="0">
                        <input type="checkbox" name="ga_enabled" value="1" {{ ($settings && $settings->ga_enabled) ? 'checked' : '' }} onchange="toggleFields(this, 'gaFields')">
                        <span class="toggle-track"></span>
                    </label>
                </div>
                <div id="gaFields" class="int-fields" style="{{ ($settings && $settings->ga_enabled) ? '' : 'display:none' }}">
                    <div class="form-group">
                        <label class="form-label">Measurement ID</label>
                        <input type="text" name="google_analytics_id" class="form-input" value="{{ old('google_analytics_id', $settings->google_analytics_id ?? '') }}" placeholder="G-XXXXXXXXXX">
                        <p class="form-hint">ID de medicion de Google Analytics 4.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Facebook Pixel --}}
        <div class="card">
            <div class="card-header"><h3>Facebook Pixel</h3></div>
            <div class="card-body">
                <div class="int-toggle">
                    <div class="int-toggle-label">
                        <span class="int-status {{ ($settings && $settings->fb_pixel_enabled && $settings->facebook_pixel_id) ? 'on' : 'off' }}"></span>
                        {{ ($settings && $settings->fb_pixel_enabled && $settings->facebook_pixel_id) ? 'Activo' : 'Inactivo' }}
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="fb_pixel_enabled" value="0">
                        <input type="checkbox" name="fb_pixel_enabled" value="1" {{ ($settings && $settings->fb_pixel_enabled) ? 'checked' : '' }} onchange="toggleFields(this, 'fbFields')">
                        <span class="toggle-track"></span>
                    </label>
                </div>
                <div id="fbFields" class="int-fields" style="{{ ($settings && $settings->fb_pixel_enabled) ? '' : 'display:none' }}">
                    <div class="form-group">
                        <label class="form-label">Pixel ID</label>
                        <input type="text" name="facebook_pixel_id" class="form-input" value="{{ old('facebook_pixel_id', $settings->facebook_pixel_id ?? '') }}" placeholder="1234567890">
                        <p class="form-hint">ID del pixel de Facebook/Meta para tracking de conversiones.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Placeholder: more integrations --}}
        <div class="card" style="border: 2px dashed var(--border, #e5e7eb); background: transparent;">
            <div class="card-body" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem; text-align:center;">
                <div style="font-size:2rem; margin-bottom:0.5rem; opacity:0.4;">&#43;</div>
                <div style="font-weight:600; color:var(--text-muted, #9ca3af); font-size:0.9rem;">Mas integraciones proximamente</div>
                <div style="color:var(--text-muted, #9ca3af); font-size:0.8rem; margin-top:0.25rem;">TikTok Pixel, Hotjar, Clarity, etc.</div>
            </div>
        </div>

        {{-- Custom Scripts --}}
        <div class="card int-full">
            <div class="card-header"><h3>Scripts Personalizados</h3></div>
            <div class="card-body">
                <div class="int-warning">
                    <span>&#9888;</span>
                    <span>Ten cuidado al agregar scripts personalizados. Codigo incorrecto puede afectar el funcionamiento del sitio.</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Scripts en &lt;head&gt;</label>
                    <textarea name="custom_head_scripts" class="form-textarea" rows="5" placeholder="<!-- Pega aqui scripts que van en el <head> -->">{{ old('custom_head_scripts', $settings->custom_head_scripts ?? '') }}</textarea>
                    <p class="form-hint">Se inyectan antes de cerrar &lt;/head&gt;. Ideal para scripts de analytics adicionales.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Scripts antes de &lt;/body&gt;</label>
                    <textarea name="custom_body_scripts" class="form-textarea" rows="5" placeholder="<!-- Pega aqui scripts que van antes de </body> -->">{{ old('custom_body_scripts', $settings->custom_body_scripts ?? '') }}</textarea>
                    <p class="form-hint">Se inyectan justo antes de cerrar &lt;/body&gt;. Ideal para chat widgets o scripts de terceros.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions" style="margin-top:1.5rem;">
        <button type="submit" class="btn btn-primary">Guardar Integraciones</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
function toggleFields(checkbox, targetId) {
    var el = document.getElementById(targetId);
    if (el) {
        el.style.display = checkbox.checked ? '' : 'none';
    }
}
</script>
@endsection
