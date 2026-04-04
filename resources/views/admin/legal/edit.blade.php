@extends('layouts.app-sidebar')
@section('title', 'Editar: ' . $document->title)

@section('content')
<div class="page-header">
    <div>
        <h2>Editar: {{ $document->title }}</h2>
        <p class="text-muted">Version actual: v{{ $document->currentVersion?->version_number ?? '1' }}</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.legal.show', $document) }}" class="btn btn-outline">Ver Documento</a>
        <a href="{{ route('admin.legal.index') }}" class="btn btn-outline">&#8592; Volver</a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-error" style="margin-bottom:1.25rem;">
        <div>
            <strong>Errores:</strong>
            <ul style="margin:0.5rem 0 0 1.25rem; font-size:0.85rem;">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ route('admin.legal.update', $document) }}" method="POST" id="legalEditForm">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            <h3>Informacion del Documento</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Titulo <span class="required">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $document->title) }}" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo <span class="required">*</span></label>
                    <select name="type" class="form-select" required>
                        @foreach(\App\Models\LegalDocument::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $document->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Meta Descripcion</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $document->meta_description) }}" class="form-input" placeholder="Breve descripcion para SEO y listados">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="draft" {{ old('status', $document->status) === 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="published" {{ old('status', $document->status) === 'published' ? 'selected' : '' }}>Publicado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:0.5rem; margin-top:1.6rem;">
                        <input type="checkbox" name="is_public" value="1" {{ old('is_public', $document->is_public) ? 'checked' : '' }}>
                        Visible al publico
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Contenido</h3>
            <span class="badge badge-blue" id="contentStatus">Sin cambios</span>
        </div>
        <div class="card-body">
            <textarea name="content" id="wysiwygEditor" class="form-textarea" rows="20">{{ old('content', $document->currentVersion?->content) }}</textarea>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Notas del Cambio</h3>
        </div>
        <div class="card-body">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Notas del cambio</label>
                <input type="text" name="change_notes" value="{{ old('change_notes') }}" class="form-input" placeholder="Ej: Actualizacion de clausulas de privacidad">
                <div class="form-hint">Opcional. Describe brevemente que cambio en esta version para llevar un registro.</div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <form method="POST" action="{{ route('admin.legal.destroy', $document) }}" onsubmit="return confirm('Eliminar este documento legal y todas sus versiones?')" style="margin-right:auto;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">Eliminar</button>
        </form>
        <a href="{{ route('admin.legal.index') }}" class="btn btn-outline">Cancelar</a>
        <button type="submit" class="btn btn-primary" id="submitBtn">Guardar Cambios</button>
    </div>
</form>

{{-- Version History --}}
<div class="card" style="margin-top:1.5rem;">
    <div class="card-header">
        <h3>Historial de Versiones</h3>
        <span class="badge badge-blue">{{ $document->versions->count() }} versiones</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Notas</th>
                        <th>Creado por</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($document->versions->sortByDesc('version_number') as $version)
                    <tr>
                        <td style="font-weight:500;">v{{ $version->version_number }}</td>
                        <td class="text-muted">{{ $version->change_notes ?? '-' }}</td>
                        <td class="text-muted">{{ $version->creator?->name ?? 'Sistema' }}</td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $version->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($version->is_active)
                                <span class="badge badge-green">Activa</span>
                            @else
                                <span class="badge badge-yellow">Anterior</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding:1.5rem;">Sin versiones registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Info Footer --}}
<div style="margin-top:1rem; padding:1rem; background:var(--bg); border-radius:var(--radius); font-size:0.82rem; color:var(--text-muted); display:flex; gap:2rem; flex-wrap:wrap;">
    <div>Creado: <strong>{{ $document->created_at->format('d/m/Y H:i') }}</strong></div>
    <div>Version actual: <strong>v{{ $document->currentVersion?->version_number ?? '0' }}</strong></div>
    <div>Total versiones: <strong>{{ $document->versions->count() }}</strong></div>
    <div>Creado por: <strong>{{ $document->creator?->name ?? 'Sistema' }}</strong></div>
</div>
@endsection

@section('scripts')
<script src="/vendor/tinymce/tinymce.min.js"></script>
<script>
    var originalContent = @json($document->currentVersion?->content ?? '');
    var contentChanged = false;

    tinymce.init({
        selector: '#wysiwygEditor',
        height: 500,
        license_key: 'gpl',
        branding: false,
        plugins: 'lists link table code fullscreen',
        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link table | code fullscreen',
        menubar: false,
        statusbar: true,
        content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; padding: 1rem; }',
        setup: function(editor) {
            editor.on('change keyup', function() {
                editor.save();
                var currentContent = editor.getContent();
                var badge = document.getElementById('contentStatus');
                var btn = document.getElementById('submitBtn');

                if (currentContent !== originalContent) {
                    contentChanged = true;
                    badge.textContent = 'Contenido modificado';
                    badge.className = 'badge badge-yellow';
                    btn.textContent = 'Guardar y crear nueva version';
                } else {
                    contentChanged = false;
                    badge.textContent = 'Sin cambios';
                    badge.className = 'badge badge-blue';
                    btn.textContent = 'Guardar Cambios';
                }
            });
        }
    });
</script>
@endsection
