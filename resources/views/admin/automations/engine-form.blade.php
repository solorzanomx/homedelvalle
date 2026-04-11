@extends('layouts.app-sidebar')
@section('title', $automation ? 'Editar Automatizacion' : 'Nueva Automatizacion')

@section('styles')
<style>
.af-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; max-width: 820px; overflow: hidden; }
.af-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); font-weight: 700; }
.af-body { padding: 1.5rem; }
.section-label { font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem; border-bottom: 1px solid var(--border); }
.section-label:first-child { margin-top: 0; }

/* Trigger cards */
.trigger-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.5rem; margin-bottom: 0.75rem; }
.trigger-card { padding: 0.6rem; border: 2px solid var(--border); border-radius: var(--radius); text-align: center; cursor: pointer; transition: all 0.15s; position: relative; }
.trigger-card:hover { border-color: var(--primary); }
.trigger-card.active { border-color: var(--primary); background: rgba(102,126,234,0.04); }
.trigger-card input { position: absolute; opacity: 0; pointer-events: none; }
.trigger-card-label { font-size: 0.78rem; font-weight: 600; }
.trigger-card-desc { font-size: 0.65rem; color: var(--text-muted); margin-top: 0.1rem; }

/* Steps builder */
.step-list { margin-bottom: 1rem; }
.step-item {
    display: grid; grid-template-columns: 36px 1fr 36px; gap: 0.5rem; align-items: start;
    margin-bottom: 0.5rem; padding: 0.75rem; background: var(--bg); border-radius: var(--radius);
    border-left: 3px solid var(--primary); position: relative;
}
.step-num { width: 28px; height: 28px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }
.step-content { min-width: 0; }
.step-type-select { margin-bottom: 0.5rem; }
.step-config { display: grid; gap: 0.4rem; }
.step-remove { background: none; border: none; color: var(--danger); cursor: pointer; font-size: 1.1rem; }

.step-item[data-type="delay"] { border-left-color: #f59e0b; }
.step-item[data-type="send_email"] { border-left-color: #3b82f6; }
.step-item[data-type="send_whatsapp"] { border-left-color: #25d366; }
.step-item[data-type="condition"] { border-left-color: #ec4899; }
.step-item[data-type="create_task"] { border-left-color: #6366f1; }
.step-item[data-type="move_pipeline"] { border-left-color: #10b981; }
.step-item[data-type="add_score"] { border-left-color: #8b5cf6; }

.connector { text-align: center; color: var(--text-muted); font-size: 0.75rem; margin: -0.15rem 0; padding-left: 36px; }
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.automations-engine.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Automatizaciones</a>
</div>

<div class="af-card">
    <div class="af-header">{{ $automation ? 'Editar: ' . $automation->name : 'Nueva Automatizacion' }}</div>
    <div class="af-body">
        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:1rem;">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif

        <form method="POST" action="{{ $automation ? route('admin.automations-engine.update', $automation) : route('admin.automations-engine.store') }}" id="autoForm">
            @csrf
            @if($automation) @method('PUT') @endif

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $automation->name ?? '') }}" required placeholder="Ej: Nutricion de leads frios">
                </div>
                <div class="form-group">
                    <label class="form-label">Descripcion</label>
                    <input type="text" name="description" class="form-input" value="{{ old('description', $automation->description ?? '') }}" placeholder="Breve descripcion...">
                </div>
            </div>

            <div style="display:flex; gap:1rem; margin-bottom:0.5rem;">
                <label style="display:flex; align-items:center; gap:0.4rem; cursor:pointer;"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" style="accent-color:var(--primary);" {{ old('is_active', $automation->is_active ?? false) ? 'checked' : '' }}> <span class="form-label" style="margin:0; font-size:0.82rem;">Activa</span></label>
                <label style="display:flex; align-items:center; gap:0.4rem; cursor:pointer;"><input type="hidden" name="allow_reentry" value="0"><input type="checkbox" name="allow_reentry" value="1" style="accent-color:var(--primary);" {{ old('allow_reentry', $automation->allow_reentry ?? false) ? 'checked' : '' }}> <span class="form-label" style="margin:0; font-size:0.82rem;">Permitir reentrada</span></label>
            </div>

            <div class="section-label">Trigger (disparador)</div>
            @php
                $triggerMeta = [
                    'form_submitted'  => ['icon' => '&#128233;', 'desc' => 'Cuando un lead envia un formulario del sitio'],
                    'new_client'      => ['icon' => '&#128100;', 'desc' => 'Cuando se crea un cliente nuevo'],
                    'segment_enter'   => ['icon' => '&#128202;', 'desc' => 'Cuando un cliente entra a un segmento'],
                    'segment_exit'    => ['icon' => '&#128201;', 'desc' => 'Cuando un cliente sale de un segmento'],
                    'stage_change'    => ['icon' => '&#8594;',   'desc' => 'Cuando cambia de etapa en pipeline'],
                    'manual'          => ['icon' => '&#9998;',   'desc' => 'Inscripcion manual por el admin'],
                    'score_threshold' => ['icon' => '&#11088;',  'desc' => 'Cuando el lead score alcanza un valor'],
                    'inactivity'      => ['icon' => '&#9203;',   'desc' => 'Despues de X dias sin actividad'],
                ];
            @endphp
            <div class="trigger-cards">
                @foreach($triggers as $val => $label)
                <label class="trigger-card {{ old('trigger_type', $automation->trigger_type ?? '') === $val ? 'active' : '' }}" onclick="this.closest('.trigger-cards').querySelectorAll('.trigger-card').forEach(c=>c.classList.remove('active')); this.classList.add('active'); showTriggerConfig('{{ $val }}');">
                    <input type="radio" name="trigger_type" value="{{ $val }}" {{ old('trigger_type', $automation->trigger_type ?? '') === $val ? 'checked' : '' }} required>
                    <div style="font-size:1.2rem; margin-bottom:0.15rem;">{!! $triggerMeta[$val]['icon'] ?? '&#9889;' !!}</div>
                    <div class="trigger-card-label">{{ $label }}</div>
                    <div class="trigger-card-desc">{{ $triggerMeta[$val]['desc'] ?? '' }}</div>
                </label>
                @endforeach
            </div>

            <div id="triggerConfigArea" style="margin-bottom:0.5rem;">
                <div id="tcFormSource" style="display:none;" class="form-group">
                    <label class="form-label">Origen del formulario</label>
                    <select name="trigger_config[source]" class="form-select">
                        <option value="all" {{ ($automation->trigger_config['source'] ?? 'all') === 'all' ? 'selected' : '' }}>Todos los formularios</option>
                        <option value="home" {{ ($automation->trigger_config['source'] ?? '') === 'home' ? 'selected' : '' }}>Home (pagina principal)</option>
                        <option value="contact" {{ ($automation->trigger_config['source'] ?? '') === 'contact' ? 'selected' : '' }}>Contacto (/contacto)</option>
                        <option value="property" {{ ($automation->trigger_config['source'] ?? '') === 'property' ? 'selected' : '' }}>Propiedad (ficha de inmueble)</option>
                        <option value="landing" {{ ($automation->trigger_config['source'] ?? '') === 'landing' ? 'selected' : '' }}>Landing (vende tu propiedad)</option>
                        <option value="form" {{ ($automation->trigger_config['source'] ?? '') === 'form' ? 'selected' : '' }}>Formularios dinamicos</option>
                    </select>
                </div>
                <div id="tcSegment" style="display:none;" class="form-group">
                    <label class="form-label">Segmento</label>
                    <select name="trigger_config[segment_id]" class="form-select">
                        <option value="">Seleccionar...</option>
                        @foreach($segments as $seg)
                        <option value="{{ $seg->id }}" {{ ($automation->trigger_config['segment_id'] ?? '') == $seg->id ? 'selected' : '' }}>{{ $seg->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="tcScore" style="display:none;" class="form-group">
                    <label class="form-label">Score minimo</label>
                    <input type="number" name="trigger_config[min_score]" class="form-input" value="{{ $automation->trigger_config['min_score'] ?? 50 }}" min="1">
                </div>
                <div id="tcInactivity" style="display:none;" class="form-group">
                    <label class="form-label">Dias sin actividad</label>
                    <input type="number" name="trigger_config[days]" class="form-input" value="{{ $automation->trigger_config['days'] ?? 7 }}" min="1">
                </div>
            </div>

            <div class="section-label">Pasos del flujo</div>
            <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:0.75rem;">Define la secuencia de acciones. Los pasos se ejecutan en orden, respetando delays y condiciones.</p>

            <div class="step-list" id="stepList"></div>
            <button type="button" class="btn btn-sm btn-outline" onclick="addStep()">+ Agregar paso</button>

            <div class="form-actions" style="margin-top:1.5rem;">
                <a href="{{ route('admin.automations-engine.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $automation ? 'Guardar Cambios' : 'Crear Automatizacion' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
var stepTypes = @json($stepTypes);
var existingSteps = @json($automation ? $automation->steps->map(fn($s) => ['type' => $s->type, 'config' => $s->config]) : []);
var stepIdx = 0;

function showTriggerConfig(type) {
    document.querySelectorAll('#triggerConfigArea > div').forEach(function(d) { d.style.display = 'none'; });
    if (type === 'form_submitted') document.getElementById('tcFormSource').style.display = '';
    if (type === 'segment_enter' || type === 'segment_exit') document.getElementById('tcSegment').style.display = '';
    if (type === 'score_threshold') document.getElementById('tcScore').style.display = '';
    if (type === 'inactivity') document.getElementById('tcInactivity').style.display = '';
}

function addStep(data) {
    data = data || { type: 'delay', config: {} };
    var i = stepIdx++;
    var typeOpts = Object.entries(stepTypes).map(function(e) {
        return '<option value="'+e[0]+'" '+(data.type===e[0]?'selected':'')+'>'+e[1]+'</option>';
    }).join('');

    var html = '<div class="step-item" id="step-'+i+'" data-type="'+data.type+'">'
        + '<div class="step-num">'+(i+1)+'</div>'
        + '<div class="step-content">'
        + '<select name="steps['+i+'][type]" class="form-select step-type-select" onchange="updateStepConfig('+i+', this.value); this.closest(\'.step-item\').dataset.type=this.value;">'+typeOpts+'</select>'
        + '<div class="step-config" id="stepConfig-'+i+'"></div>'
        + '</div>'
        + '<button type="button" class="step-remove" onclick="removeStep('+i+')">&times;</button>'
        + '</div>';

    if (stepIdx > 1) {
        document.getElementById('stepList').insertAdjacentHTML('beforeend', '<div class="connector" id="conn-'+i+'">&#8595;</div>');
    }
    document.getElementById('stepList').insertAdjacentHTML('beforeend', html);
    updateStepConfig(i, data.type, data.config);
}

function removeStep(i) {
    var el = document.getElementById('step-' + i);
    var conn = document.getElementById('conn-' + i);
    if (el) el.remove();
    if (conn) conn.remove();
    renumberSteps();
}

function renumberSteps() {
    document.querySelectorAll('.step-item .step-num').forEach(function(n, idx) { n.textContent = idx + 1; });
}

function updateStepConfig(i, type, cfg) {
    cfg = cfg || {};
    var container = document.getElementById('stepConfig-' + i);
    var html = '';
    var prefix = 'steps['+i+'][config]';

    switch(type) {
        case 'delay':
            html = '<div style="display:flex;gap:0.5rem;"><input type="number" name="'+prefix+'[value]" class="form-input" value="'+(cfg.value||1)+'" min="1" style="width:80px;"><select name="'+prefix+'[unit]" class="form-select" style="width:120px;"><option value="hours" '+(cfg.unit==='hours'?'selected':'')+'>Horas</option><option value="days" '+(cfg.unit==='days'?'selected':'')+'>Dias</option><option value="minutes" '+(cfg.unit==='minutes'?'selected':'')+'>Minutos</option></select></div>';
            break;
        case 'send_email':
            html = '<input type="text" name="'+prefix+'[subject]" class="form-input" placeholder="Asunto del email" value="'+(cfg.subject||'')+'" style="margin-bottom:0.4rem;">'
                + '<textarea name="'+prefix+'[body]" class="form-textarea" rows="3" placeholder="Cuerpo HTML del email. Usa @{{nombre}}, @{{ciudad}}, etc.">'+(cfg.body||'')+'</textarea>';
            break;
        case 'send_whatsapp':
            html = '<textarea name="'+prefix+'[message]" class="form-textarea" rows="2" placeholder="Mensaje WhatsApp. Usa @{{nombre}}, @{{telefono}}, etc.">'+(cfg.message||'')+'</textarea>';
            break;
        case 'condition':
            html = '<div style="display:flex;gap:0.5rem;flex-wrap:wrap;"><select name="'+prefix+'[field]" class="form-select" style="flex:1;min-width:120px;"><option value="lead_temperature">Temperatura</option><option value="grade">Grado</option><option value="total_score">Score</option><option value="last_message_opened">Abrio mensaje</option><option value="last_message_replied">Respondio</option><option value="has_phone">Tiene telefono</option></select>'
                + '<select name="'+prefix+'[operator]" class="form-select" style="width:100px;"><option value="equals">Igual a</option><option value="not_equals">Diferente</option><option value="greater_than">Mayor que</option><option value="is_true">Es verdadero</option><option value="is_false">Es falso</option></select>'
                + '<input type="text" name="'+prefix+'[value]" class="form-input" placeholder="Valor" value="'+(cfg.value||'')+'" style="width:100px;"></div>';
            break;
        case 'create_task':
            html = '<input type="text" name="'+prefix+'[title]" class="form-input" placeholder="Titulo de la tarea" value="'+(cfg.title||'')+'" style="margin-bottom:0.4rem;">'
                + '<div style="display:flex;gap:0.5rem;"><select name="'+prefix+'[priority]" class="form-select"><option value="alta">Alta</option><option value="media" selected>Media</option><option value="baja">Baja</option></select>'
                + '<input type="number" name="'+prefix+'[due_days]" class="form-input" value="'+(cfg.due_days||3)+'" min="1" placeholder="Dias para vencer" style="width:100px;"></div>';
            break;
        case 'move_pipeline':
            html = '<div style="display:flex;gap:0.5rem;"><select name="'+prefix+'[operation_type]" class="form-select"><option value="captacion" '+(cfg.operation_type==='captacion'?'selected':'')+'>Captacion</option><option value="venta" '+(cfg.operation_type==='venta'?'selected':'')+'>Venta</option><option value="renta" '+(cfg.operation_type==='renta'?'selected':'')+'>Renta</option></select>'
                + '<select name="'+prefix+'[stage]" class="form-select"><option value="lead">Lead</option><option value="contacto">Contacto</option></select></div>'
                + '<p style="font-size:0.72rem;color:var(--text-muted);margin-top:0.25rem;">Crea una operacion en el pipeline existente automaticamente.</p>';
            break;
        case 'add_score':
            html = '<input type="number" name="'+prefix+'[points]" class="form-input" value="'+(cfg.points||10)+'" placeholder="Puntos" style="width:100px;">';
            break;
        case 'update_field':
            html = '<div style="display:flex;gap:0.5rem;"><select name="'+prefix+'[field]" class="form-select"><option value="lead_temperature">Temperatura</option><option value="priority">Prioridad</option></select>'
                + '<input type="text" name="'+prefix+'[value]" class="form-input" placeholder="Nuevo valor" value="'+(cfg.value||'')+'"></div>';
            break;
    }
    container.innerHTML = html;
}

// Init
var initTrigger = '{{ old('trigger_type', $automation->trigger_type ?? '') }}';
if (initTrigger) showTriggerConfig(initTrigger);

if (existingSteps.length) {
    existingSteps.forEach(function(s) { addStep(s); });
} else {
    addStep({ type: 'delay', config: { value: 1, unit: 'days' } });
    addStep({ type: 'send_email', config: {} });
}
</script>
@endsection
