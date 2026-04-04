@extends('layouts.app-sidebar')
@section('title', 'Editar: ' . $contract_template->name)

@section('styles')
<style>
.editor-wrap { display: grid; grid-template-columns: 1fr 280px; gap: 1.25rem; }
.var-chip {
    display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.75rem; font-family: monospace;
    background: var(--bg); border: 1px solid var(--border); border-radius: 4px;
    cursor: pointer; margin: 0.15rem; transition: all 0.15s;
}
.var-chip:hover { background: rgba(102,126,234,0.1); border-color: var(--primary); color: var(--primary); }
@media (max-width: 1024px) { .editor-wrap { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Editar: {{ $contract_template->name }}</h2>
        <p class="text-muted">{{ $contract_template->type_label }}</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.contract-templates.preview', $contract_template) }}" class="btn btn-outline" target="_blank">Vista Previa</a>
        <a href="{{ route('admin.contract-templates.index') }}" class="btn btn-outline">&#8592; Volver</a>
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

<form action="{{ route('admin.contract-templates.update', $contract_template) }}" method="POST">
    @csrf @method('PUT')
    <div class="editor-wrap">
        <div>
            {{-- Basic Info --}}
            <div class="card">
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre <span class="required">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $contract_template->name) }}" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo <span class="required">*</span></label>
                            <select name="type" class="form-select" required>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ old('type', $contract_template->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label" style="display:flex; align-items:center; gap:0.5rem;">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $contract_template->is_active) ? 'checked' : '' }}>
                                Plantilla activa
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Body Editor --}}
            <div class="card">
                <div class="card-header">
                    <h3>Contenido del Contrato (HTML)</h3>
                </div>
                <div class="card-body">
                    <textarea name="body" id="contractBody" class="form-textarea" rows="25" style="font-family:monospace; font-size:0.82rem; line-height:1.6;" required>{{ old('body', $contract_template->body) }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <form method="POST" action="{{ route('admin.contract-templates.destroy', $contract_template) }}" onsubmit="return confirm('Eliminar esta plantilla?')" style="margin-right:auto;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
                <a href="{{ route('admin.contract-templates.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div>
            <div class="card">
                <div class="card-header"><h3>Insertar Variable</h3></div>
                <div class="card-body">
                    <p style="font-size:0.78rem; color:var(--text-muted); margin-bottom:0.5rem;">Haz clic para insertar:</p>
                    <div>
                        @foreach($variables as $var => $desc)
                            <span class="var-chip" onclick="insertVariable('{{ $var }}')" title="{{ $desc }}">{{ $var }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Info</h3></div>
                <div class="card-body" style="font-size:0.82rem; color:var(--text-muted);">
                    <div>Creada: {{ $contract_template->created_at->format('d/m/Y H:i') }}</div>
                    <div>Actualizada: {{ $contract_template->updated_at->format('d/m/Y H:i') }}</div>
                    <div>Contratos generados: {{ $contract_template->contracts()->count() }}</div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
function insertVariable(variable) {
    var editor = tinymce.get('contractBody');
    if (editor) {
        editor.insertContent(variable);
        editor.focus();
    }
}

tinymce.init({
    selector: '#contractBody',
    height: 500,
    menubar: false,
    plugins: 'lists link table code fullscreen',
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link table | code fullscreen',
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; padding: 8px; }',
    branding: false,
    license_key: 'gpl',
    relative_urls: false,
    setup: function(editor) {
        var form = editor.getElement().closest('form');
        if (form) { form.addEventListener('submit', function() { editor.save(); }); }
    }
});
</script>
@endsection
