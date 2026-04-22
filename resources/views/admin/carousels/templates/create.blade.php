@extends('layouts.app-sidebar')
@section('title', 'Nueva Plantilla')

@section('content')
<div class="page-header">
    <div>
        <h2>Nueva Plantilla</h2>
        <p class="text-muted">Define una plantilla visual para carruseles de Instagram</p>
    </div>
    <a href="{{ route('admin.carousels.templates.index') }}" class="btn btn-outline">← Volver</a>
</div>

<form method="POST" action="{{ route('admin.carousels.templates.store') }}">
@csrf

<div style="display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem; align-items: start;">

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <div class="card">
            <div class="card-header"><h3 class="card-title">Identificación</h3></div>
            <div class="card-body">

                <div class="form-group">
                    <label class="form-label">Nombre <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-input @error('name') is-invalid @enderror" required
                           placeholder="Ej: Premium Dark">
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Slug <span class="text-muted" style="font-size:0.78rem;">(se genera automáticamente)</span></label>
                        <input type="text" name="slug" value="{{ old('slug') }}"
                               class="form-input" placeholder="premium-dark">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Canvas size <span style="color:#ef4444">*</span></label>
                        <input type="text" name="canvas_size" value="{{ old('canvas_size', '1080x1080') }}"
                               class="form-input" required placeholder="1080x1080">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Vista Blade <span style="color:#ef4444">*</span></label>
                    <input type="text" name="blade_view" value="{{ old('blade_view') }}"
                           class="form-input @error('blade_view') is-invalid @enderror" required
                           placeholder="carousels.templates.premium-dark">
                    <p class="text-muted" style="font-size: 0.78rem; margin-top: 4px;">
                        Ruta de la vista Blade, ej: <code>carousels.templates.premium-dark</code>
                    </p>
                    @error('blade_view')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" rows="2" class="form-input"
                              placeholder="Descripción breve de la plantilla...">{{ old('description') }}</textarea>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Configuración avanzada</h3></div>
            <div class="card-body">

                <div class="form-group">
                    <label class="form-label">Variables por defecto <span class="text-muted" style="font-size:0.78rem;">(JSON)</span></label>
                    <textarea name="default_vars" rows="4" class="form-input" style="font-family: monospace; font-size: 0.82rem;"
                              placeholder='{"font": "Georgia", "overlay_opacity": 60}'>{{ old('default_vars') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tipos de slide soportados</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.25rem;">
                        @foreach(['cover', 'problem', 'key_stat', 'explanation', 'benefit', 'example', 'social_proof', 'cta'] as $slideType)
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" name="supported_types[]" value="{{ $slideType }}"
                                       {{ in_array($slideType, old('supported_types', [])) ? 'checked' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $slideType)) }}
                            </label>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <div class="card">
            <div class="card-header"><h3 class="card-title">Opciones</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Orden</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-input" min="0">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.88rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        Plantilla activa
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Crear plantilla</button>
                <a href="{{ route('admin.carousels.templates.index') }}" class="btn btn-outline" style="width: 100%; text-align: center;">Cancelar</a>
            </div>
        </div>

    </div>
</div>
</form>
@endsection
