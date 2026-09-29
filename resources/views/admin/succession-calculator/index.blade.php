@extends('layouts.app-sidebar')
@section('title', 'Calculadora de Sucesión')

@section('content')
<div class="page-header">
    <div>
        <h2>Calculadora de Costo de Sucesión</h2>
        <p class="text-muted">Parámetros que usa la calculadora del blog. <strong>Ninguna cifra aquí viene de un notario real todavía</strong> — son placeholders de referencia. Marca "Validado" solo cuando confirmes los rangos.</p>
    </div>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">&#8592; Blog Posts</a>
</div>

@foreach($configs as $c)
<div class="card" style="margin-bottom:1rem;">
    <div class="card-header" style="display:flex;align-items:center;gap:.6rem;">
        <h3>{{ $c->scenario === 'con_testamento' ? 'Con testamento' : 'Sin testamento' }}</h3>
        <span class="badge {{ $c->validated ? 'badge-green' : 'badge-red' }}">{{ $c->validated ? 'Validado con notario' : '⚠ PENDIENTE VALIDAR' }}</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.succession-calculator.update', $c->id) }}">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Trámite notarial/judicial — % mín.</label><input type="number" step="0.1" name="notarial_pct_min" class="form-input" value="{{ old('notarial_pct_min', $c->notarial_pct_min) }}" required></div>
                <div class="form-group"><label class="form-label">— % máx.</label><input type="number" step="0.1" name="notarial_pct_max" class="form-input" value="{{ old('notarial_pct_max', $c->notarial_pct_max) }}" required></div>
                <div class="form-group"><label class="form-label">ISAI — % mín.</label><input type="number" step="0.1" name="isai_pct_min" class="form-input" value="{{ old('isai_pct_min', $c->isai_pct_min) }}" required></div>
                <div class="form-group"><label class="form-label">— % máx.</label><input type="number" step="0.1" name="isai_pct_max" class="form-input" value="{{ old('isai_pct_max', $c->isai_pct_max) }}" required></div>
                <div class="form-group"><label class="form-label">Registro Público — $ mín.</label><input type="number" name="registro_flat_min" class="form-input" value="{{ old('registro_flat_min', $c->registro_flat_min) }}" required></div>
                <div class="form-group"><label class="form-label">— $ máx.</label><input type="number" name="registro_flat_max" class="form-input" value="{{ old('registro_flat_max', $c->registro_flat_max) }}" required></div>
                <div class="form-group"><label class="form-label">Avalúo — $ mín.</label><input type="number" name="avaluo_flat_min" class="form-input" value="{{ old('avaluo_flat_min', $c->avaluo_flat_min) }}" required></div>
                <div class="form-group"><label class="form-label">— $ máx.</label><input type="number" name="avaluo_flat_max" class="form-input" value="{{ old('avaluo_flat_max', $c->avaluo_flat_max) }}" required></div>
                <div class="form-group"><label class="form-label">Otros (edictos, gestoría) — $ mín.</label><input type="number" name="otros_flat_min" class="form-input" value="{{ old('otros_flat_min', $c->otros_flat_min) }}" required></div>
                <div class="form-group"><label class="form-label">— $ máx.</label><input type="number" name="otros_flat_max" class="form-input" value="{{ old('otros_flat_max', $c->otros_flat_max) }}" required></div>
                <div class="form-group"><label class="form-label">Por heredero adicional — $</label><input type="number" name="extra_heredero_flat" class="form-input" value="{{ old('extra_heredero_flat', $c->extra_heredero_flat) }}" required></div>
                <div></div>
                <div class="form-group"><label class="form-label">Sin escrituras del difunto — $ mín.</label><input type="number" name="sin_escrituras_extra_min" class="form-input" value="{{ old('sin_escrituras_extra_min', $c->sin_escrituras_extra_min) }}" required></div>
                <div class="form-group"><label class="form-label">— $ máx.</label><input type="number" name="sin_escrituras_extra_max" class="form-input" value="{{ old('sin_escrituras_extra_max', $c->sin_escrituras_extra_max) }}" required></div>
                <div class="form-group"><label class="form-label">Tiempo estimado — meses mín.</label><input type="number" name="tiempo_min_meses" class="form-input" value="{{ old('tiempo_min_meses', $c->tiempo_min_meses) }}" required></div>
                <div class="form-group"><label class="form-label">— meses máx.</label><input type="number" name="tiempo_max_meses" class="form-input" value="{{ old('tiempo_max_meses', $c->tiempo_max_meses) }}" required></div>
                <div class="form-group full-width">
                    <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                        <input type="checkbox" name="validated" value="1" {{ old('validated', $c->validated) ? 'checked' : '' }}>
                        Ya confirmé estos rangos con un notario
                    </label>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-textarea" rows="2">{{ old('notes', $c->notes) }}</textarea>
                </div>
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>
@endforeach
@endsection
