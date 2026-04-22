@extends('layouts.app-sidebar')
@section('title', 'Editar Plantilla')

@section('content')
<div class="page-header">
    <div>
        <h2>Editar Plantilla</h2>
        <p class="text-muted">{{ $template->name }}</p>
    </div>
    <a href="{{ route('admin.carousels.templates.index') }}" class="btn btn-outline">← Volver</a>
</div>

<form method="POST" action="{{ route('admin.carousels.templates.update', $template) }}">
@csrf @method('PUT')

<div style="display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem; align-items: start;">

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <div class="card">
            <div class="card-header"><h3 class="card-title">Identificación</h3></div>
            <div class="card-body">

                <div class="form-group">
                    <label class="form-label">Nombre <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $template->name) }}"
                           class="form-input @error('name') is-invalid @enderror" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $template->slug) }}" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Canvas size</label>
                        <input type="text" name="canvas_size" value="{{ old('canvas_size', $template->canvas_size) }}" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Vista Blade <span style="color:#ef4444">*</span></label>
                    <input type="text" name="blade_view" value="{{ old('blade_view', $template->blade_view) }}"
                           class="form-input @error('blade_view') is-invalid @enderror" required>
                    @error('blade_view')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" rows="2" class="form-input">{{ old('description', $template->description) }}</textarea>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Configuración avanzada</h3></div>
            <div class="card-body">

                <div class="form-group">
                    <label class="form-label">Variables por defecto <span class="text-muted" style="font-size:0.78rem;">(JSON)</span></label>
                    <textarea name="default_vars" rows="4" class="form-input" style="font-family: monospace; font-size: 0.82rem;">{{ old('default_vars', $template->default_vars ? json_encode($template->default_vars, JSON_PRETTY_PRINT) : '') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tipos de slide soportados</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.25rem;">
                        @foreach(['cover', 'problem', 'key_stat', 'explanation', 'benefit', 'example', 'social_proof', 'cta'] as $slideType)
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" name="supported_types[]" value="{{ $slideType }}"
                                       {{ in_array($slideType, old('supported_types', $template->supported_types ?? [])) ? 'checked' : '' }}>
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
                    <input type="number" name="sort_order" value="{{ old('sort_order', $template->sort_order) }}" class="form-input" min="0">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.88rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                        Plantilla activa
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Guardar cambios</button>
                <a href="{{ route('admin.carousels.templates.index') }}" class="btn btn-outline" style="width: 100%; text-align: center;">Cancelar</a>
            </div>
        </div>

        <div class="card" style="border-color: #fecaca;">
            <div class="card-body">
                <p style="font-size: 0.82rem; color: #6b7280; margin-bottom: 0.75rem;">
                    Esta plantilla tiene <strong>{{ $template->posts()->count() }}</strong> carrusel(es).
                </p>
                <form method="POST" action="{{ route('admin.carousels.templates.destroy', $template) }}"
                      onsubmit="return confirm('¿Eliminar esta plantilla?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger-outline" style="width: 100%;">Eliminar plantilla</button>
                </form>
            </div>
        </div>

    </div>
</div>
</form>
@endsection
