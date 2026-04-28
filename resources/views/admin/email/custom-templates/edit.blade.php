@extends('layouts.app-sidebar')

@section('title', 'Editar Template')

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">{{ $template->name }}</h1>
        <div style="display:flex;align-items:center;gap:0.75rem;margin-top:0.35rem">
            @if($template->isDraft())
                <span class="badge" style="background:#f1f5f9;color:#64748b">Borrador</span>
            @elseif($template->isPublished())
                <span class="badge badge-green">Publicado</span>
            @else
                <span class="badge badge-red">Archivado</span>
            @endif
            <span style="color:var(--text-muted);font-size:0.8rem">Creado por {{ $template->creator->name ?? 'N/A' }} · {{ $template->created_at->format('d M Y') }}</span>
        </div>
    </div>
    <a href="{{ route('admin.custom-templates.index') }}" class="btn btn-outline">← Volver</a>
</div>

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;color:#065f46;font-size:0.85rem">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;color:#991b1b;font-size:0.85rem">
    <ul style="margin:0;padding-left:1.2rem">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start">

    <!-- Main Column -->
    <div>
        <!-- Editor -->
        <form method="POST" action="{{ route('admin.custom-templates.update', $template) }}">
            @csrf @method('PUT')
            <div class="card">
                <div class="card-header"><h3>Editar Template</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Nombre <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-textarea" rows="2">{{ old('description', $template->description) }}</textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Tipo</label>
                            <select name="template_type" class="form-select">
                                <option value="custom"      {{ old('template_type', $template->template_type) === 'custom'      ? 'selected' : '' }}>Custom</option>
                                <option value="marketing"   {{ old('template_type', $template->template_type) === 'marketing'   ? 'selected' : '' }}>Marketing</option>
                                <option value="newsletter"  {{ old('template_type', $template->template_type) === 'newsletter'  ? 'selected' : '' }}>Newsletter</option>
                                <option value="promotional" {{ old('template_type', $template->template_type) === 'promotional' ? 'selected' : '' }}>Promocional</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select">
                                <option value="draft"     {{ old('status', $template->status) === 'draft'     ? 'selected' : '' }}>Borrador</option>
                                <option value="published" {{ old('status', $template->status) === 'published' ? 'selected' : '' }}>Publicado</option>
                                <option value="archived"  {{ old('status', $template->status) === 'archived'  ? 'selected' : '' }}>Archivado</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Asunto <span style="color:var(--danger)">*</span>
                            <span style="color:var(--text-muted);font-weight:400;font-size:0.75rem;margin-left:0.5rem">(soporta &#123;&#123;placeholders&#125;&#125;)</span>
                        </label>
                        <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Preview text</label>
                        <input type="text" name="preview_text" value="{{ old('preview_text', $template->preview_text) }}" class="form-input" maxlength="150">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cuerpo HTML <span style="color:var(--danger)">*</span></label>
                        <textarea name="html_body" id="html_body" class="form-textarea" rows="18" style="font-family:monospace;font-size:0.8rem" required>{{ old('html_body', $template->html_body) }}</textarea>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:0.75rem;margin-bottom:1.5rem">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="{{ route('admin.custom-templates.clone', $template) }}" class="btn btn-outline" onclick="return confirm('¿Clonar este template?')">Clonar</a>
            </div>
        </form>

        <!-- Test Email -->
        <div class="card">
            <div class="card-header"><h3>Enviar Email de Prueba</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.custom-templates.test', $template) }}">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Email destino</label>
                            <input type="email" name="test_email" value="{{ old('test_email', auth()->user()->email) }}" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Datos de muestra</label>
                            <select name="dataset" class="form-select">
                                <option value="generic">Genérico</option>
                                <option value="seller">Vendedor</option>
                                <option value="buyer">Comprador</option>
                                <option value="developer">Desarrollador</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar prueba</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Placeholders -->
        <div class="card" style="margin-bottom:1rem">
            <div class="card-header"><h3>Placeholders</h3></div>
            <div class="card-body" style="padding:1rem">
                <div style="display:flex;flex-direction:column;gap:0.5rem">
                    @foreach([
                        ['nombre',  'Nombre'],
                        ['email',   'Correo'],
                        ['colonia', 'Colonia'],
                        ['precio',  'Precio'],
                        ['fecha',   'Fecha'],
                        ['folio',   'Folio'],
                    ] as [$key, $desc])
                    <div style="display:flex;align-items:center;gap:0.5rem;cursor:pointer" onclick="insertPlaceholder('{{ $key }}')" title="Click para insertar">
                        <code style="background:var(--bg);border:1px solid var(--border);padding:0.2rem 0.4rem;border-radius:4px;font-size:0.72rem;color:var(--primary)">&#123;&#123;{{ $key }}&#125;&#125;</code>
                        <span style="font-size:0.8rem;color:var(--text-muted)">{{ $desc }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Assignments -->
        <div class="card">
            <div class="card-header">
                <h3>Asignaciones</h3>
                <span class="badge badge-blue">{{ $assignments->count() }}</span>
            </div>
            <div class="card-body" style="padding:0">
                @if($assignments->count() > 0)
                <div style="max-height:280px;overflow-y:auto">
                    @foreach($assignments as $assignment)
                    <div style="padding:0.75rem 1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:0.5rem">
                        <div style="min-width:0">
                            <p style="font-size:0.82rem;font-weight:600;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $assignment->trigger_name }}</p>
                            <p style="font-size:0.73rem;color:var(--text-muted);margin:0.1rem 0 0">{{ ucfirst(str_replace('_', ' ', $assignment->trigger_type)) }}</p>
                        </div>
                        <div style="display:flex;gap:0.35rem;flex-shrink:0">
                            <form method="POST" action="{{ route('admin.custom-templates.assignments.toggle', [$template, $assignment]) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm" style="padding:0.2rem 0.6rem;font-size:0.72rem;{{ $assignment->is_active ? 'background:#ecfdf5;color:#065f46;border-color:#a7f3d0' : 'background:var(--bg);color:var(--text-muted);border-color:var(--border)' }}">
                                    {{ $assignment->is_active ? 'Activo' : 'Inactivo' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.custom-templates.assignments.destroy', [$template, $assignment]) }}" style="display:inline" onsubmit="return confirm('¿Eliminar asignación?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding:0.2rem 0.5rem">✕</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:0.85rem">Sin asignaciones</div>
                @endif
                <div style="padding:0.75rem 1rem;border-top:1px solid var(--border)">
                    <button type="button" onclick="document.getElementById('assignModal').style.display='flex'" class="btn btn-primary" style="width:100%;justify-content:center">
                        + Agregar asignación
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div id="assignModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:50;align-items:center;justify-content:center">
    <div style="background:var(--card);border-radius:10px;padding:1.5rem;width:100%;max-width:440px;margin:1rem">
        <h3 style="margin:0 0 1.25rem;font-size:1rem;font-weight:600">Asignar a Evento</h3>
        <form method="POST" action="{{ route('admin.custom-templates.assignments.store', $template) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Tipo de evento</label>
                <select id="trigger_type" name="trigger_type" class="form-select" onchange="updateTriggerNames()" required>
                    <option value="">Selecciona tipo...</option>
                    <option value="event">Evento del sistema</option>
                    <option value="form_submission">Envío de formulario</option>
                    <option value="user_action">Acción de usuario</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre del evento</label>
                <select id="trigger_name" name="trigger_name" class="form-select" required>
                    <option value="">Selecciona un evento...</option>
                </select>
            </div>
            <div style="display:flex;gap:0.75rem;margin-top:1.25rem">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Asignar</button>
                <button type="button" onclick="document.getElementById('assignModal').style.display='none'" class="btn btn-outline" style="flex:1;justify-content:center">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function insertPlaceholder(key) {
    const t = document.getElementById('html_body');
    const s = t.selectionStart, e = t.selectionEnd;
    t.value = t.value.substring(0,s) + '{{'+key+'}}' + t.value.substring(e);
    t.selectionStart = t.selectionEnd = s + key.length + 4;
    t.focus();
}

const triggers = {
    event: {
        FormSubmitted: 'Formulario enviado',
        UserCreated: 'Usuario creado',
        UserActivated: 'Usuario activado',
        LeadAssigned: 'Lead asignado',
        PropertyListed: 'Propiedad listada',
    },
    form_submission: {
        seller_valuation: 'Solicitud de valuación',
        buyer_search: 'Búsqueda de comprador',
        contact_form: 'Formulario de contacto',
        developer_brief: 'Briefing de desarrollador',
    },
    user_action: {
        first_login: 'Primer acceso',
        profile_updated: 'Perfil actualizado',
        password_changed: 'Contraseña cambiada',
        document_uploaded: 'Documento cargado',
    },
};

function updateTriggerNames() {
    const type = document.getElementById('trigger_type').value;
    const sel = document.getElementById('trigger_name');
    sel.innerHTML = '<option value="">Selecciona un evento...</option>';
    if (triggers[type]) {
        Object.entries(triggers[type]).forEach(([k, v]) => {
            sel.innerHTML += `<option value="${k}">${v}</option>`;
        });
    }
}

document.getElementById('assignModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
@endsection
