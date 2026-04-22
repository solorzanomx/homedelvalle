@extends('layouts.app-sidebar')
@section('title', 'Nuevo Carrusel')

@section('content')
<div class="page-header">
    <div>
        <h2>Nuevo Carrusel</h2>
        <p class="text-muted">Configura el carrusel de Instagram</p>
    </div>
    <a href="{{ route('admin.carousels.index') }}" class="btn btn-outline">← Volver</a>
</div>

<form method="POST" action="{{ route('admin.carousels.store') }}">
@csrf

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start;">

    {{-- Columna principal --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <div class="card">
            <div class="card-header"><h3 class="card-title">Información general</h3></div>
            <div class="card-body">

                <div class="form-group">
                    <label class="form-label">Título del carrusel <span style="color:#ef4444">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="form-input @error('title') is-invalid @enderror"
                           placeholder="Ej: 5 razones para invertir en Coyoacán" required>
                    @error('title')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Tipo de carrusel <span style="color:#ef4444">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            @foreach(['commercial' => 'Comercial', 'educational' => 'Educativo', 'capture' => 'Captación', 'informative' => 'Informativo', 'branding' => 'Branding'] as $val => $label)
                                <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fuente</label>
                        <select name="source_type" class="form-select">
                            <option value="">Libre (sin fuente)</option>
                            <option value="property" {{ old('source_type') === 'property' ? 'selected' : '' }}>Propiedad</option>
                            <option value="blog_post" {{ old('source_type') === 'blog_post' ? 'selected' : '' }}>Artículo del blog</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">CTA (llamada a la acción)</label>
                    <input type="text" name="cta" value="{{ old('cta') }}"
                           class="form-input" placeholder="Ej: Agenda una visita hoy · link en bio">
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Caption</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Caption corto <span class="text-muted" style="font-size:0.78rem;">(máx. 280 caracteres)</span></label>
                    <textarea name="caption_short" rows="3"
                              class="form-input @error('caption_short') is-invalid @enderror"
                              maxlength="280"
                              placeholder="Caption principal para Instagram...">{{ old('caption_short') }}</textarea>
                    @error('caption_short')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Caption largo <span class="text-muted" style="font-size:0.78rem;">(opcional)</span></label>
                    <textarea name="caption_long" rows="4"
                              class="form-input"
                              placeholder="Versión extendida o alternativa del caption...">{{ old('caption_long') }}</textarea>
                </div>
            </div>
        </div>

    </div>

    {{-- Columna lateral --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <div class="card">
            <div class="card-header"><h3 class="card-title">Plantilla visual</h3></div>
            <div class="card-body">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Seleccionar plantilla</label>
                    <select name="template_id" class="form-select">
                        <option value="">Sin plantilla</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($templates->isEmpty())
                        <p class="text-muted" style="font-size: 0.8rem; margin-top: 0.5rem;">
                            No hay plantillas activas.
                            <a href="{{ route('admin.carousels.templates.create') }}">Crear una</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Crear carrusel
                </button>
                <a href="{{ route('admin.carousels.index') }}" class="btn btn-outline" style="width: 100%; text-align: center;">
                    Cancelar
                </a>
            </div>
        </div>

    </div>
</div>
</form>
@endsection
