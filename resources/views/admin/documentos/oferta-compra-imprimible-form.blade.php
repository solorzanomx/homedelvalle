@extends('layouts.app-sidebar')
@section('title', 'Versión imprimible — Carta Oferta de Compra')

@section('styles')
<style>
.flash-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; }
.flash-card label { display: block; font-size: 0.78rem; font-weight: 700; margin-bottom: 0.35rem; }
.flash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
</style>
@endsection

@section('content')
<div style="margin-bottom:1.25rem;">
    <h2 style="margin:0;font-size:1.3rem;">Versión imprimible — Carta Oferta de Compra</h2>
    <p style="margin:0.2rem 0 0;color:var(--text-muted);font-size:0.85rem;">
        Opcionalmente elige un cliente y un inmueble del CRM para prellenar sus datos. Precio, apartado, pagos y fecha siempre quedan en blanco para llenarse a mano.
    </p>
</div>

<form method="POST" action="{{ route('admin.documentos.oferta-compra.imprimible.generate') }}" target="_blank">
    @csrf

    <div class="flash-card">
        <div class="flash-grid">
            <div>
                <label for="client_id">Cliente (oferente) — opcional</label>
                <select id="client_id" name="client_id" class="form-select">
                    <option value="">— En blanco para llenar a mano —</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}">
                        {{ $client->name }} @if($client->email) ({{ $client->email }}) @endif
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="property_id">Inmueble — opcional</label>
                <select id="property_id" name="property_id" class="form-select">
                    <option value="">— En blanco para llenar a mano —</option>
                    @foreach($properties as $property)
                    <option value="{{ $property->id }}">
                        {{ $property->address ?: ($property->colony . ', ' . $property->city) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Generar PDF</button>
    <a href="{{ route('admin.documentos.index') }}" class="btn btn-outline">Cancelar</a>
</form>
@endsection
