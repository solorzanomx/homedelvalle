@extends('layouts.app-sidebar')
@section('title', 'Nuevo Documento Legal')

@section('content')
<div class="page-header">
    <div>
        <h2>Nuevo Documento Legal</h2>
        <p class="text-muted">Crear aviso de privacidad, terminos u otro documento legal</p>
    </div>
    <a href="{{ route('admin.legal.index') }}" class="btn btn-outline">&#8592; Volver</a>
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

<form action="{{ route('admin.legal.store') }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header">
            <h3>Informacion del Documento</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Titulo <span class="required">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-input" required placeholder="Ej: Aviso de Privacidad">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo <span class="required">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="">Seleccionar tipo...</option>
                        @foreach(\App\Models\LegalDocument::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Meta Descripcion</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description') }}" class="form-input" placeholder="Breve descripcion para SEO y listados">
                    <div class="form-hint">Opcional. Se usa en la pagina publica y buscadores.</div>
                </div>
                <div class="form-group full-width">
                    <label class="form-label" style="display:flex; align-items:center; gap:0.5rem;">
                        <input type="checkbox" name="is_public" value="1" {{ old('is_public', '1') ? 'checked' : '' }}>
                        Visible al publico
                    </label>
                    <div class="form-hint">Si esta marcado, el documento sera accesible en /legal/slug</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Contenido</h3>
        </div>
        <div class="card-body">
            <textarea name="content" id="wysiwygEditor" class="form-textarea" rows="20">{{ old('content') }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.legal.index') }}" class="btn btn-outline">Cancelar</a>
        <button type="submit" class="btn btn-primary">Crear Documento</button>
    </div>
</form>
@endsection

@section('scripts')
<script src="/vendor/tinymce/tinymce.min.js"></script>
<script>
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
            editor.on('change', function() {
                editor.save();
            });
        }
    });
</script>
@endsection
