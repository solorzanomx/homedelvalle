{{-- Navigation Fields Partial --}}
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 style="margin: 0; font-size: 1rem;">Navegacion</h3>
        <p class="text-muted" style="margin: 0.25rem 0 0; font-size: 0.82rem;">Configura si esta pagina aparece en el menu principal del sitio publico.</p>
    </div>
    <div class="card-body">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="hidden" name="show_in_nav" value="0">
                    <input type="checkbox" name="show_in_nav" value="1"
                           {{ old('show_in_nav', $page->show_in_nav ?? false) ? 'checked' : '' }}
                           style="width: 16px; height: 16px; accent-color: var(--primary);">
                    Mostrar en menu de navegacion
                </label>
            </div>

            <div class="form-group">
                <label class="form-label">Orden en menu</label>
                <input type="number" name="nav_order" class="form-input"
                       value="{{ old('nav_order', $page->nav_order ?? 0) }}" placeholder="0" min="0">
                <p class="form-hint">Menor numero = aparece primero en el menu.</p>
            </div>

            <div class="form-group">
                <label class="form-label">Etiqueta del menu</label>
                <input type="text" name="nav_label" class="form-input" maxlength="50"
                       value="{{ old('nav_label', $page->nav_label ?? '') }}"
                       placeholder="Ej: Inicio, Nosotros...">
                <p class="form-hint">Texto que se muestra en el menu. Si se deja vacio se usa el titulo.</p>
            </div>

            <div class="form-group">
                <label class="form-label">Estilo</label>
                <select name="nav_style" class="form-input">
                    <option value="link" {{ old('nav_style', $page->nav_style ?? 'link') === 'link' ? 'selected' : '' }}>Enlace normal</option>
                    <option value="button" {{ old('nav_style', $page->nav_style ?? '') === 'button' ? 'selected' : '' }}>Boton (destacado)</option>
                    <option value="muted" {{ old('nav_style', $page->nav_style ?? '') === 'muted' ? 'selected' : '' }}>Sutil (gris claro)</option>
                </select>
                <p class="form-hint">Como se muestra el enlace en el menu.</p>
            </div>

            <div class="form-group">
                <label class="form-label">Ruta Laravel (route name)</label>
                <input type="text" name="nav_route" class="form-input"
                       value="{{ old('nav_route', $page->nav_route ?? '') }}"
                       placeholder="Ej: home, contacto, blog.index">
                <p class="form-hint">Nombre de ruta interna. Tiene prioridad sobre URL personalizada.</p>
            </div>

            <div class="form-group">
                <label class="form-label">URL personalizada</label>
                <input type="text" name="nav_url" class="form-input"
                       value="{{ old('nav_url', $page->nav_url ?? '') }}"
                       placeholder="Ej: /nosotros, https://...">
                <p class="form-hint">Se usa si no hay ruta Laravel. Puede ser relativa o absoluta.</p>
            </div>
        </div>
    </div>
</div>
