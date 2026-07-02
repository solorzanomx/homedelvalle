@extends('layouts.app-sidebar')
@section('title', 'Oferta Flash — Carta Oferta de Compra')

@section('styles')
<style>
.flash-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; }
.flash-card label { display: block; font-size: 0.78rem; font-weight: 700; margin-bottom: 0.35rem; }
.flash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
</style>
@endsection

@section('content')
<div style="margin-bottom:1.25rem;">
    <h2 style="margin:0;font-size:1.3rem;">&#9889; Oferta Flash — Carta Oferta de Compra</h2>
    <p style="margin:0.2rem 0 0;color:var(--text-muted);font-size:0.85rem;">
        Para cuando el cliente y el inmueble ya existen en el sistema pero todavía no hay captación/pipeline abierto — elige ambos y llena la oferta, sin pasos previos.
    </p>
</div>

@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1rem;">
    <ul style="margin:0;padding-left:1rem;">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('admin.documentos.oferta-compra.flash.store') }}">
    @csrf

    <div class="flash-card">
        <div class="flash-grid">
            <div>
                <label for="client_id">Cliente (oferente) *</label>
                <select id="client_id" name="client_id" class="form-select" required>
                    <option value="">— Selecciona —</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->name }} @if($client->email) ({{ $client->email }}) @endif
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="property_id">Inmueble *</label>
                <select id="property_id" name="property_id" class="form-select" required>
                    <option value="">— Selecciona —</option>
                    @foreach($properties as $property)
                    <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                        {{ $property->address ?: ($property->colony . ', ' . $property->city) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="flash-card">
        <div class="flash-grid" style="margin-bottom:.75rem;">
            <div>
                <label for="precio_ofertado">Precio ofertado *</label>
                <input type="number" step="0.01" id="precio_ofertado" name="precio_ofertado" class="form-input" required value="{{ old('precio_ofertado') }}">
            </div>
            <div>
                <label for="monto_apartado">Apartado (deja en blanco si no aplica)</label>
                <input type="number" step="0.01" id="monto_apartado" name="monto_apartado" class="form-input" value="{{ old('monto_apartado') }}">
            </div>
            <div>
                <label for="pago_firma_contrato">Pago a firma de contrato</label>
                <input type="number" step="0.01" id="pago_firma_contrato" name="pago_firma_contrato" class="form-input" value="{{ old('pago_firma_contrato') }}">
            </div>
            <div>
                <label for="pago_firma_escritura">Pago a firma de escritura</label>
                <input type="number" step="0.01" id="pago_firma_escritura" name="pago_firma_escritura" class="form-input" value="{{ old('pago_firma_escritura') }}">
            </div>
            <div>
                <label for="forma_pago">Forma de pago</label>
                <input type="text" id="forma_pago" name="forma_pago" class="form-input" value="{{ old('forma_pago') }}" placeholder="Transferencia, crédito hipotecario...">
            </div>
            <div>
                <label for="vigencia_dias">Vigencia de la oferta (días)</label>
                <input type="number" id="vigencia_dias" name="vigencia_dias" class="form-input" value="{{ old('vigencia_dias', 8) }}" required min="8" max="90">
            </div>
        </div>
        <label for="comentarios">Comentarios adicionales</label>
        <textarea id="comentarios" name="comentarios" class="form-input" rows="2">{{ old('comentarios') }}</textarea>
    </div>

    <button type="submit" class="btn btn-primary">Generar PDF</button>
    <a href="{{ route('admin.documentos.index') }}" class="btn btn-outline">Cancelar</a>
</form>
@endsection
