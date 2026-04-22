@extends('layouts.app-sidebar')
@section('title', 'Descubrir temas')

@section('content')
<div class="page-header">
    <div>
        <h2>Descubrir temas con IA</h2>
        <p class="text-muted">Perplexity y Claude analizan tendencias y tu blog para sugerir los mejores carruseles</p>
    </div>
    <a href="{{ route('admin.carousels.index') }}" class="btn btn-outline">← Volver</a>
</div>

@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom: 1.25rem;">{{ session('error') }}</div>
@endif

<div style="display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem; align-items: start;">

    <form method="POST" action="{{ route('admin.carousels.discovery.discover') }}">
    @csrf

    {{-- Fuentes --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Fuentes de búsqueda</h3>
        </div>
        <div class="card-body" style="display: flex; flex-direction: column; gap: 0.75rem;">

            <label style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.875rem 1rem; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; background: #fff;">
                <input type="checkbox" name="sources[]" value="web" checked style="margin-top: 3px; flex-shrink: 0;">
                <div>
                    <div style="font-weight: 600; font-size: 0.9rem; color: #111827;">
                        🌐 Web — Tendencias CDMX
                        @if(empty(config('services.perplexity.api_key')))
                            <span style="color: #9ca3af; font-weight: 400; font-size: 0.8rem;">(requiere PERPLEXITY_API_KEY)</span>
                        @endif
                    </div>
                    <div style="font-size: 0.8rem; color: #6b7280; margin-top: 3px;">
                        Perplexity busca en internet: noticias del mercado inmobiliario CDMX, tendencias de temporada, factores económicos actuales.
                    </div>
                </div>
            </label>

            <label style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.875rem 1rem; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; background: #fff;">
                <input type="checkbox" name="sources[]" value="blog" checked style="margin-top: 3px; flex-shrink: 0;">
                <div>
                    <div style="font-weight: 600; font-size: 0.9rem; color: #111827;">📝 Blog propio</div>
                    <div style="font-size: 0.8rem; color: #6b7280; margin-top: 3px;">
                        Claude analiza tus últimos 20 artículos del blog y sugiere carruseles que amplíen ese contenido o llenen vacíos para Instagram.
                    </div>
                </div>
            </label>

            <label style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.875rem 1rem; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; background: #fff;" id="manual-label">
                <input type="checkbox" name="sources[]" value="manual" id="manual-checkbox" style="margin-top: 3px; flex-shrink: 0;">
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 0.9rem; color: #111827;">✏️ Tema libre</div>
                    <div style="font-size: 0.8rem; color: #6b7280; margin-top: 3px;">
                        Escribe un tema o contexto y la IA genera 5 variaciones de carruseles con distintos ángulos.
                    </div>
                </div>
            </label>

        </div>
    </div>

    {{-- Free text (shown when manual is checked) --}}
    <div class="card" id="free-text-card" style="margin-bottom: 1.5rem; display: none;">
        <div class="card-header"><h3 class="card-title">Tema o contexto adicional</h3></div>
        <div class="card-body">
            <textarea name="free_text" rows="3" class="form-input" style="width: 100%; resize: vertical;"
                      placeholder="Ej: departamentos en Polanco para inversionistas, ventajas de comprar vs rentar en 2025, zonas con mayor plusvalía en CDMX...">{{ old('free_text') }}</textarea>
            <p style="font-size: 0.78rem; color: #9ca3af; margin-top: 0.4rem;">
                Máximo 500 caracteres. Este contexto también enriquece las búsquedas web y de blog.
            </p>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem; font-size: 1rem;"
            onclick="this.disabled=true; this.textContent='Buscando temas…'; this.form.submit();">
        🔍 Descubrir 10 temas con IA
    </button>

    </form>

    {{-- Info panel --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <div class="card-header"><h3 class="card-title">¿Cómo funciona?</h3></div>
            <div class="card-body">
                <ol style="padding-left: 1.25rem; font-size: 0.85rem; color: #4b5563; line-height: 2; margin: 0;">
                    <li>Perplexity busca tendencias inmobiliarias actuales en CDMX</li>
                    <li>Claude analiza tu blog para detectar vacíos de contenido</li>
                    <li>Se generan hasta 10 sugerencias rankeadas por relevancia</li>
                    <li>Revisas y seleccionas las que quieres</li>
                    <li>Se generan los carruseles completos con IA en lote</li>
                    <li>Las diapositivas se renderizan automáticamente</li>
                </ol>
                <div style="margin-top: 1rem; padding: 0.75rem; background: #f0fdf4; border-radius: 4px; font-size: 0.8rem; color: #166534;">
                    <strong>Tiempo estimado:</strong> 20-60 segundos para descubrir temas, luego 1-3 minutos por carrusel para generar y renderizar.
                </div>
            </div>
        </div>
    </div>

</div>

<script>
const checkbox = document.getElementById('manual-checkbox');
const card     = document.getElementById('free-text-card');
checkbox.addEventListener('change', () => {
    card.style.display = checkbox.checked ? '' : 'none';
});
</script>
@endsection
