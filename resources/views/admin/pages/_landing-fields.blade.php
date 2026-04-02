@php $ls = $page->landing_settings ?? []; @endphp

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header"><h3>Landing Page</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="hidden" name="is_landing" value="0">
                <input type="checkbox" name="is_landing" value="1"
                       {{ old('is_landing', $page->is_landing ?? false) ? 'checked' : '' }}
                       style="width: 16px; height: 16px; accent-color: var(--primary);"
                       onchange="document.getElementById('landingOptions').style.display = this.checked ? 'block' : 'none';">
                Es landing page
            </label>
            <p class="form-hint">Las landing pages usan un layout minimalista sin navegacion completa.</p>
        </div>

        <div id="landingOptions" style="display: {{ old('is_landing', $page->is_landing ?? false) ? 'block' : 'none' }}; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="hidden" name="landing_hide_header" value="0">
                        <input type="checkbox" name="landing_hide_header" value="1"
                               {{ old('landing_hide_header', $ls['hide_header'] ?? true) ? 'checked' : '' }}
                               style="width: 16px; height: 16px; accent-color: var(--primary);">
                        Ocultar header / navegacion
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="hidden" name="landing_hide_footer" value="0">
                        <input type="checkbox" name="landing_hide_footer" value="1"
                               {{ old('landing_hide_footer', $ls['hide_footer'] ?? true) ? 'checked' : '' }}
                               style="width: 16px; height: 16px; accent-color: var(--primary);">
                        Ocultar footer
                    </label>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">CSS personalizado</label>
                    <textarea name="landing_custom_css" class="form-textarea" rows="4"
                              placeholder=".hero { background: linear-gradient(...); }"
                              style="font-family: monospace; font-size: 0.82rem;">{{ old('landing_custom_css', $ls['custom_css'] ?? '') }}</textarea>
                    <p class="form-hint">CSS adicional que se inyecta solo en esta landing page.</p>
                </div>
            </div>
        </div>
    </div>
</div>
