@extends('layouts.app-sidebar')
@section('title', 'Planes de póliza')

@section('content')
<div style="margin-bottom:1rem;">
    <h1 style="font-size:1.25rem;font-weight:700;margin:0;">Planes de póliza jurídica</h1>
    <div style="font-size:.82rem;color:var(--text-muted);max-width:720px;">
        Los esquemas que ofrece el proveedor (hoy Previsión Legal). El inquilino que <strong>no tiene aval en CDMX</strong> ve en su Portal los planes
        <strong>activos y con precio</strong> y elige uno; el pago lo hace directo con el proveedor. Edita aquí nombres, precios y qué incluye — no hay que programar nada.
    </div>
</div>

@foreach($plans as $plan)
<form method="POST" action="{{ route('poliza-plans.update', $plan) }}" class="card" style="margin-bottom:1rem;">
    @csrf @method('PUT')
    <div class="card-body" style="padding:1rem;">
        <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.75rem;flex-wrap:wrap;">
            <strong>{{ $plan->name }}</strong>
            @if($plan->is_active && $plan->price !== null)<span class="badge badge-green">Visible en el Portal</span>
            @elseif($plan->price === null)<span class="badge badge-yellow">Falta precio</span>
            @else<span class="badge badge-blue">Oculto</span>@endif
        </div>
        <div class="form-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.75rem;">
            <div class="form-group"><label class="form-label">Nombre del plan *</label><input name="name" class="form-input" value="{{ old('name', $plan->name) }}" required></div>
            <div class="form-group"><label class="form-label">Precio (MXN)</label><input name="price" type="number" step="0.01" min="0" class="form-input" value="{{ $plan->price }}" placeholder="Ej. 6000"></div>
            <div class="form-group"><label class="form-label">Etiqueta (opcional)</label><input name="tagline" class="form-input" value="{{ $plan->tagline }}" placeholder="La más común"></div>
            <div class="form-group"><label class="form-label">Proveedor</label><input name="provider_name" class="form-input" value="{{ $plan->provider_name }}"></div>
            <div class="form-group"><label class="form-label">Orden</label><input name="sort_order" type="number" min="0" class="form-input" value="{{ $plan->sort_order }}"></div>
        </div>
        <div class="form-group" style="margin-top:.5rem;"><label class="form-label">Descripción corta (opcional)</label><input name="description" class="form-input" value="{{ $plan->description }}"></div>
        <div class="form-group" style="margin-top:.5rem;">
            <label class="form-label">Qué incluye (uno por línea)</label>
            <textarea name="inclusions_text" class="form-input" rows="5" placeholder="Investigación del inquilino&#10;Contrato de arrendamiento&#10;Asesoría jurídica…">{{ implode("\n", $plan->inclusions ?? []) }}</textarea>
        </div>
        <div style="display:flex;align-items:center;gap:1.25rem;margin-top:.75rem;flex-wrap:wrap;">
            <label style="display:flex;gap:.4rem;align-items:center;font-size:.85rem;"><input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}> Activo (visible en el Portal)</label>
            <label style="display:flex;gap:.4rem;align-items:center;font-size:.85rem;"><input type="checkbox" name="is_recommended" value="1" {{ $plan->is_recommended ? 'checked' : '' }}> Destacar como recomendado</label>
            <button class="btn btn-primary btn-sm" style="margin-left:auto;">Guardar</button>
        </div>
    </div>
</form>
<form method="POST" action="{{ route('poliza-plans.destroy', $plan) }}" onsubmit="return confirm('¿Eliminar el plan {{ $plan->name }}?')" style="margin:-.5rem 0 1.25rem;text-align:right;">
    @csrf @method('DELETE')<button class="btn btn-sm btn-outline" style="color:var(--danger);">Eliminar plan</button>
</form>
@endforeach

<form method="POST" action="{{ route('poliza-plans.store') }}" class="card">
    @csrf
    <div class="card-body" style="padding:1rem;">
        <strong style="display:block;margin-bottom:.6rem;">➕ Agregar otro plan</strong>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.75rem;">
            <div class="form-group"><label class="form-label">Nombre *</label><input name="name" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Precio (MXN)</label><input name="price" type="number" step="0.01" min="0" class="form-input"></div>
            <div class="form-group"><label class="form-label">Proveedor</label><input name="provider_name" class="form-input" value="Previsión Legal"></div>
        </div>
        <div class="form-group"><label class="form-label">Qué incluye (uno por línea)</label><textarea name="inclusions_text" class="form-input" rows="3"></textarea></div>
        <button class="btn btn-primary btn-sm">Agregar</button>
    </div>
</form>
@endsection
