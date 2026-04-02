@extends('layouts.app-sidebar')
@section('title', $segment ? 'Editar Segmento' : 'Nuevo Segmento')

@section('styles')
<style>
.seg-form-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; max-width: 780px; overflow: hidden; }
.seg-form-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); font-weight: 700; font-size: 1rem; }
.seg-form-body { padding: 1.5rem; }

.section-label {
    font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.5px; margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.section-label:first-child { margin-top: 0; }

/* Rules builder */
.rule-row {
    display: grid; grid-template-columns: 1fr 140px 1fr 36px; gap: 0.5rem;
    align-items: center; margin-bottom: 0.5rem; padding: 0.5rem 0.75rem;
    background: var(--bg); border-radius: var(--radius);
}
.rule-remove { background: none; border: none; color: var(--danger); cursor: pointer; font-size: 1.1rem; padding: 0.25rem; }
.rule-remove:hover { opacity: 0.7; }

.preview-box {
    background: var(--bg); border-radius: var(--radius); padding: 1rem;
    margin-top: 0.75rem; min-height: 60px;
}
.preview-count { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
.preview-label { font-size: 0.78rem; color: var(--text-muted); }
.preview-list { margin-top: 0.5rem; }
.preview-item { font-size: 0.82rem; padding: 0.2rem 0; }

@media(max-width:640px) { .rule-row { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.segments.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Segmentos</a>
</div>

<div class="seg-form-card">
    <div class="seg-form-header">{{ $segment ? 'Editar Segmento' : 'Nuevo Segmento' }}</div>
    <div class="seg-form-body">
        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:1rem;">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ $segment ? route('admin.segments.update', $segment) : route('admin.segments.store') }}" id="segForm">
            @csrf
            @if($segment) @method('PUT') @endif

            <div class="form-group">
                <label class="form-label">Nombre del segmento <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $segment->name ?? '') }}" required placeholder="Ej: Leads calientes sin operacion">
            </div>

            <div class="form-group">
                <label class="form-label">Descripcion</label>
                <textarea name="description" class="form-textarea" rows="2" placeholder="Describe el proposito de este segmento...">{{ old('description', $segment->description ?? '') }}</textarea>
            </div>

            <div class="form-group" style="margin-bottom:0.5rem;">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" style="width:16px; height:16px; accent-color:var(--primary);"
                        {{ old('is_active', $segment->is_active ?? true) ? 'checked' : '' }}>
                    <span class="form-label" style="margin:0;">Segmento activo</span>
                </label>
            </div>

            <div class="section-label">Reglas de segmentacion</div>
            <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:0.75rem;">Define las condiciones que un cliente debe cumplir para entrar a este segmento. Todas las reglas se evaluan con AND (deben cumplirse todas).</p>

            <div id="rulesContainer">
                {{-- Rules added via JS --}}
            </div>
            <button type="button" class="btn btn-sm btn-outline" onclick="addRule()" style="margin-bottom:1rem;">+ Agregar regla</button>

            {{-- Preview --}}
            <div class="preview-box" id="previewBox">
                <div style="display:flex; align-items:center; gap:1rem;">
                    <div>
                        <div class="preview-count" id="previewCount">-</div>
                        <div class="preview-label">clientes coinciden</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline" onclick="previewSegment()">&#128269; Vista previa</button>
                </div>
                <div class="preview-list" id="previewList"></div>
            </div>

            <div class="form-actions" style="margin-top:1.5rem;">
                <a href="{{ route('admin.segments.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $segment ? 'Guardar Cambios' : 'Crear Segmento' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
var fields = @json($fields);
var operators = @json($operators);
var existingRules = @json(old('rules', $segment->rules ?? []));
var ruleIdx = 0;

function addRule(rule) {
    rule = rule || {};
    var i = ruleIdx++;
    var fieldOpts = Object.entries(fields).map(function(e) {
        return '<option value="'+e[0]+'" '+(rule.field===e[0]?'selected':'')+'>'+e[1]+'</option>';
    }).join('');
    var opOpts = operators.map(function(o) {
        return '<option value="'+o+'" '+(rule.operator===o?'selected':'')+'>'+o+'</option>';
    }).join('');

    var html = '<div class="rule-row" id="rule-'+i+'">'
        + '<select name="rules['+i+'][field]" class="form-select" required>'+fieldOpts+'</select>'
        + '<select name="rules['+i+'][operator]" class="form-select">'+opOpts+'</select>'
        + '<input type="text" name="rules['+i+'][value]" class="form-input" value="'+(rule.value ?? '')+'" placeholder="Valor">'
        + '<button type="button" class="rule-remove" onclick="removeRule('+i+')">&times;</button>'
        + '</div>';
    document.getElementById('rulesContainer').insertAdjacentHTML('beforeend', html);
}

function removeRule(i) {
    var el = document.getElementById('rule-' + i);
    if (el) el.remove();
}

function previewSegment() {
    var rules = [];
    document.querySelectorAll('.rule-row').forEach(function(row) {
        var selects = row.querySelectorAll('select');
        var input = row.querySelector('input[type="text"]');
        rules.push({ field: selects[0].value, operator: selects[1].value, value: input.value });
    });

    fetch('{{ route("admin.segments.preview") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ rules: rules })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('previewCount').textContent = data.count;
        var html = '';
        data.clients.forEach(function(c) {
            html += '<div class="preview-item">' + c.name + (c.email ? ' - ' + c.email : '') + (c.city ? ' (' + c.city + ')' : '') + '</div>';
        });
        document.getElementById('previewList').innerHTML = html || '<div class="preview-item" style="color:var(--text-muted);">Sin coincidencias</div>';
    });
}

// Load existing rules
if (existingRules.length) {
    existingRules.forEach(function(r) { addRule(r); });
} else {
    addRule();
}
</script>
@endsection
