@extends('layouts.app-sidebar')
@section('title', 'Editar Acuse — ' . $label)
@section('content')
<div class="page-header">
    <div>
        <h2>Acuse de recibo — {{ $label }}</h2>
        <p class="text-muted">Correo que recibe el lead al registrarse en el formulario "{{ $formType }}"</p>
    </div>
    <div style="display:flex;gap:0.5rem;">
        <a href="{{ route('admin.acuse-configs.preview', $formType) }}" target="_blank" class="btn btn-outline">Vista previa</a>
        <a href="{{ route('admin.acuse-configs.index') }}" class="btn btn-outline">← Volver</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1rem;">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 280px;gap:1.25rem;align-items:start;">

<div>
<form action="{{ route('admin.acuse-configs.update', $formType) }}" method="POST">
@csrf @method('PUT')

{{-- Identidad --}}
<div class="card" style="margin-bottom:1rem;">
<div class="card-body">
<h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin:0 0 1rem;letter-spacing:.05em;">Identidad</h4>
<div class="form-grid">
    <div class="form-group full-width">
        <label class="form-label">Asunto del correo</label>
        <input type="text" name="subject" class="form-input" value="{{ old('subject', $config->subject) }}" placeholder="Recibimos tu mensaje · Home del Valle">
        <div class="form-hint">Usa {{nombre}} para personalizar</div>
    </div>
    <div class="form-group">
        <label class="form-label">Badge / Etiqueta</label>
        <input type="text" name="badge" class="form-input" value="{{ old('badge', $config->badge) }}" placeholder="Mensaje recibido">
        <div class="form-hint">Aparece en azul sobre el título</div>
    </div>
    <div class="form-group">
        <label class="form-label">Título principal</label>
        <input type="text" name="titulo" class="form-input" value="{{ old('titulo', $config->titulo) }}" placeholder="¡Recibimos tu mensaje!">
        <div class="form-hint">Usa {{nombre}} → ", Juan"</div>
    </div>
    <div class="form-group full-width">
        <label class="form-label">Bajada / Descripción</label>
        <textarea name="bajada" class="form-textarea" rows="3">{{ old('bajada', $config->bajada) }}</textarea>
        <div class="form-hint">Soporta HTML básico. Usa {{colonia}}, {{tipo_propiedad}}, {{zonas}}, {{mascotas_texto}}</div>
    </div>
    <div class="form-group full-width">
        <label class="form-label">Nota al pie (opcional)</label>
        <input type="text" name="nota" class="form-input" value="{{ old('nota', $config->nota) }}" placeholder="Sin compromiso y sin costos...">
    </div>
</div>
</div>
</div>

{{-- CTAs --}}
<div class="card" style="margin-bottom:1rem;">
<div class="card-body">
<h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin:0 0 1rem;letter-spacing:.05em;">Botones de acción</h4>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label">Botón primario — Texto</label>
        <input type="text" name="cta1_label" class="form-input" value="{{ old('cta1_label', $config->cta1_label) }}" placeholder="Ver propiedades">
        <div class="form-hint">Usa {{colonia}} para personalizar</div>
    </div>
    <div class="form-group">
        <label class="form-label">Botón primario — Destino</label>
        <select name="cta1_type" class="form-select">
            @foreach($ctaTypes as $value => $ctaLabel)
            <option value="{{ $value }}" {{ old('cta1_type', $config->cta1_type) === $value ? 'selected' : '' }}>{{ $ctaLabel }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group full-width">
        <label class="form-label">URL fija (si destino = URL fija)</label>
        <input type="text" name="cta1_url_static" class="form-input" value="{{ old('cta1_url_static', $config->cta1_url_static) }}" placeholder="https://homedelvalle.mx/...">
    </div>
    <div class="form-group">
        <label class="form-label">Botón secundario — Texto (opcional)</label>
        <input type="text" name="cta2_label" class="form-input" value="{{ old('cta2_label', $config->cta2_label) }}" placeholder="Dejar vacío para ocultar">
    </div>
    <div class="form-group">
        <label class="form-label">Botón secundario — Destino</label>
        <select name="cta2_type" class="form-select">
            <option value="">Sin botón secundario</option>
            @foreach($ctaTypes as $value => $ctaLabel)
            <option value="{{ $value }}" {{ old('cta2_type', $config->cta2_type) === $value ? 'selected' : '' }}>{{ $ctaLabel }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group full-width">
        <label class="form-label">URL fija secundaria (si aplica)</label>
        <input type="text" name="cta2_url_static" class="form-input" value="{{ old('cta2_url_static', $config->cta2_url_static) }}" placeholder="https://homedelvalle.mx/...">
    </div>
</div>
</div>
</div>

{{-- Pasos --}}
<div class="card" style="margin-bottom:1rem;">
<div class="card-body">
<h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin:0 0 1rem;letter-spacing:.05em;">Qué sigue — 3 pasos</h4>
@foreach([1,2,3] as $n)
<div style="border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:0.75rem;">
    <p style="font-size:0.78rem;font-weight:600;color:var(--text-muted);margin:0 0 0.75rem;">Paso {{ $n }}</p>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Ícono</label>
            <select name="paso{{ $n }}_icon" class="form-select">
                @foreach($icons as $icon)
                <option value="{{ $icon }}" {{ old('paso'.$n.'_icon', $config->{'paso'.$n.'_icon'}) === $icon ? 'selected' : '' }}>{{ $icon }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Título</label>
            <input type="text" name="paso{{ $n }}_titulo" class="form-input" value="{{ old('paso'.$n.'_titulo', $config->{'paso'.$n.'_titulo'}) }}">
        </div>
        <div class="form-group full-width">
            <label class="form-label">Descripción</label>
            <input type="text" name="paso{{ $n }}_desc" class="form-input" value="{{ old('paso'.$n.'_desc', $config->{'paso'.$n.'_desc'}) }}">
        </div>
    </div>
</div>
@endforeach
</div>
</div>

<div style="display:flex;justify-content:flex-end;gap:0.75rem;">
    <a href="{{ route('admin.acuse-configs.index') }}" class="btn btn-outline">Cancelar</a>
    <button type="submit" class="btn btn-primary">Guardar cambios</button>
</div>
</form>
</div>

{{-- Sidebar --}}
<div>
<div class="card" style="margin-bottom:1rem;">
<div class="card-body">
<h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin:0 0 0.75rem;letter-spacing:.05em;">Variables disponibles</h4>
<div style="display:flex;flex-direction:column;gap:0.4rem;font-size:0.78rem;">
    @foreach(['{{nombre}}' => 'Nombre completo', '{{colonia}}' => 'Colonia (si aplica)', '{{tipo_propiedad}}' => 'Tipo de propiedad', '{{tipo_inmueble}}' => 'Tipo de inmueble', '{{zonas}}' => 'Zonas de interés', '{{presupuesto}}' => 'Presupuesto', '{{mascotas_texto}}' => 'Texto mascotas (arrendatario)'] as $var => $desc)
    <div style="display:flex;gap:0.5rem;align-items:center;">
        <code style="background:var(--bg);padding:2px 6px;border-radius:4px;font-size:0.72rem;flex-shrink:0;">{{ $var }}</code>
        <span style="color:var(--text-muted);">{{ $desc }}</span>
    </div>
    @endforeach
</div>
</div>
</div>

<div class="card">
<div class="card-body">
<h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin:0 0 0.75rem;letter-spacing:.05em;">Enviar prueba</h4>
<form action="{{ route('admin.acuse-configs.send-test', $formType) }}" method="POST">
    @csrf
    <div class="form-group">
        <input type="email" name="email" class="form-input" value="{{ auth()->user()->email }}" placeholder="Email de prueba">
    </div>
    <button type="submit" class="btn btn-outline btn-sm" style="width:100%;">Enviar correo de prueba</button>
</form>
</div>
</div>
</div>

</div>
@endsection
