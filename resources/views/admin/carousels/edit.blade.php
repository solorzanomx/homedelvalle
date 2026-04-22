@extends('layouts.app-sidebar')
@section('title', 'Editar Carrusel')

@section('content')
<div class="page-header">
    <div>
        <h2>Editar Carrusel</h2>
        <p class="text-muted">{{ Str::limit($carousel->title, 60) }}</p>
    </div>
    <a href="{{ route('admin.carousels.show', $carousel) }}" class="btn btn-outline">← Ver carrusel</a>
</div>

<form method="POST" action="{{ route('admin.carousels.update', $carousel) }}">
@csrf @method('PUT')

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start;">

    {{-- Columna principal --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <div class="card">
            <div class="card-header"><h3 class="card-title">Información general</h3></div>
            <div class="card-body">

                <div class="form-group">
                    <label class="form-label">Título <span style="color:#ef4444">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $carousel->title) }}"
                           class="form-input @error('title') is-invalid @enderror" required>
                    @error('title')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Tipo <span style="color:#ef4444">*</span></label>
                        <select name="type" class="form-select" required>
                            @foreach(['commercial' => 'Comercial', 'educational' => 'Educativo', 'capture' => 'Captación', 'informative' => 'Informativo', 'branding' => 'Branding'] as $val => $label)
                                <option value="{{ $val }}" {{ old('type', $carousel->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select">
                            @foreach(['draft' => 'Borrador', 'review' => 'En revisión', 'approved' => 'Aprobado', 'archived' => 'Archivado'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $carousel->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">CTA</label>
                    <input type="text" name="cta" value="{{ old('cta', $carousel->cta) }}" class="form-input">
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Caption</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Caption corto <span class="text-muted" style="font-size:0.78rem;">(máx. 280 caracteres)</span></label>
                    <textarea name="caption_short" rows="3" class="form-input" maxlength="280">{{ old('caption_short', $carousel->caption_short) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Caption largo</label>
                    <textarea name="caption_long" rows="4" class="form-input">{{ old('caption_long', $carousel->caption_long) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Diapositivas (solo listado de resumen en fase 1) --}}
        @if($carousel->slides->count() > 0)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Diapositivas ({{ $carousel->slides->count() }})</h3></div>
            <div class="card-body" style="padding: 0;">
                <table class="data-table">
                    <thead><tr><th>#</th><th>Tipo</th><th>Titular</th><th>Render</th></tr></thead>
                    <tbody>
                        @foreach($carousel->slides as $slide)
                        <tr>
                            <td>{{ $slide->order }}</td>
                            <td><span class="badge badge-blue">{{ $slide->type_label }}</span></td>
                            <td>{{ Str::limit($slide->headline ?? '—', 50) }}</td>
                            <td>
                                @if($slide->render_status === 'done')
                                    <span class="badge badge-green">Listo</span>
                                @elseif($slide->render_status === 'failed')
                                    <span class="badge badge-red">Error</span>
                                @else
                                    <span class="badge badge-yellow">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- Columna lateral --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <div class="card">
            <div class="card-header"><h3 class="card-title">Plantilla visual</h3></div>
            <div class="card-body">
                <select name="template_id" class="form-select">
                    <option value="">Sin plantilla</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" {{ old('template_id', $carousel->template_id) == $template->id ? 'selected' : '' }}>
                            {{ $template->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Guardar cambios</button>
                <a href="{{ route('admin.carousels.show', $carousel) }}" class="btn btn-outline" style="width: 100%; text-align: center;">Cancelar</a>
            </div>
        </div>

    </div>
</div>
</form>

{{-- Zona de peligro FUERA del form principal para evitar submit accidental --}}
<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;margin-top:0;">
    <div></div>
    <div class="card" style="border-color:#fecaca;">
        <div class="card-body">
            <p style="font-size:.82rem;color:#6b7280;margin-bottom:.75rem;">Zona de peligro</p>
            <form method="POST" action="{{ route('admin.carousels.destroy', $carousel) }}"
                  onsubmit="return confirm('¿Eliminar este carrusel y todas sus diapositivas? Esta acción no se puede deshacer.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger-outline" style="width:100%;">Eliminar carrusel</button>
            </form>
        </div>
    </div>
</div>
@endsection
