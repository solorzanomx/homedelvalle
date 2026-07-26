@extends('layouts.app-sidebar')
@section('title', 'Perfil técnico — ' . $property->title)

@section('styles')
<style>
.section-title {
    font-size: 0.9rem; font-weight: 600; color: var(--text);
    margin: 1.5rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);
}
.section-title:first-child { margin-top: 0; }
.zon-hint { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.3rem; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Perfil técnico de desarrollo</h2>
        <p class="text-muted">{{ $property->title }} — {{ $property->address }}</p>
    </div>
    <a href="{{ route('properties.show', $property) }}" class="btn btn-outline">Volver</a>
</div>

@if($errors->any())
<div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1.25rem;">
    @foreach($errors->all() as $error)
        <p style="color:var(--danger); font-size:0.82rem; margin:0.15rem 0;">{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="card" style="max-width:780px;">
    <div class="card-body">
        <form method="POST" action="{{ route('properties.development-profile.update', $property) }}">
            @csrf
            @method('PUT')

            <div class="section-title" style="margin-top:0;">Dimensiones del terreno</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Frente (m)</label>
                    <input type="number" name="frente" class="form-input" step="0.01" min="0" value="{{ old('frente', $profile->frente ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Fondo (m)</label>
                    <input type="number" name="fondo" class="form-input" step="0.01" min="0" value="{{ old('fondo', $profile->fondo ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Superficie total (m²)</label>
                    <input type="text" class="form-input" value="{{ $property->lot_area ?? $property->area ?? '—' }}" disabled>
                    <p class="form-hint">Se edita desde la ficha general de la propiedad.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Forma del terreno</label>
                    <select name="forma_terreno" class="form-select">
                        <option value="">-- Seleccionar --</option>
                        @foreach(\App\Models\PropertyDevelopmentProfile::FORMAS as $val => $label)
                            <option value="{{ $val }}" {{ old('forma_terreno', $profile->forma_terreno ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="section-title">Zonificación (SEDUVI)</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Uso de suelo (código)</label>
                    <input type="text" name="uso_suelo" class="form-input" value="{{ old('uso_suelo', $profile->uso_suelo ?? '') }}" placeholder="Ej. H4/20/Z">
                </div>
                <div class="form-group">
                    <label class="form-label">Zonificación de referencia</label>
                    <select name="zonificacion_key" class="form-select" id="zonificacionSelect" onchange="fillFromZonificacion()">
                        <option value="">-- Sin catálogo / manual --</option>
                        @foreach($zonificaciones as $key => $z)
                            <option value="{{ $key }}"
                                data-cos="{{ $z['cos'] }}" data-cus="{{ $z['cus'] }}" data-pisos="{{ $z['pisos'] }}"
                                {{ old('zonificacion_key', $profile->zonificacion_key ?? '') === $key ? 'selected' : '' }}>
                                {{ $z['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <p class="zon-hint">Si eliges una, precarga COS/CUS/niveles — puedes ajustarlos si tu predio es distinto.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">COS</label>
                    <input type="number" name="cos" id="cosInput" class="form-input" step="0.01" min="0" max="100" value="{{ old('cos', $profile->cos ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">CUS</label>
                    <input type="number" name="cus" id="cusInput" class="form-input" step="0.01" min="0" max="100" value="{{ old('cus', $profile->cus ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Niveles permitidos</label>
                    <input type="number" name="niveles_permitidos" id="nivelesInput" class="form-input" min="0" value="{{ old('niveles_permitidos', $profile->niveles_permitidos ?? '') }}">
                </div>
            </div>

            <div class="section-title">Situación legal y física</div>
            <div class="form-grid">
                @php
                    $libreGravamenValue = old('libre_gravamen', $profile?->libre_gravamen === null ? '' : ($profile->libre_gravamen ? '1' : '0'));
                @endphp
                <div class="form-group">
                    <label class="form-label">Libre de gravamen</label>
                    <select name="libre_gravamen" class="form-select">
                        <option value="" {{ $libreGravamenValue === '' ? 'selected' : '' }}>Sin verificar</option>
                        <option value="1" {{ $libreGravamenValue === '1' ? 'selected' : '' }}>Sí, libre de gravamen</option>
                        <option value="0" {{ $libreGravamenValue === '0' ? 'selected' : '' }}>No / tiene gravamen</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Restricciones (alineamiento, restricción al frente, servidumbres)</label>
                    <textarea name="restricciones" class="form-textarea" rows="2">{{ old('restricciones', $profile->restricciones ?? '') }}</textarea>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Colindancias</label>
                    <textarea name="colindancias" class="form-textarea" rows="2">{{ old('colindancias', $profile->colindancias ?? '') }}</textarea>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Servicios (agua, drenaje, luz)</label>
                    <textarea name="servicios" class="form-textarea" rows="2">{{ old('servicios', $profile->servicios ?? '') }}</textarea>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Situación legal</label>
                    <textarea name="situacion_legal" class="form-textarea" rows="2" placeholder="Sucesiones pendientes, escritura al corriente, etc.">{{ old('situacion_legal', $profile->situacion_legal ?? '') }}</textarea>
                </div>
            </div>

            <div class="section-title">Notas internas</div>
            <div class="form-group">
                <textarea name="notes" class="form-textarea" rows="3">{{ old('notes', $profile->notes ?? '') }}</textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('properties.show', $property) }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar perfil técnico</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function fillFromZonificacion() {
    var select = document.getElementById('zonificacionSelect');
    var opt = select.options[select.selectedIndex];
    if (!opt || !opt.dataset.cos) return;
    var cosInput = document.getElementById('cosInput');
    var cusInput = document.getElementById('cusInput');
    var nivelesInput = document.getElementById('nivelesInput');
    if (!cosInput.value) cosInput.value = opt.dataset.cos;
    if (!cusInput.value) cusInput.value = opt.dataset.cus;
    if (!nivelesInput.value) nivelesInput.value = opt.dataset.pisos;
}
</script>
@endsection
