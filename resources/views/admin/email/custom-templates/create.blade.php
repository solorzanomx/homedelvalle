@extends('layouts.app-sidebar')

@section('title', 'Nuevo Email Template')

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">Nuevo Email Template</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">Crea una nueva plantilla de correo con placeholders dinámicos</p>
    </div>
    <a href="{{ route('admin.custom-templates.index') }}" class="btn btn-outline">← Volver</a>
</div>

<form method="POST" action="{{ route('admin.custom-templates.store') }}" style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start">
    @csrf

    <!-- Main Column -->
    <div>
        <div class="card">
            <div class="card-header"><h3>Información del Template</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Nombre del Template <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Ej. Newsletter Abril 2026" required>
                    @error('name')<p style="color:var(--danger);font-size:0.78rem;margin-top:0.25rem">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-textarea" rows="2" placeholder="Descripción opcional del template...">{{ old('description') }}</textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Tipo <span style="color:var(--danger)">*</span></label>
                        <select name="template_type" class="form-select" required>
                            <option value="custom"      {{ old('template_type') === 'custom'      ? 'selected' : '' }}>Custom</option>
                            <option value="marketing"   {{ old('template_type') === 'marketing'   ? 'selected' : '' }}>Marketing</option>
                            <option value="newsletter"  {{ old('template_type') === 'newsletter'  ? 'selected' : '' }}>Newsletter</option>
                            <option value="promotional" {{ old('template_type') === 'promotional' ? 'selected' : '' }}>Promocional</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado <span style="color:var(--danger)">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="draft"     {{ old('status', 'draft') === 'draft'     ? 'selected' : '' }}>Borrador</option>
                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Publicar ahora</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Asunto del correo <span style="color:var(--danger)">*</span>
                        <span style="color:var(--text-muted);font-weight:400;font-size:0.75rem;margin-left:0.5rem">(soporta &#123;&#123;placeholders&#125;&#125;)</span>
                    </label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="form-input" placeholder="Ej. Hola &#123;&#123;nombre&#125;&#125;, tenemos una oferta para ti" required>
                    @error('subject')<p style="color:var(--danger);font-size:0.78rem;margin-top:0.25rem">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Preview text <span style="color:var(--text-muted);font-weight:400;font-size:0.75rem">(máx 150 chars)</span></label>
                    <input type="text" name="preview_text" value="{{ old('preview_text') }}" class="form-input" maxlength="150" placeholder="Texto corto visible en cliente de correo">
                </div>

                <div class="form-group">
                    <label class="form-label">Cuerpo HTML <span style="color:var(--danger)">*</span></label>
                    <textarea name="html_body" id="html_body" class="form-textarea" rows="16" style="font-family:monospace;font-size:0.8rem" required>{{ old('html_body') }}</textarea>
                    @error('html_body')<p style="color:var(--danger);font-size:0.78rem;margin-top:0.25rem">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div style="display:flex;gap:0.75rem">
            <button type="submit" class="btn btn-primary">Guardar Template</button>
            <a href="{{ route('admin.custom-templates.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <div class="card">
            <div class="card-header"><h3>Placeholders disponibles</h3></div>
            <div class="card-body" style="padding:1rem">
                <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.75rem">Usa estas variables en el asunto y cuerpo del correo:</p>
                <div style="display:flex;flex-direction:column;gap:0.5rem">
                    @foreach([
                        ['nombre',  'Nombre del destinatario'],
                        ['email',   'Correo electrónico'],
                        ['colonia', 'Colonia / zona'],
                        ['precio',  'Precio del inmueble'],
                        ['fecha',   'Fecha'],
                        ['folio',   'Número de folio'],
                    ] as [$key, $desc])
                    <div style="display:flex;align-items:center;gap:0.5rem;cursor:pointer" onclick="insertPlaceholder('{{ $key }}')" title="Click para insertar">
                        <code style="background:var(--bg);border:1px solid var(--border);padding:0.2rem 0.5rem;border-radius:4px;font-size:0.75rem;color:var(--primary);white-space:nowrap">&#123;&#123;{{ $key }}&#125;&#125;</code>
                        <span style="font-size:0.8rem;color:var(--text-muted)">{{ $desc }}</span>
                    </div>
                    @endforeach
                </div>
                <p style="font-size:0.75rem;color:var(--text-muted);margin-top:0.75rem">↑ Click en un placeholder para insertarlo en el cuerpo</p>
            </div>
        </div>
    </div>
</form>

<script>
function insertPlaceholder(key) {
    const textarea = document.getElementById('html_body');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + '{{' + key + '}}' + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + key.length + 4;
    textarea.focus();
}
</script>
@endsection
