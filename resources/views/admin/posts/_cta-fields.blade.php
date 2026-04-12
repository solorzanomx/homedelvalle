{{-- CTA Blocks --}}
<div class="card" style="margin-top:1.5rem;">
    <div class="card-header">
        <h3>CTAs del Articulo</h3>
        <span style="font-size:0.72rem; color:var(--text-muted);">Usa <code>@{{CTA1}}</code> <code>@{{CTA2}}</code> <code>@{{CTA3}}</code> en el contenido</span>
    </div>
    <div class="card-body">
        @for($ci = 0; $ci < 3; $ci++)
        @php
            $ctaData = old("ctas.{$ci}", $post->ctas[$ci] ?? []);
            $num = $ci + 1;
        @endphp
        <div style="padding:1rem; border:1px solid var(--border); border-radius:var(--radius); margin-bottom:{{ $ci < 2 ? '1rem' : '0' }}; {{ !empty($ctaData['title']) ? 'border-left:3px solid var(--primary);' : '' }}">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.6rem;">
                <span style="font-weight:600; font-size:0.85rem;">CTA {{ $num }}</span>
                <code style="font-size:0.7rem; background:var(--bg); padding:0.15rem 0.4rem; border-radius:4px;">@{{CTA{{ $num }}}}</code>
            </div>
            <div class="form-grid">
                <div class="form-group" style="margin-bottom:0.5rem;">
                    <label class="form-label">Titulo</label>
                    <input type="text" name="ctas[{{ $ci }}][title]" class="form-input" value="{{ $ctaData['title'] ?? '' }}" placeholder="Ej: ¿Buscas invertir en Del Valle?">
                </div>
                <div class="form-group" style="margin-bottom:0.5rem;">
                    <label class="form-label">Texto del boton</label>
                    <input type="text" name="ctas[{{ $ci }}][button_text]" class="form-input" value="{{ $ctaData['button_text'] ?? '' }}" placeholder="Ej: Solicitar asesoria gratuita">
                </div>
                <div class="form-group full-width" style="margin-bottom:0.5rem;">
                    <label class="form-label">Descripcion</label>
                    <input type="text" name="ctas[{{ $ci }}][description]" class="form-input" value="{{ $ctaData['description'] ?? '' }}" placeholder="Ej: Agenda una llamada con un asesor especializado">
                </div>
                <div class="form-group full-width" style="margin-bottom:0;">
                    <label class="form-label">Link del boton</label>
                    <input type="text" name="ctas[{{ $ci }}][link]" class="form-input" value="{{ $ctaData['link'] ?? '' }}" placeholder="Ej: /contacto o https://...">
                </div>
            </div>
        </div>
        @endfor
    </div>
</div>
